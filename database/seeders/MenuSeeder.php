<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            // ── Espresso Based ──────────────────────────────────
            [
                'name'        => 'Espresso',
                'description' => 'Shot espresso murni dengan karakter bold dan aroma kopi yang kuat.',
                'price'       => 13000,
                'category'    => 'espresso',
                'sort_order'  => 1,
            ],
            [
                'name'        => 'Americano',
                'description' => 'Espresso yang dilarutkan dengan air panas, menghasilkan kopi hitam yang lembut.',
                'price'       => 13000,
                'category'    => 'espresso',
                'sort_order'  => 2,
            ],
            [
                'name'        => 'Ice ZCoffee',
                'description' => 'Signature drink ZCoffee — espresso blend dengan susu dingin dan es batu.',
                'price'       => 15000,
                'category'    => 'espresso',
                'sort_order'  => 3,
            ],
            [
                'name'        => 'Kopi Amilk',
                'description' => 'Kopi susu khas ZCoffee dengan perpaduan espresso dan susu yang pas.',
                'price'       => 12000,
                'category'    => 'espresso',
                'sort_order'  => 4,
            ],
            [
                'name'        => 'Capuccino',
                'description' => 'Espresso dengan foam susu tebal dan tekstur yang creamy.',
                'price'       => 13000,
                'category'    => 'espresso',
                'sort_order'  => 5,
            ],

            // ── Manual Brewed ────────────────────────────────────
            [
                'name'        => 'French Press',
                'description' => 'Metode seduhan klasik menghasilkan kopi dengan body penuh dan rasa kaya.',
                'price'       => 13000,
                'category'    => 'manual',
                'sort_order'  => 1,
            ],
            [
                'name'        => 'Japanese',
                'description' => 'Cold brew method khas Jepang dengan ice drip yang menghasilkan kopi bersih.',
                'price'       => 13000,
                'category'    => 'manual',
                'sort_order'  => 2,
            ],
            [
                'name'        => 'V60',
                'description' => 'Pour-over V60 dengan teknik menuang yang presisi untuk floral notes terbaik.',
                'price'       => 13000,
                'category'    => 'manual',
                'sort_order'  => 3,
            ],
            [
                'name'        => 'Vietnam Drip',
                'description' => 'Drip khas Vietnam dengan hasil kopi yang pekat dan karakter robusta yang kuat.',
                'price'       => 13000,
                'category'    => 'manual',
                'sort_order'  => 4,
            ],
            [
                'name'        => 'Tubruk',
                'description' => 'Kopi tubruk tradisional — bubuk kopi diseduh langsung dengan air panas.',
                'price'       => 13000,
                'category'    => 'manual',
                'sort_order'  => 5,
            ],

            // ── Non Coffee ───────────────────────────────────────
            [
                'name'        => 'Chocolate',
                'description' => 'Minuman coklat creamy dengan susu segar, cocok untuk semua kalangan.',
                'price'       => 15000,
                'category'    => 'noncoffee',
                'sort_order'  => 1,
            ],
            [
                'name'        => 'Matcha',
                'description' => 'Matcha latte premium dengan susu segar dan bubuk green tea berkualitas.',
                'price'       => 15000,
                'category'    => 'noncoffee',
                'sort_order'  => 2,
            ],
            [
                'name'        => 'Red Velvet',
                'description' => 'Minuman Red Velvet lembut dengan perpaduan coklat dan cream cheese flavor.',
                'price'       => 15000,
                'category'    => 'noncoffee',
                'sort_order'  => 3,
            ],
            [
                'name'        => 'Lemon Tea',
                'description' => 'Teh segar dengan perasan lemon asli, menyegarkan dan kaya vitamin C.',
                'price'       => 15000,
                'category'    => 'noncoffee',
                'sort_order'  => 4,
            ],
            [
                'name'        => 'Taro',
                'description' => 'Minuman taro ungu yang creamy dengan aroma manis khas umbi talas.',
                'price'       => 15000,
                'category'    => 'noncoffee',
                'sort_order'  => 5,
            ],
            [
                'name'        => 'Mango Yakults',
                'description' => 'Minuman segar mangga blend dengan yakult, asam manis yang menyegarkan.',
                'price'       => 18000,
                'category'    => 'noncoffee',
                'sort_order'  => 6,
            ],
            [
                'name'        => 'Orange Yakults',
                'description' => 'Jus jeruk segar mix yakult dengan rasa citrus yang segar dan probiotik.',
                'price'       => 18000,
                'category'    => 'noncoffee',
                'sort_order'  => 7,
            ],
            [
                'name'        => 'Blue Sparkling',
                'description' => 'Minuman bersoda biru yang cantik dengan rasa butterfly pea flower yang unik.',
                'price'       => 18000,
                'category'    => 'noncoffee',
                'sort_order'  => 8,
            ],
        ];

        foreach ($menus as $menu) {
            Menu::updateOrCreate(
                ['name' => $menu['name'], 'category' => $menu['category']],
                array_merge($menu, ['is_active' => true])
            );
        }

        $this->command->info('✅ ' . count($menus) . ' menu items seeded successfully.');
    }
}
