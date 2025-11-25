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
        if (!$user->monolegUserPin) {
            return [];
        }
        $tree = [];
        $tree[] = [
            [
                'v' => (string) $user->id,
                'f' => $user->username . '<div>' . $user->monolegUserPin->pin->name_short . '</div>',
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
                    'f' => $a->username . '<div>' . $a->monolegUserPin->pin->name_short . '<div style="color:' . ($key == 0 ? 'red' : 'green') . ';">Leg ' . ($key + 1) . '</div>'
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
                            'f' => $aDownline->username . '<div>' . $aDownline->monolegUserPin->pin->name_short . '<div style="color:green">Leg 1</div>'
                        ],
                        (string) $aDownline->sponsor_id,
                        ''
                    ];
                    $aDownline = $aDownline->monolegSponsors()->where('monoleg_id', $user->id)->first();
                }
            }
        }
        return $tree;
    }
}