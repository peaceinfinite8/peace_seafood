#!/bin/bash
# ============================================================
# Peace Seafood - Development Server Starter (Port 8080)
# ============================================================

echo ""
echo "========================================================"
echo "  PEACE SEAFOOD - Development Server"
echo "  Port: 8080"
echo "========================================================"
echo ""

# Check if .env exists
if [ ! -f ".env" ]; then
    echo "[WARNING] File .env tidak ditemukan!"
    echo "[INFO] Membuat .env dari .env.example..."
    cp .env.example .env
    echo "[OK] File .env berhasil dibuat"
    echo "[ACTION] Silakan edit .env sesuai konfigurasi lokal Anda"
    echo ""
fi

# Check if port 8080 is available
echo "[INFO] Memeriksa ketersediaan port 8080..."
if lsof -Pi :8080 -sTCP:LISTEN -t >/dev/null ; then
    echo "[WARNING] Port 8080 sudah digunakan!"
    echo "[INFO] Proses yang menggunakan port 8080:"
    lsof -i :8080
    echo ""
    echo "[ACTION] Silakan stop proses tersebut atau gunakan port lain"
    exit 1
fi

echo "[OK] Port 8080 tersedia"
echo ""

# Start PHP built-in server
echo "[INFO] Memulai PHP Development Server..."
echo "[INFO] URL: http://localhost:8080/"
echo "[INFO] Tekan Ctrl+C untuk menghentikan server"
echo ""
echo "========================================================"
echo "  Server berjalan di http://localhost:8080/"
echo "  Tekan Ctrl+C untuk stop"
echo "========================================================"
echo ""

php -S localhost:8080 -t public
