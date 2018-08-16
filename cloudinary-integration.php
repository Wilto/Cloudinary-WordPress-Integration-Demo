<?php
/**
 * Plugin Name:     Cloudinary Integration Demo
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     A demo integration with Cloudinary
 * Author:          Joe McGill
 * Author URI:      http://joemcgill.net
 * Text Domain:     cloudinary-integration
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Cloudinary_Integration
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access not allowed.' );
}

if ( ! defined( 'CLD_CLOUD_NAME' ) || ! defined( 'CLD_API_KEY' ) || ! defined( 'CLD_API_SECRET' ) ) {
	return;
}

// Load dependencies.
require 'lib/cloudinary_php/src/Cloudinary.php';
require 'lib/cloudinary_php/src/Uploader.php';
require 'lib/cloudinary_php/src/Api.php';

// Load integration.
require 'inc/class-wp-cloudinary-uploads.php';

// Load smart crop logic
require 'inc/class-wp-smart-crops.php';

\Cloudinary::config( array(
	'cloud_name' => CLD_CLOUD_NAME,
	'api_key'    => CLD_API_KEY,
	'api_secret' => CLD_API_SECRET,
) );

$Cloudinary_WP_Integration = Cloudinary_WP_Integration::get_instance();
$Cloudinary_WP_Integration->setup();

function ricg_get_asyncimg() {
	wp_enqueue_script( 'async-img', plugins_url( 'js/async-img.min.js', __FILE__ ), array(), false, true );
}
add_action( 'wp_enqueue_scripts', 'ricg_get_asyncimg' );

$loudinary_Smartcrops = Cloudinary_Smartcrops::get_instance();

function ricg_get_smartcrops( $hook ) {
	wp_enqueue_script( 'cloudinary-jquery', plugins_url( 'js/cloudinary-jquery.min.js', __FILE__), array( 'jquery' ), false, true );

	// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
	wp_enqueue_script( 'smart-crops', plugins_url( 'js/smart-crops.js', __FILE__), array( 'jquery' ), false, true );
	wp_localize_script( 'smart-crops', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), false ) );

	wp_enqueue_style( 'smart-crops-styles', plugins_url( 'css/smart-crops.css', __FILE__ ) );
}
add_action( 'admin_enqueue_scripts', 'ricg_get_smartcrops' );

