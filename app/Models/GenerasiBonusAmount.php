<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GenerasiBonusAmount extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount' => 'integer',
        'level' => 'integer',
    ];

    public function pin()
    {
        return $this->belongsTo(Pin::class);
    }

    /**
     * Ambil nominal bonus generasi untuk paket (by name) dan generasi ke-n.
     * Mengembalikan null jika tidak ada data (fallback ke kode lama bisa di Helper).
     *
     * @param string $pinName 'Gold' | 'Platinum'
     * @param int $generation 1-10
     * @return int|null
     */
    public static function getAmount(string $pinName, int $generation): ?int
    {
        $pin = Pin::where('name', $pinName)->where('type', 'premium')->first();
        if (!$pin) {
            return null;
        }
        $row = static::where('pin_id', $pin->id)->where('level', $generation)->first();
        return $row ? (int) $row->amount : null;
    }

    /**
     * Ambil semua nominal untuk satu paket (level 1-10), key = level, value = amount.
     *
     * @param string $pinName
     * @return array<int, int>
     */
    public static function getAmountsForPin(string $pinName): array
    {
        $pin = Pin::where('name', $pinName)->where('type', 'premium')->first();
        if (!$pin) {
            return [];
        }
        return static::where('pin_id', $pin->id)
            ->orderBy('level')
            ->get()
            ->pluck('amount', 'level')
            ->map(fn ($v) => (int) $v)
            ->all();
    }
}
