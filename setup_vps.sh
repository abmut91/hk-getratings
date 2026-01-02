#!/bin/bash

# Script Setup Otomatis untuk hk-getratings
# Dibuat oleh Trae AI untuk memudahkan deployment

set -e # Hentikan script jika ada error

echo "=================================================="
echo "   MULAI SETUP AUTOMATIS HK-GETRATINGS (VPS)      "
echo "=================================================="

# 1. Update System
echo "[1/8] Mengupdate sistem Ubuntu..."
export DEBIAN_FRONTEND=noninteractive
apt-get update -y
apt-get upgrade -y

# 2. Install Curl & Git
echo "[2/8] Menginstall Curl & Git..."
apt-get install -y curl git

# 3. Install Node.js v18
echo "[3/8] Menginstall Node.js v18..."
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt-get install -y nodejs

# 4. Install PM2
echo "[4/8] Menginstall PM2 (Process Manager)..."
npm install -g pm2

# 5. Setup Repository
echo "[5/8] Mengunduh aplikasi..."
cd ~
if [ -d "hk-getratings" ]; then
    echo "   Folder sudah ada, mengambil update terbaru..."
    cd hk-getratings
    git reset --hard # Reset perubahan lokal jika ada
    git pull
else
    echo "   Cloning repository baru..."
    git clone https://github.com/abmut91/hk-getratings.git
    cd hk-getratings
fi

# 6. Install Dependencies
echo "[6/8] Menginstall library aplikasi..."
# Hapus node_modules lama jika ada masalah
rm -rf node_modules
npm install

# 7. Setup Environment (.env)
echo "[7/8] Konfigurasi .env..."
if [ ! -f .env ]; then
    echo "   Membuat file .env default..."
    echo "PORT=3000" > .env
    echo "GOOGLE_MAPS_API_KEY=" >> .env
    echo "   NOTE: Anda perlu mengedit file .env nanti untuk mengisi GOOGLE_MAPS_API_KEY"
else
    echo "   File .env sudah ada, melewati langkah ini."
fi

# 8. Start Application
echo "[8/8] Menjalankan aplikasi dengan PM2..."
pm2 delete hk-getratings 2>/dev/null || true # Hapus proses lama jika ada
pm2 start ecosystem.config.js
pm2 save
pm2 startup | grep "sudo" | bash # Jalankan perintah startup otomatis

echo "=================================================="
echo "   INSTALASI SELESAI!                             "
echo "=================================================="
echo ""
echo "Aplikasi Anda sekarang berjalan di:"
MY_IP=$(curl -s ifconfig.me)
echo "http://$MY_IP:3000/api/playstore?id=com.whatsapp"
echo ""
echo "PENTING: Jangan lupa isi API KEY Google Maps Anda."
echo "Caranya ketik: nano ~/hk-getratings/.env"
echo "=================================================="
