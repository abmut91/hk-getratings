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

## Dokumentasi API

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

### 3. Google Maps

Mengambil data rating tempat dari Google Maps.
**PENTING:** Memerlukan `GOOGLE_MAPS_API_KEY` di file `.env`.

-   **URL:** `/api/maps`
-   **Method:** `GET`
-   **Params:**
    -   `place_id` (required): Place ID dari Google Maps.

**Contoh:**
`GET http://localhost:3000/api/maps?place_id=ChIJ...`

### 4. Pencarian Google Play

Mencari package name aplikasi.

-   **URL:** `/api/search/playstore`
-   **Params:** `q` (keyword pencarian)

**Contoh:**
`GET http://localhost:3000/api/search/playstore?q=gojek`
