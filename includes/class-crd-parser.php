<?php

class CRD_Parser {

    public $using_columns = false;
    public $columns_started = false;

	public function get_the_directives_as_meta( $content ) {
        $lines = explode ( "\n", $content );
        $out = '';
        $directives = array();
		foreach ( $lines as $line ) {
            if ( Chordwp::starts_with ( $line, '{' ) ) {
				$directive = $this->get_directive($line);
				//var_dump($directive);
				if(!empty($directive) && is_array($directive) && (0 < sizeof($directive))){
					$directives[$directive['key']] = $directive['value'];
				}
            }
        }
		return $directives;
	}

    /**
     * Parse the song content into viewable format
     *
     * @param  string $content The song content
     * @return string The content parsed for viewing in HTML format
     */
    public function run ( $content, $show_chords=true ) {
        $lines = explode ( "\n", $content );
        $out = '';

		foreach ( $lines as $line ) {
            $out .= $this->parse_line ( $line, $show_chords );
        }


        $out .= $this->close_formatting();
        return $out;
    }

    public function parse_line ( $raw_line, $show_chords=true ) {
        $html = '';
        $chords = array();
        $lyrics = array();
        $line = trim ( strip_tags ( $raw_line ) );

        if ( ! empty ( $line ) ) {
            if ( Chordwp::starts_with ( $line, '{' ) ) {
                $html = $this->parse_directive ( $line );
            }
            else {
                // Start table for columns on first line that is not a directive
                $html = '';
                if ( $this->using_columns === true && $this->columns_started === false ) {
                    $html = $this->start_columns();
                }

                if ( strpos ( $line, '[' ) !== false ) {
                    $html .= $this->build_table ( $line, $show_chords );
					//echo "chords: ".$line."</br>";
                }
                else {
                    $html .= $this->render_lyrics( $line );
					//echo "lyrics: ".$line."</br>";
                }
            }
        }

        return $html;
    }
	
	public function get_directive($line){
		
        $out = array();
		$directive = ''; 
		$content = '';
        echo $line.'</br>';
		$line = trim ( $line );
		$line = trim ( trim ( $line, '{}' ) );
		
		if ( Chordwp::contains ( $line, '}' ) ) {
			echo $line;
			die;
        }

        if ( Chordwp::contains ( $line, ':' ) ) {
            list ( $directive, $content ) = explode ( ':', $line, 2 );
        }
        else {
            $directive = $line;
        }

        if ( ! empty ( $directive ) ) {
            $directive = strtolower ( $directive );

            switch ( $directive ) {
                case 't':
                case 'title':
                    $out['key'] = 'title';
                    $out['value'] = $content;
                    break;
                case 'artist':
                    $out['key'] = 'artist';
                    $out['value'] = $content;
                    break;
                case 'genre':
                    $out['key'] = 'genre';
                    $out['value'] = $content;
                    break;
                case 'key':
                    $out['key'] = 'key';
                    $out['value'] = $content;
                    break;
                case 'capo':
                    $out['key'] = 'capo';
                    $out['value'] = $content;
                    break;
                case 'time':
                    $out['key'] = 'time';
                    $out['value'] = $content;
                    break;
                case 'tempo':
                    $out['key'] = 'tempo';
                    $out['value'] = $content;
                    break;
					
                default:
                    break;
            }
        }

        return $out;
	}

    public function parse_directive ( $line ) {
        $out = '';
		//this first trim makes it more statble if an author puts some 
		//spaces before or after the directive...
		$line = trim ( $line );
        $line = trim ( trim ( $line, '{}' ) );
		$content = "";
        if ( Chordwp::contains ( $line, ':' ) ) {
            list ( $directive, $content ) = explode ( ':', $line, 2 );
        }
        else {
            $directive = $line;
        }

        if ( ! empty ( $directive ) ) {
            $directive = strtolower ( $directive );

            switch ( $directive ) {
                case 't':
                case 'title':
                    $out = $this->render_title ( $content );
                    break;
                case 'artist':
                    $out = $this->render_directive( 'Artist', $content );
                    break;
                case 'genre':
                    $out = $this->render_directive( 'Genre', $content );
                    break;
                case 'key':
                    $out = $this->render_directive( 'Key', $content );
                    break;
                case 'capo':
                    $out = $this->render_directive( 'Capo', $content );
                    break;
                case 'time':
                    $out = $this->render_directive( 'Time Signature', $content );
                    break;
                case 'tempo':
                    $out = $this->render_directive( 'Tempo', $content );
                    break;
                case 'soc':
                case 'start_of_chorus':
                    $out = $this->render_start_of_chorus();
                    break;
                case 'eoc':
                case 'end_of_chorus':
                    $out = $this->render_end_of_chorus();
                    break;
                case 'c':
                case 'comment':
                    $out = $this->render_comment( $content );
                    break;
                case 'ci':
                case 'comment_italic':
                    $out = $this->render_comment_italic( $content );
                    break;
                case 'cb':
                case 'comment_box':
                    $out = $this->render_comment_box( $content );
                    break;
                case 'columns':
                case 'col':
                    $this->using_columns = true;
                    break;
                case 'colb':
                case 'column_break':
                    $out = $this->column_break();
                    break;
					
                default:
                    if ( ! empty( $content ) ) {
                        $out = $this->render_comment( $content );
                    }
            }
        }

        return $out;
    }

    public function render_directive ( $label, $content ) {
        if ( 'Key' == $label ) {
            if ( has_filter( 'crd_the_chord' ) ) {
                $content = trim( $content );
                $content = apply_filters( 'crd_the_chord', $content );
            }
        }

        $html = '<p class="directive">' . $label . ': ' . $content . '</p>';
        return $html;
    }

    public function render_lyrics ( $content ) {
        $html = '<p class="lyrics">' . $content . '</p>';
        return $html;
    }

    public function render_comment ( $content ) {
        $html = '<p class="comment">' . $content . '</p>';
        return $html;
    }

    public function render_comment_italic ( $content ) {
        $html = '<p class="comment-italic">' . $content . '</p>';
        return $html;
    }

    public function render_comment_box ( $content ) {
        $html = '<p class="comment-box">' . $content . '</p>';
        return $html;
    }

    public function render_title( $content ) {
        $html = '<h1 class="song-title">' . $content . '</h1>';
        return $html;
    }

    public function render_start_of_chorus() {
        $html = '<div class="chorus">' . "\n";
        return $html;
    }

    public function render_end_of_chorus() {
        $html = '</div>' . "\n";
        return $html;
    }


    public function start_columns() {
        $this->columns_started = true;
        $html = '<div class="chordwp-column">';
        return $html;
    }

    public function column_break() {
        $html = '</div><div class="chordwp-column">';
        return $html;
    }

    public function end_columns() {
        $html = '</div><div class="chordwp-clearfix"></div>';
        return $html;
    }

    public function close_formatting() {
        $out = '';
        if ( $this->using_columns ) {
            $out .= $this->end_columns ();
        }

        return $out;
    }
	
    public function build_table( $line, $show_chords=true ) {
        $parts = explode('[', $line);

        foreach ( $parts as $part ) {
            if ( ! empty( $part ) ) {
                $phrase = explode(']', $part);
                if ( count( $phrase ) == 2 ) {
                    $chords[] = $phrase[0];
                    $lyrics[] = empty ( $phrase[1] ) ? '&nbsp;' : $phrase[1];
                }
                elseif ( count( $phrase ) == 1 ) {
                    $chords[] = '&nbsp;';
                    $lyrics[] = $phrase[0];
                }
            }
        }

        $table = '<table class="verse-line">' . "\n";

        // Render chords
        if ( $show_chords ) {
           $table .= '  <tr>' . "\n";
            foreach ( $chords as $chord ) {

                if ( has_filter( 'crd_the_chord' ) ) {
                    $chord = apply_filters( 'crd_the_chord', $chord );
                }

                $table .= '    <td class="chords">' . $chord . '</td>' .  "\n";
            }
            $table .= '  </tr>' . "\n"; 
        }
        
        // Render lyrics
        $table .= '  <tr>' . "\n";

        if ( $show_chords ) {
           foreach ( $lyrics as $lyric ) {
                $lyric = trim( $lyric );
                $lyric = strtr( $lyric, array(' ' => '&nbsp;') );
                $table .= '    <td class="lyrics">' . $lyric . '</td>' .  "\n";
            } 
        }
        else {
            $lyrics = implode( ' ', $lyrics );
            $lyrics = preg_replace( '/\s+-\s+/', '', $lyrics );
            $table .= '    <td class="lyrics">' . $lyrics . '</td>' .  "\n";
        }
        
        $table .= '  </tr>' . "\n";


        $table .= '</table>';

        return $table;
    }
}
