<?php
/**
 * Standalone helper: generates a local_products.json file.
 *
 * This file is NOT loaded by the plugin. It is a utility
 * that can be run via WP-CLI or direct admin action.
 */

defined( 'ABSPATH' ) || exit;

$upload_dir = wp_upload_dir();
$output_dir = $upload_dir['basedir'] . '/si-flash-products';
if ( ! file_exists( $output_dir ) ) {
    wp_mkdir_p( $output_dir );
}
$output_file = $output_dir . '/local_products.json';

$lang_data = array(
    'it' => array(
        'categories' => array(
            'Elettronica' => array('Smartphone', 'Laptop', 'Cuffie Bluetooth', 'Smartwatch', 'Tablet', 'Fotocamera', 'Monitor 4K', 'Tastiera Meccanica', 'Power Bank', 'Speaker Wireless'),
            'Casa'        => array('Lampada LED', 'Sedia Ergonomica', 'Tavolo in Legno', 'Quadro Moderno', 'Vaso in Ceramica', 'Tappeto Soft', 'Specchio', 'Divano 3 Posti', 'Set Posate', 'Macchina Caffè'),
            'Abbigliamento' => array('T-shirt Cotone', 'Jeans Slim Fit', 'Felpa con Cappuccio', 'Giacca Invernale', 'Scarpe Sportive', 'Cintura in Pelle', 'Cappello', 'Pantaloni Chino', 'Camicia Oxford'),
            'Bellezza'    => array('Crema Idratante', 'Profumo Luxury', 'Shampoo Bio', 'Siero Viso', 'Maschera Argilla', 'Rossetto Matte', 'Smalto', 'Crema Solare', 'Balsamo'),
            'Sport'       => array('Tappetino Yoga', 'Manubri 5kg', 'Palla da Basket', 'Corda per Saltare', 'Borraccia Termica', 'Zaino Trekking', 'Pesi Caviglie', 'Rullo Massaggi', 'Banda Elastica'),
        ),
        'adjectives'  => array('Rivoluzionario', 'Professionale', 'Eco-sostenibile', 'Intelligente', 'Classico', 'Moderno', 'Ultra-resistente', 'Essenziale', 'Edizione Limitata', 'Compatto', 'Superiore', 'Elite', 'Definitivo'),
        'ingredients' => array('Acqua Termale', 'Estratto di Aloe', 'Olio di Argan', 'Acido Ialuronico', 'Vitamina C', 'Burro di Shea', 'Proteine della Seta'),
        'allergens'   => array('Glutine', 'Lattosio', 'Frutta a guscio', 'Soia', 'Nichel Free'),
        'desc'        => "<p>Prodotto di alta qualità della categoria %s. Perfetto per ogni esigenza.</p>",
        'excerpt'     => "Descrizione breve per %s.",
        'tags'        => array('offerta', 'esclusivo'),
    ),
    'en' => array(
        'categories' => array(
            'Electronics' => array('Smartphone', 'Laptop', 'Bluetooth Headphones', 'Smartwatch', 'Tablet', 'Camera', '4K Monitor', 'Mechanical Keyboard', 'Power Bank', 'Wireless Speaker'),
            'Home'        => array('LED Lamp', 'Ergonomic Chair', 'Wooden Table', 'Modern Painting', 'Ceramic Vase', 'Soft Rug', 'Mirror', '3-Seater Sofa', 'Cutlery Set', 'Coffee Machine'),
            'Clothing'    => array('Cotton T-shirt', 'Slim Fit Jeans', 'Hoodie', 'Winter Jacket', 'Sports Shoes', 'Leather Belt', 'Hat', 'Chino Pants', 'Oxford Shirt'),
            'Beauty'      => array('Moisturizing Cream', 'Luxury Perfume', 'Organic Shampoo', 'Face Serum', 'Clay Mask', 'Matte Lipstick', 'Nail Polish', 'Sunscreen', 'Hair Conditioner'),
            'Sports'      => array('Yoga Mat', 'Dumbbells 5kg', 'Basketball', 'Jump Rope', 'Thermal Bottle', 'Trekking Backpack', 'Ankle Weights', 'Massage Roller', 'Resistance Band'),
        ),
        'adjectives'  => array('Revolutionary', 'Professional', 'Eco-friendly', 'Smart', 'Classic', 'Modern', 'Ultra-durable', 'Essential', 'Limited Edition', 'Compact', 'Superior', 'Elite', 'Ultimate'),
        'ingredients' => array('Thermal Water', 'Aloe Extract', 'Argan Oil', 'Hyaluronic Acid', 'Vitamin C', 'Shea Butter', 'Silk Proteins'),
        'allergens'   => array('Gluten', 'Lactose', 'Tree Nuts', 'Soy', 'Nickel Free'),
        'desc'        => "<p>High quality product from the %s category. Perfect for any need.</p>",
        'excerpt'     => "Short description for %s.",
        'tags'        => array('sale', 'exclusive'),
    ),
);

$cat_image_map = array(
    'Electronics' => 'Elettronica',
    'Home'        => 'Casa',
    'Clothing'    => 'Abbigliamento',
    'Beauty'      => 'Bellezza',
    'Sports'      => 'Sport',
);

$category_images = array(
    'Elettronica' => array(
        'main' => array(
            'photo-1468495244123-6c6c332eeece', 'photo-1505740420928-5e560c06d30e',
            'photo-1523275335684-37898b6baf30', 'photo-1526170315830-ef18a283ac13',
            'photo-1498049792014-1850a1b1b0b9',
        ),
        'gallery' => array(
            'photo-1544244015-0df4b3ffc6b0', 'photo-1561948955-570b270e7c36',
            'photo-1531297484001-80022131f5a1',
        ),
    ),
    'Casa' => array(
        'main' => array(
            'photo-1555041469-a586c61ea9bc', 'photo-1493663284031-b7e3aefcae8e',
            'photo-1507003211169-0a1dd7228f2d', 'photo-1540574163026-643ea20ade25',
            'photo-1533090161767-e6ffed986c88',
        ),
        'gallery' => array(
            'photo-1487700160040-b2e5f2b9c0a0', 'photo-1524758631624-e2822e304c36',
            'photo-1560448204-e02f11c3d0e2',
        ),
    ),
    'Abbigliamento' => array(
        'main' => array(
            'photo-1491553895911-0055eca6402d', 'photo-1523381210434-271e8be1f52b',
            'photo-1542291026-7eec264c27ff', 'photo-1551028719-00167b16eac5',
            'photo-1512436991641-6745b0cfb1b1',
        ),
        'gallery' => array(
            'photo-1556905055-8f358a7a47b2', 'photo-1549298916-b41d501d3772',
            'photo-1517404215738-1526349b4db0',
        ),
    ),
    'Bellezza' => array(
        'main' => array(
            'photo-1596462502278-27bfdc403348', 'photo-1522335789203-aabd1fc54bc8',
            'photo-1570172619644-dfd03ed5d881', 'photo-1567721913486-6585f069b332',
        ),
        'gallery' => array(
            'photo-1556228578-0d85b1a4d571', 'photo-1596755389378-c31d21fd1273',
            'photo-1608248543803-ba4f8c70ae0b',
        ),
    ),
    'Sport' => array(
        'main' => array(
            'photo-1571019613454-1cb2f99b2d8b', 'photo-1517836357463-d25dfeac3438',
            'photo-1519861531473-920026212ad0', 'photo-1518611012118-696072aa579a',
            'photo-1556817411-31ae72fa3ea0',
        ),
        'gallery' => array(
            'photo-1534438327276-14e5300c3a48', 'photo-1562183241-b937e95585b6',
            'photo-1571902943202-507ec2618e8f',
        ),
    ),
);

$colors    = array('Rosso', 'Blu', 'Nero', 'Bianco', 'Verde', 'Grigio', 'Oro', 'Argento');
$img_base  = 'https://images.unsplash.com/';
$img_params = '?auto=format&fit=crop&w=800&q=80';

$products = array();
$total    = 500; // per language

foreach ( $lang_data as $lang_code => $lang ) {
    $cat_keys  = array_keys( $lang['categories'] );
    $adj_count = count( $lang['adjectives'] );
    $ing_count = count( $lang['ingredients'] );
    $all_count = count( $lang['allergens'] );

    for ( $i = 1; $i <= $total; $i++ ) {
        $category  = $cat_keys[ array_rand( $cat_keys ) ];
        $base_name = $lang['categories'][ $category ][ array_rand( $lang['categories'][ $category ] ) ];
        $adj       = $lang['adjectives'][ array_rand( $lang['adjectives'] ) ];
        $title     = "$base_name $adj";

        $price      = rand( 10, 500 ) + ( rand( 0, 99 ) / 100 );
        $sale_price = rand( 0, 1 ) ? ( $price * 0.8 ) : '';

        $image_cat  = isset( $cat_image_map[ $category ] ) ? $cat_image_map[ $category ] : $category;
        $cat_imgs   = $category_images[ $image_cat ] ?? $category_images['Elettronica'];
        $main_img   = $img_base . $cat_imgs['main'][ $i % count( $cat_imgs['main'] ) ] . $img_params;
        $gallery1   = $img_base . $cat_imgs['gallery'][ $i % count( $cat_imgs['gallery'] ) ] . $img_params;
        $gallery2   = $img_base . $cat_imgs['gallery'][ ( $i + 1 ) % count( $cat_imgs['gallery'] ) ] . $img_params;

        $ing = '';
        if ( in_array( $category, array( 'Bellezza', 'Beauty' ), true ) || rand( 1, 10 ) > 8 ) {
            $ing = $lang['ingredients'][ array_rand( $lang['ingredients'] ) ] . ', ' . $lang['ingredients'][ array_rand( $lang['ingredients'] ) ];
        }
        $all = '';
        if ( in_array( $category, array( 'Bellezza', 'Beauty' ), true ) && rand( 1, 10 ) > 7 ) {
            $all = $lang['allergens'][ array_rand( $lang['allergens'] ) ];
        }

        $products[] = array(
            'post_title'      => $title,
            'post_content'    => sprintf( $lang['desc'], $category ),
            'post_excerpt'    => sprintf( $lang['excerpt'], $title ),
            'sifp_categories' => $category,
            'sifp_tag'        => strtolower( $category ) . ', ' . $adj . ', ' . implode( ', ', $lang['tags'] ),
            'sifp_img'        => $main_img,
            'sifp_gallery'    => "$gallery1,$gallery2",
            'regular_price'   => number_format( $price, 2, '.', '' ),
            'sale_price'      => $sale_price ? number_format( $sale_price, 2, '.', '' ) : '',
            'sku'             => 'SIFP-' . strtoupper( substr( $image_cat, 0, 3 ) ) . '-' . str_pad( $i, 4, '0', STR_PAD_LEFT ),
            'stock_status'    => 'instock',
            'stock_qty'       => rand( 10, 200 ),
            'weight'          => ( rand( 1, 50 ) / 10 ),
            'length'          => rand( 10, 100 ),
            'width'           => rand( 10, 100 ),
            'height'          => rand( 5, 50 ),
            'is_virtual'      => 'no',
            'is_downloadable' => 'no',
            'sifp_ingredient' => $ing,
            'sifp_allerg'     => $all,
            'attributes'      => array(
                array(
                    'name'   => 'Colore',
                    'values' => implode( ' | ', (array) array_intersect_key( $colors, array_flip( (array) array_rand( $colors, 3 ) ) ) ),
                ),
                array(
                    'name'   => 'Taglia',
                    'values' => 'S | M | L | XL',
                ),
            ),
        );
    }
}

file_put_contents( $output_file, wp_json_encode( $products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
echo "Generati " . count( $products ) . " prodotti in " . esc_html( $output_file ) . "\n";