<?php
/**
 * Plugin Name: Postie Links Add-On
 * Description: An add-on for the <a href="https://wordpress.org/plugins/postie/">Postie</a> plugin. If the email content only contains a URL the post is created with the "Link" format.
 * Version: 1.0.0
 * Author: Functions File, Barry Ceelen
 * Author URI: https://github.com/functionsfile
 * Plugin URI: https://github.com/functionsfile/postie-links
 * License: GPLv3+
 *
 * @package PostieLinksAddOn
 */

defined( 'ABSPATH' ) || exit;

define( 'FUFI_POSTIE_LINKS_ADDON_INC', plugin_dir_path( __FILE__ ) . 'includes/' );

require_once plugin_dir_path( __FILE__ ) . 'includes/core.php';
