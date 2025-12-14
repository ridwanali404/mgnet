<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Address;
use App\Models\Province;
use App\Models\City;
use App\Models\Subdistrict;

class UpdateCompanyAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder untuk update alamat perusahaan/gudang dari Jogja ke Bandung
     * Alamat baru: Cijerah, Kec. Bandung Kulon, Kota Bandung, Jawa Barat 40213
     * 
     * @return void
     */
    public function run()
    {
        $this->command->info('Memulai update alamat perusahaan/gudang...');
        
        // Cari user admin
        $adminUser = User::where('type', 'admin')->first();
        
        if (!$adminUser) {
            $this->command->error('User admin tidak ditemukan!');
            return;
        }
        
        $this->command->info("User admin ditemukan: {$adminUser->username} (ID: {$adminUser->id})");
        
        // Cari provinsi Jawa Barat
        $jawaBarat = Province::where('province', 'LIKE', '%Jawa Barat%')
            ->orWhere('province', 'LIKE', '%Jawa%Barat%')
            ->first();
        
        if (!$jawaBarat) {
            $this->command->error('Provinsi Jawa Barat tidak ditemukan!');
            return;
        }
        
        $this->command->info("Provinsi ditemukan: {$jawaBarat->province} (ID: {$jawaBarat->province_id})");
        
        // Cari Kota Bandung
        $bandungCity = City::where('province_id', $jawaBarat->province_id)
            ->where(function($q) {
                $q->where('city_name', 'LIKE', '%Bandung%')
                  ->where('type', 'Kota');
            })
            ->first();
        
        if (!$bandungCity) {
            $this->command->error('Kota Bandung tidak ditemukan!');
            return;
        }
        
        $this->command->info("Kota ditemukan: {$bandungCity->type} {$bandungCity->city_name} (ID: {$bandungCity->city_id})");
        
        // Cari Kecamatan Bandung Kulon
        $bandungKulon = Subdistrict::where('city_id', $bandungCity->city_id)
            ->where('subdistrict_name', 'LIKE', '%Bandung Kulon%')
            ->first();
        
        if (!$bandungKulon) {
            $this->command->error('Kecamatan Bandung Kulon tidak ditemukan!');
            return;
        }
        
        $this->command->info("Kecamatan ditemukan: {$bandungKulon->subdistrict_name} (ID: {$bandungKulon->subdistrict_id})");
        
        // Cari Subdistrict Cijerah di Bandung Kulon
        $cijerah = Subdistrict::where('city_id', $bandungCity->city_id)
            ->where('subdistrict_name', 'LIKE', '%Cijerah%')
            ->first();
        
        if (!$cijerah) {
            $this->command->warn('Subdistrict Cijerah tidak ditemukan, menggunakan Kecamatan Bandung Kulon sebagai fallback');
            $cijerah = $bandungKulon;
        } else {
            $this->command->info("Subdistrict ditemukan: {$cijerah->subdistrict_name} (ID: {$cijerah->subdistrict_id})");
        }
        
        // Alamat baru
        $newAddress = 'Cijerah, Kec. Bandung Kulon, Kota Bandung, Jawa Barat 40213';
        
        // Cek apakah user admin sudah punya address
        $adminAddress = $adminUser->addresses()->first();
        
        DB::beginTransaction();
        try {
            if ($adminAddress) {
                // Update address yang sudah ada
                $oldAddress = $adminAddress->address;
                $oldSubdistrict = $adminAddress->subdistrict_id;
                
                $adminAddress->update([
                    'address' => $newAddress,
                    'province_id' => $jawaBarat->province_id,
                    'city_id' => $bandungCity->city_id,
                    'subdistrict_id' => $cijerah->subdistrict_id,
                    'postal_code' => '40213',
                    'is_active' => true,
                ]);
                
                $this->command->info("✓ Alamat berhasil diupdate!");
                $this->command->line("  Alamat lama: {$oldAddress}");
                $this->command->line("  Subdistrict ID lama: {$oldSubdistrict}");
                $this->command->line("  Alamat baru: {$newAddress}");
                $this->command->line("  Subdistrict ID baru: {$cijerah->subdistrict_id}");
            } else {
                // Buat address baru jika belum ada
                $adminAddress = Address::create([
                    'user_id' => $adminUser->id,
                    'name' => 'Gudang Perusahaan',
                    'recipient' => $adminUser->name ?? 'Admin',
                    'address' => $newAddress,
                    'province_id' => $jawaBarat->province_id,
                    'city_id' => $bandungCity->city_id,
                    'subdistrict_id' => $cijerah->subdistrict_id,
                    'postal_code' => '40213',
                    'is_active' => true,
                ]);
                
                $this->command->info("✓ Alamat baru berhasil dibuat!");
                $this->command->line("  Alamat: {$newAddress}");
                $this->command->line("  Subdistrict ID: {$cijerah->subdistrict_id}");
            }
            
            DB::commit();
            $this->command->info("\n✓ Update alamat perusahaan selesai!");
            $this->command->info("Alamat gudang sekarang: {$newAddress}");
            $this->command->info("Subdistrict ID untuk ongkir: {$cijerah->subdistrict_id}");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("✗ Error: " . $e->getMessage());
            $this->command->error("Stack trace: " . $e->getTraceAsString());
        }
    }
}

