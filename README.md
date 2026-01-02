# API Pengambil Rating Aplikasi & Tempat

Aplikasi Node.js sederhana untuk mengambil data rating dari **Google Play Store**, **Apple App Store**, dan **Google Maps**.

## Instalasi

1.  Pastikan Node.js sudah terinstall.
2.  Install dependensi:
    ```bash
    npm install
    ```
3.  Buat file `.env` (jika belum ada) dan isi API Key Google Maps jika ingin menggunakan fitur Maps:
    ```
    PORT=3000
    GOOGLE_MAPS_API_KEY=API_KEY_ANDA_DISINI
    ```

## Cara Menjalankan

```bash
npm start
```

Server akan berjalan di `http://localhost:3000`.

## Cara Menggunakan (Live Demo)

Setelah deploy ke Vercel, Anda bisa mengakses API ini secara online tanpa perlu menyalakan komputer lokal.

**Base URL:** `https://hk-getratings.vercel.app` (Contoh)

### 1. Google Play Store
`GET /api/playstore?id=com.whatsapp`

### 2. Apple App Store
`GET /api/appstore?id=310633997`

### 3. Google Maps
`GET /api/maps?place_id=ChIJ...`
*Jika API Key tidak diset di server, tambahkan parameter `&key=API_KEY` di URL.*

---

## Deployment ke VPS (IONOS / DigitalOcean / AWS)

Jika Anda menggunakan VPS, berikut cara deploy menggunakan PM2:

1.  **Masuk ke VPS** via SSH.
2.  **Install Node.js & Git**:
    ```bash
    curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
    sudo apt-get install -y nodejs git
    ```
3.  **Install PM2** (Process Manager):
    ```bash
    sudo npm install -g pm2
    ```
4.  **Clone Repository**:
    ```bash
    git clone https://github.com/abmut91/hk-getratings.git
    cd hk-getratings
    ```
5.  **Install Dependensi**:
    ```bash
    npm install
    ```
6.  **Setup Environment**:
    Buat file `.env` dan isi konfigurasi (gunakan `nano .env`).
7.  **Jalankan Aplikasi**:
    ```bash
    pm2 start ecosystem.config.js
    pm2 save
    pm2 startup
    ```

---

## Dokumentasi API Lengkap

### 1. Google Play Store

Mengambil data aplikasi dari Google Play Store.

-   **URL:** `/api/playstore`
-   **Method:** `GET`
-   **Params:**
    -   `id` (required): Package name aplikasi (contoh: `com.whatsapp`)
    -   `lang` (optional): Bahasa (default: `id`)
    -   `country` (optional): Negara (default: `id`)

**Contoh:**
`GET http://localhost:3000/api/playstore?id=com.whatsapp`

### 2. Apple App Store

Mengambil data aplikasi dari Apple App Store.

-   **URL:** `/api/appstore`
-   **Method:** `GET`
-   **Params:**
    -   `id` (required): App ID (angka) dari URL App Store.
    -   `country` (optional): Negara (default: `id` untuk Indonesia)

**Contoh:**
`GET http://localhost:3000/api/appstore?id=310633997` (WhatsApp)

### 3. Google Maps (Detail Tempat)

Mengambil data rating dan ulasan tempat dari Google Maps.

-   **URL:** `/api/maps`
-   **Method:** `GET`
-   **Params:**
    -   `place_id` (required): Place ID dari Google Maps.
    -   `key` (optional): API Key Google Maps (jika tidak diset di .env server).

**Contoh:**
`GET http://localhost:3000/api/maps?place_id=ChIJN1t_tDeuEmsRUsoyG83frY4`

### 4. Pencarian Google Play

Mencari package name aplikasi.

-   **URL:** `/api/search/playstore`
-   **Params:** `q` (keyword pencarian)

**Contoh:**
`GET http://localhost:3000/api/search/playstore?q=gojek`

### 5. Pencarian Google Maps (Cari Place ID)

Mencari Place ID berdasarkan nama tempat.

-   **URL:** `/api/search/maps`
-   **Params:** `q` (keyword pencarian)
-   **Params:** `key` (optional)

**Contoh:**
`GET http://localhost:3000/api/search/maps?q=Monas`
