<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Pin;
use App\Models\User;
use App\Traits\Helper;
use App\Models\Product;
use Illuminate\Database\Seeder;

class TestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder untuk semua data testing/dummy
     * 
     * @return void
     */
    public function run()
    {
        $this->command->info('Memulai pembuatan data testing...');


        // Products dummy dengan gambar dummy
        $dummyProducts = [
            [
                'name' => 'Produk Dummy 1',
                'price' => 150000,
                'price_member' => 135000,
                'price_stockist' => 120000,
                'price_master' => 110000,
                'weight' => 250,
                'desc' => 'Ini adalah deskripsi produk dummy pertama. Produk berkualitas tinggi dengan bahan-bahan pilihan yang terjamin kualitasnya. Cocok untuk kebutuhan sehari-hari dan memberikan manfaat yang optimal.',
                'poin' => 15,
                'is_ro' => true,
            ],
            [
                'name' => 'Produk Dummy 2',
                'price' => 200000,
                'price_member' => 180000,
                'price_stockist' => 160000,
                'price_master' => 150000,
                'weight' => 500,
                'desc' => 'Produk dummy kedua dengan spesifikasi lengkap. Didesain khusus untuk memberikan pengalaman terbaik bagi pengguna. Terbuat dari bahan premium yang telah teruji kualitasnya.',
                'poin' => 20,
                'is_ro' => true,
            ],
            [
                'name' => 'Produk Dummy 3',
                'price' => 300000,
                'price_member' => 270000,
                'price_stockist' => 240000,
                'price_master' => 220000,
                'weight' => 750,
                'desc' => 'Produk dummy ketiga yang sangat direkomendasikan. Memiliki fitur-fitur unggulan dan kualitas premium. Pilihan tepat untuk kebutuhan Anda dengan harga yang terjangkau.',
                'poin' => 30,
                'is_ro' => true,
            ],
            [
                'name' => 'Produk Dummy 4',
                'price' => 450000,
                'price_member' => 400000,
                'price_stockist' => 360000,
                'price_master' => 330000,
                'weight' => 1000,
                'desc' => 'Produk dummy keempat dengan kualitas terbaik. Dikembangkan dengan teknologi terkini untuk memberikan hasil yang maksimal. Investasi yang tepat untuk masa depan.',
                'poin' => 45,
                'is_ro' => true,
            ],
            [
                'name' => 'Produk Dummy 5',
                'price' => 500000,
                'price_member' => 450000,
                'price_stockist' => 400000,
                'price_master' => 380000,
                'weight' => 1200,
                'desc' => 'Produk dummy kelima dengan desain modern dan fungsional. Kombinasi sempurna antara kualitas dan harga. Pilihan terbaik untuk memenuhi kebutuhan Anda.',
                'poin' => 50,
                'is_ro' => false,
            ],
            [
                'name' => 'Produk Dummy 6',
                'price' => 750000,
                'price_member' => 675000,
                'price_stockist' => 600000,
                'price_master' => 550000,
                'weight' => 1500,
                'desc' => 'Produk dummy keenam dengan fitur lengkap dan kualitas premium. Didesain untuk memberikan kenyamanan dan kepuasan maksimal. Solusi terbaik untuk kebutuhan Anda.',
                'poin' => 75,
                'is_ro' => true,
            ],
        ];

        foreach ($dummyProducts as $index => $productData) {
            // Generate gambar dummy untuk setiap produk (3-5 gambar per produk)
            $image_array = [];
            $imageCount = rand(3, 5);
            
            for ($i = 1; $i <= $imageCount; $i++) {
                $path = $this->createDummyImage('product/dummy_' . ($index + 1) . '_' . $i . '_', $productData['name']);
                array_push($image_array, $path);
            }

            Product::create(array_merge($productData, [
                'images' => $image_array,
            ]));
        }

        $this->command->info('Products dummy dengan gambar dummy telah dibuat: ' . count($dummyProducts) . ' produk');
        
        // Get admin user
        $admin = User::where('type', 'admin')->first();
        
        if (!$admin) {
            $this->command->error('User admin tidak ditemukan! Pastikan DatabaseSeeder sudah dijalankan terlebih dahulu.');
            return;
        }
        
        // Get pins
        $goldPin = Pin::where('name', 'Gold')->first();
        $platinumPin = Pin::where('name', 'Platinum')->first();
        
        if (!$goldPin || !$platinumPin) {
            $this->command->error('Pin Gold atau Platinum tidak ditemukan! Pastikan DatabaseSeeder sudah dijalankan terlebih dahulu.');
            return;
        }
        
        // Helper functions untuk generate random data
        $randomName = fn() => fake()->name();
        $randomEmail = fn($name) => strtolower(str_replace(' ', '', $name)) . rand(100, 999) . '@example.com';
        $randomPhone = fn() => '08' . rand(100000000, 999999999);
        $randomKtp = fn() => str_pad(rand(1000000000000000, 9999999999999999), 16, '0', STR_PAD_LEFT);
        $randomNpwp = fn() => str_pad(rand(100000000000000, 999999999999999), 15, '0', STR_PAD_LEFT);
        $randomBankAccount = fn() => str_pad(rand(100000000, 999999999), 9, '0', STR_PAD_LEFT);
        
        // Data dummy untuk simulasi Bonus Sponsor
        $this->command->info('');
        $this->command->info('Membuat data dummy untuk simulasi Bonus Sponsor...');
        
        $sponsorName = $randomName();
        $sponsorUser = User::create([
            'name' => $sponsorName,
            'email' => $randomEmail($sponsorName),
            'password' => bcrypt('password'),
            'type' => 'member',
            'username' => 'sponsor_test',
            'phone' => $randomPhone(),
            'ktp' => $randomKtp(),
            'npwp' => $randomNpwp(),
            'bank_id' => 1,
            'bank_account' => $randomBankAccount(),
            'bank_as' => $sponsorName,
            'sponsor_id' => $admin->id,
            'upline_id' => $admin->id, // Default: sama dengan sponsor_id
        ]);

        $sponsorUser->addresses()->create([
            'name' => 'Rumah',
            'address' => 'Jl. Contoh No. 123',
            'province_id' => 5,
            'city_id' => 501,
            'subdistrict_id' => 6988,
            'is_active' => true,
        ]);

        // Sponsor menggunakan paket Gold
        $sponsorUserPin = $sponsorUser->userPin()->create([
            'pin_id' => $goldPin->id,
            'name' => 'Gold',
            'code' => 'SPNSR1',
            'price' => $goldPin->price,
            'level' => $goldPin->level ?? 1,
            'is_used' => true,
        ]);

        // User dengan paket Gold yang disponsori oleh sponsor
        $goldUserName = $randomName();
        $goldUser = User::create([
            'name' => $goldUserName,
            'email' => $randomEmail($goldUserName),
            'password' => bcrypt('password'),
            'type' => 'member',
            'username' => 'gold_test',
            'phone' => $randomPhone(),
            'ktp' => $randomKtp(),
            'npwp' => $randomNpwp(),
            'bank_id' => 1,
            'bank_account' => $randomBankAccount(),
            'bank_as' => $goldUserName,
            'sponsor_id' => $sponsorUser->id,
            'upline_id' => $sponsorUser->id, // Default: sama dengan sponsor_id
        ]);

        $goldUser->addresses()->create([
            'name' => 'Rumah',
            'address' => 'Jl. Contoh No. 124',
            'province_id' => 5,
            'city_id' => 501,
            'subdistrict_id' => 6988,
            'is_active' => true,
        ]);

        // User Gold menggunakan paket Gold - akan trigger bonus sponsor
        $goldUserPin = $goldUser->userPin()->create([
            'pin_id' => $goldPin->id,
            'name' => 'Gold',
            'code' => 'GLOD01',
            'price' => $goldPin->price,
            'level' => $goldPin->level ?? 1,
            'is_used' => true,
        ]);

        // Trigger bonus sponsor untuk Gold
        Helper::upgrade($goldUserPin);

        // User dengan paket Platinum yang disponsori oleh sponsor
        $platinumUserName = $randomName();
        $platinumUser = User::create([
            'name' => $platinumUserName,
            'email' => $randomEmail($platinumUserName),
            'password' => bcrypt('password'),
            'type' => 'member',
            'username' => 'platinum_test',
            'phone' => $randomPhone(),
            'ktp' => $randomKtp(),
            'npwp' => $randomNpwp(),
            'bank_id' => 1,
            'bank_account' => $randomBankAccount(),
            'bank_as' => $platinumUserName,
            'sponsor_id' => $sponsorUser->id,
            'upline_id' => $sponsorUser->id, // Default: sama dengan sponsor_id
        ]);

        $platinumUser->addresses()->create([
            'name' => 'Rumah',
            'address' => 'Jl. Contoh No. 125',
            'province_id' => 5,
            'city_id' => 501,
            'subdistrict_id' => 6988,
            'is_active' => true,
        ]);

        // User Platinum menggunakan paket Platinum - akan trigger bonus sponsor
        $platinumUserPin = $platinumUser->userPin()->create([
            'pin_id' => $platinumPin->id,
            'name' => 'Platinum',
            'code' => 'PLAT01',
            'price' => $platinumPin->price,
            'level' => $platinumPin->level ?? 2,
            'is_used' => true,
        ]);

        // Trigger bonus sponsor untuk Platinum
        Helper::upgrade($platinumUserPin);

        $this->command->info('Data dummy untuk simulasi Bonus Sponsor telah dibuat:');
        $this->command->info('- User ' . $sponsorName . ' (sponsor, username: sponsor_test) dengan paket Gold');
        $this->command->info('- User ' . $goldUserName . ' (Gold, username: gold_test) - Bonus: Rp 300.000 (15% x Rp 2.000.000)');
        $this->command->info('- User ' . $platinumUserName . ' (Platinum, username: platinum_test) - Bonus: Rp 2.250.000 (15% x Rp 15.000.000)');

        // Data dummy untuk simulasi Bonus Generasi (10+ generasi)
        $this->command->info('');
        $this->command->info('Membuat data dummy untuk Bonus Generasi (10+ generasi)...');
        
        // User root untuk bonus generasi (akan menerima bonus dari semua generasi di bawahnya)
        $rootUserName = $randomName();
        $rootUser = User::create([
            'name' => $rootUserName,
            'email' => $randomEmail($rootUserName),
            'password' => bcrypt('password'),
            'type' => 'member',
            'username' => 'root_generasi',
            'phone' => $randomPhone(),
            'ktp' => $randomKtp(),
            'npwp' => $randomNpwp(),
            'bank_id' => 1,
            'bank_account' => $randomBankAccount(),
            'bank_as' => $rootUserName,
            'sponsor_id' => $admin->id,
            'upline_id' => $admin->id, // Default: sama dengan sponsor_id
        ]);

        $rootUser->addresses()->create([
            'name' => 'Rumah',
            'address' => 'Jl. Root No. 1',
            'province_id' => 5,
            'city_id' => 501,
            'subdistrict_id' => 6988,
            'is_active' => true,
        ]);

        // Root user menggunakan paket Platinum untuk mendapatkan bonus maksimal
        $rootUserPin = $rootUser->userPin()->create([
            'pin_id' => $platinumPin->id,
            'name' => 'Platinum',
            'code' => 'ROOT01',
            'price' => $platinumPin->price,
            'level' => $platinumPin->level,
            'is_used' => true,
        ]);

        // Buat 12 generasi untuk test (lebih dari 10 generasi)
        $currentSponsor = $rootUser;
        $generasiUsers = [];
        
        for ($gen = 1; $gen <= 12; $gen++) {
            // Alternatif paket: gen ganjil = Gold, gen genap = Platinum (untuk test berbagai kondisi)
            $usePlatinum = ($gen % 2 == 0);
            $selectedPin = $usePlatinum ? $platinumPin : $goldPin;
            $packageName = $usePlatinum ? 'Platinum' : 'Gold';
            
            $genUserName = $randomName();
            $genUser = User::create([
                'name' => $genUserName,
                'email' => $randomEmail($genUserName),
                'password' => bcrypt('password'),
                'type' => 'member',
                'username' => 'gen' . $gen . '_test',
                'phone' => $randomPhone(),
                'ktp' => $randomKtp(),
                'npwp' => $randomNpwp(),
                'bank_id' => 1,
                'bank_account' => $randomBankAccount(),
                'bank_as' => $genUserName,
                'sponsor_id' => $currentSponsor->id,
                'upline_id' => $currentSponsor->id, // Default: sama dengan sponsor_id
            ]);

            $genUser->addresses()->create([
                'name' => 'Rumah',
                'address' => 'Jl. Generasi No. ' . $gen,
                'province_id' => 5,
                'city_id' => 501,
                'subdistrict_id' => 6988,
                'is_active' => true,
            ]);

            // User menggunakan paket yang dipilih
            $genUserPin = $genUser->userPin()->create([
                'pin_id' => $selectedPin->id,
                'name' => $packageName,
                'code' => 'GEN' . str_pad($gen, 2, '0', STR_PAD_LEFT),
                'price' => $selectedPin->price,
                'level' => $selectedPin->level,
                'is_used' => true,
            ]);

            // Trigger bonus sponsor dan bonus generasi
            Helper::upgrade($genUserPin);
            
            $generasiUsers[] = [
                'user' => $genUser,
                'package' => $packageName,
                'generation' => $gen,
                'name' => $genUserName
            ];
            
            $currentSponsor = $genUser;
        }

        $this->command->info('Data dummy untuk Bonus Generasi telah dibuat:');
        $this->command->info('- User ' . $rootUserName . ' (username: root_generasi, Platinum) sebagai root');
        foreach ($generasiUsers as $genData) {
            $this->command->info('- Generasi ' . $genData['generation'] . ': ' . $genData['name'] . ' (username: ' . $genData['user']->username . ', ' . $genData['package'] . ')');
        }
        $this->command->info('');
        $this->command->info('Total: 12 generasi (lebih dari 10 generasi untuk test semua kondisi)');

        // Data dummy untuk Bonus Monoleg 9%
        $this->command->info('');
        $this->command->info('Membuat data dummy untuk Bonus Monoleg 9%...');
        
        $monolegRootName = $randomName();
        $monolegRoot = User::create([
            'name' => $monolegRootName,
            'email' => $randomEmail($monolegRootName),
            'password' => bcrypt('password'),
            'type' => 'member',
            'username' => 'monoleg_root',
            'phone' => $randomPhone(),
            'ktp' => $randomKtp(),
            'npwp' => $randomNpwp(),
            'bank_id' => 1,
            'bank_account' => $randomBankAccount(),
            'bank_as' => $monolegRootName,
            'sponsor_id' => $admin->id,
            'upline_id' => $admin->id, // Default: sama dengan sponsor_id
        ]);

        $monolegRoot->addresses()->create([
            'name' => 'Rumah',
            'address' => 'Jl. Monoleg Root No. 1',
            'province_id' => 5,
            'city_id' => 501,
            'subdistrict_id' => 6988,
            'is_active' => true,
        ]);

        // Root menggunakan paket Gold
        $monolegRootPin = $monolegRoot->userPin()->create([
            'pin_id' => $goldPin->id,
            'name' => 'Gold',
            'code' => 'MLROOT',
            'price' => $goldPin->price,
            'level' => $goldPin->level,
            'is_used' => true,
        ]);

        // Buat sponsor pertama di kiri (wajib)
        $leftSponsorName = $randomName();
        $leftSponsor = User::create([
            'name' => $leftSponsorName,
            'email' => $randomEmail($leftSponsorName),
            'password' => bcrypt('password'),
            'type' => 'member',
            'username' => 'monoleg_left',
            'phone' => $randomPhone(),
            'ktp' => $randomKtp(),
            'npwp' => $randomNpwp(),
            'bank_id' => 1,
            'bank_account' => $randomBankAccount(),
            'bank_as' => $leftSponsorName,
            'sponsor_id' => $monolegRoot->id,
            'upline_id' => $monolegRoot->id, // Default: sama dengan sponsor_id
            'placement_side' => 'left',
        ]);

        $leftSponsor->addresses()->create([
            'name' => 'Rumah',
            'address' => 'Jl. Monoleg Left No. 1',
            'province_id' => 5,
            'city_id' => 501,
            'subdistrict_id' => 6988,
            'is_active' => true,
        ]);

        $leftSponsorPin = $leftSponsor->userPin()->create([
            'pin_id' => $goldPin->id,
            'name' => 'Gold',
            'code' => 'MLLEFT',
            'price' => $goldPin->price,
            'level' => $goldPin->level,
            'is_used' => true,
        ]);

        Helper::upgrade($leftSponsorPin);

        // Buat user di leg kanan untuk test bonus monoleg
        $rightUserName = $randomName();
        $rightUser = User::create([
            'name' => $rightUserName,
            'email' => $randomEmail($rightUserName),
            'password' => bcrypt('password'),
            'type' => 'member',
            'username' => 'monoleg_right',
            'phone' => $randomPhone(),
            'ktp' => $randomKtp(),
            'npwp' => $randomNpwp(),
            'bank_id' => 1,
            'bank_account' => $randomBankAccount(),
            'bank_as' => $rightUserName,
            'sponsor_id' => $monolegRoot->id,
            'upline_id' => $monolegRoot->id, // Default: sama dengan sponsor_id
            'placement_side' => 'right',
        ]);

        $rightUser->addresses()->create([
            'name' => 'Rumah',
            'address' => 'Jl. Monoleg Right No. 1',
            'province_id' => 5,
            'city_id' => 501,
            'subdistrict_id' => 6988,
            'is_active' => true,
        ]);

        $rightUserPin = $rightUser->userPin()->create([
            'pin_id' => $platinumPin->id,
            'name' => 'Platinum',
            'code' => 'MLRIGH',
            'price' => $platinumPin->price,
            'level' => $platinumPin->level,
            'is_used' => true,
        ]);

        Helper::upgrade($rightUserPin);

        $this->command->info('Data dummy untuk Bonus Monoleg 9% telah dibuat:');
        $this->command->info('- User ' . $monolegRootName . ' (username: monoleg_root, Gold) sebagai root');
        $this->command->info('- User ' . $leftSponsorName . ' (username: monoleg_left, Gold) di kiri - akan trigger bonus sponsor');
        $this->command->info('- User ' . $rightUserName . ' (username: monoleg_right, Platinum) di kanan - akan trigger bonus monoleg 9%');

        // Data dummy untuk Profit Sharing 5% (Platinum Perdana)
        $this->command->info('');
        $this->command->info('Membuat data dummy untuk Profit Sharing 5%...');
        
        $profitSharingUserName = $randomName();
        $profitSharingUser = User::create([
            'name' => $profitSharingUserName,
            'email' => $randomEmail($profitSharingUserName),
            'password' => bcrypt('password'),
            'type' => 'member',
            'username' => 'profit_sharing_test',
            'phone' => $randomPhone(),
            'ktp' => $randomKtp(),
            'npwp' => $randomNpwp(),
            'bank_id' => 1,
            'bank_account' => $randomBankAccount(),
            'bank_as' => $profitSharingUserName,
            'sponsor_id' => $admin->id,
            'upline_id' => $admin->id, // Default: sama dengan sponsor_id
        ]);

        $profitSharingUser->addresses()->create([
            'name' => 'Rumah',
            'address' => 'Jl. Profit Sharing No. 1',
            'province_id' => 5,
            'city_id' => 501,
            'subdistrict_id' => 6988,
            'is_active' => true,
        ]);

        // User menggunakan paket Platinum (perdana) - akan trigger profit sharing
        $profitSharingUserPin = $profitSharingUser->userPin()->create([
            'pin_id' => $platinumPin->id,
            'name' => 'Platinum',
            'code' => 'PSHARE',
            'price' => $platinumPin->price,
            'level' => $platinumPin->level,
            'is_used' => true,
        ]);

        Helper::upgrade($profitSharingUserPin);

        $this->command->info('Data dummy untuk Profit Sharing 5% telah dibuat:');
        $this->command->info('- User ' . $profitSharingUserName . ' (username: profit_sharing_test, Platinum) - Aktivasi perdana Platinum');

        // Data dummy untuk Power Plus (2 tim aktif kiri & kanan)
        $this->command->info('');
        $this->command->info('Membuat data dummy untuk Bonus Power Plus...');
        
        $powerPlusRootName = $randomName();
        $powerPlusRoot = User::create([
            'name' => $powerPlusRootName,
            'email' => $randomEmail($powerPlusRootName),
            'password' => bcrypt('password'),
            'type' => 'member',
            'username' => 'powerplus_root',
            'phone' => $randomPhone(),
            'ktp' => $randomKtp(),
            'npwp' => $randomNpwp(),
            'bank_id' => 1,
            'bank_account' => $randomBankAccount(),
            'bank_as' => $powerPlusRootName,
            'sponsor_id' => $admin->id,
            'upline_id' => $admin->id, // Default: sama dengan sponsor_id
        ]);

        $powerPlusRoot->addresses()->create([
            'name' => 'Rumah',
            'address' => 'Jl. Power Plus Root No. 1',
            'province_id' => 5,
            'city_id' => 501,
            'subdistrict_id' => 6988,
            'is_active' => true,
        ]);

        $powerPlusRootPin = $powerPlusRoot->userPin()->create([
            'pin_id' => $platinumPin->id,
            'name' => 'Platinum',
            'code' => 'PPROOT',
            'price' => $platinumPin->price,
            'level' => $platinumPin->level,
            'is_used' => true,
        ]);

        // Buat 2 tim aktif (kiri & kanan)
        for ($i = 1; $i <= 2; $i++) {
            $side = $i == 1 ? 'left' : 'right';
            $teamUserName = $randomName();
            $teamUser = User::create([
                'name' => $teamUserName,
                'email' => $randomEmail($teamUserName),
                'password' => bcrypt('password'),
                'type' => 'member',
                'username' => 'powerplus_' . $side . '_' . $i,
                'phone' => $randomPhone(),
                'ktp' => $randomKtp(),
                'npwp' => $randomNpwp(),
                'bank_id' => 1,
                'bank_account' => $randomBankAccount(),
                'bank_as' => $teamUserName,
                'sponsor_id' => $powerPlusRoot->id,
                'upline_id' => $powerPlusRoot->id, // Default: sama dengan sponsor_id
                'placement_side' => $side,
            ]);

            $teamUser->addresses()->create([
                'name' => 'Rumah',
                'address' => 'Jl. Power Plus ' . ucfirst($side) . ' No. ' . $i,
                'province_id' => 5,
                'city_id' => 501,
                'subdistrict_id' => 6988,
                'is_active' => true,
            ]);

            $teamUserPin = $teamUser->userPin()->create([
                'pin_id' => $goldPin->id,
                'name' => 'Gold',
                'code' => 'PP' . strtoupper($side) . $i,
                'price' => $goldPin->price,
                'level' => $goldPin->level,
                'is_used' => true,
            ]);

            Helper::upgrade($teamUserPin);
        }

        $this->command->info('Data dummy untuk Bonus Power Plus telah dibuat:');
        $this->command->info('- User ' . $powerPlusRootName . ' (username: powerplus_root, Platinum) sebagai root');
        $this->command->info('- 2 tim aktif (kiri & kanan) telah dibuat');

        // Data dummy untuk Tabungan Umroh/Trip (3 tim aktif)
        $this->command->info('');
        $this->command->info('Membuat data dummy untuk Tabungan Umroh/Trip...');
        
        $umrohRootName = $randomName();
        $umrohRoot = User::create([
            'name' => $umrohRootName,
            'email' => $randomEmail($umrohRootName),
            'password' => bcrypt('password'),
            'type' => 'member',
            'username' => 'umroh_root',
            'phone' => $randomPhone(),
            'ktp' => $randomKtp(),
            'npwp' => $randomNpwp(),
            'bank_id' => 1,
            'bank_account' => $randomBankAccount(),
            'bank_as' => $umrohRootName,
            'sponsor_id' => $admin->id,
            'upline_id' => $admin->id, // Default: sama dengan sponsor_id
        ]);

        $umrohRoot->addresses()->create([
            'name' => 'Rumah',
            'address' => 'Jl. Umroh Root No. 1',
            'province_id' => 5,
            'city_id' => 501,
            'subdistrict_id' => 6988,
            'is_active' => true,
        ]);

        $umrohRootPin = $umrohRoot->userPin()->create([
            'pin_id' => $platinumPin->id,
            'name' => 'Platinum',
            'code' => 'UMROOT',
            'price' => $platinumPin->price,
            'level' => $platinumPin->level,
            'is_used' => true,
        ]);

        // Buat 3 tim aktif untuk memenuhi syarat tabungan umroh
        for ($i = 1; $i <= 3; $i++) {
            $teamUserName = $randomName();
            $teamUser = User::create([
                'name' => $teamUserName,
                'email' => $randomEmail($teamUserName),
                'password' => bcrypt('password'),
                'type' => 'member',
                'username' => 'umroh_team_' . $i,
                'phone' => $randomPhone(),
                'ktp' => $randomKtp(),
                'npwp' => $randomNpwp(),
                'bank_id' => 1,
                'bank_account' => $randomBankAccount(),
                'bank_as' => $teamUserName,
                'sponsor_id' => $umrohRoot->id,
                'upline_id' => $umrohRoot->id, // Default: sama dengan sponsor_id
                'placement_side' => $i == 1 ? 'left' : ($i == 2 ? 'right' : 'left'),
            ]);

            $teamUser->addresses()->create([
                'name' => 'Rumah',
                'address' => 'Jl. Umroh Team No. ' . $i,
                'province_id' => 5,
                'city_id' => 501,
                'subdistrict_id' => 6988,
                'is_active' => true,
            ]);

            $teamUserPin = $teamUser->userPin()->create([
                'pin_id' => $goldPin->id,
                'name' => 'Gold',
                'code' => 'UMTEAM' . $i,
                'price' => $goldPin->price,
                'level' => $goldPin->level,
                'is_used' => true,
            ]);

            Helper::upgrade($teamUserPin);
        }

        $this->command->info('Data dummy untuk Tabungan Umroh/Trip telah dibuat:');
        $this->command->info('- User ' . $umrohRootName . ' (username: umroh_root, Platinum) sebagai root');
        $this->command->info('- 3 tim aktif telah dibuat (minimal syarat untuk tabungan umroh)');

        // Data dummy untuk Masa Aktif & Maintenance
        $this->command->info('');
        $this->command->info('Membuat data dummy untuk Masa Aktif & Maintenance...');
        
        // User Gold dengan masa aktif 45 hari
        $activeGoldName = $randomName();
        $activeGold = User::create([
            'name' => $activeGoldName,
            'email' => $randomEmail($activeGoldName),
            'password' => bcrypt('password'),
            'type' => 'member',
            'username' => 'active_gold',
            'phone' => $randomPhone(),
            'ktp' => $randomKtp(),
            'npwp' => $randomNpwp(),
            'bank_id' => 1,
            'bank_account' => $randomBankAccount(),
            'bank_as' => $activeGoldName,
            'sponsor_id' => $admin->id,
            'upline_id' => $admin->id, // Default: sama dengan sponsor_id
            'active_until' => Carbon::now()->addDays(45),
            'active_days_initial' => 45,
            'is_active' => true,
        ]);

        $activeGold->addresses()->create([
            'name' => 'Rumah',
            'address' => 'Jl. Active Gold No. 1',
            'province_id' => 5,
            'city_id' => 501,
            'subdistrict_id' => 6988,
            'is_active' => true,
        ]);

        $activeGoldPin = $activeGold->userPin()->create([
            'pin_id' => $goldPin->id,
            'name' => 'Gold',
            'code' => 'ACTGOL',
            'price' => $goldPin->price,
            'level' => $goldPin->level,
            'is_used' => true,
        ]);

        // User Platinum dengan masa aktif 90 hari
        $activePlatinumName = $randomName();
        $activePlatinum = User::create([
            'name' => $activePlatinumName,
            'email' => $randomEmail($activePlatinumName),
            'password' => bcrypt('password'),
            'type' => 'member',
            'username' => 'active_platinum',
            'phone' => $randomPhone(),
            'ktp' => $randomKtp(),
            'npwp' => $randomNpwp(),
            'bank_id' => 1,
            'bank_account' => $randomBankAccount(),
            'bank_as' => $activePlatinumName,
            'sponsor_id' => $admin->id,
            'upline_id' => $admin->id, // Default: sama dengan sponsor_id
            'active_until' => Carbon::now()->addDays(90),
            'active_days_initial' => 90,
            'is_active' => true,
        ]);

        $activePlatinum->addresses()->create([
            'name' => 'Rumah',
            'address' => 'Jl. Active Platinum No. 1',
            'province_id' => 5,
            'city_id' => 501,
            'subdistrict_id' => 6988,
            'is_active' => true,
        ]);

        $activePlatinumPin = $activePlatinum->userPin()->create([
            'pin_id' => $platinumPin->id,
            'name' => 'Platinum',
            'code' => 'ACTPLA',
            'price' => $platinumPin->price,
            'level' => $platinumPin->level,
            'is_used' => true,
        ]);

        $this->command->info('Data dummy untuk Masa Aktif & Maintenance telah dibuat:');
        $this->command->info('- User ' . $activeGoldName . ' (username: active_gold, Gold) - Masa aktif 45 hari');
        $this->command->info('- User ' . $activePlatinumName . ' (username: active_platinum, Platinum) - Masa aktif 90 hari');

        // Data dummy untuk Push-up Mechanism Bonus Generasi
        $this->command->info('');
        $this->command->info('Membuat data dummy untuk Push-up Mechanism Bonus Generasi...');
        
        // Push-up: Platinum di bawah Gold
        $pushUpPlatRootName = $randomName();
        $pushUpPlatRoot = User::create([
            'name' => $pushUpPlatRootName,
            'email' => $randomEmail($pushUpPlatRootName),
            'password' => bcrypt('password'),
            'type' => 'member',
            'username' => 'pushup_plat_root',
            'phone' => $randomPhone(),
            'ktp' => $randomKtp(),
            'npwp' => $randomNpwp(),
            'bank_id' => 1,
            'bank_account' => $randomBankAccount(),
            'bank_as' => $pushUpPlatRootName,
            'sponsor_id' => $admin->id,
            'upline_id' => $admin->id, // Default: sama dengan sponsor_id
        ]);

        $pushUpPlatRoot->addresses()->create([
            'name' => 'Rumah',
            'address' => 'Jl. Push-up Platinum Root No. 1',
            'province_id' => 5,
            'city_id' => 501,
            'subdistrict_id' => 6988,
            'is_active' => true,
        ]);

        $pushUpPlatRootPin = $pushUpPlatRoot->userPin()->create([
            'pin_id' => $platinumPin->id,
            'name' => 'Platinum',
            'code' => 'PUPROOT',
            'price' => $platinumPin->price,
            'level' => $platinumPin->level,
            'is_used' => true,
        ]);

        // Gold di bawah Platinum
        $pushUpGoldName = $randomName();
        $pushUpGold = User::create([
            'name' => $pushUpGoldName,
            'email' => $randomEmail($pushUpGoldName),
            'password' => bcrypt('password'),
            'type' => 'member',
            'username' => 'pushup_gold',
            'phone' => $randomPhone(),
            'ktp' => $randomKtp(),
            'npwp' => $randomNpwp(),
            'bank_id' => 1,
            'bank_account' => $randomBankAccount(),
            'bank_as' => $pushUpGoldName,
            'sponsor_id' => $pushUpPlatRoot->id,
            'upline_id' => $pushUpPlatRoot->id, // Default: sama dengan sponsor_id
        ]);

        $pushUpGold->addresses()->create([
            'name' => 'Rumah',
            'address' => 'Jl. Push-up Gold No. 1',
            'province_id' => 5,
            'city_id' => 501,
            'subdistrict_id' => 6988,
            'is_active' => true,
        ]);

        $pushUpGoldPin = $pushUpGold->userPin()->create([
            'pin_id' => $goldPin->id,
            'name' => 'Gold',
            'code' => 'PUGOLD',
            'price' => $goldPin->price,
            'level' => $goldPin->level,
            'is_used' => true,
        ]);

        Helper::upgrade($pushUpGoldPin);

        // Platinum di bawah Gold (akan trigger push-up ke Platinum root)
        $pushUpPlatBelowName = $randomName();
        $pushUpPlatBelow = User::create([
            'name' => $pushUpPlatBelowName,
            'email' => $randomEmail($pushUpPlatBelowName),
            'password' => bcrypt('password'),
            'type' => 'member',
            'username' => 'pushup_plat_below',
            'phone' => $randomPhone(),
            'ktp' => $randomKtp(),
            'npwp' => $randomNpwp(),
            'bank_id' => 1,
            'bank_account' => $randomBankAccount(),
            'bank_as' => $pushUpPlatBelowName,
            'sponsor_id' => $pushUpGold->id,
            'upline_id' => $pushUpGold->id, // Default: sama dengan sponsor_id
        ]);

        $pushUpPlatBelow->addresses()->create([
            'name' => 'Rumah',
            'address' => 'Jl. Push-up Platinum Below No. 1',
            'province_id' => 5,
            'city_id' => 501,
            'subdistrict_id' => 6988,
            'is_active' => true,
        ]);

        $pushUpPlatBelowPin = $pushUpPlatBelow->userPin()->create([
            'pin_id' => $platinumPin->id,
            'name' => 'Platinum',
            'code' => 'PUPBELOW',
            'price' => $platinumPin->price,
            'level' => $platinumPin->level,
            'is_used' => true,
        ]);

        Helper::upgrade($pushUpPlatBelowPin);

        // Push-up: Akun tidak aktif 90 hari
        $pushUpActiveRootName = $randomName();
        $pushUpActiveRoot = User::create([
            'name' => $pushUpActiveRootName,
            'email' => $randomEmail($pushUpActiveRootName),
            'password' => bcrypt('password'),
            'type' => 'member',
            'username' => 'pushup_active_root',
            'phone' => $randomPhone(),
            'ktp' => $randomKtp(),
            'npwp' => $randomNpwp(),
            'bank_id' => 1,
            'bank_account' => $randomBankAccount(),
            'bank_as' => $pushUpActiveRootName,
            'sponsor_id' => $admin->id,
            'upline_id' => $admin->id, // Default: sama dengan sponsor_id
            'is_active' => true,
            'active_until' => Carbon::now()->addDays(30),
        ]);

        $pushUpActiveRoot->addresses()->create([
            'name' => 'Rumah',
            'address' => 'Jl. Push-up Active Root No. 1',
            'province_id' => 5,
            'city_id' => 501,
            'subdistrict_id' => 6988,
            'is_active' => true,
        ]);

        $pushUpActiveRootPin = $pushUpActiveRoot->userPin()->create([
            'pin_id' => $platinumPin->id,
            'name' => 'Platinum',
            'code' => 'PUAROOT',
            'price' => $platinumPin->price,
            'level' => $platinumPin->level,
            'is_used' => true,
        ]);

        // Inactive user (tidak aktif lebih dari 90 hari)
        $pushUpInactiveName = $randomName();
        $pushUpInactive = User::create([
            'name' => $pushUpInactiveName,
            'email' => $randomEmail($pushUpInactiveName),
            'password' => bcrypt('password'),
            'type' => 'member',
            'username' => 'pushup_inactive',
            'phone' => $randomPhone(),
            'ktp' => $randomKtp(),
            'npwp' => $randomNpwp(),
            'bank_id' => 1,
            'bank_account' => $randomBankAccount(),
            'bank_as' => $pushUpInactiveName,
            'sponsor_id' => $pushUpActiveRoot->id,
            'upline_id' => $pushUpActiveRoot->id, // Default: sama dengan sponsor_id
            'is_active' => false,
            'active_until' => Carbon::now()->subDays(100), // Tidak aktif lebih dari 90 hari
        ]);

        $pushUpInactive->addresses()->create([
            'name' => 'Rumah',
            'address' => 'Jl. Push-up Inactive No. 1',
            'province_id' => 5,
            'city_id' => 501,
            'subdistrict_id' => 6988,
            'is_active' => true,
        ]);

        $pushUpInactivePin = $pushUpInactive->userPin()->create([
            'pin_id' => $goldPin->id,
            'name' => 'Gold',
            'code' => 'PUINACT',
            'price' => $goldPin->price,
            'level' => $goldPin->level,
            'is_used' => true,
        ]);

        Helper::upgrade($pushUpInactivePin);
        // Update lagi untuk memastikan tidak aktif
        $pushUpInactive->update([
            'is_active' => false,
            'active_until' => Carbon::now()->subDays(100),
        ]);

        // User di bawah inactive (akan trigger push-up ke active root)
        $pushUpBelowInactiveName = $randomName();
        $pushUpBelowInactive = User::create([
            'name' => $pushUpBelowInactiveName,
            'email' => $randomEmail($pushUpBelowInactiveName),
            'password' => bcrypt('password'),
            'type' => 'member',
            'username' => 'pushup_below_inactive',
            'phone' => $randomPhone(),
            'ktp' => $randomKtp(),
            'npwp' => $randomNpwp(),
            'bank_id' => 1,
            'bank_account' => $randomBankAccount(),
            'bank_as' => $pushUpBelowInactiveName,
            'sponsor_id' => $pushUpInactive->id,
            'upline_id' => $pushUpInactive->id, // Default: sama dengan sponsor_id
        ]);

        $pushUpBelowInactive->addresses()->create([
            'name' => 'Rumah',
            'address' => 'Jl. Push-up Below Inactive No. 1',
            'province_id' => 5,
            'city_id' => 501,
            'subdistrict_id' => 6988,
            'is_active' => true,
        ]);

        $pushUpBelowInactivePin = $pushUpBelowInactive->userPin()->create([
            'pin_id' => $platinumPin->id,
            'name' => 'Platinum',
            'code' => 'PUBELOW',
            'price' => $platinumPin->price,
            'level' => $platinumPin->level,
            'is_used' => true,
        ]);

        Helper::upgrade($pushUpBelowInactivePin);

        $this->command->info('Data dummy untuk Push-up Mechanism telah dibuat:');
        $this->command->info('- Push-up Platinum di bawah Gold: pushup_plat_root (Platinum) → pushup_gold (Gold) → pushup_plat_below (Platinum)');
        $this->command->info('  → Bonus akan di-push-up ke pushup_plat_root');
        $this->command->info('- Push-up Akun Tidak Aktif 90 Hari: pushup_active_root (Platinum aktif) → pushup_inactive (Gold tidak aktif) → pushup_below_inactive (Platinum)');
        $this->command->info('  → Bonus akan di-push-up ke pushup_active_root');

        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('SEMUA DATA TESTING TELAH DIBUAT!');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('Ringkasan:');
        $this->command->info('1. Bonus Sponsor 15% - sponsor_test, gold_test, platinum_test');
        $this->command->info('2. Bonus Generasi 19% - root_generasi dengan 12 generasi');
        $this->command->info('3. Bonus Monoleg 9% - monoleg_root dengan leg kanan');
        $this->command->info('4. Profit Sharing 5% - profit_sharing_test (Platinum perdana)');
        $this->command->info('5. Bonus Power Plus - powerplus_root dengan 2 tim aktif');
        $this->command->info('6. Tabungan Umroh/Trip - umroh_root dengan 3 tim aktif');
        $this->command->info('7. Masa Aktif - active_gold (45 hari), active_platinum (90 hari)');
        $this->command->info('8. Push-up Mechanism - pushup_plat_root (Platinum di bawah Gold), pushup_active_root (Akun tidak aktif 90 hari)');
    }

    /**
     * Create dummy image for products
     * 
     * @param string $path_save Path prefix untuk menyimpan gambar
     * @param string $productName Nama produk untuk ditampilkan di gambar
     * @param int $width Lebar gambar (default 512)
     * @param int $height Tinggi gambar (default 512)
     * @return string Path gambar yang disimpan
     */
    public function createDummyImage($path_save, $productName, $width = 512, $height = 512)
    {
        // Generate warna background random
        $colors = [
            ['r' => 240, 'g' => 248, 'b' => 255], // Alice Blue
            ['r' => 255, 'g' => 250, 'b' => 240], // Floral White
            ['r' => 245, 'g' => 255, 'b' => 250], // Mint Cream
            ['r' => 255, 'g' => 245, 'b' => 238], // Seashell
            ['r' => 248, 'g' => 248, 'b' => 255], // Ghost White
            ['r' => 255, 'g' => 250, 'b' => 250], // Snow
            ['r' => 240, 'g' => 255, 'b' => 240], // Honeydew
            ['r' => 255, 'g' => 248, 'b' => 220], // Cornsilk
            ['r' => 230, 'g' => 240, 'b' => 250], // Light Blue
            ['r' => 250, 'g' => 240, 'b' => 230], // Light Peach
        ];
        $bgColor = $colors[array_rand($colors)];
        
        // Create image dengan background
        $image = Image::canvas($width, $height, "rgb({$bgColor['r']}, {$bgColor['g']}, {$bgColor['b']})");
        
        // Add border dengan warna lebih gelap
        $borderColor = "rgb(" . max(0, $bgColor['r'] - 40) . ", " . max(0, $bgColor['g'] - 40) . ", " . max(0, $bgColor['b'] - 40) . ")";
        $image->rectangle(10, 10, $width - 11, $height - 11, function ($draw) use ($borderColor) {
            $draw->border(3, $borderColor);
        });
        
        // Add simple geometric shape sebagai placeholder
        $centerX = $width / 2;
        $centerY = $height / 2;
        $shapeSize = min($width, $height) / 3;
        
        // Draw circle atau rectangle sebagai placeholder
        if (rand(0, 1)) {
            // Circle
            $image->circle($shapeSize, $centerX, $centerY, function ($draw) {
                $draw->background('#cccccc');
                $draw->border(2, '#999999');
            });
        } else {
            // Rectangle
            $rectSize = $shapeSize / 2;
            $image->rectangle(
                $centerX - $rectSize, 
                $centerY - $rectSize, 
                $centerX + $rectSize, 
                $centerY + $rectSize, 
                function ($draw) {
                    $draw->background('#cccccc');
                    $draw->border(2, '#999999');
                }
            );
        }
        
        // Save image
        $path = $path_save . date('YmdHis') . round(microtime(true) * 1000) . '.jpg';
        $image->save(storage_path('app/public/') . $path, 90);
        
        return $path;
    }
}

