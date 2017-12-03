<?php

class CRD_Shortcode_Manager {

    public static function register () {
        add_shortcode ( 'chordwp', array ( 'CRD_Shortcode_Manager', 'chordwp' ) );
		add_shortcode( 'abcjs', array ( 'CRD_Shortcode_Manager', 'create_music' ));
		add_shortcode( 'abcjs-midi', array ( 'CRD_Shortcode_Manager', 'create_midi' ));
		add_shortcode( 'chordjs', array ( 'CRD_Shortcode_Manager', 'create_chord_diagram' ));
		add_shortcode( 'include-pdf', array ( 'CRD_Shortcode_Manager', 'include_pdf' ));
	}

    public static function chordwp ( $atts, $content, $tag ) {
        $parser = new CRD_Parser();
        $out = $parser->run( $content );
        $out = '<div class="chordwp-container">' . $out . "</div>";
        return $out;
    }

	// This turns the shortcode parameter back into the originally pasted string.
	public static function process_abc( $content ) {
		$content2 = preg_replace("&<br />\n&", "\x01", $content);
		$content2 = preg_replace("&\r\n&", "\x01", $content2);
		$content2 = preg_replace("&\n&", "\x01", $content2);
		$content2 = preg_replace("-&#8221;-", "\\\"", $content2);
		$content2 = preg_replace("-&#8222;-", "\\\"", $content2);
		$content2 = preg_replace("-&#8217;-", "'", $content2);
		$content2 = preg_replace("-&#8243;-", "\\\"", $content2);
		$content2 = preg_replace("-&#8220;-", "\\\"", $content2);
		return $content2;
	}
	
	//-- Interpret the [abcjs] shortcode
	public static function create_music( $atts, $content ) {
		$a = shortcode_atts( array(
			'class' => 'abc-paper',
			'parser' => '{}',
			'engraver' => '{}',
			'render' => '{}',
			'midi-player' => false,
		), $atts );

		//var_dump($a);
		
		$content2 = CRD_Shortcode_Manager::process_abc($content);

		$id = 'abc-paper-' . uniqid();
		$output = '<div id="' . $id . '" class="' . $a['class'] . '"></div>' .
			'<script type="text/javascript">' .
			'ABCJS.renderAbc("' . $id . '", "' . $content2 . '".replace(/\x01/g,"\n"), ' . $a['parser'] . ', ' . $a['engraver'] . ', ' . $a['render'] . ');' .
			'</script>';

		if(true == $a['midi-player']){
			$output .= CRD_Shortcode_Manager::create_midi( $atts, $content );
		}
		return $output;
	}

	//-- Interpret the [abcjs-midi] shortcode
	public static function create_midi( $atts, $content ) {
		$a = shortcode_atts( array(
			'class' => 'abc-midi',
			'parser' => '{}',
			'midi' => '{}'
		), $atts );
	//'midi' => '{ generateInline: true }'

		$content2 = CRD_Shortcode_Manager::process_abc($content);
		
		$id = 'abc-midi-' . uniqid();
		$output = '<div id="' . $id . '" class="' . $a['class'] . '"></div>' .
				  '<script type="text/javascript">' .
				  'ABCJS.renderMidi("' . $id . '", "' . $content2 . '".replace(/\x01/g,"\n"), ' . $a['parser'] . ', ' . $a['midi'] . ', {});' .
				  '</script>';

		return $output;
	}
	
	//-- Interpret the [chordjs] shortcode
	// name=Cmaj7;positions=x3545x;fingers=-1324-;size=2
	public static function create_chord_diagram($atts, $content = null, $tag = ''){
		$a = shortcode_atts( array(
			'class' => 'chordjs',
			'name' => '{}',
			'positions' => '{}',
			'fingers' => '{}',
			'size' => '{}'
		), $atts );
		
		$out = "";
		
		$out = '<chord name='.$a['name'].' positions='.$a['positions'].' fingers='.$a['fingers'].' size='.$a['size'].' ></chord>';
        return $out;
	}

	public static function include_pdf($atts, $content = null, $tag = ''){
		$a = shortcode_atts( array(
			'class' => 'include-pdf',
			'name' => '{}',
			'data' => '{}',
			'width' => '{}',
			'height' => '{}'
		), $atts );

		//var_dump($a);
		
		/*
		* in case of a missing or empty data field we assume that the user
		* has simply forgotton to specify it. We give a hint whats wrong here.
		*/
		if(empty($a['data']) || (0 == strcmp("{}", $a['data']))){
			$out = "<p>Error in include-pdf-ShortCode! 'data' field is missing or incomplete.";
			$out .= " You need to specify a path and filename to the pdf you want to reference</p>";
			return $out;
		}

		/*
		* in case we find "<", this means someone trys to send us html or script code...
		*/
		if(false != strpos($a['data'], "<")){
			$out = "<p>Please provide an URL. HTML or Script code is not acceptable.</p>";
			return $out;
		}

		/*
		* in case of a data field referencing a pdf from another site, we throw an error as 
		* it is assumed that this could be a security issue.
		*/
		$uploadDir = wp_get_upload_dir();
		//var_dump($uploadDir);
		$uploadURL_without_http = substr($uploadDir['baseurl'], 8); //in case of https we return one char less but that does not matter.
		if(false == strpos($a['data'], $uploadURL_without_http)){
			$out = "Crossite references are not allowed! You may only reference pdf files from the upload directory of this Server.";
			return $out;
		}
		
		if(is_ssl()){
			$i = 0;
			$a['data'] = str_replace("http:", "https:", $a['data'], $i);
 		}
		
		
		if(empty($content)){
			$content ='<p><b>pdf-Plugin not supported by your Browser</b></br> Please download: ';
			$content .= '<a href="'.$a['data'].'" target=_blank>'.$a['name'].'</a>';
		}
		
		
		$out = '<object type="application/pdf" name='.$a['name'].' width='.$a['width'].' height='.$a['height'].' data='.$a['data'].' >';
		$out .= $content;
		$out .= '</object>';

        return $out;
	}
}
