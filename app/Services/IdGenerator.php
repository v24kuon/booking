<?php

namespace App\Services;

use App\Models\IdSequence;
use Illuminate\Support\Facades\DB;

class IdGenerator
{
    public function next(string $sequenceKey, string $prefix, int $digits): string
    {
        return DB::transaction(function () use ($sequenceKey, $prefix, $digits): string {
            $sequence = IdSequence::query()->lockForUpdate()->find($sequenceKey);

            if ($sequence === null) {
                $sequence = IdSequence::create([
                    'key' => $sequenceKey,
                    'next_number' => 1,
                ]);
            }

            $number = (int) $sequence->next_number;
            $sequence->next_number = $number + 1;
            $sequence->save();

            return $prefix.str_pad((string) $number, $digits, '0', STR_PAD_LEFT);
        });
    }
}
