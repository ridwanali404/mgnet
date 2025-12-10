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
        $omsetHarian = UserPin::whereDate('created_at', $date)
            ->select(
                DB::raw('DATE(created_at) as tanggal'),
                DB::raw('SUM(price) as total_omset'),
                DB::raw('COUNT(*) as jumlah_pin')
            )
            ->groupBy('tanggal')
            ->first();
        
        // Omset Harian per hari dalam range
        $omsetHarianRange = UserPin::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                DB::raw('DATE(created_at) as tanggal'),
                DB::raw('SUM(price) as total_omset'),
                DB::raw('COUNT(*) as jumlah_pin')
            )
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'desc')
            ->get();
        
        // Total omset semua
        $totalOmsetSemua = UserPin::sum('price');
        
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
}

