<?php
/**
 * Flash_Products
 *
 * @package   Flash_Products
 * @author    Mauro Arnone <mauro.arnone.ma@gmail.com>
 * @copyright InnovazioneWeb
 * @license   GPL v.3
 * @link      innovazioneweb.com
 */



 if (is_admin()) { // note the use of is_admin() to double check that this is happening in the admin
  $config = array(
    'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
    'proper_folder_name' => 'Flash-Products', // this is the name of the folder your plugin lives in
    // 'api_url' => 'https://api.github.com/repos/username/repository-name', // the GitHub API url of your GitHub repo
    // 'raw_url' => 'https://raw.github.com/username/repository-name/master', // the GitHub raw url of your GitHub repo
    'github_url' => 'https://github.com/Immens95/Flash-Products.git', // the GitHub url of your GitHub repo
    // 'zip_url' => 'https://github.com/Immens95/Flash-Products.git/zipball/master', // the zip url of the GitHub repo
    'sslverify' => true, // whether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
    'requires' => '3.0', // which version of WordPress does your plugin require?
    'tested' => '3.3', // which version of WordPress is your plugin tested up to?
    'readme' => 'README.txt', // which file to use as the readme for the version number
    // 'access_token' => '', // Access private repositories by authorizing under Plugins > GitHub Updates when this example plugin is installed
  );
  new WP_GitHub_Updater($config);
}


















 /**
 * Fired during plugin activation.
 *
 * This function create flash products meta table, called 'flash_products_meta' in the database.
 *
 * @since      1.0.0
 * @package    Flash_Products
 * @author     InnovazioneWeb <info@innovazioneweb.com>
 */
function FP_create_meta_table( $version = FProd_VERSION ){
	update_option( 'flash_products_meta_table', FProd_VERSION );
    global $wpdb;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $table_name = $wpdb->prefix . "flash_products_meta";  //get the database table prefix to create my new table

    $sql = "CREATE TABLE $table_name (
      id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      meta_key varchar(255),
      meta_value text,
      assoc_id varchar(255),
      assoc_tb varchar(255),
      PRIMARY KEY  (id),
      KEY meta_key (meta_key),
      KEY assoc_id (assoc_id),
      KEY assoc_tb (assoc_tb)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    dbDelta( $sql );

  $meta_table = get_option( 'flash_products_meta_table' );
  if ( $meta_table != $version ) {
    update_option( 'flash_products_meta_table', $version );
  }
}
/**
 * This function retrieve flash products meta_value or entire row from table 'flash_products_meta'
 * @since      1.0.0
 * @package    Flash_Products
 * @author     InnovazioneWeb <info@innovazioneweb.com>
 */
function FP_get_meta( $meta_key, $type = 'var' ){
  global $wpdb;
  $table = $wpdb->prefix . "flash_products_meta";
  if ( $type == 'var' ) {
    $result = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM %i WHERE meta_key = %s", [ $table, $meta_key ] ) );
  } elseif ( $type == 'all' ) {
    $result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %i WHERE meta_key = %s ORDER BY id", [ $table, $meta_key ] ) );
  } else {
    $result = $wpdb->get_row( $wpdb->prepare( "SELECT meta_value FROM %i WHERE meta_key = %s", [ $table, $meta_key ] ), $type );
  }
  return $result;
}
/**
 * This function retrieve flash products meta_value or entire results from table 'flash_products_meta'
 * @since      1.0.0
 * @package    Flash_Products
 * @author     InnovazioneWeb <info@innovazioneweb.com>
 */
function FP_get_meta_by_assoc_id( $assoc_id, $type = 'var' ){
  global $wpdb;
  $table = $wpdb->prefix . "flash_products_meta";
  if ( $type == 'var' ) {
    $result = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM %i WHERE assoc_id = %s", [ $table, $assoc_id ]) );
  } else {
    $result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %i WHERE assoc_id = %s", [ $table, $assoc_id ] ), $type );
  }
  return $result;
}
/**
 * This function retrieve flash products meta_value or entire results from table 'flash_products_meta'
 * @since      1.0.0
 * @package    Flash_Products
 * @author     InnovazioneWeb <info@innovazioneweb.com>
 */
function FP_get_meta_by_assoc_tb( $assoc_tb, $type = 'var' ){
  global $wpdb;
  $table = $wpdb->prefix . "flash_products_meta";
  if ( $type == 'var' ) {
    $result = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM %i WHERE assoc_tb = %s", [ $table, $assoc_tb ] ) );
  } else {
    $result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %i WHERE assoc_tb = %s", [ $table, $assoc_tb ] ), $type );
  }
  return $result;
}
/**
 * This function retrieve flash products meta_value or entire row from table 'flash_products_meta'
 * @since      1.0.0
 * @package    Flash_Products
 * @author     InnovazioneWeb <info@innovazioneweb.com>
 */
function FP_get_meta_by_id( $id, $type = 'var' ){
  global $wpdb;
  $table = $wpdb->prefix . "flash_products_meta";
  if ( $type == 'var' ) {
    $result = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM %i WHERE id = %s", [ $table, $id ] ) );
  } else {
    $result = $wpdb->get_row( $wpdb->prepare( "SELECT meta_value FROM %i WHERE id = %s", [ $table, $id ] ), $type );
  }
  return $result;
}
/**
 * This function insert meta row in the table 'flash_products_meta'
 * @since      1.0.0
 * @package    Flash_Products
 * @author     InnovazioneWeb <info@innovazioneweb.com>
 */
function FP_insert_meta( $meta_key, $meta_value, $assoc_id = null, $assoc_tb = null ){
  global $wpdb;
  $table = $wpdb->prefix . "flash_products_meta";
  $result = $wpdb->insert( $table, array( 'meta_key'=>$meta_key, 'meta_value'=>$meta_value, 'assoc_id'=>$assoc_id,'assoc_tb'=>$assoc_tb ) );
  return $result;
}
/**
 * This function update meta row in the table 'flash_products_meta'
 * @since      1.0.0
 * @package    Flash_Products
 * @author     InnovazioneWeb <info@innovazioneweb.com>
 */
function FP_update_meta( $meta_key, $meta_value, $assoc_id = null, $assoc_tb = null ){
  global $wpdb;
  $table = $wpdb->prefix . "flash_products_meta";
  $meta = FP_get_meta( $meta_key );

  if ( $meta != null ) {
    $result = $wpdb->update( $table, array( 'meta_value'=>$meta_value, 'assoc_id'=>$assoc_id,'assoc_tb'=>$assoc_tb ), array( 'meta_key'=>$meta_key ) );
  } else {
    $result = $wpdb->insert( $table, array( 'meta_key'=>$meta_key, 'meta_value'=>$meta_value, 'assoc_id'=>$assoc_id,'assoc_tb'=>$assoc_tb ) );
  }
  return $result;
}
/**
 * This function delete meta row from table 'flash_products_meta'
 * @since      1.0.0
 * @package    Flash_Products
 * @author     InnovazioneWeb <info@innovazioneweb.com>
 */
function FP_delete_meta( $meta_key ){
  global $wpdb;
  $table = $wpdb->prefix . "flash_products_meta";
  $result = $wpdb->delete( $table, array( 'meta_key'=>$meta_key ) );
  return $result;
}
/**
 * This function delete meta row from table 'flash_products_meta'
 * @since      1.0.0
 * @package    Flash_Products
 * @author     InnovazioneWeb <info@innovazioneweb.com>
 */
function FP_delete_meta_by_id( $id ){
  global $wpdb;
  $table = $wpdb->prefix . "flash_products_meta";
  $result = $wpdb->delete( $table, array( 'id'=>$id ) );
  return $result;
}
/**
 * This is a debug function
 * @since      1.0.0
 * @package    Flash_Products
 * @author     InnovazioneWeb <info@innovazioneweb.com>
 */
function FP_debug( $var ){ ?>
	<pre> <?php var_dump($var); ?> </pre> <?php
}

function FP_general_setting( $setting = array() ){

  $name = ( isset($setting['name']) ) ? $setting['name'] : '';
  $title = ( isset($setting['title']) ) ? $setting['title'] : '';
  $default = ( isset($setting['default']) ) ? $setting['default'] : null;
  $data_default = ( FP_get_meta($name) ) ? FP_get_meta($name) : $default;
  $options = ( isset($setting['options']) ) ? $setting['options'] : array();
  $type = ( isset($setting['type']) ) ? $setting['type'] : 'text';
  $class = ( isset($setting['class']) ) ? $setting['class'] : '';
  $text = ( isset($setting['text']) ) ? $setting['text'] : '';
  $info = ( isset($setting['info']) ) ? $setting['info'] : '';
  $other = ( isset($setting['other']) ) ? $setting['other'] : '';
  ?>

  <div class="FOsettingEl <?php echo esc_attr($class);?>" title="<?php echo esc_attr($info).' ______ '.esc_html_e('nome dell\'impostazione nel database: ( ', 'flash_order').esc_attr($name).' )';?>">
      <?php if($title != ''){ ?>
          <strong class="FOtextSettings" style="flex-basis:100%"><?php echo esc_attr($title);?></strong>
      <?php }?>
      <p class="FOtextSettings"><?php echo esc_attr($text);?></p>
      <?php if($type == 'textarea'){ ?>
         <textarea type="<?php echo esc_attr($type);?>" name="setting[<?php echo esc_attr($name);?>]" <?php echo esc_attr($other);?>><?php echo esc_attr($data_default);?></textarea>
      <?php } elseif ($type != 'select') { ?>
          <input type="<?php echo esc_attr($type); ?>" name="setting[<?php echo esc_attr($name); ?>]" value="<?php echo esc_attr($data_default);?>" <?php echo esc_attr($other);?>>
      <?php } else{ ?>
          <select name="setting[<?php echo esc_attr($name); ?>]" value="<?php echo esc_attr($data_default); ?>" <?php echo esc_attr($other);?>>
              <option selected disabled hidden><?php echo esc_attr($data_default); ?></option>
              <?php if ( count($options) ) { ?>
                  <?php foreach ($options as $option) { ?>
                      <option value="<?php echo esc_attr($option);?>"><?php echo esc_attr($option);?></option>
                  <?php } ?>
              <?php } ?>
          </select>
      <?php } ?>
      <?php if ( $default != '' ) { ?>
          <span class="dashicons dashicons-image-rotate pointer" onclick="FO_adm_restore_prev_prev( this, <?php echo "'".esc_attr($default)."'";?> )"></span>
      <?php } ?>
  </div>

  <?php
}

function FP_save_settings( $args, $assoc_id = '', $debug = false ){
  if ( isset($_POST["update"]) && current_user_can( 'manage_options' ) ) {
      // if ( !wp_verify_nonce( $_POST['_fononce_save_settings'], 'FO_save_settings' ) ) {
      //     return;
      // }
      if ( isset( $_POST[$args] ) ) { 
          foreach ($_POST[$args] as $key => $value) {
              if ( isset( $_POST[$args][$key] ) ) {
              // FP_debug($key);
                  FP_update_meta( $key, $value, $assoc_id ); 
              }
          }
      } //$_SERVER['SERVER_NAME']
      if ($debug) {
          FP_debug($_POST);
      }
      $url = 'Location: '.$_SERVER['REQUEST_URI'];
      header( $url );
  }
}




