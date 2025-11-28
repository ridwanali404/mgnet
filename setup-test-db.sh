#!/bin/bash

# Script untuk setup database testing
# Usage: ./setup-test-db.sh

echo "Setting up test database (mgnet_test)..."

# Baca konfigurasi dari .env
if [ ! -f .env ]; then
    echo "✗ File .env tidak ditemukan!"
    exit 1
fi

DB_HOST=$(grep "^DB_HOST" .env | cut -d '=' -f2 | tr -d ' ' | head -1)
DB_USERNAME=$(grep "^DB_USERNAME" .env | cut -d '=' -f2 | tr -d ' ' | head -1)
DB_PASSWORD=$(grep "^DB_PASSWORD" .env | cut -d '=' -f2 | tr -d ' ' | head -1)
DB_PORT=$(grep "^DB_PORT" .env | cut -d '=' -f2 | tr -d ' ' | head -1)

# Default values
DB_HOST=${DB_HOST:-127.0.0.1}
DB_PORT=${DB_PORT:-3306}

echo "Database Host: $DB_HOST"
echo "Database Port: $DB_PORT"
echo "Database User: $DB_USERNAME"

# Buat database testing
if [ -z "$DB_PASSWORD" ] || [ "$DB_PASSWORD" == "" ]; then
    echo "Mencoba membuat database tanpa password..."
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -e "CREATE DATABASE IF NOT EXISTS mgnet_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>&1
    MYSQL_EXIT=$?
else
    echo "Mencoba membuat database dengan password..."
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS mgnet_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>&1
    MYSQL_EXIT=$?
fi

if [ $? -eq 0 ]; then
    echo "✓ Database mgnet_test berhasil dibuat"
    
    # Run migrations untuk database testing
    echo "Running migrations untuk database testing..."
    DB_DATABASE=mgnet_test php artisan migrate --force
    
    if [ $? -eq 0 ]; then
        echo "✓ Migrations berhasil dijalankan"
        echo ""
        echo "✓ Setup database testing selesai!"
        echo "Sekarang Anda bisa menjalankan test dengan: php artisan test"
    else
        echo "✗ Gagal menjalankan migrations"
        exit 1
    fi
else
    echo "✗ Gagal membuat database. Pastikan:"
    echo "  1. Kredensial database benar di .env"
    echo "  2. User memiliki permission untuk membuat database"
    echo "  3. MySQL server sedang berjalan"
    exit 1
fi

