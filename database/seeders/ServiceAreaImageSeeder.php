<?php

namespace Database\Seeders;

use App\Models\Business;
use Illuminate\Database\Seeder;

class ServiceAreaImageSeeder extends Seeder
{
    public function run(): void
    {
        $business = Business::where('name', BusinessSeeder::NAME)->firstOrFail();

        foreach ($this->imageDefinitions() as $areaSlug => $images) {
            $area = $business->serviceAreas()->where('slug', $areaSlug)->firstOrFail();
            $sort = 0;

            foreach ($images as $image) {
                $area->images()->updateOrCreate(
                    ['image_url' => $image['url']],
                    ['tags' => $image['tags'], 'alt_text' => $image['alt'], 'sort_order' => $sort++]
                );
            }
        }
    }

    /**
     * Images per service area, keyed by the area's slug.
     *
     * @return array<string, list<array{url: string, tags: list<string>, alt: string}>>
     */
    private function imageDefinitions(): array
    {
        return [
            'studio-front' => [
                ['url' => 'images/service/studio-front.webp', 'tags' => ['showcase'], 'alt' => 'Etalase dan meja pelayanan Studio Front'],
                ['url' => 'images/scenes/storefront.webp', 'tags' => ['storefront'], 'alt' => 'Suasana depan studio'],
            ],
            'workshop-room' => [
                ['url' => 'images/service/workshop-room.webp', 'tags' => ['workshop'], 'alt' => 'Meja panjang dan papan tulis di Ruang Workshop'],
                ['url' => 'images/scenes/workspace.webp', 'tags' => ['room'], 'alt' => 'Suasana Ruang Workshop'],
            ],
            'online-consultation' => [
                ['url' => 'images/service/online-consultation.webp', 'tags' => ['video'], 'alt' => 'Layar video call konsultasi online'],
            ],
            'onsite-visit' => [
                ['url' => 'images/service/onsite-visit.webp', 'tags' => ['visit'], 'alt' => 'Tim studio mengunjungi lokasi usaha pelanggan'],
            ],
        ];
    }
}
