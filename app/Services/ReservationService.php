<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractEvent;
use App\Models\CourseProgram;
use App\Models\Deadline;
use App\Models\Member;
use App\Models\Reservation;
use App\Models\Session;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    public function __construct(public IdGenerator $idGenerator) {}

    /**
     * @return array{reserve_deadline:int, cancel_deadline:int}
     */
    public function deadlines(): array
    {
        // deadline_master is expected to be a single-row config table, but keep the read deterministic
        // in case multiple rows exist.
        $deadline = Deadline::query()
            ->orderBy('reserve_deadline')
            ->orderBy('cancel_deadline')
            ->first();

        return [
            'reserve_deadline' => (int) ($deadline?->reserve_deadline ?? 0),
            'cancel_deadline' => (int) ($deadline?->cancel_deadline ?? 0),
        ];
    }

    /**
     * @return Collection<int, array{contract: Contract, reserve_payment:int, consume_amount:int, label:string}>
     */
    public function normalCandidates(Member $member, Session $session): Collection
    {
        $session->loadMissing('program');
        $program = $session->program;

        $contracts = Contract::query()
            ->where('member_id', $member->getKey())
            ->whereIn('status', [1, 2])
            ->where('plan_remain_count', '>', 0)
            ->where(function ($query) {
                $query
                    ->whereNull('plan_limit_date')
                    ->orWhere('plan_limit_date', '>=', now()->toDateString());
            })
            ->with('plan')
            ->orderBy('contract_id')
            ->get();

        $subscriptionCourseIds = $contracts
            ->map(fn (Contract $contract) => $contract->plan)
            ->filter(fn ($plan) => $plan !== null && (int) $plan->plan_type === Plan::TYPE_SUBSCRIPTION && ! empty($plan->cource_id))
            ->map(fn ($plan) => (string) $plan->cource_id)
            ->unique()
            ->values();

        $allowedSubscriptionCourseIds = $subscriptionCourseIds->isEmpty()
            ? []
            : CourseProgram::query()
                ->whereIn('cource_id', $subscriptionCourseIds)
                ->where('program_category', $program->program_category)
                ->pluck('cource_id')
                ->map(fn ($courceId) => (string) $courceId)
                ->flip()
                ->all();

        return $contracts
            ->map(function (Contract $contract) use ($allowedSubscriptionCourseIds, $program): ?array {
                $plan = $contract->plan;
                if ($plan === null || (int) $plan->status !== 1) {
                    return null;
                }

                $planType = (int) $plan->plan_type;
                $consumeAmount = 0;
                $reservePayment = 0;
                $labelPrefix = '';

                if ($planType === Plan::TYPE_SUBSCRIPTION) {
                    // Subscription: always 1, but must be within course category set.
                    if (empty($plan->cource_id)) {
                        return null;
                    }

                    if (! isset($allowedSubscriptionCourseIds[(string) $plan->cource_id])) {
                        return null;
                    }

                    $reservePayment = 5;
                    $consumeAmount = 1;
                    $labelPrefix = 'サブスク';
                } elseif ($planType === Plan::TYPE_TICKET) {
                    // Ticket
                    $consumeAmount = (int) ($program->program_ticket ?? 0);
                    if ($consumeAmount <= 0) {
                        return null;
                    }

                    $reservePayment = 3;
                    $labelPrefix = '回数券';
                } elseif ($planType === Plan::TYPE_POINT) {
                    // Point
                    $consumeAmount = (int) ($program->program_point ?? 0);
                    if ($consumeAmount <= 0) {
                        return null;
                    }

                    $reservePayment = 2;
                    $labelPrefix = 'ポイント';
                } else {
                    return null;
                }

                if ((int) $contract->plan_remain_count < $consumeAmount) {
                    return null;
                }

                $limitLabel = $contract->plan_limit_date ? $contract->plan_limit_date->toDateString() : '無期限';

                return [
                    'contract' => $contract,
                    'reserve_payment' => $reservePayment,
                    'consume_amount' => $consumeAmount,
                    'label' => sprintf(
                        '%s / %s（残:%d / 期限:%s）',
                        $labelPrefix,
                        $plan->plan_name,
                        (int) $contract->plan_remain_count,
                        $limitLabel
                    ),
                ];
            })
            ->filter()
            ->values();
    }

    public function createNormalReservation(Member $member, string $sessionId, string $contractId, string $channel = 'web'): Reservation
    {
        $deadlines = $this->deadlines();

        try {
            return DB::transaction(function () use ($channel, $contractId, $deadlines, $member, $sessionId): Reservation {
                $now = now();

                $session = Session::query()
                    ->with('program')
                    ->where('status', 1)
                    ->lockForUpdate()
                    ->findOrFail($sessionId);

                if ($deadlines['reserve_deadline'] > 0) {
                    $reserveDeadlineAt = $session->start_at->copy()->subHours($deadlines['reserve_deadline']);
                    if ($now->greaterThan($reserveDeadlineAt)) {
                        throw ValidationException::withMessages([
                            'session_id' => '予約締切を過ぎています。',
                        ]);
                    }
                }

                if ((int) $session->reserved_count >= (int) $session->capacity) {
                    throw ValidationException::withMessages([
                        'session_id' => '満席のため予約できません。',
                    ]);
                }

                $alreadyReserved = Reservation::query()
                    ->where('member_id', $member->getKey())
                    ->where('session_id', $session->getKey())
                    ->where('reserve_status', '!=', 9)
                    ->lockForUpdate()
                    ->exists();

                if ($alreadyReserved) {
                    throw ValidationException::withMessages([
                        'session_id' => 'すでにこの枠を予約しています。',
                    ]);
                }

                $contract = Contract::query()
                    ->with('plan')
                    ->where('member_id', $member->getKey())
                    ->where('contract_id', $contractId)
                    ->whereIn('status', [1, 2])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($contract->plan_limit_date !== null && $contract->plan_limit_date->lt($now->toDateString())) {
                    throw ValidationException::withMessages([
                        'contract_id' => 'この契約は期限切れです。',
                    ]);
                }

                $plan = $contract->plan;
                if ($plan === null || (int) $plan->status !== 1) {
                    throw ValidationException::withMessages([
                        'contract_id' => 'この契約は利用できません。',
                    ]);
                }

                $planType = (int) $plan->plan_type;
                $reservePayment = match ($planType) {
                    1 => 5,
                    2 => 3,
                    3 => 2,
                    default => 0,
                };

                if ($reservePayment === 0) {
                    throw ValidationException::withMessages([
                        'contract_id' => 'この契約は通常予約に利用できません。',
                    ]);
                }

                $consumeAmount = match ($planType) {
                    1 => 1,
                    2 => (int) ($session->program->program_ticket ?? 0),
                    3 => (int) ($session->program->program_point ?? 0),
                    default => 0,
                };

                if ($consumeAmount <= 0) {
                    throw ValidationException::withMessages([
                        'session_id' => 'この枠は選択した支払い方法では予約できません。',
                    ]);
                }

                if ($planType === 1) {
                    if (empty($plan->cource_id)) {
                        throw ValidationException::withMessages([
                            'contract_id' => 'このサブスク契約はコース未設定のため利用できません。',
                        ]);
                    }

                    $allowed = CourseProgram::query()
                        ->where('cource_id', $plan->cource_id)
                        ->where('program_category', $session->program->program_category)
                        ->exists();

                    if (! $allowed) {
                        throw ValidationException::withMessages([
                            'contract_id' => 'このサブスク契約では対象カテゴリの予約ができません。',
                        ]);
                    }
                }

                if ((int) $contract->plan_remain_count < $consumeAmount) {
                    throw ValidationException::withMessages([
                        'contract_id' => '残数が不足しています。',
                    ]);
                }

                // Consume rights
                $contract->plan_remain_count = (int) $contract->plan_remain_count - $consumeAmount;
                $contract->upd_time = $now;
                $contract->save();

                // Reserve seat
                $session->reserved_count = (int) $session->reserved_count + 1;
                $session->upd_time = $now;
                $session->save();

                $reservation = new Reservation;
                $reservation->reserve_id = $this->idGenerator->next('reserve', 'R', 8);
                $reservation->crt_time = $now;
                $reservation->upd_time = $now;
                $reservation->fill([
                    'member_id' => $member->getKey(),
                    'session_id' => $session->getKey(),
                    'program_id' => $session->program_id,
                    'trial_program_id' => null,
                    'contract_id' => $contract->getKey(),
                    'reserve_payment' => $reservePayment,
                    'reserve_type' => 1,
                    'channel' => $channel,
                    'reserve_status' => 1,
                    'payment_status' => Reservation::PAYMENT_STATUS_PAID,
                    'attendance_status' => 9,
                    'additional_info' => [
                        'consumption' => [
                            'amount' => $consumeAmount,
                            'plan_type' => $planType,
                            'plan_id' => $plan->getKey(),
                        ],
                    ],
                ]);
                $reservation->save();

                $this->recordContractEvent(
                    contract: $contract,
                    eventType: 'reserve.consume',
                    now: $now,
                    additional: [
                        'reserve_id' => $reservation->getKey(),
                        'session_id' => $session->getKey(),
                        'delta' => -$consumeAmount,
                    ],
                );

                return $reservation;
            });
        } catch (QueryException $e) {
            // Handle unique constraint violation gracefully (portable across DB engines).
            if ($this->isUniqueConstraintViolation($e)) {
                throw ValidationException::withMessages([
                    'session_id' => 'すでにこの枠を予約しています。',
                ]);
            }

            throw $e;
        }
    }

    public function createTrialReservation(Member $member, string $sessionId, string $channel = 'web'): Reservation
    {
        $deadlines = $this->deadlines();
        $programId = null;

        try {
            return DB::transaction(function () use (&$programId, $channel, $deadlines, $member, $sessionId): Reservation {
                $now = now();

                $session = Session::query()
                    ->with('program')
                    ->where('status', 1)
                    ->lockForUpdate()
                    ->findOrFail($sessionId);

                $programId = (string) $session->program_id;

                if ($deadlines['reserve_deadline'] > 0) {
                    $reserveDeadlineAt = $session->start_at->copy()->subHours($deadlines['reserve_deadline']);
                    if ($now->greaterThan($reserveDeadlineAt)) {
                        throw ValidationException::withMessages([
                            'session_id' => '予約締切を過ぎています。',
                        ]);
                    }
                }

                if ((int) $session->reserved_exp_count >= (int) $session->exp_capacity) {
                    throw ValidationException::withMessages([
                        'session_id' => '体験枠が満席のため予約できません。',
                    ]);
                }

                $alreadyReserved = Reservation::query()
                    ->where('member_id', $member->getKey())
                    ->where('session_id', $session->getKey())
                    ->where('reserve_status', '!=', 9)
                    ->lockForUpdate()
                    ->exists();

                if ($alreadyReserved) {
                    throw ValidationException::withMessages([
                        'session_id' => 'すでにこの枠を予約しています。',
                    ]);
                }

                $alreadyTrialed = Reservation::query()
                    ->where('member_id', $member->getKey())
                    ->where('trial_program_id', $programId)
                    ->where('reserve_type', 2)
                    ->where('reserve_status', '!=', 9)
                    ->lockForUpdate()
                    ->exists();

                if ($alreadyTrialed) {
                    throw ValidationException::withMessages([
                        'session_id' => 'このプログラムの体験はすでに予約済みです。',
                    ]);
                }

                // Reserve seat (trial)
                $session->reserved_exp_count = (int) $session->reserved_exp_count + 1;
                $session->upd_time = $now;
                $session->save();

                $reservation = new Reservation;
                $reservation->reserve_id = $this->idGenerator->next('reserve', 'R', 8);
                $reservation->crt_time = $now;
                $reservation->upd_time = $now;
                $reservation->fill([
                    'member_id' => $member->getKey(),
                    'session_id' => $session->getKey(),
                    'program_id' => $programId,
                    'trial_program_id' => $programId,
                    'contract_id' => null,
                    'reserve_payment' => 1, // cash
                    'reserve_type' => 2, // trial
                    'channel' => $channel,
                    'reserve_status' => 1,
                    'payment_status' => Reservation::PAYMENT_STATUS_PAID,
                    'attendance_status' => 9,
                ]);
                $reservation->save();

                return $reservation;
            });
        } catch (QueryException $e) {
            if ($this->isUniqueConstraintViolation($e)) {
                $alreadyReserved = Reservation::query()
                    ->where('member_id', $member->getKey())
                    ->where('session_id', $sessionId)
                    ->where('reserve_status', '!=', 9)
                    ->exists();

                if ($alreadyReserved) {
                    throw ValidationException::withMessages([
                        'session_id' => 'すでにこの枠を予約しています。',
                    ]);
                }

                if ($programId !== null) {
                    $alreadyTrialed = Reservation::query()
                        ->where('member_id', $member->getKey())
                        ->where('trial_program_id', $programId)
                        ->where('reserve_type', 2)
                        ->where('reserve_status', '!=', 9)
                        ->exists();

                    if ($alreadyTrialed) {
                        throw ValidationException::withMessages([
                            'session_id' => 'このプログラムの体験はすでに予約済みです。',
                        ]);
                    }
                }
            }

            throw $e;
        }
    }

    public function cancelReservation(Member $member, Reservation $reservation, ?string $cancelReason = null): Reservation
    {
        $deadlines = $this->deadlines();

        return DB::transaction(function () use ($cancelReason, $deadlines, $member, $reservation): Reservation {
            $now = now();

            /** @var Reservation $reservation */
            $reservation = Reservation::query()
                ->where('member_id', $member->getKey())
                ->lockForUpdate()
                ->findOrFail($reservation->getKey());

            if ((int) $reservation->reserve_status === 9) {
                return $reservation;
            }

            $session = Session::query()
                ->with('program')
                ->lockForUpdate()
                ->findOrFail($reservation->session_id);

            // Always release seat (even after deadline)
            if ((int) $reservation->reserve_type === 1) {
                $session->reserved_count = max(0, (int) $session->reserved_count - 1);
            } else {
                $session->reserved_exp_count = max(0, (int) $session->reserved_exp_count - 1);
            }
            $session->upd_time = $now;
            $session->save();

            $isBeforeCancelDeadline = true;
            if ($deadlines['cancel_deadline'] > 0) {
                $cancelDeadlineAt = $session->start_at->copy()->subHours($deadlines['cancel_deadline']);
                $isBeforeCancelDeadline = $now->lessThanOrEqualTo($cancelDeadlineAt);
            }

            if ((int) $reservation->reserve_type === 1 && $reservation->contract_id !== null && $isBeforeCancelDeadline) {
                $consumeAmount = (int) data_get($reservation->additional_info, 'consumption.amount', 0);

                if ($consumeAmount > 0) {
                    $contract = Contract::query()
                        ->with('plan')
                        ->where('member_id', $member->getKey())
                        ->where('contract_id', $reservation->contract_id)
                        ->whereIn('status', [1, 2])
                        ->lockForUpdate()
                        ->first();

                    if ($contract !== null) {
                        $isExpired = $contract->plan_limit_date !== null
                            && $contract->plan_limit_date->lt($now->toDateString());

                        if (! $isExpired) {
                            $contract->plan_remain_count = (int) $contract->plan_remain_count + $consumeAmount;
                            $contract->upd_time = $now;
                            $contract->save();

                            $this->recordContractEvent(
                                contract: $contract,
                                eventType: 'reserve.cancel_return',
                                now: $now,
                                additional: [
                                    'reserve_id' => $reservation->getKey(),
                                    'session_id' => $session->getKey(),
                                    'delta' => $consumeAmount,
                                ],
                            );
                        }
                    }
                }
            }

            $reservation->reserve_status = 9;
            $reservation->canceled_at = $now;
            $reservation->cancel_reason = $cancelReason;
            $reservation->upd_time = $now;
            $reservation->save();

            return $reservation;
        });
    }

    private function recordContractEvent(Contract $contract, string $eventType, Carbon $now, array $additional = []): void
    {
        $event = new ContractEvent;
        $event->crt_time = $now;
        $event->fill([
            'event_type' => $eventType,
            'contract_id' => $contract->getKey(),
            'start_date' => $contract->start_date,
            'end_date' => $contract->end_date,
            'plan_remain_count' => (int) $contract->plan_remain_count,
            'plan_limit_date' => $contract->plan_limit_date,
            'auto_renewal_flag' => (int) $contract->auto_renewal_flag,
            'additional_info' => $additional,
        ]);
        $event->save();
    }

    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        $sqlState = $e->errorInfo[0] ?? null;
        if ($sqlState === null) {
            return false;
        }

        // 23000: integrity constraint violation (SQLite/MySQL, etc.)
        // 23505: unique_violation (PostgreSQL)
        return in_array((string) $sqlState, ['23000', '23505'], true);
    }
}
