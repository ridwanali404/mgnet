# JADWAL PERHITUNGAN BONUS

Dokumen ini menjelaskan jadwal perhitungan bonus berdasarkan spesifikasi yang telah ditetapkan.

## 📅 BONUS YANG DIBAYAR HARIAN

Bonus-bonus berikut **dibayar/dihitung harian**:

### 1. Bonus Sponsor (bns spsonr) - 15%
- **Status**: Dibuat langsung saat upgrade/aktivasi
- **Lokasi**: `app/Traits/Helper.php` - fungsi `upgrade()`
- **Cara kerja**: 
  - Dibayar langsung saat user melakukan upgrade paket
  - Tidak perlu perhitungan harian terpisah karena langsung dibuat saat event upgrade

### 2. Bonus Generasi - 19%
- **Status**: Dibuat langsung saat upgrade/aktivasi
- **Lokasi**: `app/Traits/Helper.php` - fungsi `upgrade()`
- **Cara kerja**: 
  - Dibayar langsung saat user melakukan upgrade paket
  - Distribusi ke 10 generasi dengan persentase: 25%, 20%, 15%, 12%, 10%, 8%, 6%, 5%, 4%, 3%
  - Tidak perlu perhitungan harian terpisah karena langsung dibuat saat event upgrade

### 3. Bonus Monoleg - 9%
- **Status**: Dibuat langsung saat upgrade/aktivasi
- **Lokasi**: `app/Traits/Helper.php` - fungsi `upgrade()`
- **Cara kerja**: 
  - Dibayar langsung saat user melakukan upgrade paket Gold atau Platinum
  - Syarat: sponsor harus memiliki minimal 1 sponsor langsung
  - Tidak perlu perhitungan harian terpisah karena langsung dibuat saat event upgrade

### 4. Profit Sharing - 5%
- **Status**: Dihitung harian jika sudah Qualified
- **Lokasi**: `app/Traits/Helper.php` - fungsi `calculateProfitSharing()`
- **Scheduler**: `app/Console/Kernel.php` - setiap hari jam 23:30
- **Syarat Qualified**: 
  - User Platinum aktivasi perdana
  - Minimal 3 tim aktif
- **Cara kerja**: 
  - Dihitung setiap hari dari omzet perusahaan hari tersebut
  - Hanya untuk user yang sudah Qualified
  - Akumulasi maksimal Rp 22.500.000
  - Payout dilakukan bulanan melalui `payoutProfitSharing()`

### 5. Uang Trip (Tabungan Umroh) - 4%
- **Status**: Dihitung harian jika sudah Qualified, masuk tabel klaim
- **Lokasi**: `app/Traits/Helper.php` - fungsi `calculateUmrohTrip()`
- **Scheduler**: `app/Console/Kernel.php` - setiap hari jam 23:45
- **Syarat Qualified**: 
  - User Gold atau Platinum
  - Minimal 3 tim aktif
- **Cara kerja**: 
  - Dihitung setiap hari dari omzet perusahaan hari tersebut
  - Hanya untuk user yang sudah Qualified
  - Masuk ke tabel `umroh_trip_savings` (tabel klaim)
  - Akumulasi maksimal Rp 50.000.000 per tahun
  - Bisa diklaim untuk event perusahaan

---

## 📆 BONUS YANG DIHITUNG BULANAN

### 1. Bonus Power Plus - 8%
- **Status**: Dihitung bulanan
- **Lokasi**: `app/Traits/Helper.php` - fungsi `calculatePowerPlus()`
- **Scheduler**: `app/Console/Commands/RunMonthlyClosing.php` - saat monthly closing
- **Syarat Qualified**: 
  - User memiliki 2 tim aktif (kiri & kanan)
  - Omzet kaki kecil minimal 15.000 point (qualified 15k) atau 30.000 point (qualified 30k)
- **Cara kerja**: 
  - Dihitung setiap bulan berdasarkan omzet bulanan (akumulasi harian)
  - Total payout: 8% dari omzet perusahaan bulanan
  - Distribusi: 4% untuk qualified 15k, 4% untuk qualified 30k
  - Dibayar saat monthly closing

---

## 🔧 IMPLEMENTASI TEKNIS

### Scheduler Harian (`app/Console/Kernel.php`)
```php
// 23:00 - Pair bonus
Helper::pair(date('Y-m-d'));

// 23:30 - Profit Sharing (jika Qualified)
Helper::calculateProfitSharing(date('Y-m-d'));

// 23:45 - Uang Trip (jika Qualified)
Helper::calculateUmrohTrip(date('Y-m-d'));
```

### Monthly Closing (`app/Console/Commands/RunMonthlyClosing.php`)
```php
// Profit Sharing payout (bulanan)
Helper::payoutProfitSharing($month);

// Power Plus calculation (bulanan)
Helper::calculatePowerPlus($month);
```

---

## 📊 RINGKASAN

| Bonus | Frekuensi | Dibuat Saat | Syarat Qualified |
|-------|-----------|-------------|------------------|
| Bonus Sponsor | Harian | Upgrade | - |
| Bonus Generasi | Harian | Upgrade | - |
| Bonus Monoleg | Harian | Upgrade | Minimal 1 sponsor langsung |
| Profit Sharing | Harian | Scheduler | Platinum perdana + 3 tim aktif |
| Uang Trip | Harian | Scheduler | Gold/Platinum + 3 tim aktif |
| Power Plus | Bulanan | Monthly Closing | 2 tim aktif + omzet minimal |

---

## 📝 CATATAN PENTING

1. **Bonus Sponsor, Generasi, Monoleg**: Dibuat langsung saat upgrade, tidak perlu scheduler harian
2. **Profit Sharing**: Dihitung harian tapi hanya untuk yang Qualified, payout bulanan
3. **Uang Trip**: Dihitung harian untuk yang Qualified, masuk tabel klaim
4. **Power Plus**: Hanya dihitung bulanan, bukan harian

