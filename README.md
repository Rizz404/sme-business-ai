# SME Business AI

Situs bisnis interaktif untuk UMKM dengan **AI Business Assistant**. Pengunjung menjelajahi bisnis seperti berdiri di meja depan (katalog produk dan layanan, area layanan, keunggulan, informasi, pemesanan sesi) sambil mengobrol dengan assistant AI. Assistant menjawab dari data bisnis yang terkontrol, mencari katalog sesuai kebutuhan dan anggaran, mengecek ketersediaan sesi, membuat permintaan pemesanan, dan menyerahkan percakapan ke tim bila perlu. Tim mengelola semuanya lewat panel admin.

Satu aplikasi bisa melayani beberapa bisnis. Setiap bisnis punya halaman sendiri di `/{businessSlug}`.

Proyek ini turunan dari Cafe AI: mekanismenya (gateway model, daftar putih tool, permintaan yang dikonfirmasi manusia) dipertahankan, sedangkan kosakata domain, skema, dan tampilan panggung diganti.

## Fitur

### Sisi pengunjung
- **Halaman bertema "stage"**: meja depan, katalog, area layanan, keunggulan, info bisnis, tim, dan pemesanan. Latarnya ilustrasi SVG dengan karakter assistant yang berdiri di kiri-tengah, gelembung bicara di sebelahnya, dan dock ikon bulat di bawah sebagai navigasi. Lengkap dengan narrator dan efek suara (tombol bisa dimatikan).
- **Tiga bahasa**: Indonesia (`id`), Inggris (`en`), dan Jepang (`ja`), dipilih lewat parameter `?lang=`.
- **Katalog**: produk, layanan, paket, konsultasi, produk digital, dan tambahan, lengkap dengan harga, tag, persyaratan, durasi, dan penanda "tidak tersedia".
- **Area layanan**: toko, bengkel kerja, online, dan kunjungan ke lokasi pelanggan, lengkap dengan kapasitas, fasilitas, dan biaya pemesanan.
- **Chat dengan AI Business Assistant**
  - Tahu di halaman mana pengunjung berada (item katalog atau area yang sedang dilihat, form pemesanan yang sedang diisi), jadi bisa menjawab "yang ini".
  - Menjawab dalam bahasa yang ditulis pengunjung.
  - Menampilkan kartu katalog, area layanan, dan ketersediaan langsung di dalam chat.
- **Pemesanan sesi**: pilih tanggal dan sesi 2 jam, jumlah peserta, area layanan, lalu kirim permintaan (status awal `pending`). Tidak ada pembayaran di muka.

### Sisi admin (`/admin`)
- Dashboard ringkasan.
- CRUD **katalog** (harga, kategori, tag, persyaratan, durasi, tandai tidak tersedia).
- CRUD **area layanan** (kapasitas, biaya pemesanan, minimum pesanan, gambar).
- CRUD **knowledge items**, yaitu basis pengetahuan yang menjadi sumber jawaban assistant (kebijakan, keunggulan, acara, lokasi, FAQ).
- Daftar **pemesanan** dan ubah statusnya (`pending`, `confirmed`, `cancelled`). Membatalkan pemesanan mengembalikan slotnya ke inventori.
- **Handover**: percakapan yang diserahkan assistant ke tim. Tim bisa membalas langsung dan menandainya selesai.
- Peran pengguna per bisnis: `owner` dan `staff`.

## Cara kerja AI Business Assistant

Kode ada di [app/Modules/Assistant/](app/Modules/Assistant) (model, prompt, tool) dan [app/Modules/Conversation/](app/Modules/Conversation) (satu giliran percakapan).

- **[ReplyToGuest](app/Modules/Conversation/Actions/ReplyToGuest.php)** menjalankan satu giliran percakapan dan menyimpan riwayatnya di database. Ia memanggil model lewat [ModelGateway](app/Modules/Assistant/Gateway/ModelGateway.php), yaitu endpoint chat yang kompatibel dengan OpenAI, lalu menjalankan loop tool-calling (maksimal 6 putaran per giliran). Seluruh giliran dibatasi 85 detik supaya muat dalam batas 100 detik Cloudflare. Anggaran ini ada di [config/assistant.php](config/assistant.php). Prompt disusun oleh [PromptComposer](app/Modules/Assistant/Prompt/PromptComposer.php) dari templat di `Prompt/Templates/*.json`.
- **[ToolRegistry](app/Modules/Assistant/Tools/ToolRegistry.php)** adalah daftar putih alat yang bisa dipanggil model; satu alat satu kelas di [Handlers/](app/Modules/Assistant/Tools/Handlers). Semua fakta bisnis, katalog, harga, dan ketersediaan harus lewat sini, karena model sendiri tidak dipercaya menyimpan data apa pun.

  | Tool                     | Fungsi                                                                          |
  | ------------------------ | ------------------------------------------------------------------------------- |
  | `search_knowledge`       | Mencari di knowledge base bisnis                                                |
  | `search_catalog`         | Mencari katalog menurut kata kunci, kategori, tag, atau anggaran                |
  | `get_catalog_item`       | Detail satu item katalog: harga, persyaratan, durasi, status tidak tersedia     |
  | `search_service_area`    | Mencari area layanan yang cocok dengan tanggal, jumlah peserta, dan sesi        |
  | `check_availability`     | Cek ketersediaan slot dan biaya pemesanan untuk satu sesi                       |
  | `create_booking_request` | Membuat permintaan pemesanan (tidak pernah langsung mengonfirmasi)              |
  | `request_human_handover` | Menyerahkan percakapan ke tim                                                   |

- **[ContentGuard](app/Modules/Conversation/Support/ContentGuard.php)** adalah pengaman deterministik di kode. Pesan yang kasar, bersifat seksual, atau ilegal (Indonesia, Inggris, Jepang) dijawab dengan balasan baku tanpa sampai ke model.
- **Harga tanpa sumber ditolak**: balasan yang memuat angka harga yang tidak berasal dari tool akan ditantang, supaya model tidak mengarang harga.
- **Persyaratan dan jaminan**: assistant hanya mengulang persyaratan dari data katalog dan tidak menjanjikan hasil; cakupan akhir dikonfirmasi tim.
- **Handover**: alasan yang didukung adalah `special_request`, `complaint`, `group_booking`, `private_event`, `custom_order`, `payment_issue`, dan `low_confidence`.

## Pemetaan domain (turunan dari Cafe AI)

| Cafe AI                          | SME Business AI                                              |
| -------------------------------- | ------------------------------------------------------------ |
| Cafe (tenant)                    | Business (`businesses`, `{businessSlug}`)                    |
| MenuItem                         | CatalogItem (produk, layanan, paket, konsultasi, dst.)       |
| SeatingArea, SeatingImage        | ServiceArea, ServiceAreaImage                                |
| TableInventory (meja per sesi)   | SlotInventory (unit paralel per sesi)                        |
| Reservation, Occasion            | Booking, Purpose                                             |
| AI Barista                       | AI Business Assistant                                        |
| `barista:chat`                   | `sme:chat`                                                   |

## Teknologi

- PHP 8.3+ (dikembangkan di 8.4), Laravel 13
- SQLite untuk development dan tes; **PostgreSQL untuk produksi**. Session, cache, dan queue memakai driver `database`
- Vite 8 dan Tailwind CSS 4 untuk frontend, dengan JavaScript vanilla (tanpa framework) di `resources/js/`
- Font Fraunces dan DM Sans (Bunny Fonts), ilustrasi SVG di `public/images/`
- LLM lewat HTTP (endpoint kompatibel OpenAI)
- PHPUnit 12 untuk tes, Laravel Pint untuk format kode

## Memulai

### Prasyarat
PHP 8.3+ dengan ekstensi SQLite, Composer, dan Node.js 22.12+ dengan npm. Untuk fitur chat AI, Anda juga perlu server LLM yang kompatibel dengan OpenAI.

### Instalasi

```bash
composer setup
```

Perintah itu menjalankan `composer install`, menyalin `.env.example` ke `.env`, membuat `APP_KEY`, menjalankan migrasi, lalu `npm install` dan `npm run build`.

Isi data demo (hanya jalan di environment `local` dan `testing`):

```bash
php artisan db:seed
```

Seeder membuat bisnis demo **Maju Jaya Studio** (slug `maju-jaya-studio`, studio desain dan cetak di Bandung) dengan 12 item katalog, 4 area layanan, inventori slot untuk 60 hari ke depan, dan knowledge items dalam tiga bahasa.

### Menjalankan

```bash
composer dev
```

| Halaman         | URL                                      |
| --------------- | ---------------------------------------- |
| Halaman pembuka | `http://localhost:8000/`                 |
| Bisnis demo     | `http://localhost:8000/maju-jaya-studio` |
| Admin           | `http://localhost:8000/admin`            |

Akun admin demo (hanya untuk lokal, jangan dipakai di produksi):

```
email    : owner@smebusiness.test
password : password
```

### Konfigurasi LLM

Atur di `.env`:

```dotenv
LOCAL_LLM_BASE_URL=   # mis. http://127.0.0.1:1234 (LM Studio) atau http://127.0.0.1:11434 (Ollama)
LOCAL_LLM_API_KEY=    # kosongkan jika server tidak memakai autentikasi
LOCAL_LLM_MODEL=      # nama model yang dimuat di server
```

Model harus mendukung function calling. Tanpa konfigurasi ini, halaman bisnis tetap jalan tetapi chat assistant tidak bisa menjawab.

### Mencoba assistant dari terminal

```bash
php artisan sme:chat maju-jaya-studio --locale=id
```

## Endpoint utama

| Method | Path                                                   | Keterangan                     |
| ------ | ------------------------------------------------------ | ------------------------------ |
| GET    | `/{businessSlug}`                                      | Meja depan bisnis              |
| GET    | `/{businessSlug}/catalog`, `/catalog/{itemSlug}`       | Daftar dan detail katalog      |
| GET    | `/{businessSlug}/service`, `/service/{areaSlug}`       | Daftar dan detail area layanan |
| GET    | `/{businessSlug}/facilities`, `/facilities/{id}`       | Keunggulan dan fasilitas       |
| GET    | `/{businessSlug}/info`, `/staff`, `/booking`           | Info, tim, pemesanan           |
| POST   | `/{businessSlug}/booking/quote`                        | Cek ketersediaan dan biaya     |
| POST   | `/{businessSlug}/booking`                              | Kirim permintaan pemesanan     |
| POST   | `/{businessSlug}/assistant/start`                      | Mulai percakapan               |
| POST   | `/{businessSlug}/assistant/message`                    | Kirim pesan ke assistant       |
| GET    | `/{businessSlug}/assistant/history`                    | Riwayat percakapan             |

Endpoint assistant dan pemesanan dibatasi laju (rate limit). Pembatas assistant didefinisikan di [AppServiceProvider](app/Providers/AppServiceProvider.php), dan login admin dibatasi 5 percobaan per menit.

## Struktur proyek

```
app/
  Console/Commands/     sme:chat, sme:create-admin, sme:open-inventory
  Http/
    Controllers/        halaman pengunjung, Api/ (chat dan pemesanan JSON), Admin/, Auth/
    Middleware/         ResolveBusiness (slug ke bisnis dan bahasa), AssignRequestId
    Requests/           Form Request: Admin/, Api/, Auth/
  Models/               Business, CatalogItem, ServiceArea, SlotInventory, Booking, Conversation, HandoverRequest, ...
  Modules/              satu folder per konteks: Actions/, Queries/, Support/, Enums/, Presenters/
    Assistant/          Gateway/ (model), Prompt/, Tools/ (daftar putih alat + Handlers/)
    Conversation/       balasan pengunjung, handover, ContentGuard
    Experience/         data panggung (teks, narasi, latar)
    Catalog/ ServiceArea/ Knowledge/ Booking/ Operations/
  Policies/             satu bisnis hanya mengelola datanya sendiri
config/                 business.php (bahasa, scene, trusted proxies), assistant.php (anggaran AI)
database/               factories, migrations, seeders
deploy/                 contoh env produksi (PostgreSQL) dan PHP; nginx/supervisor hanya acuan, tidak dipakai di Plesk
lang/{id,en,ja}/        teks panggung, validasi pemesanan, dan pesan handover
public/images/          scenes/, character/, assistant/, catalog/, service/ (ilustrasi SVG)
resources/
  css/                  theme.css (token admin), base.css, presentation.css (panggung: stage/*.css)
  js/                   app.js, stage, narrator, assistant, booking, sound, shared/, ui/
  views/                halaman pengunjung (business/), admin (admin/), components/
scripts/                check-ui-conventions.mjs (npm run lint:ui)
tests/                  Feature/, Unit/, Support/, js/
```

## Mengganti ilustrasi

Semua gambar adalah SVG di `public/images/`. Latar scene ada di `scenes/`, karakter assistant di `character/`, avatar bulat di `assistant/avatar.svg`. Gambar katalog dan area layanan diacu dari kolom `image_url` (path di bawah `public/` atau URL penuh), jadi foto asli bisa dipakai langsung dari panel admin. Pemetaan scene ke gambar ada di [config/business.php](config/business.php) (`scenes`).

## Produksi

- Database produksi **PostgreSQL**; lihat [deploy/env.production.example](deploy/env.production.example). Hindari SQL mentah khusus SQLite dan ingat `LIKE` di PostgreSQL case-sensitive (pencarian teks dilakukan di PHP lewat `App\Support\SearchText`).
- Seeder demo tidak jalan di produksi. Buat akun owner dengan command berikut; kata sandi dibaca lewat prompt tersembunyi, bukan argumen:

  ```bash
  php artisan sme:create-admin --business=<slug> --business-name="<Nama Bisnis>" --email=<email> --name="<Nama>"
  ```

  `--business-name` hanya dipakai bila bisnis dengan slug itu belum ada (bisnis kosong dibuat berstatus `published`).
- Slot hanya dibuka untuk 60 hari ke depan. Command `php artisan sme:open-inventory` (idempoten) memperpanjangnya untuk setiap area aktif tanpa menimpa jumlah yang sudah dipesan; area yang belum punya slot memakai `default_slots` dan `default_units` di [config/business.php](config/business.php). Command ini dijadwalkan harian pukul 01:00 di [routes/console.php](routes/console.php), jadi scheduler harus aktif (Laravel Toolkit di Plesk) dan sebaiknya dijalankan sekali setelah area layanan dibuat.
- Di belakang Cloudflare, isi `TRUSTED_PROXIES` dengan IP/CIDR proxy yang sebenarnya (bukan `*`) agar batas laju memakai IP pengunjung. Dibaca oleh [config/business.php](config/business.php) dan diterapkan di `AppServiceProvider`.
- Workflow CI hanya berjalan manual (`workflow_dispatch`) dan bukan gerbang deploy.
- Jalankan `npm run build` dan `php artisan migrate --force`. Set `APP_ENV=production` dan `APP_DEBUG=false`.
- Karena ada batas 100 detik dari Cloudflare, jangan menaikkan batas waktu respons assistant melebihi 85 detik.

## Pengujian dan gaya kode

Tes dijalankan hanya bila diminta:

```bash
composer test
php artisan test --compact tests/Feature/AssistantChatTest.php
npm run lint:ui
npm run test:ui
```

Format kode PHP: `vendor/bin/pint --dirty`.

## Lisensi

Project ini dibangun di atas [Laravel](https://laravel.com), yang berlisensi [MIT](https://opensource.org/licenses/MIT).
