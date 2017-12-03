<?php
/*
Plugin Name: ChordWP
Plugin URI: https://whiteharvest.net/plugins/chordwp/
Description: Share your sheet music and lyrics using ChordPro formatted music in WordPress
Version: 1.1.0+KSp
Author: Lee Blue (Karsten Spriestersbach for adding chordtipdiagrams (chordjs) and abcjs)
Author URI: http://whiteharvest.net

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
				CRD_Sheet_Music_TOC::register();
                CRD_Styles::register();
                CRD_Script::register();
				CRD_Playlist_Taxonomy::register();
            }

            add_action( 'init', array( 'CRD_Sheet_Music', 'register' ) );
            add_action( 'init', array( 'CRD_Sheet_Music_TOC', 'register' ) );
            add_action( 'init', array( 'CRD_Playlist_Taxonomy', 'register' ) );
            add_filter( 'the_content', array( 'CRD_Sheet_Music', 'render_song' ), 10 );
            add_filter( 'pre_get_posts', array( 'CRD_Sheet_Music', 'add_to_taxonomies' ) );

            $this->register_propack_notice();
            $this->register_task_listener();
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

            define( 'CRD_VERSION_NUMBER', '1.1.0' );
            define( 'CRD_PLUGIN_FILE', $plugin_file );
            define( 'CRD_PATH', WP_PLUGIN_DIR . '/' . basename(dirname($plugin_file)) . '/' );
//            define( 'CRD_URL',  WP_PLUGIN_URL . '/' . basename(dirname($plugin_file)) . '/' );
            define( 'CRD_DEBUG', true );
        }

        protected function register_task_listener() {
            add_action( 'init', function() {

                // CRD_Log::write('Running task listner' . print_r( $_REQUEST , true) );
                if ( isset( $_REQUEST['chordwp-task'] ) && ! empty( $_REQUEST['chordwp-task']) ) {
                    $task = $_REQUEST['chordwp-task'];
                    CRD_Log::write( "Task listener found task: $task" );
                    switch ( $task ) {
                        case 'dismiss-propack-01':
                            $this->dismiss_notice('propack-01');
                            break;
                        case 'dismiss-propack-02':
                            $this->dismiss_notice('propack-02');
                            break;
                    }
                }
                
            });
        }

        protected function dismiss_notice( $notice_name ) {
            $dismissed_notices = get_option( 'chordwp-dismissed-notices', array() );
            CRD_Log::write("Dismissing notice: $notice_name from array: " . print_r( $dismissed_notices, true ) );

            if ( ! in_array( $notice_name, $dismissed_notices ) ) {
                $dismissed_notices[] = $notice_name;
                update_option( 'chordwp-dismissed-notices', $dismissed_notices );
            }
        }

        protected function register_propack_notice() {
            /*
            add_action( 'admin_notices', function() {
                if ( ! ChordWP::is_notice_dismissed('propack-01') ) {
                    ?>
                    <div class="notice notice-info is-dismissible" style="padding: 20px 10px;">
                        <strong>Get The ChordWP ProPack!</strong><br>
                        Extra features include: Transpose, print, and download chords and lyrics, just the lyrics, or a ChordPro music file.
                        <p>
                            <a class="button" href="https://whiteharvest.net/downloads/chordwp-propack/" target="_bank">Learn more about the ChordWP ProPack</a>
                            &nbsp; <a href="?chordwp-task=dismiss-propack-01">Dismiss Message</a>
                        </p>
                    </div>
                    <?php
                }
            });
            */

            add_action( 'admin_notices', function() {
                global $post_type;
                if ( 'crd_sheet_music' == $post_type && ! class_exists('CWPPRO') ) {
                    ?>
                    <div class="notice notice-info">
                        <p style="float: left;"><strong>Get More Features With The ChordWP ProPack</strong><br>
                        Let people transpose, download, and print your music.</p>
                        <p style="float: left; margin: 12px 20px;">
                            <a class="button" href="https://whiteharvest.net/downloads/chordwp-propack/" target="_bank">
                            Learn more
                            <i class="dashicons dashicons-controls-play" aria-hidden="true" style="line-height: 1.3em;"></i>
                            </a>
                        </p>
                        <div style="clear:both;"></div>
                    </div>
                    <?php    
                }
                
            });
        }

        /**
         * Return true if the option has been dismissed, otherwise false.
         */
        public static function is_notice_dismissed( $notice_name ) {
            $dismissed_notices = get_option('chordwp-dismissed-notices', array() );
            return in_array( $notice_name, $dismissed_notices );
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
  /*      public static function plugin_url() {
            return CRD_URL;
        }
*/
        /**
         * Get the plugin path
         *
         * @return string
         */
/*        public static function plugin_path() {
            return CRD_PATH;
        }
*/
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
            $debug = defined( CRD_DEBUG ) ? CRD_DEBUG : true;
        }

    }

    ChordWP::instance();
}
