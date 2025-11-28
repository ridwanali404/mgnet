# Setup Database Testing

## Konfigurasi Database Testing

Sistem testing menggunakan database terpisah `mgnet_test` agar tidak mengganggu database utama.

## Setup Database Testing

### 1. Buat Database Testing

Jalankan script setup:

```bash
./setup-test-db.sh
```

Atau manual:

```bash
# Baca kredensial dari .env
DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2)
DB_USERNAME=$(grep DB_USERNAME .env | cut -d '=' -f2)
DB_PASSWORD=$(grep DB_PASSWORD .env | cut -d '=' -f2)

# Buat database
mysql -h "$DB_HOST" -u "$DB_USERNAME" -p"$DB_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS mgnet_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
DB_DATABASE=mgnet_test php artisan migrate --force
```

### 2. Konfigurasi

Database testing sudah dikonfigurasi di:
- `phpunit.xml` - Set `DB_DATABASE=mgnet_test` untuk environment testing
- `tests/TestCase.php` - Otomatis menggunakan database `mgnet_test` saat testing

### 3. Menjalankan Test

```bash
# Run semua test
php artisan test

# Run test tertentu
php artisan test --filter=BonusTest

# Run dengan refresh database
php artisan test --filter=BonusTest --refresh
```

## Catatan

- Database `mgnet_test` akan di-refresh setiap kali test dijalankan (menggunakan `RefreshDatabase` trait)
- Database utama dari `.env` tidak akan terganggu
- Pastikan database `mgnet_test` sudah dibuat sebelum menjalankan test

