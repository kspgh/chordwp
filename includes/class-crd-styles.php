<?php

class CRD_Styles {

    public static function register() {
        add_action ( 'wp_enqueue_scripts', array ( 'CRD_Styles', 'load_styles' ) );
    }

    public static function load_styles () {
//        $source = ChordWP::plugin_url() . 'assets/css/chordwp.css';
        $source = plugins_url() . '/chordwp/assets/css/chordwp.css';
        wp_enqueue_style ( 'chordwp_styles', $source );
//        $source = ChordWP::plugin_url() . 'assets/css/abcjs-midi.css';
        $source = plugins_url() . '/chordwp/assets/css/abcjs-midi.css';
        wp_enqueue_style ( 'abcjs_styles', $source );
    }
}
