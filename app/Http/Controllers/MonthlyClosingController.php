<?php

namespace App\Http\Controllers;

use App\Models\MonthlyClosing;
use Illuminate\Http\Request;
use DateTime;
use Session;
use App\Models\KeyValue;
use App\Models\User;
use App\Models\Bonus;
use App\Models\Transaction;
use App\Models\OfficialTransaction;
use Carbon\Carbon;
use App\Traits\Helper;
use App\Models\DailyPoin;

class MonthlyClosingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        $month = $request->month;
        $date = DateTime::createFromFormat('Y-m', $month);

        // check if it was closing
        if (MonthlyClosing::whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))->count()) {
            Session::flash('fail', 'Closing sudah pernah dilakukan');
            return back();
        }

        // Bonus RO/Automaintain
        // RO jika akumulasi 170 PV dalam masa aktif ATAU AUTOMAINTAIN Rp 1.700.000
        // Hasil: Tambah 45 hari masa aktif, bonus naik ke upline seperti join Gold tanpa sponsor
        
        // 1. Cek user yang sudah mencapai 170 PV dalam bulan tersebut (dalam masa aktif)
        // Hanya untuk pin: Gold, Gold Upgrade Platinum, dan Platinum
        $allUsers = User::whereHas('userPin', function ($q) {
            $q->whereHas('pin', function ($q_pin) {
                $q_pin->whereIn('name', ['Gold', 'Gold Upgrade Platinum', 'Platinum']);
        });
        })->get();
        
        $roQualifiedUsers = collect();
        foreach ($allUsers as $user) {
            // Cek apakah user masih dalam masa aktif
            if (!$user->active_until) {
                continue;
            }
            
            // Hitung total PV yang terkumpul dalam masa aktif untuk bulan tersebut
            $activeFrom = Carbon::parse($user->active_until)->subDays($user->active_days_initial ?? 45);
            $activeUntil = Carbon::parse($user->active_until);
            
            // Pastikan kita hanya menghitung PV dalam bulan yang sedang di-closing
            $monthStart = $date->format('Y-m-01');
            $monthEnd = $date->format('Y-m-t');
            $checkFrom = max($activeFrom->format('Y-m-d'), $monthStart);
            $checkUntil = min($activeUntil->format('Y-m-d'), $monthEnd);
            
            // Hitung PV dari transaksi dalam masa aktif bulan tersebut
            $transactionPoin = Transaction::where('user_id', $user->id)
            ->where('type', 'general')
            ->where('poin', '>', 0)
                ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
                ->whereBetween('created_at', [$checkFrom . ' 00:00:00', $checkUntil . ' 23:59:59'])
                ->sum('poin');
            
            // Hitung PV dari official transaction dalam masa aktif bulan tersebut
            $officialPoin = OfficialTransaction::where('user_id', $user->id)
                ->where('poin', '>', 0)
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
                ->whereBetween('created_at', [$checkFrom . ' 00:00:00', $checkUntil . ' 23:59:59'])
                ->sum('poin');
            
            // Hitung PV dari daily poin dalam masa aktif bulan tersebut
            $dailyPoinPV = $user->dailyPoins()
                ->where('pv', '>', 0)
                ->whereBetween('date', [$checkFrom, $checkUntil])
                ->sum('pv');
            
            $totalPVInActive = $transactionPoin + $officialPoin + $dailyPoinPV;
            
            // Cek apakah sudah mencapai 170 PV dalam masa aktif
            if ($totalPVInActive >= 170) {
                $roQualifiedUsers->push($user);
            }
        }
        
        // 2. Cek user yang melakukan AUTOMAINTAIN Rp 1.700.000 dalam bulan tersebut
        // Cek base pin (Gold, Gold Upgrade Platinum, Platinum) dengan is_ro = true
        $automaintainUsers = User::whereHas('userPins', function ($q) use ($date) {
            $q->whereHas('pin', function ($q_pin) {
                $q_pin->whereIn('name', ['Gold', 'Gold Upgrade Platinum', 'Platinum']);
            })
            ->where('is_ro', true)
            ->where('is_used', true)
            ->whereYear('created_at', $date->format('Y'))
            ->whereMonth('created_at', $date->format('m'));
        })->get();
        
        // Gabungkan kedua kelompok user yang qualified
        $allROUsers = $roQualifiedUsers->merge($automaintainUsers)->unique('id');
        
        // Validasi potensi bonus generasi RO yang sudah dibuat di Helper::upgrade()
        // Bonus generasi sudah dibuat sebagai potensi saat RO dibuat, sekarang divalidasi apakah qualified
        $allROUserIds = $allROUsers->pluck('id')->toArray();
        
        // Cari semua user yang punya pin RO (is_ro = true) dalam bulan tersebut
        $allROPinUsers = User::whereHas('userPins', function ($q) use ($date) {
            $q->whereHas('pin', function ($q_pin) {
                $q_pin->whereIn('name', ['Gold', 'Gold Upgrade Platinum', 'Platinum']);
            })
            ->where('is_ro', true)
            ->where('is_used', true)
            ->whereYear('created_at', $date->format('Y'))
            ->whereMonth('created_at', $date->format('m'));
        })->get();
        
        // Hapus bonus generasi untuk user yang tidak qualified (potensi tidak terpenuhi)
        foreach ($allROPinUsers as $roUser) {
            if (!in_array($roUser->id, $allROUserIds)) {
                // User tidak qualified, hapus bonus generasi RO yang sudah dibuat sebagai potensi
                Bonus::where('type', 'Bonus Generasi')
                    ->where('description', 'like', '%RO ' . $roUser->username . '%')
                    ->whereYear('created_at', $date->format('Y'))
                    ->whereMonth('created_at', $date->format('m'))
                    ->delete();
            }
        }

        // Untuk user yang qualified, bonus generasi tetap ada (potensi terpenuhi)
        // Tambah 45 hari masa aktif untuk user yang qualified
        foreach ($allROUsers as $user) {
            if ($user->active_until) {
                Helper::extendActiveStatus($user, 'ro_qualified');
            }
        }

        // create closing
        MonthlyClosing::create([
            'created_at' => $month.'-01 00:00:00',
            'updated_at' => $month.'-01 00:00:00',
        ]);

        Session::flash('success', 'Closing berhasil');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MonthlyClosing  $monthlyClosing
     * @return \Illuminate\Http\Response
     */
    public function show(MonthlyClosing $monthlyClosing)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MonthlyClosing  $monthlyClosing
     * @return \Illuminate\Http\Response
     */
    public function edit(MonthlyClosing $monthlyClosing)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MonthlyClosing  $monthlyClosing
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MonthlyClosing $monthlyClosing)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MonthlyClosing  $monthlyClosing
     * @return \Illuminate\Http\Response
     */
    public function destroy(MonthlyClosing $monthlyClosing)
    {
        //
    }

    public function poin()
    {
        ini_set('max_execution_time', '-1');
        ini_set('memory_limit', '-1');
        $month = request()->month ?? date('Y-m');
        $date = DateTime::createFromFormat('Y-m', $month);

        $transactions = Transaction::whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))
            ->where('type', 'general')
            ->where('poin', '>', 0)
            ->whereNull('user_id')
            ->whereNotNull('sponsor_id')
            ->whereIn('status', ['paid', 'packed', 'shipped', 'received'])
            ->latest();
        $userIdArray = clone $transactions;
        $users = $userIdArray->groupBy('sponsor_id')->pluck('sponsor_id')->toArray();

        $ot = OfficialTransaction::whereYear('created_at', $date->format('Y'))->whereMonth('created_at', $date->format('m'))->whereIn('status', ['paid', 'packed', 'shipped', 'received'])->latest();
        $userIdArray = clone $ot;
        $users2 = $userIdArray->groupBy('user_id')->pluck('user_id')->toArray();
        $user_ids = array_unique(array_merge($users, $users2), SORT_REGULAR);
        foreach ($user_ids as $key => $user_id) {
            $user = User::find($user_id);
            $poin = $user->monthlyPoin($month);
            if ($poin < 39) {
                // find who is upper qualified
                // Menggunakan upline_id untuk tree bonus (kecuali bonus sponsor dan generasi)
                $is_found = false;
                $upline = $user->upline;
                $i = 1;
                while ($upline) {
                    if ($upline->monthlyQualified($month)) {
                        $is_found = true;
                        $found = $upline;
                        break;
                    }
                    $upline = $upline->upline;
                }
            }
        }
    }
}
