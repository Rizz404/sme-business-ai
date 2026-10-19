<?php

namespace Database\Seeders;

use App\Models\Business;
use Illuminate\Database\Seeder;

class ServiceAreaSeeder extends Seeder
{
    public function run(): void
    {
        $business = Business::where('name', BusinessSeeder::NAME)->firstOrFail();
        $sort = 0;

        foreach ($this->serviceDefinitions() as $definition) {
            $business->serviceAreas()->updateOrCreate(
                ['slug' => $definition['slug']],
                [...$definition, 'sort_order' => $sort++, 'is_active' => true]
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function serviceDefinitions(): array
    {
        return [
            [
                'slug' => 'studio-front', 'name' => 'Studio Front', 'area_type' => 'storefront', 'min_attendees' => 1, 'max_attendees' => 4,
                'features' => ['wifi', 'air_conditioning', 'refreshments', 'parking'], 'booking_fee' => 0, 'minimum_order' => null,
                'description' => 'Etalase dan meja pelayanan di depan studio untuk melihat contoh cetak, memilih bahan, dan mengambil pesanan.',
                'translations' => [
                    'id' => ['name' => 'Etalase Studio', 'description' => 'Etalase dan meja pelayanan di depan studio untuk melihat contoh cetak, memilih bahan, dan mengambil pesanan.'],
                    'en' => ['name' => 'Studio Front', 'description' => 'The showcase and service desk at the front of the studio for browsing print samples, choosing materials and picking up orders.'],
                    'ja' => ['name' => 'スタジオフロント', 'description' => 'スタジオ入口のショーケースと受付カウンター。印刷サンプルの確認、素材選び、商品のお受け取りができます。'],
                ],
            ],
            [
                'slug' => 'workshop-room', 'name' => 'Workshop Room', 'area_type' => 'workshop', 'min_attendees' => 2, 'max_attendees' => 10,
                'features' => ['wifi', 'power_outlet', 'projector', 'whiteboard', 'air_conditioning'], 'booking_fee' => 100000, 'minimum_order' => 500000,
                'description' => 'Ruang kerja bersama untuk workshop branding, sesi revisi desain, dan pelatihan tim dengan proyektor serta papan tulis.',
                'translations' => [
                    'id' => ['name' => 'Ruang Workshop', 'description' => 'Ruang kerja bersama untuk workshop branding, sesi revisi desain, dan pelatihan tim dengan proyektor serta papan tulis.'],
                    'en' => ['name' => 'Workshop Room', 'description' => 'A shared working room for branding workshops, design revision sessions and team training, with a projector and whiteboard.'],
                    'ja' => ['name' => 'ワークショップルーム', 'description' => 'ブランディングのワークショップ、デザイン修正セッション、チーム研修に使える共同作業室。プロジェクターとホワイトボード付きです。'],
                ],
            ],
            [
                'slug' => 'online-consultation', 'name' => 'Online Consultation', 'area_type' => 'online', 'min_attendees' => 1, 'max_attendees' => 6,
                'features' => ['video_call', 'wifi', 'refreshments'], 'booking_fee' => 0, 'minimum_order' => null,
                'description' => 'Sesi video call dengan desainer atau tim produksi. Tautan rapat dikirim lewat WhatsApp atau email setelah dikonfirmasi.',
                'translations' => [
                    'id' => ['name' => 'Konsultasi Online', 'description' => 'Sesi video call dengan desainer atau tim produksi. Tautan rapat dikirim lewat WhatsApp atau email setelah dikonfirmasi.'],
                    'en' => ['name' => 'Online Consultation', 'description' => 'A video call with a designer or the production team. The meeting link is sent by WhatsApp or email once confirmed.'],
                    'ja' => ['name' => 'オンライン相談', 'description' => 'デザイナーや制作チームとのビデオ通話。確認後、WhatsAppまたはメールで会議リンクをお送りします。'],
                ],
            ],
            [
                'slug' => 'onsite-visit', 'name' => 'On-Site Visit', 'area_type' => 'onsite', 'min_attendees' => 1, 'max_attendees' => 5,
                'features' => ['equipment', 'parking'], 'booking_fee' => 150000, 'minimum_order' => 1000000,
                'description' => 'Tim kami datang ke lokasi usaha Anda di area Bandung untuk survei, pengukuran spanduk, atau foto produk di tempat.',
                'translations' => [
                    'id' => ['name' => 'Kunjungan ke Lokasi', 'description' => 'Tim kami datang ke lokasi usaha Anda di area Bandung untuk survei, pengukuran spanduk, atau foto produk di tempat.'],
                    'en' => ['name' => 'On-Site Visit', 'description' => 'Our team visits your business location in the Bandung area for a survey, banner measurements or on-location product photos.'],
                    'ja' => ['name' => '現地訪問', 'description' => 'バンドン周辺のお客様の事業所へ伺い、下見、バナーの採寸、現地での商品撮影を行います。'],
                ],
            ],
        ];
    }
}
