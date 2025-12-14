<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\Helper;
use App\Models\User;
use App\Models\GlobalProfitSharingDaily;
use Carbon\Carbon;

class AdminGlobalProfitSharingController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display GPS detail report page
     */
    public function index(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $startDate = $request->start_date ?? date('Y-m-01');
        $endDate = $request->end_date ?? date('Y-m-d');
        
        // Generate array tanggal dari startDate sampai endDate
        $dates = [];
        $currentDate = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);
        
        while ($currentDate->lte($endDateCarbon)) {
            $dateStr = $currentDate->format('Y-m-d');
            
            // Hitung omset harian
            $totalOmzet = Helper::transactionPoinDaily($dateStr) * 1000; // Convert poin ke rupiah
            
            // Persentase GPS (5%)
            $gpsPercent = 0.05;
            $totalGpsAmount = round($totalOmzet * $gpsPercent);
            
            // Hitung jumlah Platinum perdana aktif pada tanggal tersebut
            // Gunakan data GPS daily yang sudah ada untuk akurasi
            $platinumCount = GlobalProfitSharingDaily::whereDate('date', $dateStr)
                ->distinct('user_id')
                ->count('user_id');
            
            // Jika belum ada data GPS daily, hitung dari user aktif
            // Untuk akurasi, kita perlu cek apakah user sudah aktif pada tanggal tersebut
            if ($platinumCount == 0) {
                // Hitung user yang sudah aktif pada tanggal tersebut
                $platinumCount = User::whereHas('profitSharings', function ($q) {
                    $q->where('is_perdana_platinum', true);
                })
                ->whereHas('premiumUserPin', function ($q) {
                    $q->whereHas('pin', function ($qPin) {
                        $qPin->where('name', 'Platinum')->where('type', 'premium');
                    });
                })
                ->where('is_active', true)
                ->where(function ($q) use ($dateStr) {
                    // User harus sudah aktif pada tanggal tersebut
                    $q->whereNull('active_until')
                       ->orWhere('active_until', '>=', $dateStr);
                })
                ->count();
            }
            
            // Hitung GPS per member
            $gpsAmountPerMember = $platinumCount > 0 ? round($totalGpsAmount / $platinumCount) : 0;
            
            // Total GPS yang dibagikan
            $totalGpsDistributed = GlobalProfitSharingDaily::whereDate('date', $dateStr)
                ->sum('amount');
            
            // Jika belum ada data, hitung dari jumlah platinum * GPS per member
            if ($totalGpsDistributed == 0 && $platinumCount > 0) {
                $totalGpsDistributed = $platinumCount * $gpsAmountPerMember;
            }
            
            $dates[] = [
                'date' => $dateStr,
                'date_formatted' => $currentDate->translatedFormat('d F Y'),
                'total_omzet' => $totalOmzet,
                'gps_percent' => $gpsPercent,
                'total_gps_amount' => $totalGpsAmount,
                'platinum_count' => $platinumCount,
                'gps_amount_per_member' => $gpsAmountPerMember,
                'total_gps_distributed' => $totalGpsDistributed,
            ];
            
            $currentDate->addDay();
        }
        
        return view('admin-global-profit-sharing.index', compact(
            'date',
            'startDate',
            'endDate',
            'dates'
        ));
    }
    
    /**
     * Get GPS detail for a specific date
     */
    public function detail(Request $request)
    {
        $date = $request->date;
        $month = $request->month;
        
        if (!$date) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal tidak valid'
            ], 400);
        }
        
        // Ambil data GPS daily untuk tanggal tersebut
        $gpsDailies = GlobalProfitSharingDaily::whereDate('date', $date)
            ->with('user:id,username,name')
            ->orderBy('amount', 'desc')
            ->get();
        
        // Format data untuk response
        $data = $gpsDailies->map(function($daily) {
            return [
                'username' => $daily->user->username,
                'name' => $daily->user->name,
                'amount' => $daily->amount,
                'amount_formatted' => number_format($daily->amount, 0, ',', '.'),
                'profile_url' => url('user/' . $daily->user->id . '/profile')
            ];
        });
        
        $total = $gpsDailies->sum('amount');
        
        return response()->json([
            'success' => true,
            'date' => $date,
            'date_formatted' => Carbon::parse($date)->translatedFormat('d F Y'),
            'data' => $data,
            'total' => $total,
            'total_formatted' => number_format($total, 0, ',', '.')
        ]);
    }
}
