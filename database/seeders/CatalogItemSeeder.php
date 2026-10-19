<?php

namespace Database\Seeders;

use App\Models\Business;
use Illuminate\Database\Seeder;

class CatalogItemSeeder extends Seeder
{
    public function run(): void
    {
        $business = Business::where('name', BusinessSeeder::NAME)->firstOrFail();
        $sort = 0;

        foreach ($this->catalogDefinitions() as $definition) {
            $business->catalogItems()->updateOrCreate(
                ['slug' => $definition['slug']],
                [...$definition, 'image_url' => 'images/catalog/'.$definition['slug'].'.svg', 'sort_order' => $sort++, 'is_active' => true]
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function catalogDefinitions(): array
    {
        return [
            [
                'slug' => 'business-card-pack', 'category' => 'product', 'name' => 'Paket Kartu Nama 100 pcs', 'price' => 120000, 'delivery' => 'pickup', 'duration_minutes' => null,
                'tags' => ['popular'], 'requirements' => ['documents'], 'is_featured' => true,
                'description' => 'Kartu nama 100 lembar, kertas art carton 310 gsm, dua sisi, siap diambil dalam 2 hari kerja setelah desain disetujui.',
                'translations' => [
                    'id' => ['name' => 'Paket Kartu Nama 100 pcs', 'description' => 'Kartu nama 100 lembar, kertas art carton 310 gsm, dua sisi, siap diambil dalam 2 hari kerja setelah desain disetujui.'],
                    'en' => ['name' => 'Business Card Pack (100 pcs)', 'description' => '100 double-sided business cards on 310 gsm art carton, ready for pickup within 2 working days after the design is approved.'],
                    'ja' => ['name' => '名刺100枚パック', 'description' => '310gsmアートカートンの両面名刺100枚。デザイン承認後、2営業日でお受け取りいただけます。'],
                ],
            ],
            [
                'slug' => 'custom-tshirt-print', 'category' => 'product', 'name' => 'Cetak Kaos Custom', 'price' => 85000, 'delivery' => 'shipping', 'duration_minutes' => null,
                'tags' => ['bestseller', 'local'], 'requirements' => ['documents'], 'is_featured' => true,
                'description' => 'Kaos katun combed 30s dengan sablon DTF, harga per potong untuk pemesanan mulai 12 potong.',
                'translations' => [
                    'id' => ['name' => 'Cetak Kaos Custom', 'description' => 'Kaos katun combed 30s dengan sablon DTF, harga per potong untuk pemesanan mulai 12 potong.'],
                    'en' => ['name' => 'Custom T-Shirt Print', 'description' => 'Combed cotton 30s T-shirts with DTF printing, priced per piece for orders of 12 or more.'],
                    'ja' => ['name' => 'オリジナルTシャツプリント', 'description' => 'コーマ綿30sのTシャツにDTFプリント。12枚以上のご注文で、1枚あたりの価格です。'],
                ],
            ],
            [
                'slug' => 'brand-sticker-set', 'category' => 'product', 'name' => 'Set Stiker Merek', 'price' => 65000, 'delivery' => 'shipping', 'duration_minutes' => null,
                'tags' => ['new', 'eco'], 'requirements' => [], 'is_featured' => false,
                'description' => 'Satu lembar A4 stiker vinyl potong kiss-cut dengan logo atau ilustrasi merek Anda, tahan air.',
                'translations' => [
                    'id' => ['name' => 'Set Stiker Merek', 'description' => 'Satu lembar A4 stiker vinyl potong kiss-cut dengan logo atau ilustrasi merek Anda, tahan air.'],
                    'en' => ['name' => 'Brand Sticker Set', 'description' => 'One A4 sheet of waterproof kiss-cut vinyl stickers with your logo or brand illustration.'],
                    'ja' => ['name' => 'ブランドステッカーセット', 'description' => 'ロゴやブランドイラスト入りの防水ビニールステッカー（キスカット）A4サイズ1枚。'],
                ],
            ],
            [
                'slug' => 'logo-design', 'category' => 'service', 'name' => 'Desain Logo', 'price' => 750000, 'delivery' => 'online', 'duration_minutes' => null,
                'tags' => ['popular', 'signature'], 'requirements' => ['appointment', 'documents'], 'is_featured' => true,
                'description' => 'Tiga konsep logo awal, dua kali revisi, dan berkas final dalam format vektor serta PNG transparan. Pengerjaan 5 hari kerja.',
                'translations' => [
                    'id' => ['name' => 'Desain Logo', 'description' => 'Tiga konsep logo awal, dua kali revisi, dan berkas final dalam format vektor serta PNG transparan. Pengerjaan 5 hari kerja.'],
                    'en' => ['name' => 'Logo Design', 'description' => 'Three initial logo concepts, two rounds of revisions and final files in vector and transparent PNG formats. Takes 5 working days.'],
                    'ja' => ['name' => 'ロゴデザイン', 'description' => '初期コンセプト3案、修正2回、ベクターと透過PNGの最終データ。制作期間は5営業日です。'],
                ],
            ],
            [
                'slug' => 'product-photo-shoot', 'category' => 'service', 'name' => 'Foto Produk', 'price' => 450000, 'delivery' => 'onsite', 'duration_minutes' => 120,
                'tags' => ['popular'], 'requirements' => ['appointment', 'site_visit'], 'is_featured' => false,
                'description' => 'Sesi foto 2 jam di studio kami untuk hingga 10 produk, termasuk retusi dasar dan 20 foto final.',
                'translations' => [
                    'id' => ['name' => 'Foto Produk', 'description' => 'Sesi foto 2 jam di studio kami untuk hingga 10 produk, termasuk retusi dasar dan 20 foto final.'],
                    'en' => ['name' => 'Product Photo Shoot', 'description' => 'A 2-hour shoot at our studio for up to 10 products, including basic retouching and 20 final photos.'],
                    'ja' => ['name' => '商品撮影', 'description' => '当スタジオで2時間、最大10点の商品を撮影。基本レタッチと最終写真20枚を含みます。'],
                ],
            ],
            [
                'slug' => 'social-media-content-pack', 'category' => 'service', 'name' => 'Paket Konten Media Sosial', 'price' => 1200000, 'delivery' => 'online', 'duration_minutes' => null,
                'tags' => ['bestseller'], 'requirements' => ['prepayment', 'internet'], 'is_featured' => false,
                'description' => '12 desain feed dan 4 cerita per bulan lengkap dengan jadwal unggah dan ide keterangan. Dibayar di muka per bulan.',
                'translations' => [
                    'id' => ['name' => 'Paket Konten Media Sosial', 'description' => '12 desain feed dan 4 cerita per bulan lengkap dengan jadwal unggah dan ide keterangan. Dibayar di muka per bulan.'],
                    'en' => ['name' => 'Social Media Content Pack', 'description' => '12 feed designs and 4 stories per month with a posting schedule and caption ideas. Paid monthly in advance.'],
                    'ja' => ['name' => 'SNSコンテンツパック', 'description' => '月12本のフィードデザインとストーリー4本。投稿スケジュールとキャプション案付き。月額前払いです。'],
                ],
            ],
            [
                'slug' => 'starter-brand-kit', 'category' => 'package', 'name' => 'Starter Brand Kit', 'price' => 2500000, 'delivery' => 'online', 'duration_minutes' => null,
                'tags' => ['signature', 'popular'], 'requirements' => ['appointment', 'deposit'], 'is_featured' => true,
                'description' => 'Logo, palet warna, panduan tipografi, 100 kartu nama, dan 1 set stiker. Cocok untuk usaha yang baru mulai.',
                'translations' => [
                    'id' => ['name' => 'Starter Brand Kit', 'description' => 'Logo, palet warna, panduan tipografi, 100 kartu nama, dan 1 set stiker. Cocok untuk usaha yang baru mulai.'],
                    'en' => ['name' => 'Starter Brand Kit', 'description' => 'Logo, colour palette, typography guide, 100 business cards and 1 sticker set. Ideal for a business just starting out.'],
                    'ja' => ['name' => 'スターターブランドキット', 'description' => 'ロゴ、カラーパレット、タイポグラフィガイド、名刺100枚、ステッカー1セット。開業したての事業に最適です。'],
                ],
            ],
            [
                'slug' => 'grand-opening-bundle', 'category' => 'package', 'name' => 'Paket Grand Opening', 'price' => 3900000, 'delivery' => 'onsite', 'duration_minutes' => null,
                'tags' => ['promo'], 'requirements' => ['appointment', 'deposit', 'site_visit'], 'is_featured' => false,
                'description' => 'Spanduk, 200 brosur, foto produk, dan 8 konten media sosial untuk pembukaan toko Anda.',
                'translations' => [
                    'id' => ['name' => 'Paket Grand Opening', 'description' => 'Spanduk, 200 brosur, foto produk, dan 8 konten media sosial untuk pembukaan toko Anda.'],
                    'en' => ['name' => 'Grand Opening Bundle', 'description' => 'A banner, 200 flyers, product photos and 8 social media posts for your shop opening.'],
                    'ja' => ['name' => 'グランドオープンバンドル', 'description' => 'バナー、チラシ200枚、商品写真、SNS投稿8本をセットにした、開店準備パッケージ。'],
                ],
            ],
            [
                'slug' => 'brand-consultation', 'category' => 'consultation', 'name' => 'Konsultasi Merek 60 Menit', 'price' => 150000, 'delivery' => 'online', 'duration_minutes' => 60,
                'tags' => ['popular'], 'requirements' => ['appointment', 'internet'], 'is_featured' => false,
                'description' => 'Sesi 60 menit dengan desainer senior untuk membahas positioning, nama, dan arah visual usaha Anda.',
                'translations' => [
                    'id' => ['name' => 'Konsultasi Merek 60 Menit', 'description' => 'Sesi 60 menit dengan desainer senior untuk membahas positioning, nama, dan arah visual usaha Anda.'],
                    'en' => ['name' => 'Brand Consultation (60 min)', 'description' => 'A 60-minute session with a senior designer on your positioning, naming and visual direction.'],
                    'ja' => ['name' => 'ブランド相談（60分）', 'description' => 'シニアデザイナーと60分、ポジショニング、ネーミング、ビジュアルの方向性を相談できます。'],
                ],
            ],
            [
                'slug' => 'discovery-call', 'category' => 'consultation', 'name' => 'Discovery Call 30 Menit', 'price' => 0, 'delivery' => 'online', 'duration_minutes' => 30,
                'tags' => ['new', 'promo'], 'requirements' => ['internet'], 'is_featured' => false,
                'description' => 'Panggilan perkenalan gratis untuk menjelaskan kebutuhan Anda dan menerima rekomendasi paket yang sesuai.',
                'translations' => [
                    'id' => ['name' => 'Discovery Call 30 Menit', 'description' => 'Panggilan perkenalan gratis untuk menjelaskan kebutuhan Anda dan menerima rekomendasi paket yang sesuai.'],
                    'en' => ['name' => 'Discovery Call (30 min)', 'description' => 'A free introductory call to explain your needs and get a recommendation for the right package.'],
                    'ja' => ['name' => 'ディスカバリーコール（30分）', 'description' => 'ご要望をお聞きし、最適なパッケージをご提案する無料の初回相談です。'],
                ],
            ],
            [
                'slug' => 'editable-template-pack', 'category' => 'digital', 'name' => 'Paket Template Editable', 'price' => 99000, 'delivery' => 'online', 'duration_minutes' => null,
                'tags' => ['eco', 'bestseller'], 'requirements' => [], 'is_featured' => false,
                'description' => '30 template desain Canva dan Figma untuk menu, katalog, dan unggahan media sosial. Unduh langsung setelah pembayaran.',
                'translations' => [
                    'id' => ['name' => 'Paket Template Editable', 'description' => '30 template desain Canva dan Figma untuk menu, katalog, dan unggahan media sosial. Unduh langsung setelah pembayaran.'],
                    'en' => ['name' => 'Editable Template Pack', 'description' => '30 Canva and Figma design templates for menus, catalogs and social media posts. Download straight after payment.'],
                    'ja' => ['name' => '編集可能テンプレートパック', 'description' => 'メニュー、カタログ、SNS投稿向けのCanvaとFigmaのデザインテンプレート30点。お支払い後すぐにダウンロードできます。'],
                ],
            ],
            [
                'slug' => 'rush-delivery', 'category' => 'addon', 'name' => 'Tambahan Pengerjaan Kilat', 'price' => 100000, 'delivery' => 'delivery', 'duration_minutes' => null,
                'tags' => ['new'], 'requirements' => ['appointment'], 'is_featured' => false, 'is_unavailable' => true,
                'description' => 'Percepat pesanan cetak Anda menjadi selesai di hari yang sama, berlaku untuk pesanan yang masuk sebelum pukul 11.00.',
                'translations' => [
                    'id' => ['name' => 'Tambahan Pengerjaan Kilat', 'description' => 'Percepat pesanan cetak Anda menjadi selesai di hari yang sama, berlaku untuk pesanan yang masuk sebelum pukul 11.00.'],
                    'en' => ['name' => 'Rush Delivery Add-on', 'description' => 'Get your print order done the same day, for orders placed before 11:00.'],
                    'ja' => ['name' => '特急対応オプション', 'description' => '11:00までのご注文で、印刷物を当日中に仕上げます。'],
                ],
            ],
        ];
    }
}
