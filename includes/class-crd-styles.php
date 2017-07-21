<?php

class CRD_Styles {

    public static function register() {
        add_action ( 'wp_enqueue_scripts', array ( 'CRD_Styles', 'load_styles' ) );
    }

    public static function load_styles () {
        $source = ChordWP::plugin_url() . 'assets/css/chordwp.css';
        wp_enqueue_style ( 'chordwp_styles', $source );
    }

}
