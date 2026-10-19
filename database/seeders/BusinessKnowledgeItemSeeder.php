<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessKnowledgeItem;
use Illuminate\Database\Seeder;

class BusinessKnowledgeItemSeeder extends Seeder
{
    public function run(): void
    {
        $business = Business::where('name', BusinessSeeder::NAME)->firstOrFail();
        $sort = 0;

        foreach ($this->knowledgeDefinitions() as $definition) {
            $business->knowledgeItems()->updateOrCreate(
                ['title' => $definition['title']],
                [...$definition, 'sort_order' => $sort++, 'is_active' => true]
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function knowledgeDefinitions(): array
    {
        return [
            [
                'category' => BusinessKnowledgeItem::CATEGORY_GENERAL, 'title' => 'Tentang Maju Jaya Studio', 'tags' => ['about', 'cerita', 'story'], 'image_url' => null,
                'body' => 'Maju Jaya Studio adalah studio desain dan cetak di Bandung yang membantu UMKM tampil rapi: logo, kemasan, konten media sosial, dan cetak kebutuhan usaha. Kami buka Senin sampai Sabtu pukul 09.00 sampai 18.00.',
                'translations' => [
                    'en' => ['title' => 'About Maju Jaya Studio', 'body' => 'Maju Jaya Studio is a design and print studio in Bandung that helps small businesses look their best: logos, packaging, social media content and everyday printing. We are open Monday to Saturday from 09:00 to 18:00.'],
                    'ja' => ['title' => 'Maju Jaya Studioについて', 'body' => 'Maju Jaya Studioはバンドンのデザイン・印刷スタジオで、ロゴ、パッケージ、SNSコンテンツ、事業用の印刷を通じて中小事業者の魅力づくりをお手伝いします。営業は月曜〜土曜の9:00〜18:00です。'],
                ],
            ],
            [
                'category' => BusinessKnowledgeItem::CATEGORY_POLICIES, 'title' => 'Kebijakan pemesanan dan pembatalan', 'tags' => ['pemesanan', 'booking', 'batal', 'cancel'], 'image_url' => null,
                'body' => 'Slot ditahan selama 15 menit dari awal sesi. Pembatalan atau perubahan jadwal gratis sampai 24 jam sebelum sesi. Setiap sesi berlangsung 2 jam. Biaya pemesanan hanya berlaku untuk Ruang Workshop dan Kunjungan ke Lokasi, dan dihitung sebagai bagian dari minimum pesanan.',
                'translations' => [
                    'en' => ['title' => 'Booking and cancellation policy', 'body' => 'Slots are held for 15 minutes from the start of the session. Cancelling or rescheduling is free up to 24 hours before the session. Every session lasts 2 hours. The booking fee only applies to the Workshop Room and On-Site Visit, and counts towards the minimum order.'],
                    'ja' => ['title' => 'ご予約とキャンセルのポリシー', 'body' => '枠はセッション開始から15分間お取り置きします。キャンセルや日程変更はセッションの24時間前まで無料です。1セッションは2時間です。予約費用はワークショップルームと現地訪問のみ必要で、最低ご注文額に充当されます。'],
                ],
            ],
            [
                'category' => BusinessKnowledgeItem::CATEGORY_POLICIES, 'title' => 'Revisi dan hak cipta', 'tags' => ['revisi', 'revision', 'hak cipta', 'copyright', 'lisensi'], 'image_url' => null,
                'body' => 'Setiap paket desain mencakup jumlah revisi yang tertulis pada deskripsinya; revisi tambahan dihitung terpisah. Setelah pelunasan, hak pakai atas desain final menjadi milik Anda, sementara berkas mentah tetap disimpan oleh studio kecuali diminta secara tertulis.',
                'translations' => [
                    'en' => ['title' => 'Revisions and ownership', 'body' => 'Each design package includes the number of revisions stated in its description; extra revisions are charged separately. Once paid in full, the usage rights to the final design are yours, while raw working files stay with the studio unless requested in writing.'],
                    'ja' => ['title' => '修正と権利について', 'body' => '各デザインパッケージには説明に記載の回数の修正が含まれ、追加の修正は別料金です。お支払い完了後、最終デザインの使用権はお客様に帰属し、作業用の元データは書面でのご依頼がない限り当スタジオが保管します。'],
                ],
            ],
            [
                'category' => BusinessKnowledgeItem::CATEGORY_POLICIES, 'title' => 'Metode pembayaran', 'tags' => ['bayar', 'payment', 'qris', 'transfer', 'dp'], 'image_url' => null,
                'body' => 'Kami menerima transfer bank, QRIS, dan kartu debit. Paket desain dan cetak besar memerlukan uang muka 50 persen, sisanya dilunasi saat pengambilan atau sebelum berkas final dikirim.',
                'translations' => [
                    'en' => ['title' => 'Payment methods', 'body' => 'We accept bank transfer, QRIS and debit cards. Design packages and large print jobs need a 50 percent deposit, with the balance paid on pickup or before the final files are sent.'],
                    'ja' => ['title' => 'お支払い方法', 'body' => '銀行振込、QRIS、デビットカードをご利用いただけます。デザインパッケージと大口の印刷は50%の手付金が必要で、残額はお受け取り時、または最終データの送付前にお支払いください。'],
                ],
            ],
            [
                'category' => BusinessKnowledgeItem::CATEGORY_FACILITIES, 'title' => 'Wi-Fi gratis dan area tunggu', 'tags' => ['wifi', 'internet', 'tunggu', 'waiting'], 'image_url' => null,
                'body' => 'Wi-Fi cepat gratis untuk semua tamu studio, dilengkapi sofa area tunggu, air minum, dan colokan listrik untuk mengisi daya sambil menunggu cetakan selesai.',
                'translations' => [
                    'en' => ['title' => 'Free Wi-Fi and waiting area', 'body' => 'Fast Wi-Fi is free for all studio visitors, with a sofa waiting area, drinking water and power outlets so you can charge while your prints finish.'],
                    'ja' => ['title' => '無料Wi-Fiと待合スペース', 'body' => '来店のお客様は高速Wi-Fiを無料でご利用いただけます。ソファの待合スペース、お水、電源もあり、印刷の仕上がりを待つ間に充電できます。'],
                ],
            ],
            [
                'category' => BusinessKnowledgeItem::CATEGORY_FACILITIES, 'title' => 'Studio foto mini', 'tags' => ['foto', 'photo', 'studio', 'produk'], 'image_url' => null,
                'body' => 'Sudut foto produk dengan latar polos, lampu softbox, dan meja putar. Dapat dipakai bersama layanan Foto Produk atau disewa per jam setelah berkonsultasi dengan tim.',
                'translations' => [
                    'en' => ['title' => 'Mini photo studio', 'body' => 'A product photo corner with plain backdrops, softbox lights and a turntable. Use it with the Product Photo Shoot service, or rent it by the hour after talking to the team.'],
                    'ja' => ['title' => 'ミニ撮影スタジオ', 'body' => '無地の背景、ソフトボックス、ターンテーブルを備えた商品撮影コーナー。商品撮影サービスと併せて、またはチームへの相談後に1時間単位でご利用いただけます。'],
                ],
            ],
            [
                'category' => BusinessKnowledgeItem::CATEGORY_FACILITIES, 'title' => 'Proofing cetak sebelum produksi', 'tags' => ['proof', 'contoh', 'sample', 'cetak'], 'image_url' => null,
                'body' => 'Untuk pesanan cetak di atas Rp 500.000, kami menyediakan cetak contoh satu lembar agar warna dan bahan dapat Anda periksa sebelum produksi massal dimulai.',
                'translations' => [
                    'en' => ['title' => 'Print proofing before production', 'body' => 'For print orders above Rp 500,000 we provide a one-sheet proof so you can check colours and material before full production starts.'],
                    'ja' => ['title' => '本生産前の試し刷り', 'body' => 'Rp 500,000を超える印刷のご注文には、色や素材を確認いただけるよう、本生産の前に1枚の試し刷りをご用意します。'],
                ],
            ],
            [
                'category' => BusinessKnowledgeItem::CATEGORY_FACILITIES, 'title' => 'Akses parkir dan ramah kursi roda', 'tags' => ['parkir', 'parking', 'kursi roda', 'wheelchair', 'akses'], 'image_url' => null,
                'body' => 'Tersedia parkir motor dan dua tempat parkir mobil di depan studio. Pintu masuk tanpa tangga dan toilet yang dapat diakses kursi roda.',
                'translations' => [
                    'en' => ['title' => 'Parking and wheelchair access', 'body' => 'Motorbike parking and two car spaces are available in front of the studio. The entrance is step-free and the restroom is wheelchair accessible.'],
                    'ja' => ['title' => '駐車場と車いす対応', 'body' => 'スタジオ前にバイク用駐輪場と車2台分の駐車場があります。入口は段差がなく、お手洗いは車いすでもご利用いただけます。'],
                ],
            ],
            [
                'category' => BusinessKnowledgeItem::CATEGORY_EVENTS, 'title' => 'Workshop branding untuk UMKM', 'tags' => ['workshop', 'branding', 'pelatihan', 'training'], 'image_url' => null,
                'body' => 'Setiap bulan kami mengadakan workshop branding setengah hari untuk pemilik usaha: menentukan nama, warna, dan gaya visual. Pendaftaran lewat tim kami; kuota 10 peserta per sesi.',
                'translations' => [
                    'en' => ['title' => 'Branding workshop for small businesses', 'body' => 'Every month we run a half-day branding workshop for business owners: choosing a name, colours and visual style. Register through our team; capacity is 10 participants per session.'],
                    'ja' => ['title' => '中小事業者向けブランディングワークショップ', 'body' => '毎月、事業主向けに半日のブランディングワークショップを開催しています。名前、色、ビジュアルスタイルを決めます。お申し込みはスタッフまで。1回の定員は10名です。'],
                ],
            ],
            [
                'category' => BusinessKnowledgeItem::CATEGORY_EVENTS, 'title' => 'Kemitraan dan pesanan korporat', 'tags' => ['kemitraan', 'partnership', 'korporat', 'bulk', 'grosir'], 'image_url' => null,
                'body' => 'Kami menerima kemitraan dengan komunitas, reseller, dan perusahaan untuk pesanan dalam jumlah besar. Harga khusus dan jadwal produksi disusun oleh tim; hubungi kami untuk penawaran.',
                'translations' => [
                    'en' => ['title' => 'Partnerships and corporate orders', 'body' => 'We partner with communities, resellers and companies on large orders. Special pricing and production schedules are arranged by the team; contact us for a quotation.'],
                    'ja' => ['title' => '提携と法人のご注文', 'body' => 'コミュニティ、リセラー、企業と大口注文での提携を承っています。特別価格と製作スケジュールはスタッフがご相談のうえ作成しますので、お見積りはお問い合わせください。'],
                ],
            ],
            [
                'category' => BusinessKnowledgeItem::CATEGORY_LOCATION, 'title' => 'Lokasi dan parkir', 'tags' => ['lokasi', 'location', 'alamat', 'address', 'parkir'], 'image_url' => null,
                'body' => 'Studio kami berada di Jl. Braga No. 18, Bandung, sekitar lima menit jalan kaki dari Jalan Asia Afrika. Parkir tersedia di depan studio.',
                'translations' => [
                    'en' => ['title' => 'Location and parking', 'body' => 'Our studio is at Jl. Braga No. 18, Bandung, about a five-minute walk from Jalan Asia Afrika. Parking is available in front of the studio.'],
                    'ja' => ['title' => 'アクセスと駐車場', 'body' => '当スタジオはバンドンのJl. Braga No. 18にあり、Jalan Asia Afrikaから徒歩約5分です。スタジオ前に駐車場があります。'],
                ],
            ],
            [
                'category' => BusinessKnowledgeItem::CATEGORY_LOCATION, 'title' => 'Pengiriman dan pengambilan pesanan', 'tags' => ['delivery', 'kirim', 'antar', 'pickup', 'ambil'], 'image_url' => null,
                'body' => 'Pesanan dapat diambil di studio atau dikirim lewat kurir ke seluruh Indonesia. Pengiriman dalam kota Bandung memakai kurir instan dengan ongkos sesuai tarif aplikasi.',
                'translations' => [
                    'en' => ['title' => 'Delivery and pickup', 'body' => 'Orders can be picked up at the studio or shipped by courier across Indonesia. Within Bandung we use an instant courier, charged at the app\'s rate.'],
                    'ja' => ['title' => '配送とお受け取り', 'body' => 'ご注文はスタジオでのお受け取りか、インドネシア全国への宅配が可能です。バンドン市内は即日配達の配送業者を使用し、料金はアプリの料金に従います。'],
                ],
            ],
            [
                'category' => BusinessKnowledgeItem::CATEGORY_FAQ, 'title' => 'Apakah boleh datang tanpa janji temu?', 'tags' => ['walk-in', 'janji', 'appointment', 'datang'], 'image_url' => null,
                'body' => 'Boleh untuk melihat contoh dan memesan cetak ringan. Untuk konsultasi desainer, workshop, atau kunjungan lokasi, kami sarankan memesan slot terlebih dahulu agar tim siap.',
                'translations' => [
                    'en' => ['title' => 'Can I drop in without an appointment?', 'body' => 'Yes, to browse samples and order simple prints. For designer consultations, workshops or site visits we recommend booking a slot first so the team is ready.'],
                    'ja' => ['title' => '予約なしで伺ってもよいですか？', 'body' => 'サンプルの確認や簡単な印刷のご注文でしたら可能です。デザイナーへの相談、ワークショップ、現地訪問は、準備のため事前に枠をご予約いただくことをおすすめします。'],
                ],
            ],
            [
                'category' => BusinessKnowledgeItem::CATEGORY_FAQ, 'title' => 'Berapa lama pengerjaan desain dan cetak?', 'tags' => ['lama', 'durasi', 'turnaround', 'waktu', 'kilat'], 'image_url' => null,
                'body' => 'Cetak kartu nama dan stiker memerlukan 2 hari kerja setelah desain disetujui. Desain logo memerlukan 5 hari kerja. Layanan pengerjaan kilat untuk hari yang sama tersedia terbatas; tanyakan kepada tim.',
                'translations' => [
                    'en' => ['title' => 'How long do design and printing take?', 'body' => 'Business cards and stickers take 2 working days after the design is approved. A logo design takes 5 working days. Same-day rush service is limited; ask the team.'],
                    'ja' => ['title' => 'デザインと印刷にはどのくらいかかりますか？', 'body' => '名刺とステッカーはデザイン承認後2営業日、ロゴデザインは5営業日です。当日仕上げの特急対応は数に限りがありますので、スタッフにお尋ねください。'],
                ],
            ],
            [
                'category' => BusinessKnowledgeItem::CATEGORY_FAQ, 'title' => 'Apakah ada faktur atau kuitansi resmi?', 'tags' => ['faktur', 'invoice', 'kuitansi', 'pajak', 'npwp'], 'image_url' => null,
                'body' => 'Ya. Kami menerbitkan faktur atau kuitansi untuk setiap pesanan. Beri tahu nama perusahaan dan NPWP Anda saat memesan bila memerlukan faktur atas nama usaha.',
                'translations' => [
                    'en' => ['title' => 'Do you issue invoices or official receipts?', 'body' => 'Yes. We issue an invoice or receipt for every order. Tell us your company name and tax number when ordering if you need an invoice in the business\'s name.'],
                    'ja' => ['title' => '請求書や領収書は発行されますか？', 'body' => 'はい、すべてのご注文で請求書または領収書を発行します。法人名義の請求書が必要な場合は、ご注文時に会社名と納税者番号をお知らせください。'],
                ],
            ],
        ];
    }
}
