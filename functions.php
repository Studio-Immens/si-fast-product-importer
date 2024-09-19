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






