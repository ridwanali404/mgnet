<?php

namespace App\Http\Controllers;

use App\Models\User;

class TreeController extends Controller
{
    function relationship(User $user): string
    {
        $is_parent = $user->sponsor_id ? '1' : '0';
        $is_siblings = $user->sponsor?->sponsors()->where('id', '!=', $user->id)->count() ? '1' : '0';
        $is_children = $user->sponsors()->count() ? '1' : '0';
        return $is_parent . $is_siblings . $is_children;
    }

    function packageClass(User $user): string
    {
        if (!$user->userPin || !$user->userPin->pin) {
            return 'package-free';
        }
        
        $pinName = $user->userPin->pin->name;
        
        // Check for Gold package
        if (str_contains($pinName, 'Gold') || in_array($pinName, ['Gold', 'Basic Upgrade Gold', 'Silver Upgrade Gold', 'BSM GOLD', 'BSM GOLD UP'])) {
            return 'package-gold';
        }
        
        // Check for Platinum package
        if (str_contains($pinName, 'Platinum') || in_array($pinName, ['Platinum', 'Basic Upgrade Platinum', 'Silver Upgrade Platinum', 'Gold Upgrade Platinum', 'BSM PLATINUM', 'BSM PLATINUM UP'])) {
            return 'package-platinum';
        }
        
        return 'package-free';
    }

    private function buildTreeData(User $user, int $level = 0, int $maxLevel = 10)
    {
        $data = [
            'id' => (string) $user->id,
            'name' => $user->username,
            'title' => $user->name,
            'relationship' => auth()->user()->type == 'admin' ? $this->relationship($user) : ('00' . ($user->sponsors()->count() ? '1' : '0')),
            'packageClass' => $this->packageClass($user),
        ];

        // Jika masih dalam batas level, load children
        if ($level < $maxLevel) {
            $sponsors = $user->sponsors()->with('userPin.pin')->get();
            $data['children'] = $sponsors->map(function ($a) use ($level, $maxLevel) {
                return $this->buildTreeData($a, $level + 1, $maxLevel);
            });
        }

        return $data;
    }

    public function dataSource()
    {
        $user = User::where('id', request()->user_id ?? auth()->id())
            ->with(['userPin.pin'])
            ->first();
        
        return $this->buildTreeData($user, 0, 10);
    }

    public function children(User $user)
    {
        return [
            'children' => $user->sponsors()->with('userPin.pin')->get()->map(function ($a) {
                return [
                    'id' => (string) $a->id,
                    'name' => $a->username,
                    'title' => $a->name,
                    'relationship' => $this->relationship($a),
                    'packageClass' => $this->packageClass($a),
                ];
            }),
        ];
    }

    public function parent(User $user)
    {
        $a = $user->sponsor()->with('userPin.pin')->first();
        return [
            'id' => (string) $a->id,
            'name' => $a->username,
            'title' => $a->name,
            'relationship' => $this->relationship($a),
            'packageClass' => $this->packageClass($a),
        ];
    }

    public function siblings(User $user)
    {
        return [
            'siblings' => $user->sponsor->sponsors()->with('userPin.pin')->where('id', '!=', $user->id)->get()->map(function ($a) {
                return [
                    'id' => (string) $a->id,
                    'name' => $a->username,
                    'title' => $a->name,
                    'relationship' => $this->relationship($a),
                    'packageClass' => $this->packageClass($a),
                ];
            }),
        ];
    }

    public function families(User $user)
    {
        $parent = User::where('id', $user->sponsor_id)->with(['userPin.pin', 'sponsors' => function ($q) {
            $q->with('userPin.pin');
        }])->first();
        return [
            'id' => (string) $parent->id,
            'name' => $parent->username,
            'title' => $parent->name,
            'relationship' => $this->relationship($user),
            'packageClass' => $this->packageClass($parent),
            'children' => $parent->sponsors()->where('id', '!=', $user->id)->get()->map(function ($a) {
                return [
                    'id' => (string) $a->id,
                    'name' => $a->username,
                    'title' => $a->name,
                    'relationship' => $this->relationship($a),
                    'packageClass' => $this->packageClass($a),
                ];
            }),
        ];
    }

    public function recentBonuses(User $user)
    {
        $bonuses = $user->bonuses()
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($bonus) {
                return [
                    'created_at' => $bonus->created_at ? $bonus->created_at->format('Y-m-d H:i:s') : '',
                    'type' => $bonus->type ?? '',
                    'description' => $bonus->description ?? '',
                    'amount' => (int) $bonus->amount,
                    'is_poin' => (bool) $bonus->is_poin,
                    'paid_at' => $bonus->paid_at ? $bonus->paid_at->format('Y-m-d H:i:s') : null,
                    'used_at' => $bonus->used_at ? $bonus->used_at->format('Y-m-d H:i:s') : null,
                ];
            });

        return [
            'bonuses' => $bonuses
        ];
    }
}