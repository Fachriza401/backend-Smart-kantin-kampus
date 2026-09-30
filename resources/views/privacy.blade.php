<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kebijakan Privasi — Smart Kantin Kampus</title>
    <meta name="description" content="Kebijakan privasi aplikasi Smart Kantin Kampus.">
    <style>
        :root {
            --bg: #f6faf7;
            --surface: #ffffff;
            --text: #1f2a24;
            --muted: #5b6b62;
            --accent: #16a34a;
            --border: #dfe9e2;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #0f1512;
                --surface: #17201b;
                --text: #e6efe9;
                --muted: #9fb2a7;
                --accent: #4ade80;
                --border: #26332c;
            }
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font: 16px/1.65 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }
        main {
            max-width: 720px;
            margin: 0 auto;
            padding: 40px 16px 64px;
        }
        header { border-bottom: 3px solid var(--accent); margin-bottom: 24px; padding-bottom: 12px; }
        h1 { font-size: 1.75rem; line-height: 1.25; margin: 0 0 4px; }
        .updated { color: var(--muted); font-size: .9rem; margin: 0; }
        section {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 16px;
        }
        h2 { font-size: 1.05rem; margin: 0 0 8px; color: var(--accent); }
        p, ul { margin: 0 0 8px; }
        ul { padding-left: 20px; }
        li { margin-bottom: 4px; }
        footer { color: var(--muted); font-size: .85rem; text-align: center; margin-top: 32px; }
    </style>
</head>
<body>
<main>
    <header>
        <h1>Kebijakan Privasi Smart Kantin Kampus</h1>
        <p class="updated">Terakhir diperbarui: 30 September 2026</p>
    </header>

    <section>
        <h2>1. Pendahuluan</h2>
        <p>Smart Kantin Kampus adalah aplikasi pre-order makanan dan minuman di kantin kampus. Kebijakan ini menjelaskan data apa yang kami kumpulkan, bagaimana data tersebut digunakan, dan hak Anda atas data tersebut.</p>
    </section>

    <section>
        <h2>2. Data yang Kami Kumpulkan</h2>
        <ul>
            <li><strong>Pembeli (Guest Checkout):</strong> nama dan email yang Anda isi saat memesan, isi pesanan, waktu pengambilan, catatan pesanan, serta metode dan status pembayaran.</li>
            <li><strong>Akun staf (Admin, Tenant, Kasir):</strong> nama, email, password (disimpan dalam bentuk hash, bukan teks asli), dan foto profil bila diunggah.</li>
            <li><strong>Rating dan ulasan</strong> yang Anda berikan untuk menu yang dipesan.</li>
            <li><strong>Kamera</strong> hanya digunakan untuk memindai QR code pesanan. Gambar dari kamera tidak disimpan.</li>
            <li><strong>Galeri foto</strong> hanya diakses ketika Anda memilih foto profil atau foto menu. Hanya foto yang Anda pilih yang diunggah.</li>
        </ul>
        <p>Kami <strong>tidak</strong> mengumpulkan lokasi, kontak, data kesehatan, maupun data untuk pelacakan atau iklan.</p>
    </section>

    <section>
        <h2>3. Penggunaan Data</h2>
        <p>Data digunakan untuk: (a) membuat dan memproses pesanan, (b) memberi nomor antrean dan notifikasi status pesanan, (c) mencatat pembayaran, (d) menampilkan riwayat pesanan, dan (e) menyusun laporan penjualan untuk pengelola kantin.</p>
    </section>

    <section>
        <h2>4. Penyimpanan dan Pembagian Data</h2>
        <p>Data pesanan dan akun disimpan di server Smart Kantin Kampus dan dikirim melalui koneksi terenkripsi (HTTPS). Perangkat Anda hanya menyimpan informasi sesi, seperti nama dan email guest atau status login staf, agar Anda tidak perlu mengisinya ulang.</p>
        <p>Pesanan Anda dapat dilihat oleh staf kantin (Tenant, Kasir, dan Admin) untuk memprosesnya. Kami tidak menjual atau membagikan data Anda kepada pihak ketiga.</p>
    </section>

    <section>
        <h2>5. Keamanan Data</h2>
        <p>Kami menerapkan langkah keamanan yang wajar, termasuk enkripsi HTTPS dan penyimpanan password dalam bentuk hash. Tidak ada sistem yang sepenuhnya bebas risiko, sehingga kami menyarankan Anda menjaga kerahasiaan kredensial akun.</p>
    </section>

    <section>
        <h2>6. Hak Anda</h2>
        <p>Anda berhak meminta akses, perbaikan, atau penghapusan data pribadi Anda. Staf dapat memperbarui profil melalui menu Edit Profil. Untuk permintaan lain, termasuk penghapusan data pesanan guest, hubungi Admin Smart Kantin Kampus.</p>
    </section>

    <section>
        <h2>7. Kontak</h2>
        <p>Pertanyaan terkait kebijakan privasi dapat disampaikan kepada Admin Smart Kantin Kampus melalui menu Bantuan di aplikasi.</p>
    </section>

    <section>
        <h2>8. Perubahan Kebijakan</h2>
        <p>Kebijakan ini dapat diperbarui sewaktu-waktu. Versi terbaru selalu tersedia di halaman ini.</p>
    </section>

    <footer>&copy; {{ date('Y') }} Smart Kantin Kampus</footer>
</main>
</body>
</html>
