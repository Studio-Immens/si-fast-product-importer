<?php
/**
 * Standalone helper: generates a local_products.json file.
 *
 * This file is NOT loaded by the plugin. It is a utility
 * that can be run via WP-CLI or direct admin action.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Generate local products database JSON file
 */
function sifp_generate_local_db() {
	$sifp_upload_dir = wp_upload_dir();
	$sifp_output_dir = $sifp_upload_dir['basedir'] . '/si-fast-product-importer';
	if ( ! file_exists( $sifp_output_dir ) ) {
		wp_mkdir_p( $sifp_output_dir );
	}
	$sifp_output_file = $sifp_output_dir . '/local_products.json';

	$sifp_lang_data = array(
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

	$sifp_cat_image_map = array(
		'Electronics' => 'Elettronica',
		'Home'        => 'Casa',
		'Clothing'    => 'Abbigliamento',
		'Beauty'      => 'Bellezza',
		'Sports'      => 'Sport',
	);

	$sifp_category_images = array(
		'Elettronica' => array(
			'main'    => array_fill( 0, 5, '' ),
			'gallery' => array_fill( 0, 3, '' ),
		),
		'Casa' => array(
			'main'    => array_fill( 0, 5, '' ),
			'gallery' => array_fill( 0, 3, '' ),
		),
		'Abbigliamento' => array(
			'main'    => array_fill( 0, 5, '' ),
			'gallery' => array_fill( 0, 3, '' ),
		),
		'Bellezza' => array(
			'main'    => array_fill( 0, 4, '' ),
			'gallery' => array_fill( 0, 3, '' ),
		),
		'Sport' => array(
			'main'    => array_fill( 0, 5, '' ),
			'gallery' => array_fill( 0, 3, '' ),
		),
	);

	$sifp_colors    = array('Rosso', 'Blu', 'Nero', 'Bianco', 'Verde', 'Grigio', 'Oro', 'Argento');
	$sifp_img_base  = wc_placeholder_img_src();
	$sifp_img_params = '';

	$sifp_products = array();
	$sifp_total    = 500;

	foreach ( $sifp_lang_data as $sifp_lang_code => $sifp_lang ) {
		$sifp_cat_keys = array_keys( $sifp_lang['categories'] );

		for ( $sifp_i = 1; $sifp_i <= $sifp_total; $sifp_i++ ) {
			$sifp_category  = $sifp_cat_keys[ array_rand( $sifp_cat_keys ) ];
			$sifp_base_name = $sifp_lang['categories'][ $sifp_category ][ array_rand( $sifp_lang['categories'][ $sifp_category ] ) ];
			$sifp_adj       = $sifp_lang['adjectives'][ array_rand( $sifp_lang['adjectives'] ) ];
			$sifp_title     = "$sifp_base_name $sifp_adj";

			$sifp_price      = wp_rand( 10, 500 ) + ( wp_rand( 0, 99 ) / 100 );
			$sifp_sale_price = wp_rand( 0, 1 ) ? ( $sifp_price * 0.8 ) : '';

			$sifp_image_cat  = isset( $sifp_cat_image_map[ $sifp_category ] ) ? $sifp_cat_image_map[ $sifp_category ] : $sifp_category;
			$sifp_cat_imgs   = $sifp_category_images[ $sifp_image_cat ] ?? $sifp_category_images['Elettronica'];
			$sifp_main_img   = $sifp_img_base . $sifp_cat_imgs['main'][ $sifp_i % count( $sifp_cat_imgs['main'] ) ] . $sifp_img_params;
			$sifp_gallery1   = $sifp_img_base . $sifp_cat_imgs['gallery'][ $sifp_i % count( $sifp_cat_imgs['gallery'] ) ] . $sifp_img_params;
			$sifp_gallery2   = $sifp_img_base . $sifp_cat_imgs['gallery'][ ( $sifp_i + 1 ) % count( $sifp_cat_imgs['gallery'] ) ] . $sifp_img_params;

			$sifp_ing = '';
			if ( in_array( $sifp_category, array( 'Bellezza', 'Beauty' ), true ) || wp_rand( 1, 10 ) > 8 ) {
				$sifp_ing = $sifp_lang['ingredients'][ array_rand( $sifp_lang['ingredients'] ) ] . ', ' . $sifp_lang['ingredients'][ array_rand( $sifp_lang['ingredients'] ) ];
			}
			$sifp_all = '';
			if ( in_array( $sifp_category, array( 'Bellezza', 'Beauty' ), true ) && wp_rand( 1, 10 ) > 7 ) {
				$sifp_all = $sifp_lang['allergens'][ array_rand( $sifp_lang['allergens'] ) ];
			}

			$sifp_products[] = array(
				'post_title'      => $sifp_title,
				'post_content'    => sprintf( $sifp_lang['desc'], $sifp_category ),
				'post_excerpt'    => sprintf( $sifp_lang['excerpt'], $sifp_title ),
				'sifp_categories' => $sifp_category,
				'sifp_tag'        => strtolower( $sifp_category ) . ', ' . $sifp_adj . ', ' . implode( ', ', $sifp_lang['tags'] ),
				'sifp_img'        => $sifp_main_img,
				'sifp_gallery'    => "$sifp_gallery1,$sifp_gallery2",
				'regular_price'   => number_format( $sifp_price, 2, '.', '' ),
				'sale_price'      => $sifp_sale_price ? number_format( $sifp_sale_price, 2, '.', '' ) : '',
				'sku'             => 'SIFP-' . strtoupper( substr( $sifp_image_cat, 0, 3 ) ) . '-' . str_pad( $sifp_i, 4, '0', STR_PAD_LEFT ),
				'stock_status'    => 'instock',
				'stock_qty'       => wp_rand( 10, 200 ),
				'weight'          => ( wp_rand( 1, 50 ) / 10 ),
				'length'          => wp_rand( 10, 100 ),
				'width'           => wp_rand( 10, 100 ),
				'height'          => wp_rand( 5, 50 ),
				'is_virtual'      => 'no',
				'is_downloadable' => 'no',
				'sifp_ingredient' => $sifp_ing,
				'sifp_allerg'     => $sifp_all,
				'attributes'      => array(
					array(
						'name'   => 'Colore',
						'values' => implode( ' | ', (array) array_intersect_key( $sifp_colors, array_flip( (array) array_rand( $sifp_colors, 3 ) ) ) ),
					),
					array(
						'name'   => 'Taglia',
						'values' => 'S | M | L | XL',
					),
				),
			);
		}
	}

	file_put_contents( $sifp_output_file, wp_json_encode( $sifp_products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
	echo "Generati " . count( $sifp_products ) . " prodotti in " . esc_html( $sifp_output_file ) . "\n";
}

sifp_generate_local_db();
