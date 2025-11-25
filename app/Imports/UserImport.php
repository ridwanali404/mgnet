<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Address;
use App\Models\UserPin;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class UserImport implements ToModel, WithStartRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        // bank_id
        $bank = \App\Bank::where('name', 'like', '%' . $row[22] . '%')->first();
        $bank_id = $bank->id ?? null;

        // sponsor_id
        $sponsor = \App\User::where('image', $row[2])->first();
        $sponsor_id = $sponsor->id ?? null;

        $user = new User([
            'id' => $row[0],
            'name' => $row[5],
            'email' => $row[27],
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'username' => trim($row[25], " "),
            'phone' => $row[11],

            'bank_id' => $bank_id,
            'bank_account' => $row[23],
            'bank_as' => $row[21],

            'image' => $row[1],
            // idpw tmp
            'sponsor_id' => $sponsor_id,
        ]);

        // province_id
        $province = \App\Province::where('province', 'like', '%' . $row[9] . '%')->first();
        $province_id = $province->id ?? null;

        // city_id
        $city = \App\City::where('city_name', 'like', '%' . $row[9] . '%')->first();
        $city_id = $city->id ?? null;

        // create address
        if ($row[7]) {
            $address = new Address([
                'user_id' => $user->id,
                'name' => $user->name,
                'address' => $row[7],
                'phone' => $row[11],
                'province_id' => $province_id,
                'city_id' => $city_id,
                'postal_code' => $row[10],
                'is_active' => true,
            ]);
        }

        // create pin
        if ($row[16] == '0000-00-00 00:00:00') {
            $pin = \App\Pin::where('name', 'Free Member')->first();
        } else {
            $pin = \App\Pin::where('name', 'CR Reseller')->first();
        }
        $userPin = new UserPin([
            'user_id' => $user->id,
            'pin_id' => $pin->id,
            'code' => strtoupper(str_random(6)),
            'name' => $pin->name,
            'price' => $pin->price,
            'level' => $pin->level,
            'is_used' => true,
        ]);
        if (isset($address)) {
            return [$user, $address, $userPin];
        }
        return [$user, $userPin];
    }
}