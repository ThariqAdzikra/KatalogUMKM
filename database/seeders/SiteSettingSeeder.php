<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSettings = [
            // Logo & Branding
            [
                'key' => 'logo_path',
                'value' => 'images/logo.png',
                'type' => 'image',
                'group' => 'appearance',
            ],
            [
                'key' => 'brand_name',
                'value' => 'LaptopPremium',
                'type' => 'string',
                'group' => 'appearance',
            ],
            
            // Hero Section
            [
                'key' => 'hero_title',
                'value' => "Temukan Laptop\nImpian Anda",
                'type' => 'text',
                'group' => 'hero',
            ],
            [
                'key' => 'hero_subtitle',
                'value' => 'Koleksi laptop terlengkap dengan spesifikasi terbaik untuk kebutuhan kerja, gaming, dan entertainment. Dapatkan harga terbaik dengan garansi resmi dan layanan purna jual terpercaya.',
                'type' => 'text',
                'group' => 'hero',
            ],
            
            // Carousel
            [
                'key' => 'carousel_images',
                'value' => json_encode(['images/background.jpeg']),
                'type' => 'json',
                'group' => 'carousel',
            ],
        ];

        foreach ($defaultSettings as $setting) {
            SiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
