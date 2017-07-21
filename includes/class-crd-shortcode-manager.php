<?php

class CRD_Shortcode_Manager {

    public static function register () {
        add_shortcode ( 'chordwp', array ( 'CRD_Shortcode_Manager', 'chordwp' ) );
    }

    public static function chordwp ( $atts, $content, $tag ) {
        $parser = new CRD_Parser();
        $out = $parser->run( $content );
        return $out;
    }
}
