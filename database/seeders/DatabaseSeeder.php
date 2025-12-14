<?php

use Illuminate\Database\Seeder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use App\Models\Province;
use App\Models\City;
use App\Models\Subdistrict;
use App\Models\Bank;
use App\Models\KeyValue;
use App\Models\User;
use App\Models\Product;
use App\Models\Pin;
use App\Models\UserPin;
use App\Models\Blog;
use App\Models\Gallery;
use App\Traits\Helper;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UserSeeder::class);
        DB::statement("SET foreign_key_checks=0");
        \App\Models\Customize::truncate();
        Gallery::truncate();
        Blog::truncate();
        \App\Models\Banner::truncate();
        \App\Models\AboutUs::truncate();
        \App\Models\ContactUs::truncate();

        Pin::truncate();
        Product::truncate();
        User::truncate();
        KeyValue::truncate();
        Bank::truncate();
        Subdistrict::truncate();
        City::truncate();
        Province::truncate();
        DB::statement("SET foreign_key_checks=1");

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $file = new Filesystem;
        // $file->cleanDirectory('storage/app/public');

        Storage::disk('public')->makeDirectory('product');

        // rajaongkir
        $path = 'database/db_rajaongkir.sql';
        DB::unprepared(file_get_contents($path));
        $this->command->info('rajaongkir seeded');

        // banks
        $path = 'database/bank.sql';
        DB::unprepared(file_get_contents($path));
        $this->command->info('bank seeded');

        // keyValue
        KeyValue::create([
            'key' => 'testimony',
            'value' => 'Testimoni Customer jarajan',
        ]);
        KeyValue::create([
            'key' => 'testimony_text',
            'value' => '"CR luar biasa"',
        ]);
        KeyValue::create([
            'key' => 'testimony_footer',
            'value' => 'PT Bisnis Sukses Mulia',
        ]);
        KeyValue::create([
            'key' => 'banner_title',
            'value' => 'Camp Reseller',
        ]);
        KeyValue::create([
            'key' => 'banner_subtitle',
            'value' => 'PT Bisnis Sukses Mulia',
        ]);

        KeyValue::create([
            'key' => 'weekly_admin_fee_percent',
            'value' => '5',
        ]);
        KeyValue::create([
            'key' => 'monthly_admin_fee',
            'value' => '10000',
        ]);
        for ($i=1; $i <= 10; $i++) {
            if ($i <= 1) {
                $value = 20;
            } else if ($i <= 2) {
                $value = 10;
            } else if ($i <= 4) {
                $value = 4;
            } else if ($i <= 6) {
                $value = 3;
            } else if ($i <= 8) {
                $value = 2;
            } else {
                $value = 1;
            }
            KeyValue::create([
                'key' => 'weekly_unilevel_'.$i,
                'value' => $value,
            ]);
        }
        for ($i=1; $i <= 10; $i++) {
            if ($i <= 1) {
                $value = 30;
            } else if ($i <= 2) {
                $value = 30;
            } else if ($i <= 4) {
                $value = 8;
            } else if ($i <= 6) {
                $value = 6;
            } else if ($i <= 8) {
                $value = 4;
            } else {
                $value = 2;
            }
            KeyValue::create([
                'key' => 'monthly_ro_unilevel_'.$i,
                'value' => $value,
            ]);
        }

        // pins
        $free = Pin::create([
            'name' => 'Free Member',
            'type' => 'free',
            'price' => 0,
        ]);

        // Paket Gold: Rp 2.000.000, 200 POIN
        Pin::create([
            'name' => 'Gold',
            'type' => 'premium',
            'price' => 2000000,
            'poin_pair' => 200,
            'voucher_umroh' => 2000000,
            'profit_sharing_percent' => 0,
            'profit_sharing_max' => 0,
            'trip_umroh_percent' => 4,
            'bonus_sponsor_percent' => 15,
            'monoleg_percent' => 9,
            'generasi_percent' => 19,
            'powerplus_percent' => 8,
            'is_generasi' => true,
            'level' => 1,
            'active_days' => 45, // Masa aktif 45 hari untuk Gold
            'ro_price' => 1700000, // Harga RO 1.7 juta untuk Gold
        ]);

        // Paket Platinum: Rp 15.000.000, 1500 POIN
        Pin::create([
            'name' => 'Platinum',
            'type' => 'premium',
            'price' => 15000000,
            'poin_pair' => 1500,
            'voucher_umroh' => 2000000,
            'profit_sharing_percent' => 5,
            'profit_sharing_max' => 22500000,
            'trip_umroh_percent' => 4,
            'bonus_sponsor_percent' => 15,
            'monoleg_percent' => 9,
            'generasi_percent' => 19,
            'powerplus_percent' => 8,
            'is_generasi' => true,
            'level' => 2,
            'active_days' => 90, // Masa aktif 90 hari untuk Platinum
            'ro_price' => 12750000, // Harga RO 12.75 juta untuk Platinum
        ]);

        // Pin Upgrade Gold ke Platinum: menggunakan selisih harga dan poin
        // Harga: Rp 13.000.000 (selisih Platinum - Gold)
        // Poin Pair: 1300 (selisih Platinum - Gold)
        // RO Price: Rp 11.050.000 (selisih Platinum - Gold)
        // Tidak memberikan bonus sponsor dan profit sharing
        Pin::create([
            'name' => 'Gold Upgrade Platinum',
            'type' => 'upgrade',
            'price' => 13000000, // Selisih: 15000000 - 2000000
            'poin_pair' => 1300, // Selisih: 1500 - 200
            'voucher_umroh' => 0, // Tidak ada tambahan voucher
            'profit_sharing_percent' => 0, // Tidak memberikan profit sharing
            'profit_sharing_max' => 0,
            'trip_umroh_percent' => 0,
            'bonus_sponsor_percent' => 0, // Tidak memberikan bonus sponsor
            'monoleg_percent' => 0,
            'generasi_percent' => 0,
            'powerplus_percent' => 0,
            'is_generasi' => false,
            'level' => 2, // Level Platinum
            'active_days' => 45, // Selisih: 90 - 45 (tambahan masa aktif)
            'ro_price' => 11050000, // Selisih: 12750000 - 1700000
        ]);

        $admin = User::create([
            'id' => 1,
            'image' => 0,
            'name' => 'Administrator',
            'email' => 'mg@mgnet.co.id',
            'password' => bcrypt('testing'),
            'type' => 'admin',
            'username' => 'admin',
            'phone' => '85201031214',
            'bank_id' => 1,
            'bank_account' => '123456789',
            'bank_as' => 'PT Bisnis Sukses Mulia',
            'upline_id' => null, // Admin tidak punya upline
        ]);

        // Cari provinsi Jawa Barat
        $jawaBarat = Province::where('province', 'LIKE', '%Jawa Barat%')
            ->orWhere('province', 'LIKE', '%Jawa%Barat%')
            ->first();
        
        if (!$jawaBarat) {
            $this->command->warn('Provinsi Jawa Barat tidak ditemukan, menggunakan default address');
            $admin->addresses()->create([
                'name' => 'Gudang Perusahaan',
                'address' => 'Cijerah, Kec. Bandung Kulon, Kota Bandung, Jawa Barat 40213',
                'province_id' => 5,
                'city_id' => 501,
                'subdistrict_id' => 6988,
                'postal_code' => '40213',
                'is_active' => true,
            ]);
        } else {
            // Cari Kota Bandung
            $bandungCity = City::where('province_id', $jawaBarat->province_id)
                ->where(function($q) {
                    $q->where('city_name', 'LIKE', '%Bandung%')
                      ->where('type', 'Kota');
                })
                ->first();
            
            if (!$bandungCity) {
                $this->command->warn('Kota Bandung tidak ditemukan, menggunakan default address');
                $admin->addresses()->create([
                    'name' => 'Gudang Perusahaan',
                    'address' => 'Cijerah, Kec. Bandung Kulon, Kota Bandung, Jawa Barat 40213',
                    'province_id' => $jawaBarat->province_id,
                    'city_id' => 501,
                    'subdistrict_id' => 6988,
                    'postal_code' => '40213',
                    'is_active' => true,
                ]);
            } else {
                // Cari Kecamatan Bandung Kulon
                $bandungKulon = Subdistrict::where('city_id', $bandungCity->city_id)
                    ->where('subdistrict_name', 'LIKE', '%Bandung Kulon%')
                    ->first();
                
                // Cari Subdistrict Cijerah di Bandung Kulon
                $cijerah = Subdistrict::where('city_id', $bandungCity->city_id)
                    ->where('subdistrict_name', 'LIKE', '%Cijerah%')
                    ->first();
                
                if (!$cijerah) {
                    $this->command->warn('Subdistrict Cijerah tidak ditemukan, menggunakan Kecamatan Bandung Kulon sebagai fallback');
                    $cijerah = $bandungKulon;
                }
                
                // Alamat baru: Cijerah, Kec. Bandung Kulon, Kota Bandung, Jawa Barat 40213
                $newAddress = 'Cijerah, Kec. Bandung Kulon, Kota Bandung, Jawa Barat 40213';
                
                // Tentukan subdistrict_id dengan fallback
                $subdistrictId = 6988; // Default fallback
                if ($cijerah) {
                    $subdistrictId = $cijerah->subdistrict_id;
                } elseif ($bandungKulon) {
                    $subdistrictId = $bandungKulon->subdistrict_id;
                }
                
                $admin->addresses()->create([
                    'name' => 'Gudang Perusahaan',
                    'recipient' => $admin->name ?? 'Admin',
                    'address' => $newAddress,
                    'province_id' => $jawaBarat->province_id,
                    'city_id' => $bandungCity->city_id,
                    'subdistrict_id' => $subdistrictId,
                    'postal_code' => '40213',
                    'is_active' => true,
                ]);
            }
        }

        $admin->userPin()->create([
            'pin_id' => Pin::where('name', 'Free Member')->value('id'),
            'name' => 'Free Member',
            'code' => strtoupper(str_random(6)),
            'price' => 0,
        ]);

        // MARKETPLACE

        // customize
        $photoFile = new UploadedFile(public_path('images/mpc.png'), 'mpc.png', $finfo->file(public_path('images/mpc.png')), File::size(public_path('images/mpc.png')), 0, false);
        $path = Storage::disk('public')->putFile('upload/customize', $photoFile);
        $customize = \App\Models\Customize::create(array(
            'title' => 'MG Network',
            'meta_description' => 'MG Network',
            'meta_keywords' => 'skin care, jual beli',
            'image' => 'storage/'.$path,
        ));

        // $galleries = factory(App\Gallery::class, 40)->create();
        // $blogs = factory(App\Blog::class, 12)->create();

        // banner
        $photoFile = new UploadedFile(public_path('img/banner/header_one.jpg'), 'user.png', $finfo->file(public_path('img/banner/header_one.jpg')), File::size(public_path('img/banner/header_one.jpg')), 0, false);
        $path = Storage::disk('public')->putFile('upload/banner', $photoFile);
        $b1 = \App\Models\Banner::create(array(
            'number' => 1,
            'image' => 'storage/'.$path
        ));
        $photoFile = new UploadedFile(public_path('img/banner/header_two.jpg'), 'user.png', $finfo->file(public_path('img/banner/header_two.jpg')), File::size(public_path('img/banner/header_two.jpg')), 0, false);
        $path = Storage::disk('public')->putFile('upload/banner', $photoFile);
        $b2 = \App\Models\Banner::create(array(
            'number' => 2,
            'image' => 'storage/'.$path
        ));

        // about us
        $photoFile = new UploadedFile(public_path('img/about_us/dashboard.png'), 'user.png', $finfo->file(public_path('img/about_us/dashboard.png')), File::size(public_path('img/about_us/dashboard.png')), 0, false);
        $path = Storage::disk('public')->putFile('upload/about_us', $photoFile);
        $about_us = \App\Models\AboutUs::create(array(
            'title' => 'Camp Reseller',
            'sub_title' => 'PT Bisnis Sukses Mulia',
            'text' => 'Menjadi perusahaan network marketing modern terbesar di Indonesia yang siap mendukung juga mengembangkan produk UMKM , dan produk kebutuhan sehari-hari dengan mengedepankan teknologi digital terkini.',
            'image' => 'storage/'.$path,
            'video' => 'https://www.youtube.com/embed/23FAGg8lAQQ'
        ));

        // contact us
        $contact_us = \App\Models\ContactUs::create(array(
            'company' => 'PT Mahkota Global Network',
            'address_line_1' => 'Jl. Batusari No. 37a',
            'address_line_2' => 'Sanur, Denpasar Selatan, Bali 80228',
            'phone' => '0274 000000',
            'text' => 'Menjadi perusahaan network marketing modern terbesar di Indonesia yang siap mendukung juga mengembangkan produk UMKM , dan produk kebutuhan sehari-hari dengan mengedepankan teknologi digital terkini.',
            'email' => 'support@mgnet.co.id',
            'instagram' => 'https://www.instagram.com/camp_reseller150k',
            'facebook' => 'https://www.facebook.com/groups/1233197023410185',
            'youtube' => 'https://www.youtube.com/channel/UCcTn-e0bRT1l7lns_ItfRMQ'
        ));
    }

    public function uploadImage($image_path, $width, $height, $path_save)
    {
        $image = Image::make(public_path($image_path));
        $path = $path_save . date('YmdHis') . round(microtime(true) * 1000) . '.jpg';
        $image->fit($width, $height, function($constraint){
            $constraint->upsize();
        })->save(storage_path('app/public/') . $path);
        return $path;
    }
}
