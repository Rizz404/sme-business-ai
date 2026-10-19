<?php

namespace Database\Seeders;

use App\Models\Business;
use Illuminate\Database\Seeder;

class BusinessSeeder extends Seeder
{
    /**
     * The demo business's name, used by the other seeders to look it up.
     */
    public const NAME = 'Maju Jaya Studio';

    public function run(): void
    {
        Business::firstOrCreate(
            ['name' => self::NAME],
            [
                'slug' => Business::generateUniqueSlug(self::NAME),
                'description' => 'Studio desain dan cetak di Bandung untuk UMKM: logo, kemasan, konten media sosial, dan cetak kebutuhan usaha dengan harga yang jelas.',
                'translations' => [
                    'id' => ['description' => 'Studio desain dan cetak di Bandung untuk UMKM: logo, kemasan, konten media sosial, dan cetak kebutuhan usaha dengan harga yang jelas.'],
                    'en' => ['description' => 'A design and print studio in Bandung for small businesses: logos, packaging, social media content and everyday business printing with clear prices.'],
                    'ja' => ['description' => 'バンドンにある中小事業者向けのデザイン・印刷スタジオ。ロゴ、パッケージ、SNSコンテンツ、事業用の印刷を明朗な価格でご提供します。'],
                ],
                'address' => 'Jl. Braga No. 18, Braga',
                'city' => 'Bandung',
                'country' => 'Indonesia',
                'latitude' => -6.9175,
                'longitude' => 107.6091,
                'phone' => '+62 22 4201 8800',
                'whatsapp' => '6281234567890',
                'email' => 'hello@smebusiness.test',
                'instagram' => '@majujaya.studio.demo',
                'timezone' => 'Asia/Jakarta',
                'currency' => 'IDR',
                'default_locale' => 'id',
                'opening_time' => '09:00',
                'closing_time' => '18:00',
                'public_status' => 'published',
            ]
        );
    }
}
