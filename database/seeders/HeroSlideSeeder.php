<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HeroSlide;

class HeroSlideSeeder extends Seeder
{
    public function run(): void
    {
        $slides = [
            [
                'title' => 'Discover Premium Products',
                'subtitle' => 'Curated collection for every lifestyle',
                'button_text' => 'Shop Now',
                'link_url' => '/shop',
                'image_path' => 'https://picsum.photos/seed/hero-slide-1/600/600',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Exclusive Islamabad Deals',
                'subtitle' => 'Fast delivery and cash on delivery available',
                'button_text' => 'View Specials',
                'link_url' => '/shop?sort=featured',
                'image_path' => 'https://picsum.photos/seed/hero-slide-2/600/600',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Smart Shopping in Pakistan',
                'subtitle' => 'Quality electronics, fashion, and home essentials',
                'button_text' => 'Explore All',
                'link_url' => '/shop',
                'image_path' => 'https://picsum.photos/seed/hero-slide-3/600/600',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($slides as $slide) {
            HeroSlide::firstOrCreate(['title' => $slide['title']], $slide);
        }
    }
}
