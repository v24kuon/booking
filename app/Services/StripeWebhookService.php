<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractEvent;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StripeWebhookService
{
    public function __construct(public IdGenerator $idGenerator) {}

    /**
     * @param  array<string, mixed>  $event
     */
    public function handle(array $event): void
    {
        $eventId = $event['id'] ?? null;
        $eventType = $event['type'] ?? null;

        if (! is_string($eventId) || $eventId === '' || ! is_string($eventType) || $eventType === '') {
            return;
        }

        DB::transaction(function () use ($event, $eventId, $eventType): void {
            $now = now();

            try {
                $payloadJson = json_encode($event, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                $payloadJson = null;
            }

            $inserted = DB::table('stripe_webhook_events')->insertOrIgnore([
                'event_id' => $eventId,
                'event_type' => $eventType,
                'payload' => $payloadJson,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($inserted === 0) {
                return;
            }

            match ($eventType) {
                'customer.subscription.created' => $this->handleSubscriptionCreated($event, $now),
                'invoice.payment_succeeded' => $this->handleInvoicePaymentSucceeded($event, $now),
                'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event, $now),
                'checkout.session.completed' => $this->handleCheckoutSessionCompleted($event, $now),
                default => null,
            };
        });
    }

    /**
     * @param  array<string, mixed>  $event
     */
    private function handleSubscriptionCreated(array $event, Carbon $now): void
    {
        $subscription = $event['data']['object'] ?? null;
        if (! is_array($subscription)) {
            return;
        }

        $subscriptionId = $subscription['id'] ?? null;
        $customerId = $subscription['customer'] ?? null;
        $priceId = $subscription['items']['data'][0]['price']['id'] ?? null;

        if (! is_string($subscriptionId) || $subscriptionId === '' || ! is_string($priceId) || $priceId === '') {
            Log::warning('Stripe webhook: subscription.created missing required fields.', [
                'subscription_id' => $subscriptionId,
                'price_id' => $priceId,
            ]);

            return;
        }

        $memberId = $this->resolveMemberId($subscription);
        if ($memberId === null) {
            Log::warning('Stripe webhook: subscription.created could not resolve member.', [
                'subscription_id' => $subscriptionId,
            ]);

            return;
        }

        $plan = $this->resolvePlanByStripePriceId($priceId);
        if ($plan === null) {
            Log::warning('Stripe webhook: subscription.created could not resolve plan by price id.', [
                'subscription_id' => $subscriptionId,
                'price_id' => $priceId,
            ]);

            return;
        }

        $contract = Contract::query()
            ->where('additional_info->stripe_subscription_id', $subscriptionId)
            ->lockForUpdate()
            ->first();

        $additional = is_array($contract?->additional_info) ? $contract->additional_info : [];
        $additional['stripe_subscription_id'] = $subscriptionId;
        $additional['stripe_price_id'] = $priceId;

        if (is_string($customerId) && $customerId !== '') {
            $additional['stripe_customer_id'] = $customerId;
        }

        if ($contract === null) {
            $contract = new Contract;
            $contract->contract_id = $this->idGenerator->next('contract', 'CT', 8);
            $contract->crt_time = $now;
            $contract->plan_remain_count = 0;
        }

        $contract->upd_time = $now;
        $contract->fill([
            'member_id' => $memberId,
            'plan_id' => $plan->getKey(),
            'start_date' => $contract->start_date ?? $now->toDateString(),
            'end_date' => null,
            'plan_limit_date' => null,
            'auto_renewal_flag' => 1,
            'status' => 1,
            'additional_info' => $additional,
        ]);
        $contract->save();

        $this->recordContractEvent(
            contract: $contract,
            eventType: 'stripe.customer.subscription.created',
            now: $now,
            additional: [
                'stripe' => [
                    'subscription_id' => $subscriptionId,
                    'customer_id' => $customerId,
                    'price_id' => $priceId,
                ],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $event
     */
    private function handleInvoicePaymentSucceeded(array $event, Carbon $now): void
    {
        $invoice = $event['data']['object'] ?? null;
        if (! is_array($invoice)) {
            return;
        }

        $subscriptionId = $invoice['subscription'] ?? null;
        $priceId = $invoice['lines']['data'][0]['price']['id'] ?? null;

        if (! is_string($subscriptionId) || $subscriptionId === '' || ! is_string($priceId) || $priceId === '') {
            Log::warning('Stripe webhook: invoice.payment_succeeded missing required fields.', [
                'subscription_id' => $subscriptionId,
                'price_id' => $priceId,
            ]);

            return;
        }

        $plan = $this->resolvePlanByStripePriceId($priceId);
        if ($plan === null) {
            Log::warning('Stripe webhook: invoice.payment_succeeded could not resolve plan by price id.', [
                'subscription_id' => $subscriptionId,
                'price_id' => $priceId,
            ]);

            return;
        }

        $contract = Contract::query()
            ->where('additional_info->stripe_subscription_id', $subscriptionId)
            ->lockForUpdate()
            ->first();

        if ($contract === null) {
            $memberId = $this->resolveMemberId($invoice);
            if ($memberId === null) {
                Log::warning('Stripe webhook: invoice.payment_succeeded could not resolve member.', [
                    'subscription_id' => $subscriptionId,
                ]);

                return;
            }

            $contract = new Contract;
            $contract->contract_id = $this->idGenerator->next('contract', 'CT', 8);
            $contract->crt_time = $now;
            $contract->plan_remain_count = 0;
            $contract->fill([
                'member_id' => $memberId,
                'plan_id' => $plan->getKey(),
                'start_date' => $now->toDateString(),
                'end_date' => null,
                'plan_limit_date' => null,
                'auto_renewal_flag' => 1,
                'status' => 1,
                'additional_info' => [
                    'stripe_subscription_id' => $subscriptionId,
                    'stripe_price_id' => $priceId,
                ],
            ]);
        }

        $contract->plan_id = $plan->getKey();
        $contract->plan_remain_count = (int) $plan->plan_usage_count;
        $contract->upd_time = $now;
        $contract->auto_renewal_flag = 1;
        $contract->status = 1;

        $additional = is_array($contract->additional_info) ? $contract->additional_info : [];
        $additional['stripe_subscription_id'] = $subscriptionId;
        $additional['stripe_price_id'] = $priceId;
        $contract->additional_info = $additional;

        $contract->save();

        $this->recordContractEvent(
            contract: $contract,
            eventType: 'stripe.invoice.payment_succeeded',
            now: $now,
            additional: [
                'stripe' => [
                    'subscription_id' => $subscriptionId,
                    'price_id' => $priceId,
                    'invoice_id' => $invoice['id'] ?? null,
                ],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $event
     */
    private function handleSubscriptionDeleted(array $event, Carbon $now): void
    {
        $subscription = $event['data']['object'] ?? null;
        if (! is_array($subscription)) {
            return;
        }

        $subscriptionId = $subscription['id'] ?? null;
        if (! is_string($subscriptionId) || $subscriptionId === '') {
            return;
        }

        $contract = Contract::query()
            ->where('additional_info->stripe_subscription_id', $subscriptionId)
            ->lockForUpdate()
            ->first();

        if ($contract === null) {
            Log::warning('Stripe webhook: subscription.deleted contract not found.', [
                'subscription_id' => $subscriptionId,
            ]);

            return;
        }

        $contract->end_date = $now->toDateString();
        $contract->auto_renewal_flag = 9;
        $contract->status = 9;
        $contract->upd_time = $now;
        $contract->save();

        $this->recordContractEvent(
            contract: $contract,
            eventType: 'stripe.customer.subscription.deleted',
            now: $now,
            additional: [
                'stripe' => [
                    'subscription_id' => $subscriptionId,
                ],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $event
     */
    private function handleCheckoutSessionCompleted(array $event, Carbon $now): void
    {
        $session = $event['data']['object'] ?? null;
        if (! is_array($session)) {
            return;
        }

        $mode = $session['mode'] ?? null;
        if ($mode !== 'payment') {
            return;
        }

        $metadata = $session['metadata'] ?? [];
        $metadata = is_array($metadata) ? $metadata : [];

        $reserveId = $metadata['reserve_id'] ?? $metadata['reservation_id'] ?? null;
        if (is_string($reserveId) && $reserveId !== '') {
            $this->handleTrialCardPaid($reserveId, $session, $now);

            return;
        }

        $memberId = $this->resolveMemberId($session);
        if ($memberId === null) {
            Log::warning('Stripe webhook: checkout.session.completed could not resolve member.', [
                'checkout_session_id' => $session['id'] ?? null,
            ]);

            return;
        }

        $planId = $metadata['plan_id'] ?? null;
        $stripePriceId = $metadata['stripe_price_id'] ?? $metadata['price_id'] ?? null;

        $plan = null;
        if (is_string($planId) && $planId !== '') {
            $plan = Plan::query()->find($planId);
        } elseif (is_string($stripePriceId) && $stripePriceId !== '') {
            $plan = $this->resolvePlanByStripePriceId($stripePriceId);
        }

        if ($plan === null) {
            Log::warning('Stripe webhook: checkout.session.completed could not resolve plan.', [
                'checkout_session_id' => $session['id'] ?? null,
                'plan_id' => $planId,
                'stripe_price_id' => $stripePriceId,
            ]);

            return;
        }

        $planType = (int) $plan->plan_type;
        if (! in_array($planType, [2, 3], true)) {
            Log::warning('Stripe webhook: checkout.session.completed unsupported plan_type for top-up.', [
                'checkout_session_id' => $session['id'] ?? null,
                'plan_id' => $plan->getKey(),
                'plan_type' => $planType,
            ]);

            return;
        }

        $limitDate = $this->calculatePlanLimitDate($plan, $now);
        $grantAmount = (int) $plan->plan_usage_count;

        $contract = Contract::query()
            ->where('member_id', $memberId)
            ->where('plan_id', $plan->getKey())
            ->whereIn('status', [1, 2])
            ->where(function ($query) use ($now) {
                $query
                    ->whereNull('plan_limit_date')
                    ->orWhere('plan_limit_date', '>=', $now->toDateString());
            })
            ->orderByDesc('plan_limit_date')
            ->lockForUpdate()
            ->first();

        if ($contract === null) {
            $contract = new Contract;
            $contract->contract_id = $this->idGenerator->next('contract', 'CT', 8);
            $contract->crt_time = $now;
            $contract->plan_remain_count = 0;
            $contract->fill([
                'member_id' => $memberId,
                'plan_id' => $plan->getKey(),
                'start_date' => $now->toDateString(),
                'end_date' => null,
                'plan_limit_date' => $limitDate?->toDateString(),
                'auto_renewal_flag' => 9,
                'status' => 1,
                'additional_info' => [],
            ]);
        }

        $contract->plan_remain_count = (int) $contract->plan_remain_count + $grantAmount;
        $contract->plan_limit_date = $limitDate?->toDateString();
        $contract->upd_time = $now;
        $contract->status = 1;

        $additional = is_array($contract->additional_info) ? $contract->additional_info : [];
        $additional['stripe_checkout_session_id'] = $session['id'] ?? null;
        $additional['stripe_price_id'] = $stripePriceId;
        $contract->additional_info = $additional;

        $contract->save();

        $this->recordContractEvent(
            contract: $contract,
            eventType: 'stripe.checkout.session.completed',
            now: $now,
            additional: [
                'stripe' => [
                    'checkout_session_id' => $session['id'] ?? null,
                    'price_id' => $stripePriceId,
                ],
                'grant' => [
                    'amount' => $grantAmount,
                    'plan_type' => $planType,
                    'limit_date' => $limitDate?->toDateString(),
                ],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $session
     */
    private function handleTrialCardPaid(string $reserveId, array $session, Carbon $now): void
    {
        $reservation = Reservation::query()
            ->where('reserve_id', $reserveId)
            ->lockForUpdate()
            ->first();

        if ($reservation === null) {
            Log::warning('Stripe webhook: checkout.session.completed reservation not found.', [
                'reserve_id' => $reserveId,
                'checkout_session_id' => $session['id'] ?? null,
            ]);

            return;
        }

        $additional = is_array($reservation->additional_info) ? $reservation->additional_info : [];
        $additional['stripe'] = array_merge(
            is_array($additional['stripe'] ?? null) ? $additional['stripe'] : [],
            [
                'checkout_session_id' => $session['id'] ?? null,
                'payment_intent' => $session['payment_intent'] ?? null,
            ]
        );

        $reservation->payment_status = 1;
        $reservation->additional_info = $additional;
        $reservation->upd_time = $now;
        $reservation->save();
    }

    private function resolvePlanByStripePriceId(string $stripePriceId): ?Plan
    {
        return Plan::query()
            ->where('additional_info->stripe_price_id', $stripePriceId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $object
     */
    private function resolveMemberId(array $object): ?string
    {
        $metadata = $object['metadata'] ?? [];
        $metadata = is_array($metadata) ? $metadata : [];

        $memberId = $metadata['member_id'] ?? null;
        if (is_string($memberId) && $memberId !== '') {
            return $memberId;
        }

        $email = $object['customer_email'] ?? null;
        if (! is_string($email) || $email === '') {
            $customerDetails = $object['customer_details'] ?? null;
            if (is_array($customerDetails)) {
                $email = $customerDetails['email'] ?? null;
            }
        }

        if (! is_string($email) || $email === '') {
            return null;
        }

        $member = Member::query()
            ->where('member_mail', $email)
            ->first();

        return $member?->getKey();
    }

    private function calculatePlanLimitDate(Plan $plan, Carbon $now): ?Carbon
    {
        $days = (int) $plan->plan_usage_date;
        if ($days <= 0) {
            return null;
        }

        return $now->copy()->addDays($days)->startOfDay();
    }

    /**
     * @param  array<string, mixed>  $additional
     */
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
}
