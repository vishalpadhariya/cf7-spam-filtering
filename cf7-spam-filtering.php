<?php

/**
 * 
 * @link              https://vishalpadhariya.in/
 * @since             1.0.0
 * @package           CF7_SPAM_FILTERING
 *
 * @wordpress-plugin
 * Plugin Name:       CF7 Spam Filtering
 * Plugin URI:        https://github.com/vishalpadhariya/cf7-spam-filtering
 * Description:       Filter CF7 Form submission for spam filtering.
 * Version:           1.0.0
 * Author:            Vishal Padhariya
 * Author URI:        https://vishalpadhariya.in/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cf7-spam-filtering
 * Domain Path:       /languages
 */

/**
 * If this file is called directly, abort
 */
if (!defined('WPINC')) {
    die;
}

/**
 * Plugin Version
 */
define('CF7_SPAM_FILTERING_VERSION', '1.0.0');

include_once ABSPATH . 'wp-admin/includes/plugin.php';
// Check if Contact Form 7 is installed and activated.
if (!is_plugin_active('contact-form-7/wp-contact-form-7.php')) {

    /** Deactivate the plugin. */
    deactivate_plugins(plugin_basename(__FILE__));

    wp_die('Contact Form 7 plugin is required to activate this plugin. Please install and activate Contact Form 7.');
}


/**
 * Fired on plugin activation
 * 
 * activate_cf7_spam_filtering
 * 
 */
function activate_cf7_spam_filtering()
{
}

/**
 * Fired on plugin deactivation
 * 
 * deactivate_cf7_spam_filtering
 * 
 */
function deactivate_cf7_spam_filtering()
{
}

register_activation_hook(__FILE__, 'activate_cf7_spam_filtering');
register_deactivation_hook(__FILE__, 'deactivate_cf7_spam_filtering');


require plugin_dir_path(__FILE__) . 'includes/class-cf7-spam-filtering.php';
