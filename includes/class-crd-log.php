<?php

class CRD_Log {

    public static function write( $data ) {
        if ( CRD_DEBUG ) {
            $backtrace = debug_backtrace();
            $file = $backtrace[0]['file'];
            $line = $backtrace[0]['line'];
            $date = current_time('m/d/Y g:i:s A') . ' ' . get_option('timezone_string');
            $out = "========== $date ==========\nFile: $file" . ' :: Line: ' . $line . "\n$data";

            $log_path = Chordwp::plugin_path();

            if( is_writable( $log_path ) ) {
                file_put_contents( $log_path . 'log.txt', $out . "\n\n", FILE_APPEND );
            }
        }
    }

}
