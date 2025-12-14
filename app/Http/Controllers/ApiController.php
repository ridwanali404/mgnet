<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
    public function monoleg()
    {
        $user = User::find(request()->id);
        if (!$user) {
            return [];
        }
        
        // Jangan tampilkan monoleg untuk user admin
        if ($user->type == 'admin') {
            return [];
        }
        
        $tree = [];
        $pinName = '-';
        
        // Cek apakah user punya premiumUserPin (Gold/Platinum) atau monolegUserPin (pin BSM)
        // Prioritaskan premiumUserPin jika user memiliki keduanya
        if ($user->premiumUserPin) {
            // Sistem monoleg Gold/Platinum (baru) - tampilkan leg kiri dan jalur monoleg
            // Menggunakan uplines (berdasarkan upline_id) bukan sponsors (berdasarkan sponsor_id)
            $pinName = $user->premiumUserPin->pin->name_short ?? '-';
            $tree[] = [
                [
                    'v' => (string) $user->id,
                    'f' => $user->username . '<div>' . $pinName . '</div>',
                ],
                '',
                ''
            ];
            
            // Ambil semua uplines berdasarkan urutan created_at (downline berdasarkan upline_id)
            $allUplines = $user->uplines()->whereHas('premiumUserPin')->orderBy('created_at', 'asc')->get();
            
            if ($allUplines->count() > 0) {
                // Upline pertama = leg kiri
                $firstUpline = $allUplines->first();
                $tree[] = [
                    [
                        'v' => (string) $firstUpline->id,
                        'f' => $firstUpline->username . '<div>' . ($firstUpline->premiumUserPin ? $firstUpline->premiumUserPin->pin->name_short : '-') . '<div style="color:red;">Leg Kiri</div>'
                    ],
                    (string) $user->id,
                    ''
                ];
                
                // Recursive untuk Leg Kiri (tampilkan semua downline sampai 10 level)
                // Untuk Leg Kiri, kita tidak perlu legNumber karena ini adalah leg kiri utama
                $this->addMonolegDownlineForLeftLeg($firstUpline, $user->id, $tree, 0, 10);
                
                // Upline kedua dan seterusnya = Leg 1, Leg 2, Leg 3, dst (jalur monoleg)
                $monolegUplines = $allUplines->skip(1)->values();
                foreach ($monolegUplines as $key => $monolegUpline) {
                    $legNumber = $key + 1;
                    $tree[] = [
                        [
                            'v' => (string) $monolegUpline->id,
                            'f' => $monolegUpline->username . '<div>' . ($monolegUpline->premiumUserPin ? $monolegUpline->premiumUserPin->pin->name_short : '-') . '<div style="color:green;">Leg ' . $legNumber . '</div>'
                        ],
                        (string) $user->id,
                        ''
                    ];
                    
                    // Recursive untuk downline di jalur monoleg (level dimulai dari 2)
                    $this->addMonolegDownline($monolegUpline, $user->id, $tree, (string)$legNumber, 2, 10);
                }
            }
        } elseif ($user->monolegUserPin) {
            // Sistem monoleg BSM (lama)
            $pinName = $user->monolegUserPin->pin->name_short ?? '-';
            $tree[] = [
                [
                    'v' => (string) $user->id,
                    'f' => $user->username . '<div>' . $pinName . '</div>',
                ],
                '',
                ''
            ];
            $sponsors = $user->monolegSponsors()->with('monolegUserPin')->get();
            $sponsors = $sponsors->sortBy('monolegUserPin.updated_at')->values();
            foreach ($sponsors as $key => $a) {
                $tree[] = [
                    [
                        'v' => (string) $a->id,
                        'f' => $a->username . '<div>' . ($a->monolegUserPin ? $a->monolegUserPin->pin->name_short : '-') . '<div style="color:' . ($key == 0 ? 'red' : 'green') . ';">Leg ' . ($key + 1) . '</div>'
                    ],
                    (string) $user->id,
                    ''
                ];
                if ($key != 0) {
                    $aDownline = $a->monolegSponsors()->where('monoleg_id', $user->id)->first();
                    while ($aDownline) {
                        $tree[] = [
                            [
                                'v' => (string) $aDownline->id,
                                'f' => $aDownline->username . '<div>' . ($aDownline->monolegUserPin ? $aDownline->monolegUserPin->pin->name_short : '-') . '<div style="color:green">Leg 1</div>'
                            ],
                            (string) $aDownline->sponsor_id,
                            ''
                        ];
                        $aDownline = $aDownline->monolegSponsors()->where('monoleg_id', $user->id)->first();
                    }
                }
            }
        } else {
            // User tidak punya pin premium atau BSM
            return [];
        }
        
        return $tree;
    }
    
    /**
     * Tambahkan downline di jalur monoleg secara recursive
     * Menggunakan uplines (berdasarkan upline_id) bukan sponsors (berdasarkan sponsor_id)
     * Hanya tampilkan downline pertama (Leg Kiri/lanjutan monoleg) tanpa leg baru
     * Recursive sampai 10 level ke bawah
     * Level dimulai dari 2 (karena level 1 adalah Leg 1, Leg 2, dst)
     */
    private function addMonolegDownline($user, $rootId, &$tree, $legNumber = '1', $level = 2, $maxLevel = 10)
    {
        // Batasi sampai 10 level
        if ($level > $maxLevel) {
            return;
        }
        
        // Gunakan uplines (berdasarkan upline_id) bukan sponsors
        $downlines = $user->uplines()
            ->whereHas('premiumUserPin')
            ->orderBy('created_at', 'asc')
            ->get();
        
        if ($downlines->count() > 0) {
            // Hanya tampilkan downline pertama = Leg Kiri (lanjutan monoleg ke bawah)
            // Tidak tampilkan downline kedua dan seterusnya
            $firstDownline = $downlines->first();
            $tree[] = [
                [
                    'v' => (string) $firstDownline->id,
                    'f' => $firstDownline->username . '<div>' . ($firstDownline->premiumUserPin ? $firstDownline->premiumUserPin->pin->name_short : '-') . '<div style="color:green;">Monoleg Level ' . $level . '</div>'
                ],
                (string) $firstDownline->upline_id,
                ''
            ];
            
            // Recursive untuk lanjutan monoleg ke bawah dengan level + 1
            $this->addMonolegDownline($firstDownline, $rootId, $tree, $legNumber, $level + 1, $maxLevel);
        }
    }
    
    /**
     * Tambahkan downline untuk Leg Kiri secara recursive
     * Leg Kiri tidak menampilkan downline apapun (hanya tampil sendiri)
     */
    private function addMonolegDownlineForLeftLeg($user, $rootId, &$tree, $level = 0, $maxLevel = 10)
    {
        // Leg Kiri tidak menampilkan downline apapun
        return;
    }

    /**
     * API untuk mendapatkan leg omset powerplus untuk member tertentu
     */
    public function powerplusLegOmzet(Request $request)
    {
        $userId = $request->input('user_id');
        $month = $request->input('month', date('Y-m'));
        
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }
        
        // Cari qualification untuk bulan tersebut
        $startDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->endOfMonth();
        
        $qualification = $user->powerPlusQualifications()
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('date', 'desc')
            ->first();
        
        // Ambil semua uplines untuk menentukan jumlah leg
        // Semua leg dihitung untuk Power Plus (tidak ada Leg Kiri yang dikecualikan)
        $allUplines = $user->uplines()
            ->whereHas('premiumUserPin')
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Buat array leg lengkap berdasarkan jumlah upline dan mapping username
        $completeLegOmzets = [];
        $legUsernames = []; // Mapping leg name ke username
        foreach ($allUplines as $index => $upline) {
            $legName = 'Leg ' . ($index + 1);
            $completeLegOmzets[$legName] = 0; // Default 0
            $legUsernames[$legName] = $upline->username;
        }
        
        // Jika ada qualification, gunakan data dari qualification
        if ($qualification && $qualification->leg_omzets) {
            // Pastikan leg_omzets selalu array
            $legOmzetsData = $qualification->leg_omzets;
            // Jika masih string (JSON), decode dulu
            if (is_string($legOmzetsData)) {
                $legOmzetsData = json_decode($legOmzetsData, true) ?: [];
            }
            // Pastikan adalah array sebelum foreach
            if (is_array($legOmzetsData)) {
                foreach ($legOmzetsData as $legName => $omzet) {
                    $completeLegOmzets[$legName] = $omzet;
                }
            }
        } else {
            // Jika belum ada data, hitung langsung
            $legOmzets = \App\Traits\Helper::calculateAllLegOmzetMonthly($user, $month);
            if (is_array($legOmzets)) {
                foreach ($legOmzets as $legName => $omzet) {
                    $completeLegOmzets[$legName] = $omzet;
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'leg_omzets' => $completeLegOmzets,
            'leg_usernames' => $legUsernames
        ]);
    }

    /**
     * API untuk mendapatkan detail breakdown omset leg
     * Hanya bisa diakses oleh admin
     */
    public function powerplusLegOmzetBreakdown(Request $request)
    {
        $userId = $request->input('user_id');
        $legNumber = $request->input('leg_number'); // Leg 1, Leg 2, dst
        $month = $request->input('month', date('Y-m'));
        
        $user = User::find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }
        
        // Ambil semua uplines untuk menentukan leg
        $allUplines = $user->uplines()
            ->whereHas('premiumUserPin')
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Parse leg number (Leg 1 -> index 0, Leg 2 -> index 1, dst)
        $legIndex = intval(str_replace('Leg ', '', $legNumber)) - 1;
        
        if (!isset($allUplines[$legIndex])) {
            return response()->json([
                'success' => false,
                'message' => 'Leg tidak ditemukan'
            ], 404);
        }
        
        $legUser = $allUplines[$legIndex];
        
        // Dapatkan breakdown detail
        $breakdown = \App\Traits\Helper::getLegOmzetBreakdown($legUser, $month);
        
        return response()->json([
            'success' => true,
            'leg_name' => $legNumber,
            'leg_username' => $legUser->username,
            'breakdown' => $breakdown
        ]);
    }
}