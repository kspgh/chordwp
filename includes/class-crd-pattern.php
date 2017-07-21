<?php

class CRD_Pattern {

    /**
     * The expected pattern before the processing has been performed
     * @var string
     */
    protected $before;

    /**
     * The expected pattern after the processing has been performed
     * @var string
     */
    protected $after;

    public function __construct ( $file_name, $dir=null ) {
        $this->read_file ( $file_name, $dir );
    }

    /**
     * Read the file and parse the before and after sections
     *
     * @param string $file_name The name of the file to read
     * @param string $dir The path to the file to read (optional)
     * @throws CRD_Exception_FileNotFound
     * @return void
     */
    public function read_file ( $file_name, $dir=null ) {
        if ( ! isset( $dir ) ) {
            $pattern_dir = Chordwp::plugin_path() . 'tests/patterns/';
        }

        $path = $pattern_dir . $file_name;

        if ( file_exists ( $path ) ) {
            $path = $pattern_dir . $file_name;
            $content = file_get_contents ( $path );
            list ( $before, $after ) = explode ( "\n\n", $content );
            $this->before = $before;
            $this->after = rtrim($after, "\t\r\n");
        }
        else {
            $current_dir = getcwd();
            throw new CRD_Exception_FileNotFound ( "Current directory: $current_dir :: Path: $path" );
        }
    }

    /**
     * Return the before section of the pattern file
     *
     * @return string
     */
    public function get_before () {
        return $this->before;
    }

    /**
     * Return the after section of the pattern file
     *
     * @return string
     */
    public function get_after () {
        return $this->after;
    }

}
