# IMPLEMENTATION PLAN - MARKETING PLAN PT.MG Net

## 📋 OVERVIEW
Dokumen ini berisi rencana implementasi untuk sistem bonus berdasarkan Marketing Plan PT.MG Net yang telah dibaca dari dokumen "MP Mg.docx".

---

## 🎯 BONUS YANG PERLU DIIMPLEMENTASIKAN

### 1. ✅ Bonus Sponsor 15% (Direct Sponsor Bonus)
**Status:** Sudah ada (sebagian)
**Lokasi:** `app/Traits/Helper.php` line 95-144

**Yang sudah ada:**
- Bonus sponsor menggunakan persentase (`bonus_sponsor_percent`)
- Sudah mendukung backward compatibility dengan nominal

**Yang perlu diperbaiki:**
- ✅ Pastikan persentase 15% untuk semua paket (Gold & Platinum)
- ✅ Status tidak aktif tetap mendapatkan bonus sponsor (perlu verifikasi)
- ✅ Penempatan jalur sponsorisasi bisa melebar atau kedalaman (sudah ada)

**Paket:**
- Silver (Rp 2.000.000) → 15% = Rp 300.000
- Platinum (Rp 15.000.000) → 15% = Rp 2.250.000

---

### 2. ✅ Bonus Generasi 19% (Unilevel Bonus)
**Status:** Sudah ada
**Lokasi:** `app/Traits/Helper.php` line 158-194

**Yang sudah ada:**
- ✅ Alokasi 19% dari harga paket
- ✅ Distribusi 10 generasi dengan persentase: 25%, 20%, 15%, 12%, 10%, 8%, 6%, 5%, 4%, 3%
- ✅ Hanya untuk Gold dan Platinum
- ✅ Berlaku repeat order

**Yang perlu ditambahkan:**
- ⚠️ **Push-up mechanism**: Jika di bawah Silver terdapat mitra Platinum, selisih generasi naik ke upline Platinum di atasnya
- ⚠️ **Push-up untuk akun tidak aktif 90 hari**: Perlu implementasi pengecekan status aktif

**Alokasi:**
- Platinum: Rp 15.000.000 × 19% = Rp 2.850.000
- Gold: Rp 2.000.000 × 19% = Rp 380.000

---

### 3. ⚠️ Bonus Monoleg 9%
**Status:** Sudah ada (sebagian)
**Lokasi:** `app/Traits/Helper.php` line 43-90, 145-155

**Yang sudah ada:**
- ✅ Sistem monoleg untuk BSM
- ✅ Komisi monoleg dari PIN PAKET RO

**Yang perlu ditambahkan:**
- ⚠️ **Bonus Monoleg 9%** untuk paket Gold & Platinum (bukan hanya BSM)
- ⚠️ Dari 1 jalur pertumbuhan (leg kanan) - unlimited depth
- ⚠️ Syarat: telah memiliki 1 sponsor langsung
- ⚠️ Berlaku repeat order

**Perhitungan:**
- 9% dari harga paket yang diupgrade
- Platinum: Rp 15.000.000 × 9% = Rp 1.350.000
- Gold: Rp 2.000.000 × 9% = Rp 180.000

---

### 4. ❌ Bonus Power Plus (8%)
**Status:** Belum ada
**Lokasi:** Perlu dibuat baru

**Spesifikasi:**
- Diberikan kepada mitra yang memiliki **2 tim aktif (kiri & kanan)** yang disponsori langsung
- Besaran bonus: **5% dari total payout perusahaan** (bukan 8% dari paket)
- Dibagikan kepada qualified members:
  - Omzet kaki kecil 15.000 point → 4% dibagi jumlah qualified
  - Omzet kaki kecil 30.000 point → 4% dibagi jumlah qualified

**Yang perlu dibuat:**
1. Migration untuk tracking omzet kiri/kanan per user
2. Logic untuk menghitung omzet kaki kecil
3. Logic untuk menentukan qualified members
4. Perhitungan 5% dari total payout perusahaan
5. Distribusi bonus ke qualified members

**Catatan:** Deskripsi di dokumen agak membingungkan (8% vs 5%), perlu konfirmasi dengan business owner.

---

### 5. ❌ Bonus Profit Sharing 5% (Khusus Platinum)
**Status:** Belum ada
**Lokasi:** Perlu dibuat baru

**Spesifikasi:**
- 5% dari total omzet perusahaan (dihitung harian, dibayar bulanan)
- Hanya untuk mitra yang **Aktivasi paket Perdana Platinum**
- **Tidak berlaku repeat order**
- Maksimal Rp 22.500.000 di wallet cashback
- Syarat akun harus aktif

**Yang perlu dibuat:**
1. Migration untuk tracking aktivasi paket perdana Platinum
2. Perhitungan harian 5% dari total omzet perusahaan
3. Akumulasi di wallet cashback (maksimal Rp 22.500.000)
4. Payout bulanan
5. Validasi akun aktif

---

### 6. ❌ Tabungan Umroh / Trip (4%)
**Status:** Belum ada
**Lokasi:** Perlu dibuat baru

**Spesifikasi:**
- 4% dari omzet perusahaan
- Minimal **3 tim aktif** (disponsori langsung)
- Berlaku untuk Gold & Platinum
- Akun wajib aktif
- Maksimal Rp 50.000.000 per tahun
- Tidak dapat diuangkan
- Bisa diklaim untuk event perusahaan

**Yang perlu dibuat:**
1. Migration untuk tracking tabungan umroh/trip
2. Perhitungan 4% dari omzet perusahaan
3. Validasi 3 tim aktif
4. Capping Rp 50.000.000 per tahun
5. Sistem klaim untuk event

---

## 📅 MASA AKTIF & MAINTENANCE

**Status:** Perlu verifikasi implementasi

**Spesifikasi:**
- Masa aktif awal:
  - Gold: 45 hari sejak tanggal join
  - Platinum: 90 hari sejak tanggal join
- Perpanjangan 45 hari berikutnya jika:
  1. Repeat order / automaintain minimal paket GOLD, atau
  2. Mensponsori 2 orang baru dalam masa aktif

**Yang perlu dibuat/diverifikasi:**
1. Migration untuk tracking masa aktif per user
2. Logic perpanjangan otomatis
3. Validasi repeat order minimal GOLD
4. Validasi sponsor 2 orang baru

---

## 👥 ATURAN SPONSOR & PENEMPATAN

**Status:** Perlu verifikasi implementasi

**Spesifikasi:**
1. Sponsor pertama **harus di sisi kiri**
2. Spillover dari upline tetap di jalur kiri
3. Setelah sponsor pertama terpenuhi, bebas menempatkan di kiri/kanan

**Yang perlu dibuat/diverifikasi:**
1. Validasi penempatan sponsor pertama di kiri
2. Logic spillover ke kiri
3. Logic penempatan bebas setelah sponsor pertama

---

## 🔄 ATURAN REPEAT ORDER PAKET

**Status:** Perlu verifikasi implementasi

**Spesifikasi:**
- PLATINUM: Rp 12.750.000
- GOLD: Rp 1.700.000
- Bisa belanja ulang full atau dari Automaintain

**Yang perlu dibuat/diverifikasi:**
1. Harga repeat order untuk Platinum dan Gold
2. Logic repeat order dari belanja ulang
3. Logic repeat order dari Automaintain

---

## 🗄️ DATABASE MIGRATIONS YANG DIPERLUKAN

### 1. Migration untuk Bonus Power Plus
```php
Schema::create('power_plus_qualifications', function (Blueprint $table) {
    $table->id();
    $table->bigInteger('user_id')->unsigned();
    $table->foreign('user_id')->references('id')->on('users');
    $table->bigInteger('left_omzet')->default(0); // Omzet kaki kiri
    $table->bigInteger('right_omzet')->default(0); // Omzet kaki kanan
    $table->bigInteger('smaller_leg_omzet')->default(0); // Omzet kaki kecil
    $table->boolean('is_qualified_15k')->default(false);
    $table->boolean('is_qualified_30k')->default(false);
    $table->date('date');
    $table->timestamps();
});
```

### 2. Migration untuk Profit Sharing
```php
Schema::create('profit_sharings', function (Blueprint $table) {
    $table->id();
    $table->bigInteger('user_id')->unsigned();
    $table->foreign('user_id')->references('id')->on('users');
    $table->boolean('is_perdana_platinum')->default(false);
    $table->bigInteger('daily_accumulation')->default(0);
    $table->bigInteger('monthly_total')->default(0);
    $table->bigInteger('wallet_cashback')->default(0); // Maksimal 22.500.000
    $table->date('date');
    $table->timestamps();
});
```

### 3. Migration untuk Tabungan Umroh/Trip
```php
Schema::create('umroh_trip_savings', function (Blueprint $table) {
    $table->id();
    $table->bigInteger('user_id')->unsigned();
    $table->foreign('user_id')->references('id')->on('users');
    $table->bigInteger('yearly_accumulation')->default(0); // Maksimal 50.000.000
    $table->bigInteger('claimed_amount')->default(0);
    $table->integer('active_teams_count')->default(0); // Minimal 3
    $table->year('year');
    $table->timestamps();
});
```

### 4. Migration untuk Masa Aktif
```php
Schema::table('users', function (Blueprint $table) {
    $table->date('active_until')->nullable();
    $table->integer('active_days_initial')->nullable(); // 45 untuk Gold, 90 untuk Platinum
});
```

---

## 🔧 IMPLEMENTATION TASKS

### Phase 1: Verifikasi & Perbaikan Bonus yang Sudah Ada
- [ ] Verifikasi Bonus Sponsor 15% sudah benar
- [ ] Implementasi push-up mechanism untuk Bonus Generasi
- [ ] Implementasi push-up untuk akun tidak aktif 90 hari
- [ ] Verifikasi status tidak aktif tetap dapat bonus sponsor

### Phase 2: Implementasi Bonus Monoleg 9%
- [ ] Tambahkan logic Bonus Monoleg 9% untuk Gold & Platinum
- [ ] Implementasi unlimited depth untuk leg kanan
- [ ] Validasi syarat 1 sponsor langsung
- [ ] Implementasi repeat order untuk monoleg

### Phase 3: Implementasi Bonus Power Plus
- [ ] Buat migration untuk tracking omzet kiri/kanan
- [ ] Implementasi perhitungan omzet kaki kecil
- [ ] Implementasi logic qualified members (15k & 30k point)
- [ ] Implementasi perhitungan 5% dari total payout perusahaan
- [ ] Implementasi distribusi bonus ke qualified members

### Phase 4: Implementasi Profit Sharing 5%
- [ ] Buat migration untuk profit sharing
- [ ] Implementasi tracking aktivasi paket perdana Platinum
- [ ] Implementasi perhitungan harian 5% dari omzet perusahaan
- [ ] Implementasi akumulasi wallet cashback (maksimal 22.5M)
- [ ] Implementasi payout bulanan
- [ ] Validasi akun aktif

### Phase 5: Implementasi Tabungan Umroh/Trip
- [ ] Buat migration untuk tabungan umroh/trip
- [ ] Implementasi perhitungan 4% dari omzet perusahaan
- [ ] Validasi 3 tim aktif
- [ ] Implementasi capping 50M per tahun
- [ ] Implementasi sistem klaim event

### Phase 6: Implementasi Masa Aktif & Maintenance
- [ ] Buat migration untuk masa aktif
- [ ] Implementasi logic masa aktif awal (45/90 hari)
- [ ] Implementasi perpanjangan otomatis
- [ ] Validasi repeat order minimal GOLD
- [ ] Validasi sponsor 2 orang baru

### Phase 7: Implementasi Aturan Sponsor & Penempatan
- [ ] Validasi sponsor pertama di kiri
- [ ] Implementasi logic spillover ke kiri
- [ ] Implementasi penempatan bebas setelah sponsor pertama

### Phase 8: Verifikasi Repeat Order
- [ ] Verifikasi harga repeat order (Platinum: 12.75M, Gold: 1.7M)
- [ ] Verifikasi logic repeat order dari belanja ulang
- [ ] Verifikasi logic repeat order dari Automaintain

---

## 📝 NOTES & CLARIFICATIONS NEEDED

1. **Bonus Power Plus**: Dokumen menyebutkan "8%" di judul tapi "5% dari total payout perusahaan" di deskripsi. Perlu konfirmasi.

2. **Paket Silver vs Gold**: Dokumen menyebutkan "Silver" di beberapa tempat, tapi di sistem ada "Gold". Perlu konfirmasi apakah:
   - Silver = Gold?
   - Atau ada paket Silver terpisah?

3. **Repeat Order GOLD**: Di dokumen tertulis "GOLG" (kemungkinan typo untuk "GOLD").

4. **Bonus Monoleg**: Perlu konfirmasi apakah bonus monoleg 9% berlaku untuk semua paket atau hanya tertentu.

5. **Total Payout Perusahaan**: Untuk Bonus Power Plus, perlu definisi jelas apa yang dimaksud "total payout perusahaan".

6. **Omzet Perusahaan**: Untuk Profit Sharing dan Tabungan Umroh, perlu definisi jelas bagaimana menghitung "omzet perusahaan".

---

## 🎯 PRIORITY ORDER

1. **HIGH PRIORITY:**
   - Verifikasi & perbaikan bonus yang sudah ada
   - Implementasi Bonus Monoleg 9%
   - Implementasi Masa Aktif & Maintenance

2. **MEDIUM PRIORITY:**
   - Implementasi Bonus Power Plus
   - Implementasi Profit Sharing 5%

3. **LOW PRIORITY:**
   - Implementasi Tabungan Umroh/Trip
   - Aturan Sponsor & Penempatan (jika belum ada)

---

## 📚 REFERENCE FILES

- Marketing Plan: `docs/MP Mg.md`
- Helper Trait: `app/Traits/Helper.php`
- Bonus Model: `app/Models/Bonus.php`
- Pin Model: `app/Models/Pin.php`
- User Model: `app/Models/User.php`

---

**Dibuat:** {{ date('Y-m-d') }}
**Versi:** 1.0

