import 'dotenv/config';
import express from 'express';
import gplay from 'google-play-scraper';
import appStore from 'app-store-scraper';
import axios from 'axios';
import cors from 'cors';
import fs from 'fs';

const app = express();
const PORT = process.env.PORT || 3000;

app.use(cors());
app.use(express.json());

// Helper function untuk format response standar
const formatResponse = (success, data, message = '') => {
    return {
        success,
        message,
        data
    };
};

app.get('/', (req, res) => {
    res.send({
        message: 'Rating Scraper API is running',
        last_updated: '2025-01-02 (Update: Web Setup Config)',
        endpoints: {
            google_play: '/api/playstore?id=com.henskristal.hens_kristal',
            app_store: '/api/appstore?id=6473765666',
            google_maps: '/api/maps?place_id=PLACE_ID_HERE',
            search_maps: '/api/search/maps?q=Monas',
            setup_config: '/setup?key=YOUR_API_KEY'
        }
    });
});

// Endpoint untuk setup API Key via Browser (Solusi jika tidak bisa paste di terminal)
app.get('/setup', (req, res) => {
    const key = req.query.key;
    if (!key) {
        return res.send(`
            <h2>Cara Setting API Key</h2>
            <p>Masukkan API Key Anda di URL. Contoh:</p>
            <code>${req.protocol}://${req.get('host')}/setup?key=MASUKKAN_KEY_DISINI</code>
        `);
    }

    try {
        const envContent = `PORT=80\nGOOGLE_MAPS_API_KEY=${key}`;
        fs.writeFileSync('.env', envContent);
        
        // Update process env agar langsung aktif tanpa restart (untuk sesi ini)
        process.env.GOOGLE_MAPS_API_KEY = key;

        res.send(`
            <h1>Berhasil Disimpan!</h1>
            <p>API Key Google Maps telah berhasil disimpan ke server.</p>
            <p>Server sekarang menggunakan key: <b>${key.substring(0, 10)}...</b></p>
            <p>Silakan coba tes endpoint maps sekarang: <a href="/api/search/maps?q=Monas">Cari Monas</a></p>
            <br>
            <p><i>Catatan: Agar perubahan permanen jika server restart otomatis, jalankan 'pm2 restart hk-getratings' di terminal nanti (tidak wajib sekarang).</i></p>
        `);
    } catch (err) {
        res.status(500).send('Gagal menyimpan file: ' + err.message);
    }
});

// 1. Google Play Store Route
app.get('/api/playstore', async (req, res) => {
    const { id, lang, country } = req.query;

    if (!id) {
        return res.status(400).json(formatResponse(false, null, 'Parameter "id" (package name) diperlukan.'));
    }

    try {
        const [details, reviewsData] = await Promise.all([
            gplay.app({
                appId: id,
                lang: lang || 'id',
                country: country || 'id'
            }),
            gplay.reviews({
                appId: id,
                lang: lang || 'id',
                country: country || 'id',
                num: 100 // Ambil 100 ulasan (Google Play Scraper support up to 3000 w/ pagination, but 100 is safe default)
            })
        ]);

        // reviewsData bisa berupa array atau object tergantung versi, kita handle keduanya
        const reviews = Array.isArray(reviewsData) ? reviewsData : (reviewsData.data || []);

        const result = {
            title: details.title,
            appId: details.appId,
            score: details.score,
            ratings: details.ratings,
            reviews_count: details.reviews,
            version: details.version,
            recentChanges: details.recentChanges,
            description: details.description,
            screenshots: details.screenshots,
            developer: details.developer,
            url: details.url,
            icon: details.icon,
            recent_reviews: reviews // Return semua yang didapat (default 100)
        };

        res.json(formatResponse(true, result));
    } catch (error) {
        console.error('Google Play Error:', error.message);
        res.status(500).json(formatResponse(false, null, 'Gagal mengambil data dari Google Play Store: ' + error.message));
    }
});

// 2. Apple App Store Route
app.get('/api/appstore', async (req, res) => {
    const { id, country } = req.query;

    if (!id) {
        return res.status(400).json(formatResponse(false, null, 'Parameter "id" (App ID) diperlukan.'));
    }

    try {
        const details = await appStore.app({
            id: id,
            country: country || 'id'
        });

        // Fetch 10 pages of reviews parallelly (Total ~500 reviews)
        // App Store Scraper fetches 50 reviews per page.
        // Fetching "ALL" is hard because API limits, so we fetch a large reasonable amount (500).
        const pageCount = 10;
        const reviewPromises = [];
        for (let i = 1; i <= pageCount; i++) {
            reviewPromises.push(
                appStore.reviews({
                    id: id,
                    country: country || 'id',
                    sort: appStore.sort.RECENT,
                    page: i
                }).catch(() => []) // Ignore errors on specific pages
            );
        }

        const reviewsResults = await Promise.all(reviewPromises);
        const allReviews = reviewsResults.flat();

        const result = {
            title: details.title,
            appId: details.id,
            score: details.score,
            ratings: details.ratings, // Jumlah rating
            reviews_count: details.reviews, // Jumlah ulasan (jika ada)
            currentVersion: details.version,
            description: details.description,
            screenshots: details.screenshots,
            developer: details.developer,
            url: details.url,
            icon: details.icon,
            recent_reviews: allReviews // Return semua yang didapat (hingga ~500)
        };

        res.json(formatResponse(true, result));
    } catch (error) {
        console.error('App Store Error:', error.message);
        res.status(500).json(formatResponse(false, null, 'Gagal mengambil data dari App Store: ' + error.message));
    }
});

// 3. Google Maps Route (Menggunakan Google Places API)
app.get('/api/maps', async (req, res) => {
    const { place_id } = req.query;
    // Prioritaskan Environment Variable, tapi izinkan override via query param
    const apiKey = process.env.GOOGLE_MAPS_API_KEY || req.query.key;

    if (!apiKey) {
        return res.status(500).json(formatResponse(false, null, 'API Key Google Maps tidak ditemukan. Set di .env atau kirim via parameter &key='));
    }

    if (!place_id) {
        return res.status(400).json(formatResponse(false, null, 'Parameter "place_id" diperlukan.'));
    }

    try {
        // Menggunakan Place Details API
        const response = await axios.get(`https://maps.googleapis.com/maps/api/place/details/json`, {
            params: {
                place_id: place_id,
                fields: 'name,rating,user_ratings_total,url,icon,reviews,formatted_address,formatted_phone_number,website,opening_hours,photos,geometry,types,price_level',
                key: apiKey,
                language: 'id'
            }
        });

        if (response.data.status !== 'OK') {
            throw new Error(response.data.error_message || response.data.status);
        }

        const details = response.data.result;

        const result = {
            ...details, // Sertakan semua data raw
            title: details.name,
            placeId: place_id,
            score: details.rating,
            ratings: details.user_ratings_total,
            address: details.formatted_address,
            phone: details.formatted_phone_number,
            website: details.website,
            reviews_count: details.reviews ? details.reviews.length : 0,
            url: details.url,
            icon: details.icon,
            recent_reviews: details.reviews // Array ulasan lengkap (author, text, rating, time)
        };

        res.json(formatResponse(true, result));
    } catch (error) {
        console.error('Google Maps Error:', error.message);
        res.status(500).json(formatResponse(false, null, 'Gagal mengambil data dari Google Maps: ' + error.message));
    }
});

// 4. Endpoint Pencarian (Opsional, untuk membantu user mencari ID)
app.get('/api/search/playstore', async (req, res) => {
    const { q } = req.query;
    if (!q) return res.status(400).json(formatResponse(false, null, 'Query "q" diperlukan'));
    
    try {
        const results = await gplay.search({
            term: q,
            num: 5,
            lang: 'id',
            country: 'id'
        });
        res.json(formatResponse(true, results));
    } catch (error) {
        res.status(500).json(formatResponse(false, null, error.message));
    }
});

// 5. Endpoint Pencarian Google Maps (Untuk mendapatkan Place ID)
app.get('/api/search/maps', async (req, res) => {
    const { q } = req.query;
    const apiKey = process.env.GOOGLE_MAPS_API_KEY || req.query.key;

    if (!apiKey) {
        return res.status(500).json(formatResponse(false, null, 'API Key Google Maps tidak ditemukan.'));
    }
    
    if (!q) return res.status(400).json(formatResponse(false, null, 'Query "q" (nama tempat) diperlukan'));

    try {
        // Menggunakan Text Search API
        const response = await axios.get(`https://maps.googleapis.com/maps/api/place/textsearch/json`, {
            params: {
                query: q,
                key: apiKey,
                language: 'id'
            }
        });

        if (response.data.status !== 'OK' && response.data.status !== 'ZERO_RESULTS') {
             throw new Error(response.data.error_message || response.data.status);
        }

        const results = response.data.results.map(place => ({
            title: place.name,
            placeId: place.place_id,
            address: place.formatted_address,
            score: place.rating,
            ratings: place.user_ratings_total
        }));

        res.json(formatResponse(true, results));
    } catch (error) {
        console.error('Maps Search Error:', error.message);
        res.status(500).json(formatResponse(false, null, 'Gagal mencari tempat: ' + error.message));
    }
});

app.listen(PORT, () => {
    console.log(`Server berjalan di http://localhost:${PORT}`);
});

export default app;
