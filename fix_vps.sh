#!/bin/bash
# Script perbaikan otomatis untuk VPS
# Cara pakai: bash fix_vps.sh

echo "--- Memulai Perbaikan Otomatis ---"

# 1. Matikan Apache/Nginx yang sering memblokir port 80
echo "[1/4] Memastikan Port 80 kosong..."
service apache2 stop 2>/dev/null
systemctl disable apache2 2>/dev/null
service nginx stop 2>/dev/null
systemctl disable nginx 2>/dev/null

# 2. Konfigurasi ulang Port ke 80
echo "[2/4] Mengatur aplikasi ke Port 80..."
# Ganti 3000 jadi 80 di file config
sed -i 's/3000/80/g' ecosystem.config.cjs

# 3. Restart Aplikasi
echo "[3/4] Merestart aplikasi..."
pm2 restart hk-getratings --update-env
pm2 save

# 4. Tunggu sebentar
echo "Menunggu aplikasi startup (5 detik)..."
sleep 5

# 5. Cek koneksi internal
echo "[4/4] Verifikasi..."
STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://localhost)

if [ "$STATUS" == "200" ]; then
    echo ""
    echo "✅ SUKSES! Aplikasi berjalan normal di dalam VPS."
    echo "---------------------------------------------------"
    echo "Silakan buka link ini di browser Anda:"
    echo "http://74.208.64.184/setup?key=API_KEY_ANDA"
    echo "---------------------------------------------------"
    echo "PENTING: Jika link di atas masih loading terus/error,"
    echo "Berarti FIREWALL IONOS (Cloud Panel) memblokir akses."
    echo "Solusi: Login ke website IONOS -> Server -> Firewall -> Tambahkan Rule TCP Port 80."
else
    echo ""
    echo "❌ GAGAL. Aplikasi tidak merespon di Port 80."
    echo "Kemungkinan error di script. Cek logs dengan: pm2 logs"
fi
