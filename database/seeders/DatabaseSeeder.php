<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->environment('local', 'testing')) {
            $this->call([
                UserSeeder::class,
                BusinessSeeder::class,
                BusinessUserSeeder::class,
                CatalogItemSeeder::class,
                ServiceAreaSeeder::class,
                ServiceAreaImageSeeder::class,
                SlotInventorySeeder::class,
                BusinessKnowledgeItemSeeder::class,
            ]);
        }
    }
}
