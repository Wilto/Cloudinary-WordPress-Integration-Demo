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
 * @package Cloudinary_Integration
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
require 'inc/class-cloudinary-wp-integration.php';

\Cloudinary::config(
	array(
		'cloud_name' => CLD_CLOUD_NAME,
		'api_key'    => CLD_API_KEY,
		'api_secret' => CLD_API_SECRET,
	)
);

$cloudinary_wp_integration = Cloudinary_WP_Integration::get_instance();
$cloudinary_wp_integration->setup();


