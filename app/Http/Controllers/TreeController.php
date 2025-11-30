<?php

namespace App\Http\Controllers;

use App\Models\User;

class TreeController extends Controller
{
    private function getTreeType()
    {
        return request()->tree_type ?? 'upline'; // default: upline, option: sponsor
    }

    private function isSponsorTree()
    {
        return $this->getTreeType() === 'sponsor';
    }

    function relationship(User $user, $treeType = null): string
    {
        $treeType = $treeType ?? $this->getTreeType();
        $isSponsorTree = $treeType === 'sponsor';
        
        $parentId = $isSponsorTree ? $user->sponsor_id : $user->upline_id;
        $is_parent = $parentId ? '1' : '0';
        
        if ($isSponsorTree) {
            $is_siblings = $user->sponsor?->sponsors()->where('id', '!=', $user->id)->count() ? '1' : '0';
            $is_children = $user->sponsors()->count() ? '1' : '0';
        } else {
            $is_siblings = $user->upline?->uplines()->where('id', '!=', $user->id)->count() ? '1' : '0';
            $is_children = $user->uplines()->count() ? '1' : '0';
        }
        
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

    private function buildTreeData(User $user, int $level = 0, int $maxLevel = 10, $treeType = null)
    {
        $treeType = $treeType ?? $this->getTreeType();
        $isSponsorTree = $treeType === 'sponsor';
        
        $children = $isSponsorTree ? $user->sponsors() : $user->uplines();
        $childrenCount = $children->count();
        
        $data = [
            'id' => (string) $user->id,
            'name' => $user->username,
            'title' => $user->name,
            'relationship' => auth()->user()->type == 'admin' ? $this->relationship($user, $treeType) : ('00' . ($childrenCount ? '1' : '0')),
            'packageClass' => $this->packageClass($user),
        ];

        // Jika masih dalam batas level, load children
        if ($level < $maxLevel) {
            $childrenData = $children->with('userPin.pin')->get();
            $data['children'] = $childrenData->map(function ($a) use ($level, $maxLevel, $treeType) {
                return $this->buildTreeData($a, $level + 1, $maxLevel, $treeType);
            });
        }

        return $data;
    }

    public function dataSource()
    {
        $user = User::where('id', request()->user_id ?? auth()->id())
            ->with(['userPin.pin'])
            ->first();
        
        $treeType = $this->getTreeType();
        return $this->buildTreeData($user, 0, 10, $treeType);
    }

    public function children(User $user)
    {
        $treeType = $this->getTreeType();
        $isSponsorTree = $treeType === 'sponsor';
        $children = $isSponsorTree ? $user->sponsors() : $user->uplines();
        
        return [
            'children' => $children->with('userPin.pin')->get()->map(function ($a) use ($treeType) {
                return [
                    'id' => (string) $a->id,
                    'name' => $a->username,
                    'title' => $a->name,
                    'relationship' => $this->relationship($a, $treeType),
                    'packageClass' => $this->packageClass($a),
                ];
            }),
        ];
    }

    public function parent(User $user)
    {
        $treeType = $this->getTreeType();
        $isSponsorTree = $treeType === 'sponsor';
        $parent = $isSponsorTree ? $user->sponsor() : $user->upline();
        
        $a = $parent->with('userPin.pin')->first();
        if (!$a) {
            return null;
        }
        
        return [
            'id' => (string) $a->id,
            'name' => $a->username,
            'title' => $a->name,
            'relationship' => $this->relationship($a, $treeType),
            'packageClass' => $this->packageClass($a),
        ];
    }

    public function siblings(User $user)
    {
        $treeType = $this->getTreeType();
        $isSponsorTree = $treeType === 'sponsor';
        
        if ($isSponsorTree) {
            $parent = $user->sponsor;
            if (!$parent) {
                return ['siblings' => []];
            }
            $siblings = $parent->sponsors()->where('id', '!=', $user->id);
        } else {
            $parent = $user->upline;
            if (!$parent) {
                return ['siblings' => []];
            }
            $siblings = $parent->uplines()->where('id', '!=', $user->id);
        }
        
        return [
            'siblings' => $siblings->with('userPin.pin')->get()->map(function ($a) use ($treeType) {
                return [
                    'id' => (string) $a->id,
                    'name' => $a->username,
                    'title' => $a->name,
                    'relationship' => $this->relationship($a, $treeType),
                    'packageClass' => $this->packageClass($a),
                ];
            }),
        ];
    }

    public function families(User $user)
    {
        $treeType = $this->getTreeType();
        $isSponsorTree = $treeType === 'sponsor';
        
        $parentId = $isSponsorTree ? $user->sponsor_id : $user->upline_id;
        if (!$parentId) {
            return null;
        }
        
        $parent = User::where('id', $parentId)->with(['userPin.pin'])->first();
        if (!$parent) {
            return null;
        }
        
        if ($isSponsorTree) {
            $parent->load(['sponsors' => function ($q) {
                $q->with('userPin.pin');
            }]);
            $siblings = $parent->sponsors()->where('id', '!=', $user->id);
        } else {
            $parent->load(['uplines' => function ($q) {
                $q->with('userPin.pin');
            }]);
            $siblings = $parent->uplines()->where('id', '!=', $user->id);
        }
        
        return [
            'id' => (string) $parent->id,
            'name' => $parent->username,
            'title' => $parent->name,
            'relationship' => $this->relationship($user, $treeType),
            'packageClass' => $this->packageClass($parent),
            'children' => $siblings->get()->map(function ($a) use ($treeType) {
                return [
                    'id' => (string) $a->id,
                    'name' => $a->username,
                    'title' => $a->name,
                    'relationship' => $this->relationship($a, $treeType),
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