<?php
/*
Plugin Name: ChordWP
Plugin URI: http://leehblue.com
Description: Show ChordPro formatted music in WordPress
Version: 1.0.1
Author: Lee Blue
Author URI: http://leehblue.com

-------------------------------------------------------------------------
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists('Chordwp') ) {

    /**
     * Unit Tests main class
     *
     * The main Unit Tests class should not be extended
     */
    final class ChordWP {

        protected static $instance;

        /**
         * ChordWP should only be loaded one time
         *
         * @since 1.0
         * @static
         * @return ChordWP instance
         */
        public static function instance() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private function __construct() {
            // Define constants
            $this->define_constants();

            // Register autoloader
            spl_autoload_register( array( $this, 'class_loader' ) );

            // Activate plugin
            register_activation_hook( __FILE__, array( $this, 'activate' ) );

            // Initialize plugin
            add_action( 'init', array( $this, 'init' ), 0 );
        }

        /**
         * Initialize the plugin
         *
         * Hooked into action: init
         */
        public function init() {

            if ( ! is_admin() ) {
                CRD_Shortcode_Manager::register();
                CRD_Styles::register();
            }

            add_action( 'init', array( 'CRD_Sheet_Music', 'register' ) );
            add_filter( 'the_content', array( 'CRD_Sheet_Music', 'render_song' ), 10 );
            add_filter( 'pre_get_posts', array( 'CRD_Sheet_Music', 'add_to_taxonomies' ) );
        }

        /**
         * Dynamically load ChordWP classes as they are needed.
         *
         * Hooked into spl_autoload_register
         */
        public static function class_loader($class) {
            if(self::starts_with($class, 'CRD_')) {
                $class = strtolower($class);
                $file = 'class-' . str_replace( '_', '-', $class ) . '.php';
                $root = CRD_PATH;
                include_once $root . 'includes/' . $file;
            }
        }

        /**
         * Flush rewrite rules when the plugin is activated
         *
         * Hooked into register_activation_hook
         */
        public function activate() {
            CRD_Log::write('Activate ChordWP!');
            CRD_Sheet_Music::register();
            flush_rewrite_rules();
        }

        /**
         * Define basic internal settings for the plugin
         */
        private function define_constants() {
            $plugin_file = __FILE__;
            if(isset($plugin)) { $plugin_file = $plugin; }
            elseif (isset($mu_plugin)) { $plugin_file = $mu_plugin; }
            elseif (isset($network_plugin)) { $plugin_file = $network_plugin; }

            define( 'CRD_VERSION_NUMBER', '1.0' );
            define( 'CRD_PLUGIN_FILE', $plugin_file );
            define( 'CRD_PATH', WP_PLUGIN_DIR . '/' . basename(dirname($plugin_file)) . '/' );
            define( 'CRD_URL',  WP_PLUGIN_URL . '/' . basename(dirname($plugin_file)) . '/' );
            define( 'CRD_DEBUG', true );
        }

        /********************************************************
         * Helper functions
         ********************************************************/

        /**
         * Check to see if the given haystack starts with the needle.
         *
         * @param string $haystack
         * @param string $needle
         * @return boolean True if $haystack starts with $needle
         */
        public static function starts_with( $haystack, $needle ) {
            $length = strlen($needle);
            return (substr($haystack, 0, $length) === $needle);
        }

        public static function contains( $haystack, $needle ) {
            return strpos ( $haystack, $needle ) !== false;
        }

        /**
         * Get the plugin url
         *
         * @return string
         */
        public static function plugin_url() {
            return CRD_URL;
        }

        /**
         * Get the plugin path
         *
         * @return string
         */
        public static function plugin_path() {
            return CRD_PATH;
        }

        /**
         * Return the plugin version number
         *
         * @return string
         */
        public static function version() {
            return CRD_VERSION_NUMBER;
        }

        /**
         * Return true if debug mode is on, otherwise false.
         *
         * If the debug constant is false or not defined then debug mode is off.
         * If the CRD_DEBUG is true, then debug mode is on.
         *
         * @return boolean
         */
        public static function debug() {
            $debug = defined( CRD_DEBUG ) ? CRD_DEBUG : false;
        }

    }

    ChordWP::instance();
}
