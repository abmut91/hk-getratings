#!/bin/bash
# Script debug yang diperbaiki
echo "=== 1. Cek Process yang menggunakan Port 80 ==="
# Gunakan ss karena netstat mungkin tidak ada
ss -tulnp | grep :80
echo ""

echo "=== 2. Cek Konfigurasi PM2 Saat Ini ==="
pm2 list
pm2 describe hk-getratings | grep "PORT"
echo ""

echo "=== 3. Cek Error Log Aplikasi (Pastikan PORT 80 terpakai) ==="
# Baca log dari file langsung untuk memastikan
tail -n 20 ~/.pm2/logs/hk-getratings-out.log
tail -n 20 ~/.pm2/logs/hk-getratings-error.log
echo ""

echo "=== 4. Test Koneksi Lokal ke Port 80 ==="
curl -v http://localhost:80 2>&1 | head -n 10
