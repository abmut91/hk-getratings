#!/bin/bash
# Script debug untuk melihat kenapa aplikasi gagal
echo "=== 1. Cek Process yang menggunakan Port 80 ==="
lsof -i :80 || netstat -tulnp | grep :80
echo ""

echo "=== 2. Cek Error Log Aplikasi ==="
pm2 logs hk-getratings --lines 30 --nostream
echo ""

echo "=== 3. Cek Status Firewall ==="
ufw status
