<?php

class CRD_Script {

    public static function register() {
        add_action ( 'wp_enqueue_scripts', array ( 'CRD_Script', 'load_scripts' ) );
		add_action ( 'wp_footer', array ( 'CRD_Script', 'footer' ) );
    }

    public static function load_scripts () {
//        $source = ChordWP::plugin_url() . 'assets/js/chords.js';
        $source = plugins_url() . '/chordwp/assets/js/chords.js';
		wp_enqueue_script ( 'chord_diagram', $source/*, array(), '1.0.0', true*/);
//        $source = ChordWP::plugin_url() . 'assets/js/abcjs_basic_midi_3.1.4-min.js';
        $source = plugins_url() . '/chordwp/assets/js/abcjs_basic_midi_3.1.4-min.js';
		wp_enqueue_script ( 'abcjs_basic', $source/*, array(), '1.0.0', true*/);
//        $source = ChordWP::plugin_url() . 'assets/js/soundfont.js';
        $source = plugins_url() . '/chordwp/assets/js/soundfont.js';
		wp_enqueue_script ( 'abcjs_soundfont', $source/*, array(), '1.0.0', true*/);

		wp_enqueue_script( 'abcjs-font-awesome', 'https://use.fontawesome.com/b8d1222982.js');

    }

    public static function footer () {
		echo '<script>chords.replace()</script>';
    }

}
