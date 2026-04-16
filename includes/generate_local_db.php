<?php
defined( 'ABSPATH' ) || exit;

$categories_data = [
    'Elettronica' => ['Smartphone', 'Laptop', 'Cuffie Bluetooth', 'Smartwatch', 'Tablet', 'Fotocamera', 'Monitor 4K', 'Tastiera Meccanica'],
    'Casa' => ['Lampada LED', 'Sedia Ergonomica', 'Tavolo in Legno', 'Quadro Moderno', 'Vaso in Ceramica', 'Tappeto Soft', 'Specchio', 'Divano 3 Posti'],
    'Abbigliamento' => ['T-shirt Cotone', 'Jeans Slim Fit', 'Felpa con Cappuccio', 'Giacca Invernale', 'Scarpe Sportive', 'Cintura in Pelle', 'Cappello'],
    'Bellezza' => ['Crema Idratante', 'Profumo Luxury', 'Shampoo Bio', 'Siero Viso', 'Maschera Argilla', 'Rossetto Matte', 'Smalto'],
    'Sport' => ['Tappetino Yoga', 'Manubri 5kg', 'Palla da Basket', 'Corda per Saltare', 'Borraccia Termica', 'Zaino Trekking', 'Pesi Caviglie']
];

$adjectives = ['Premium', 'Professional', 'Eco-friendly', 'Smart', 'Classic', 'Modern', 'Ultra', 'Essential', 'Limited Edition', 'Compact'];
$colors = ['Rosso', 'Blu', 'Nero', 'Bianco', 'Verde', 'Grigio', 'Oro', 'Argento'];

$products = [];

for ($i = 1; $i <= 1000; $i++) {
    $cat_keys = array_keys($categories_data);
    $category = $cat_keys[array_rand($cat_keys)];
    $base_name = $categories_data[$category][array_rand($categories_data[$category])];
    $adj = $adjectives[array_rand($adjectives)];
    
    $title = "$base_name $adj " . ($i % 100);
    $price = rand(10, 500) + (rand(0, 99) / 100);
    $sale_price = rand(0, 1) ? ($price * 0.8) : '';
    
    $products[] = [
        'post_title' => $title,
        'post_content' => "<p>Questo è un prodotto di alta qualità della categoria $category. Perfetto per ogni esigenza.</p>",
        'post_excerpt' => "Descrizione breve per $title.",
        'sifp_categories' => $category,
        'sifp_tag' => strtolower($category) . ", $adj",
        'sifp_img' => "https://picsum.photos/800/800?random=$i",
        'sifp_gallery' => "https://picsum.photos/800/800?random=" . ($i + 1000) . ",https://picsum.photos/800/800?random=" . ($i + 2000),
        'regular_price' => number_format($price, 2, '.', ''),
        'sale_price' => $sale_price ? number_format($sale_price, 2, '.', '') : '',
        'sku' => "SIFP-" . strtoupper(substr($category, 0, 3)) . "-" . str_pad($i, 4, '0', STR_PAD_LEFT),
        'stock_status' => 'instock',
        'stock_qty' => rand(10, 200),
        'weight' => (rand(1, 50) / 10),
        'length' => rand(10, 100),
        'width' => rand(10, 100),
        'height' => rand(5, 50),
        'is_virtual' => 'no',
        'is_downloadable' => 'no',
        'attributes' => [
            [
                'name' => 'Colore',
                'values' => implode(' | ', (array)array_intersect_key($colors, array_flip((array)array_rand($colors, 3))))
            ],
            [
                'name' => 'Taglia',
                'values' => 'S | M | L | XL'
            ]
        ]
    ];
}

file_put_contents('local_products.json', json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Generati 1000 prodotti in local_products.json\n";
