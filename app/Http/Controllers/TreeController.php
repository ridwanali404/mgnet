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
    public function dataSource()
    {
        $user = User::where('id', request()->user_id ?? auth()->id())->with('sponsors')->first();
        return [
            'id' => (string) $user->id,
            'name' => $user->username,
            'title' => $user->name,
            'relationship' => auth()->user()->type == 'admin' ? $this->relationship($user) : ('00' . ($user->sponsors()->count() ? '1' : '0')),
            'children' => $user->sponsors->map(function ($a) {
                return [
                    'id' => (string) $a->id,
                    'name' => $a->username,
                    'title' => $a->name,
                    'relationship' => $this->relationship($a),
                ];
            }),
        ];
    }

    public function children(User $user)
    {
        return [
            'children' => $user->sponsors->map(function ($a) {
                return [
                    'id' => (string) $a->id,
                    'name' => $a->username,
                    'title' => $a->name,
                    'relationship' => $this->relationship($a),
                ];
            }),
        ];
    }

    public function parent(User $user)
    {
        $a = $user->sponsor;
        return [
            'id' => (string) $a->id,
            'name' => $a->username,
            'title' => $a->name,
            'relationship' => $this->relationship($a),
        ];
    }

    public function siblings(User $user)
    {
        return [
            'siblings' => $user->sponsor->sponsors()->where('id', '!=', $user->id)->get()->map(function ($a) {
                return [
                    'id' => (string) $a->id,
                    'name' => $a->username,
                    'title' => $a->name,
                    'relationship' => $this->relationship($a),
                ];
            }),
        ];
    }

    public function families(User $user)
    {
        $parent = User::where('id', $user->sponsor_id)->with('sponsors')->first();
        return [
            'id' => (string) $parent->id,
            'name' => $parent->username,
            'title' => $parent->name,
            'relationship' => $this->relationship($user),
            'children' => $parent->sponsors()->where('id', '!=', $user->id)->get()->map(function ($a) {
                return [
                    'id' => (string) $a->id,
                    'name' => $a->username,
                    'title' => $a->name,
                    'relationship' => $this->relationship($a),
                ];
            }),
        ];
    }
}