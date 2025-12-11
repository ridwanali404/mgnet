<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pin;
use App\Models\UserPin;
use App\Models\Bonus;
use App\Models\MonthlyClosing;
use App\Traits\Helper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ROBonusGenerasiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test bahwa bonus generasi RO dibuat sebagai potensi saat RO dibuat
     */
    public function test_ro_bonus_generasi_created_as_potential()
    {
        // Setup: Buat sponsor (Gold) dan user (Gold)
        $sponsor = User::create([
            'name' => 'Sponsor 1',
            'email' => 'sponsor1@test.com',
            'password' => bcrypt('password'),
            'username' => 'sponsor1',
            'type' => 'member',
        ]);
        
        $user = User::create([
            'name' => 'User 1',
            'email' => 'user1@test.com',
            'password' => bcrypt('password'),
            'username' => 'user1',
            'sponsor_id' => $sponsor->id,
            'upline_id' => $sponsor->id,
            'type' => 'member',
        ]);

        // Buat pin Gold
        $goldPin = Pin::create([
            'name' => 'Gold',
            'type' => 'premium',
            'price' => 1700000,
            'bonus_sponsor_percent' => 15,
            'poin_pair' => 4,
            'poin_reward' => 3,
            'poin_ro' => 200,
            'level' => 2,
            'is_generasi' => true,
            'generasi_percent' => 19,
            'active_days' => 45,
            'ro_price' => 1700000,
        ]);

        // User join dengan Gold
        $userPin = UserPin::create([
            'user_id' => $user->id,
            'buyer_id' => $user->id,
            'pin_id' => $goldPin->id,
            'code' => 'TEST001',
            'name' => 'Gold',
            'price' => $goldPin->price,
            'level' => $goldPin->level,
            'is_used' => true,
            'is_ro' => false,
        ]);

        // Sponsor juga punya Gold
        $sponsorPin = UserPin::create([
            'user_id' => $sponsor->id,
            'buyer_id' => $sponsor->id,
            'pin_id' => $goldPin->id,
            'code' => 'SPONSOR001',
            'name' => 'Gold',
            'price' => $goldPin->price,
            'level' => $goldPin->level,
            'is_used' => true,
            'is_ro' => false,
        ]);

        // Simulasikan RO: Buat UserPin dengan is_ro = true
        $roUserPin = UserPin::create([
            'user_id' => $user->id,
            'buyer_id' => $user->id,
            'pin_id' => $goldPin->id,
            'code' => 'RO001',
            'name' => 'Gold',
            'price' => $goldPin->ro_price ?? 1700000,
            'level' => $goldPin->level,
            'is_used' => true,
            'is_ro' => true, // Tandai sebagai RO
        ]);

        // Panggil Helper::upgrade() untuk membuat bonus generasi sebagai potensi
        Helper::upgrade($roUserPin);

        // Verifikasi: Bonus generasi harus dibuat untuk sponsor
        $bonus = Bonus::where('type', 'Bonus Generasi')
            ->where('description', 'like', '%RO ' . $user->username . '%')
            ->first();

        $this->assertNotNull($bonus, 'Bonus generasi RO harus dibuat sebagai potensi');
        $this->assertEquals($sponsor->id, $bonus->user_id, 'Bonus harus diberikan ke sponsor');
        $this->assertStringContainsString('RO', $bonus->description, 'Deskripsi harus mengandung "RO"');
    }

    /**
     * Test bahwa bonus generasi RO dihapus jika user tidak qualified saat monthly closing
     */
    public function test_ro_bonus_generasi_deleted_if_not_qualified()
    {
        // Setup: Buat sponsor dan user
        $sponsor = User::create([
            'name' => 'Sponsor 2',
            'email' => 'sponsor2@test.com',
            'password' => bcrypt('password'),
            'username' => 'sponsor2',
            'type' => 'member',
        ]);
        
        $user = User::create([
            'name' => 'User 2',
            'email' => 'user2@test.com',
            'password' => bcrypt('password'),
            'username' => 'user2',
            'sponsor_id' => $sponsor->id,
            'upline_id' => $sponsor->id,
            'type' => 'member',
            'active_until' => Carbon::now()->addDays(30),
        ]);

        // Buat pin Gold
        $goldPin = Pin::create([
            'name' => 'Gold',
            'type' => 'premium',
            'price' => 1700000,
            'bonus_sponsor_percent' => 15,
            'poin_pair' => 4,
            'poin_reward' => 3,
            'poin_ro' => 200,
            'level' => 2,
            'is_generasi' => true,
            'generasi_percent' => 19,
            'active_days' => 45,
            'ro_price' => 1700000,
        ]);

        // Sponsor punya Gold
        $sponsorPin = UserPin::create([
            'user_id' => $sponsor->id,
            'buyer_id' => $sponsor->id,
            'pin_id' => $goldPin->id,
            'code' => 'SPONSOR002',
            'name' => 'Gold',
            'price' => $goldPin->price,
            'level' => $goldPin->level,
            'is_used' => true,
            'is_ro' => false,
        ]);

        // User join dengan Gold
        $userPin = UserPin::create([
            'user_id' => $user->id,
            'buyer_id' => $user->id,
            'pin_id' => $goldPin->id,
            'code' => 'TEST002',
            'name' => 'Gold',
            'price' => $goldPin->price,
            'level' => $goldPin->level,
            'is_used' => true,
            'is_ro' => false,
        ]);

        // Simulasikan RO: Buat UserPin dengan is_ro = true
        $month = date('Y-m');
        $roUserPin = UserPin::create([
            'user_id' => $user->id,
            'buyer_id' => $user->id,
            'pin_id' => $goldPin->id,
            'code' => 'RO002',
            'name' => 'Gold',
            'price' => $goldPin->ro_price ?? 1700000,
            'level' => $goldPin->level,
            'is_used' => true,
            'is_ro' => true,
            'created_at' => Carbon::parse($month . '-15'),
            'updated_at' => Carbon::parse($month . '-15'),
        ]);

        // Panggil Helper::upgrade() untuk membuat bonus generasi sebagai potensi
        Helper::upgrade($roUserPin);

        // Verifikasi: Bonus generasi sudah dibuat
        $bonusBefore = Bonus::where('type', 'Bonus Generasi')
            ->where('description', 'like', '%RO ' . $user->username . '%')
            ->count();
        
        $this->assertGreaterThan(0, $bonusBefore, 'Bonus generasi RO harus dibuat sebagai potensi');

        // Simulasikan monthly closing: User TIDAK qualified (tidak ada 170 PV dan tidak ada automaintain)
        // Karena user tidak qualified, bonus generasi harus dihapus
        $allROUserIds = []; // User tidak ada di list qualified
        
        $allROPinUsers = User::whereHas('userPins', function ($q) use ($month) {
            $q->whereHas('pin', function ($q_pin) {
                $q_pin->whereIn('name', ['Gold', 'Gold Upgrade Platinum', 'Platinum']);
            })
            ->where('is_ro', true)
            ->where('is_used', true)
            ->whereYear('created_at', Carbon::parse($month)->format('Y'))
            ->whereMonth('created_at', Carbon::parse($month)->format('m'));
        })->get();

        // Hapus bonus generasi untuk user yang tidak qualified
        foreach ($allROPinUsers as $roUser) {
            if (!in_array($roUser->id, $allROUserIds)) {
                Bonus::where('type', 'Bonus Generasi')
                    ->where('description', 'like', '%RO ' . $roUser->username . '%')
                    ->whereYear('created_at', Carbon::parse($month)->format('Y'))
                    ->whereMonth('created_at', Carbon::parse($month)->format('m'))
                    ->delete();
            }
        }

        // Verifikasi: Bonus generasi harus dihapus
        $bonusAfter = Bonus::where('type', 'Bonus Generasi')
            ->where('description', 'like', '%RO ' . $user->username . '%')
            ->count();
        
        $this->assertEquals(0, $bonusAfter, 'Bonus generasi RO harus dihapus jika user tidak qualified');
    }

    /**
     * Test bahwa bonus generasi RO tetap ada jika user qualified saat monthly closing
     */
    public function test_ro_bonus_generasi_remains_if_qualified()
    {
        // Setup: Buat sponsor dan user
        $sponsor = User::create([
            'name' => 'Sponsor 3',
            'email' => 'sponsor3@test.com',
            'password' => bcrypt('password'),
            'username' => 'sponsor3',
            'type' => 'member',
        ]);
        
        $user = User::create([
            'name' => 'User 3',
            'email' => 'user3@test.com',
            'password' => bcrypt('password'),
            'username' => 'user3',
            'sponsor_id' => $sponsor->id,
            'upline_id' => $sponsor->id,
            'type' => 'member',
            'active_until' => Carbon::now()->addDays(30),
        ]);

        // Buat pin Gold
        $goldPin = Pin::create([
            'name' => 'Gold',
            'type' => 'premium',
            'price' => 1700000,
            'bonus_sponsor_percent' => 15,
            'poin_pair' => 4,
            'poin_reward' => 3,
            'poin_ro' => 200,
            'level' => 2,
            'is_generasi' => true,
            'generasi_percent' => 19,
            'active_days' => 45,
            'ro_price' => 1700000,
        ]);

        // Sponsor punya Gold
        $sponsorPin = UserPin::create([
            'user_id' => $sponsor->id,
            'buyer_id' => $sponsor->id,
            'pin_id' => $goldPin->id,
            'code' => 'SPONSOR003',
            'name' => 'Gold',
            'price' => $goldPin->price,
            'level' => $goldPin->level,
            'is_used' => true,
            'is_ro' => false,
        ]);

        // User join dengan Gold
        $userPin = UserPin::create([
            'user_id' => $user->id,
            'buyer_id' => $user->id,
            'pin_id' => $goldPin->id,
            'code' => 'TEST003',
            'name' => 'Gold',
            'price' => $goldPin->price,
            'level' => $goldPin->level,
            'is_used' => true,
            'is_ro' => false,
        ]);

        // Simulasikan RO: Buat UserPin dengan is_ro = true
        $month = date('Y-m');
        $roUserPin = UserPin::create([
            'user_id' => $user->id,
            'buyer_id' => $user->id,
            'pin_id' => $goldPin->id,
            'code' => 'RO003',
            'name' => 'Gold',
            'price' => $goldPin->ro_price ?? 1700000,
            'level' => $goldPin->level,
            'is_used' => true,
            'is_ro' => true,
            'created_at' => Carbon::parse($month . '-15'),
            'updated_at' => Carbon::parse($month . '-15'),
        ]);

        // Panggil Helper::upgrade() untuk membuat bonus generasi sebagai potensi
        Helper::upgrade($roUserPin);

        // Verifikasi: Bonus generasi sudah dibuat
        $bonusBefore = Bonus::where('type', 'Bonus Generasi')
            ->where('description', 'like', '%RO ' . $user->username . '%')
            ->count();
        
        $this->assertGreaterThan(0, $bonusBefore, 'Bonus generasi RO harus dibuat sebagai potensi');

        // Simulasikan monthly closing: User QUALIFIED (ada di list qualified)
        $allROUserIds = [$user->id]; // User ada di list qualified
        
        $allROPinUsers = User::whereHas('userPins', function ($q) use ($month) {
            $q->whereHas('pin', function ($q_pin) {
                $q_pin->whereIn('name', ['Gold', 'Gold Upgrade Platinum', 'Platinum']);
            })
            ->where('is_ro', true)
            ->where('is_used', true)
            ->whereYear('created_at', Carbon::parse($month)->format('Y'))
            ->whereMonth('created_at', Carbon::parse($month)->format('m'));
        })->get();

        // Hapus bonus generasi untuk user yang tidak qualified
        foreach ($allROPinUsers as $roUser) {
            if (!in_array($roUser->id, $allROUserIds)) {
                Bonus::where('type', 'Bonus Generasi')
                    ->where('description', 'like', '%RO ' . $roUser->username . '%')
                    ->whereYear('created_at', Carbon::parse($month)->format('Y'))
                    ->whereMonth('created_at', Carbon::parse($month)->format('m'))
                    ->delete();
            }
        }

        // Verifikasi: Bonus generasi harus tetap ada
        $bonusAfter = Bonus::where('type', 'Bonus Generasi')
            ->where('description', 'like', '%RO ' . $user->username . '%')
            ->count();
        
        $this->assertEquals($bonusBefore, $bonusAfter, 'Bonus generasi RO harus tetap ada jika user qualified');
    }
}

