<?php

namespace App\Http\Controllers;

use App\Models\Pin;
use App\Models\GenerasiBonusAmount;
use Illuminate\Http\Request;

class GenerasiBonusController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Daftar paket yang punya nominal bonus generasi (Gold, Platinum).
     */
    public function index()
    {
        $pins = Pin::where('is_generasi', true)
            ->where('type', 'premium')
            ->whereIn('name', ['Gold', 'Platinum'])
            ->orderByRaw("FIELD(name, 'Gold', 'Platinum')")
            ->get();

        return view('generasi-bonus.index', compact('pins'));
    }

    /**
     * Form edit nominal per generasi (1-10) untuk satu paket.
     */
    public function edit(Pin $pin)
    {
        $this->authorizePin($pin);
        $amounts = GenerasiBonusAmount::getAmountsForPin($pin->name);
        for ($level = 1; $level <= 10; $level++) {
            if (!isset($amounts[$level])) {
                $amounts[$level] = 0;
            }
        }
        ksort($amounts);
        return view('generasi-bonus.edit', compact('pin', 'amounts'));
    }

    /**
     * Simpan nominal bonus generasi untuk satu paket.
     */
    public function update(Request $request, Pin $pin)
    {
        $this->authorizePin($pin);
        $request->validate([
            'amount_1' => 'required|integer|min:0',
            'amount_2' => 'required|integer|min:0',
            'amount_3' => 'required|integer|min:0',
            'amount_4' => 'required|integer|min:0',
            'amount_5' => 'required|integer|min:0',
            'amount_6' => 'required|integer|min:0',
            'amount_7' => 'required|integer|min:0',
            'amount_8' => 'required|integer|min:0',
            'amount_9' => 'required|integer|min:0',
            'amount_10' => 'required|integer|min:0',
        ]);

        for ($level = 1; $level <= 10; $level++) {
            $amount = (int) $request->input('amount_' . $level, 0);
            GenerasiBonusAmount::updateOrCreate(
                ['pin_id' => $pin->id, 'level' => $level],
                ['amount' => $amount]
            );
        }

        session()->flash('success', 'Nominal bonus generasi paket ' . $pin->name . ' berhasil disimpan.');
        return redirect()->route('generasi-bonus.index');
    }

    private function authorizePin(Pin $pin): void
    {
        if (!$pin->is_generasi || $pin->type !== 'premium' || !in_array($pin->name, ['Gold', 'Platinum'])) {
            abort(404);
        }
    }
}
