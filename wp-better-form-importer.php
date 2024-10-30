<?php
/**
 * Plugin Name: WP Better HTML Form Importer
 * Plugin URI: http://whoischris.com
 * Description: This plugin imports badly coded forms into WordPress and Gravity Forms
 * Version: 1.1.1
 * Author: Chris Flannagan
 * Author URI: http://whoischris.com
 * License: GPL2
 */

if( !class_exists( 'WP_Form_Import' ) )
{
    class WP_Form_Import
    {
        const PLUGIN_SLUG = 'wp-better-form-importer';
        const PLUGIN_ABBR = 'wpbfi';

        /**
         * Construct the plugin object
         */
        public function __construct()
        {
            // register actions
            add_action( 'admin_init', array( &$this, 'admin_init' ) );
            add_action( 'admin_menu', array( &$this, 'add_controls' ) );
            // register shortcode

        } // END public function __construct

        /**
         * Activate the plugin
         */
        public static function activate()
        {
            // Do nothing
        } // END public static function activate

        /**
         * Deactivate the plugin
         */
        public static function deactivate()
        {
            // Do nothing
        } // END public static function deactivate

        public function add_controls() {

            //Place a link to our settings page under the Wordpress "Settings" menu
            add_menu_page( 'WP Better Form Importer', 'Better Form Importer', 'manage_options', self::PLUGIN_SLUG . '-tool', array( $this, 'importer_page' ) );

        }

        public function importer_page() {

            //Include our settings page template
            include(sprintf("%s/%s-tool.php", dirname(__FILE__), self::PLUGIN_SLUG));

        }

        /**
         * hook into WP's admin_init action hook
         */
        public function admin_init()
        {
        } // END public static function activate
    }
} // END if(!class_exists('WP_Form_Import'));

// Add a link to the settings page onto the plugin page
if( class_exists( 'WP_Form_Import' ) )
{
    // Installation and uninstallation hooks
    register_activation_hook( __FILE__, array( 'WP_Form_Import', 'activate' ) );
    register_deactivation_hook( __FILE__, array( 'WP_Form_Import', 'deactivate' ) );

    // instantiate the plugin class
    $WP_Form_Import = new WP_Form_Import();
}