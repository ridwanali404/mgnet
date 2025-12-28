<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserPin;
use App\Models\Bonus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportOmsetController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display report omset page
     */
    public function index(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $startDate = $request->start_date ?? date('Y-m-01');
        $endDate = $request->end_date ?? date('Y-m-d');
        
        // Omset Harian - data dari penjualan pin harian
        // FIX: Untuk AUTO RO (is_ro = true), gunakan ro_price (1.7 juta) bukan price penuh
        $omsetHarian = UserPin::whereDate('created_at', $date)
            ->with('pin')
            ->get()
            ->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            })
            ->map(function ($group) {
                $totalOmset = $group->sum(function ($userPin) {
                    // Jika ini AUTO RO, gunakan ro_price atau default 1.7 juta
                    if ($userPin->is_ro) {
                        $roPrice = $userPin->pin->ro_price ?? 1700000;
                        return $roPrice;
                    }
                    // Untuk pin normal, gunakan price
                    return $userPin->price;
                });
                return (object) [
                    'tanggal' => $group->first()->created_at->format('Y-m-d'),
                    'total_omset' => $totalOmset,
                    'jumlah_pin' => $group->count(),
                ];
            })
            ->first();
        
        // Omset Harian per hari dalam range
        $omsetHarianRange = UserPin::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with('pin')
            ->get()
            ->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            })
            ->map(function ($group) {
                $totalOmset = $group->sum(function ($userPin) {
                    // Jika ini AUTO RO, gunakan ro_price atau default 1.7 juta
                    if ($userPin->is_ro) {
                        $roPrice = $userPin->pin->ro_price ?? 1700000;
                        return $roPrice;
                    }
                    // Untuk pin normal, gunakan price
                    return $userPin->price;
                });
                return (object) [
                    'tanggal' => $group->first()->created_at->format('Y-m-d'),
                    'total_omset' => $totalOmset,
                    'jumlah_pin' => $group->count(),
                ];
            })
            ->sortByDesc('tanggal')
            ->values();
        
        // Total omset semua
        $totalOmsetSemua = UserPin::with('pin')->get()->sum(function ($userPin) {
            // Jika ini AUTO RO, gunakan ro_price atau default 1.7 juta
            if ($userPin->is_ro) {
                $roPrice = $userPin->pin->ro_price ?? 1700000;
                return $roPrice;
            }
            // Untuk pin normal, gunakan price
            return $userPin->price;
        });
        
        // Total Bonus Harian
        $totalBonusHarian = Bonus::whereDate('created_at', $date)
            ->select(
                DB::raw('DATE(created_at) as tanggal'),
                DB::raw('SUM(amount) as total_bonus'),
                DB::raw('COUNT(*) as jumlah_bonus')
            )
            ->groupBy('tanggal')
            ->first();
        
        // Total Bonus Harian per hari dalam range
        $totalBonusHarianRange = Bonus::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                DB::raw('DATE(created_at) as tanggal'),
                DB::raw('SUM(amount) as total_bonus'),
                DB::raw('COUNT(*) as jumlah_bonus')
            )
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'desc')
            ->get();
        
        // Total bonus semua
        $totalBonusSemua = Bonus::sum('amount');
        
        return view('report-omset.index', compact(
            'date',
            'startDate',
            'endDate',
            'omsetHarian',
            'omsetHarianRange',
            'totalOmsetSemua',
            'totalBonusHarian',
            'totalBonusHarianRange',
            'totalBonusSemua'
        ));
    }
    
    /**
     * Get omset breakdown for a specific date
     * Menampilkan breakdown HASIL PENJUALAN vs HASIL AUTO RO
     */
    public function getOmsetBreakdown(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        
        // Ambil semua user pin untuk tanggal tersebut
        $userPins = UserPin::whereDate('created_at', $date)
            ->with(['user', 'pin'])
            ->get();
        
        // Pisahkan HASIL PENJUALAN dan HASIL AUTO RO
        $hasilPenjualan = [];
        $hasilAutoRO = [];
        $totalPenjualan = 0;
        $totalAutoRO = 0;
        
        foreach ($userPins as $userPin) {
            if ($userPin->is_ro) {
                // HASIL AUTO RO
                $roPrice = $userPin->pin->ro_price ?? 1700000;
                $hasilAutoRO[] = [
                    'username' => $userPin->user->username ?? '-',
                    'pin_name' => $userPin->pin->name ?? '-',
                    'amount' => $roPrice,
                ];
                $totalAutoRO += $roPrice;
            } else {
                // HASIL PENJUALAN
                $hasilPenjualan[] = [
                    'username' => $userPin->user->username ?? '-',
                    'pin_name' => $userPin->pin->name ?? '-',
                    'amount' => $userPin->price,
                ];
                $totalPenjualan += $userPin->price;
            }
        }
        
        return response()->json([
            'date' => $date,
            'hasil_penjualan' => $hasilPenjualan,
            'hasil_auto_ro' => $hasilAutoRO,
            'total_penjualan' => $totalPenjualan,
            'total_auto_ro' => $totalAutoRO,
            'total_omset' => $totalPenjualan + $totalAutoRO,
        ]);
    }
}

