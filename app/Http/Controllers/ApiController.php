<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function monoleg()
    {
        $user = User::find(request()->id);
        if (!$user) {
            return [];
        }
        
        $tree = [];
        $pinName = '-';
        
        // Cek apakah user punya monolegUserPin (pin BSM) atau premiumUserPin (Gold/Platinum)
        if ($user->monolegUserPin) {
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
        } elseif ($user->premiumUserPin) {
            // Sistem monoleg Gold/Platinum (baru) - tampilkan leg kanan
            $pinName = $user->premiumUserPin->pin->name_short ?? '-';
            $tree[] = [
                [
                    'v' => (string) $user->id,
                    'f' => $user->username . '<div>' . $pinName . '</div>',
                ],
                '',
                ''
            ];
            
            // Ambil semua sponsors (kiri dan kanan)
            $allSponsors = $user->sponsors()->whereHas('premiumUserPin')->orderBy('created_at', 'asc')->get();
            
            // Pisahkan kiri dan kanan
            $leftSponsors = $allSponsors->where('placement_side', 'left');
            $rightSponsors = $allSponsors->where('placement_side', 'right');
            
            // Tampilkan sponsor pertama di kiri (jika ada)
            if ($leftSponsors->count() > 0) {
                $firstLeft = $leftSponsors->first();
                $tree[] = [
                    [
                        'v' => (string) $firstLeft->id,
                        'f' => $firstLeft->username . '<div>' . ($firstLeft->premiumUserPin ? $firstLeft->premiumUserPin->pin->name_short : '-') . '<div style="color:red;">Leg Kiri</div>'
                    ],
                    (string) $user->id,
                    ''
                ];
            }
            
            // Tampilkan semua di leg kanan (monoleg)
            foreach ($rightSponsors as $key => $rightSponsor) {
                $tree[] = [
                    [
                        'v' => (string) $rightSponsor->id,
                        'f' => $rightSponsor->username . '<div>' . ($rightSponsor->premiumUserPin ? $rightSponsor->premiumUserPin->pin->name_short : '-') . '<div style="color:green;">Leg Kanan ' . ($key + 1) . '</div>'
                    ],
                    (string) $user->id,
                    ''
                ];
                
                // Recursive untuk downline di leg kanan
                $this->addMonolegDownline($rightSponsor, $user->id, $tree);
            }
        } else {
            // User tidak punya pin premium atau BSM
            return [];
        }
        
        return $tree;
    }
    
    /**
     * Tambahkan downline di leg kanan secara recursive
     */
    private function addMonolegDownline($user, $rootId, &$tree)
    {
        $downlines = $user->sponsors()
            ->whereHas('premiumUserPin')
            ->where('placement_side', 'right')
            ->orderBy('created_at', 'asc')
            ->get();
        
        foreach ($downlines as $downline) {
            $tree[] = [
                [
                    'v' => (string) $downline->id,
                    'f' => $downline->username . '<div>' . ($downline->premiumUserPin ? $downline->premiumUserPin->pin->name_short : '-') . '<div style="color:green;">Leg Kanan</div>'
                ],
                (string) $downline->sponsor_id,
                ''
            ];
            
            // Recursive untuk downline berikutnya
            $this->addMonolegDownline($downline, $rootId, $tree);
        }
    }
}