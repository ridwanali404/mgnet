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
use App\Models\Blog;
use App\Models\Gallery;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

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

        Pin::create([
            'name' => 'Premium Member A',
            'type' => 'premium',
            'price' => 100000,
        ]);

        Pin::create([
            'name' => 'Premium Member B',
            'type' => 'premium',
            'price' => 200000,
        ]);

        Pin::create([
            'name' => 'MPC Reseller',
            'type' => 'premium',
            'price' => 100000,
        ]);

        $admin = User::create([
            'id' => 2,
            'image' => 0,
            'name' => 'Administrator',
            'email' => 'pt.bisnissuksesmulia@gmail.com',
            'password' => bcrypt('testing'),
            'type' => 'admin',
            'username' => 'admin',
            'phone' => '85201031214',
            'bank_id' => 1,
            'bank_account' => '123456789',
            'bank_as' => 'PT Bisnis Sukses Mulia',
        ]);

        $admin->addresses()->create([
            'name' => 'Office',
            'address' => 'Mantrijeron',
            'province_id' => 5,
            'city_id' => 501,
            'subdistrict_id' => 6988,
            'is_active' => true,
        ]);

        $admin->userPin()->create([
            'pin_id' => Pin::where('name', 'Free Member')->value('id'),
            'name' => 'Free Member',
            'code' => strtoupper(str_random(6)),
            'price' => 0,
        ]);

        // ======================

        // $admin = User::create([
        //     'name' => 'Administrator',
        //     'email' => 'admin@merahputihcoffee.com',
        //     'password' => bcrypt('testing'),
        //     'type' => 'admin',
        //     'username' => 'admin',
        //     'phone' => '85201031214',
        //     'bank_id' => 1,
        //     'bank_account' => '0891-01-033236-53-2',
        //     'bank_as' => 'PT JARAJAN GLOBAL INTERNASIONAL',
        // ]);

        // $admin->addresses()->create([
        //     'name' => 'Office',
        //     'address' => 'Mantrijeron',
        //     'province_id' => 5,
        //     'city_id' => 501,
        //     'subdistrict_id' => 6988,
        //     'is_active' => true,
        // ]);

        // $admin->userPin()->create([
        //     'pin_id' => $free->id,
        //     'name' => 'Free Member',
        //     'code' => strtoupper(str_random(6)),
        //     'price' => 0,
        // ]);

        // $member = User::create([
        //     'name' => 'Riska',
        //     'email' => 'riskaanwar24@gmail.com',
        //     'password' => bcrypt('testing'),
        //     'username' => 'riska',
        //     'phone' => '81907462695',
        //     'bank_id' => 4,
        //     'bank_account' => '0817096422',
        //     'bank_as' => 'Riska Anwar',
        //     'sponsor_id' => $admin->id,
        // ]);

        // $member->addresses()->create([
        //     'name' => 'Rumah',
        //     'address' => 'Semarang Tengah',
        //     'province_id' => 10,
        //     'city_id' => 399,
        //     'subdistrict_id' => 5509,
        //     'is_active' => true,
        // ]);

        // $member->userPin()->create([
        //     'pin_id' => $free->id,
        //     'name' => 'Free Member',
        //     'code' => strtoupper(str_random(6)),
        //     'price' => 0,
        // ]);

        // $user = User::create([
        //     'name' => 'Ridwan',
        //     'email' => 'ridwanali404@gmail.com',
        //     'password' => bcrypt('testing'),
        //     'username' => 'ridwan',
        //     'phone' => '82339144512',
        //     'bank_id' => 3,
        //     'bank_account' => '1610004079419',
        //     'bank_as' => 'Muhammad Ridwan Ali',
        //     'sponsor_id' => $member->id,
        // ]);

        // $user->addresses()->create([
        //     'name' => 'Rumah',
        //     'address' => 'Ampenan Selatan',
        //     'province_id' => 22,
        //     'city_id' => 276,
        //     'subdistrict_id' => 3876,
        //     'is_active' => true,
        // ]);

        // $user->userPin()->create([
        //     'pin_id' => $free->id,
        //     'name' => 'Free Member',
        //     'code' => strtoupper(str_random(6)),
        //     'price' => 0,
        // ]);

        $image_array = [];
        $path = $this->uploadImage('images/seed/product1/Kemasan-600x648.jpg', 512, 512, 'product/mpc_');
        array_push($image_array, $path);
        $path = $this->uploadImage('images/seed/product1/Long-Berry-1.jpg', 512, 512, 'product/mpc_');
        array_push($image_array, $path);
        $path = $this->uploadImage('images/seed/product1/Long-Berry-2-600x755 (1).jpg', 512, 512, 'product/mpc_');
        array_push($image_array, $path);
        $path = $this->uploadImage('images/seed/product1/Long-berry-2-600x755.jpg', 512, 512, 'product/mpc_');
        array_push($image_array, $path);

        // product
        Product::create([
            'name' => 'Arabica Long Berry',
            'price' => 160000,
            'price_member' => 150000,
            'weight' => 100,
            'desc' => "Long Berry adalah varian kopi Arabika yang bentuk fisiknya lebih panjang dari biji kopi pada umumnya. Long Berry yang ditanam didataran tinggi Gayo adalah tanaman kopi yang berusia kurang lebih 50-100 tahun, kopi tersebut adalah kopi yang ditanam oleh orang-orang Belanda dulu. Karakter kopi ini lebih ringan, tetapi mempunyai profile yang balance dan bercitarasa herbal seperti kebanyakan kopi Aceh pada umumnya atau karakteristik daerah.

            Gayo Long Berry memiliki kekentalan (body) menengah ringan dengan keasaman lembut. Sedikit aroma kacang segar dan bunga, pedas segar disertai dengan coklat dan karamel.

            Characteristics:

            The Flavor: Unique flavor, chocolaty, sweet in delicate, bolt bodies and clean cup.",
            'poin' => 20,
            'is_ro' => true,
            'images' => $image_array,
        ]);

        $image_array = [];
        $path = $this->uploadImage('images/seed/product2/Kemasan-600x648 (1).jpg', 512, 512, 'product/mpc_');
        array_push($image_array, $path);
        // $path = $this->uploadImage('images/seed/product2/Wild-Luwak-1-600x755.jpg', 512, 512, 'product/mpc_');
        // array_push($image_array, $path);
        // $path = $this->uploadImage('images/seed/product2/Wild-Luwak-2-600x755.jpg', 512, 512, 'product/mpc_');
        // array_push($image_array, $path);
        // $path = $this->uploadImage('images/seed/product2/Wild-Luwak-600x737.jpg', 512, 512, 'product/mpc_');
        // array_push($image_array, $path);

        Product::create([
            'name' => 'Arabica Luwak Liar',
            'price' => 260000,
            'price_member' => 250000,
            'weight' => 250,
            'desc' => "Kopi Luwak merupakan kopi dengan harga jual tertinggi di dunia.  Proses terbentuknya dan rasanya yang sangat unik menjadi alasan utama tingginya harga jual jenis ini. Pada dasarnya, kopi ini merupakan kopi jenis arabika, biji kopi ini kemuadian dimakan oleh luwak atau sejenis musang. Akan tetapi tidak semua bagian biji dapat dicerna oleh luwak. Bagian dalam biji kopi tersebut keluar bersama kotorannya. Karena telah bertahan lama di dalam pencernaan luwak, biji ini telah mengalami fermentasi singkat oleh bakteri pengurai alami di dalam perutnya yang memberi cita rasa tambahan yang unik.

            Charactersitics:

            Flavor : Caramel, Chocolate, Floral, Fruity Delicate, Low Acidity, Herbal, Spicy, Full Body, Extraordinary Smooth, and Long aroma aftertaste, Soft Choco, Medium (full city) roasted",
            'poin' => 40,
            'is_ro' => true,
            'images' => $image_array,
        ]);

        // MARKETPLACE

        // customize
        $photoFile = new UploadedFile(public_path('images/mpc.png'), 'mpc.png', $finfo->file(public_path('images/mpc.png')), File::size(public_path('images/mpc.png')), 0, false);
        $path = Storage::disk('public')->putFile('upload/customize', $photoFile);
        $customize = \App\Models\Customize::create(array(
            'title' => 'Camp Reseller',
            'meta_description' => 'PT Bisnis Sukses Mulia',
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
            'company' => 'PT Bisnis Sukses Mulia',
            'address_line_1' => 'Jl. Batusari No. 37a',
            'address_line_2' => 'Sanur, Denpasar Selatan, Bali 80228',
            'phone' => '0274 453001',
            'text' => 'Menjadi perusahaan network marketing modern terbesar di Indonesia yang siap mendukung juga mengembangkan produk UMKM , dan produk kebutuhan sehari-hari dengan mengedepankan teknologi digital terkini.',
            'email' => 'pt.bisnissuksesmulia@gmail.com',
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
