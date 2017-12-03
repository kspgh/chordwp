<?php

class CRD_Sheet_Music_TOC {

    public static function register () {
        add_shortcode ( 'sheet-music-toc', array ( 'CRD_Sheet_Music_TOC', 'sheet_music_toc' ) );
	}

	private static function create_abc_menu($atts, $content, $tag){
		$menuLabels = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
		$halfLen = sizeof($menuLabels) / 2;
		$menu = '<table style="text-align:center"><tr>';

/*		$args = array(
			'post_type' => 'crd_sheet_music',
			'post_status' => 'publish',
			'posts_per_page' => '10',
			'order' => 'asc', 
			'orderby' => 'title'
			);
		$the_query = new WP_Query( $args );
		if( $the_query->have_posts()){
		}
*/
		$link = get_permalink();
		$link = explode( '?', $link);
		$baselink = $link[0];
		if(count( $link ) == 2){
			$baselink .= '?' . $link[1] . '&';
		}else{
			$baselink .= '?';
		}

		foreach ($menuLabels as $value){
			
			if(0 < $halfLen){
				$halfLen--;
				
			}else{
				$menu .= "</tr><tr>";
				$halfLen = sizeof($menuLabels);
			}
			
			$menu .= '<td><a href="'.$baselink.'titleIndex='.$value.'">'.$value.'</a></td>';
		}
		$menu .= "</tr></table>";
		//wp_reset_postdata();
		return $menu;
	}
	
	private static function getAllSheetMusicByChar($atts, $content, $tag){
		$out = '';
		$directives_default = array( /*0 => array(*/
					'title' => '--',
					'artist' => '--',
					'genre' => '--',
					'key' => '--',
					'capo' => '--',
					'time' => '--',
					'tempo' => '--'/*)*/
					);
		

		$paged = 1;
		$postTitleFirstChar = "";
		$link = get_permalink();
		$link = explode( '?', $link);
		$baselink = $link[0];
		$args = array();
		$the_query = array();
		
		if( isset( $_GET['titleIndex'])){
			$postTitleFirstChar = $_GET['titleIndex'];
			
			global $wpdb;
			$postids = $wpdb->get_col($wpdb->prepare("
				SELECT      ID
				FROM        $wpdb->posts
				WHERE       SUBSTR($wpdb->posts.post_title,1,1) = %s
				AND 		$wpdb->posts.post_type = 'crd_sheet_music'
				ORDER BY    $wpdb->posts.post_title", $postTitleFirstChar));
			
			if ( $postids ) {
				$args = array(
					'post__in' => $postids,
					'post_type' => /*CRD_Sheet_Music::the_post_type()*/'crd_sheet_music',
					'post_status' => 'publish',
					'posts_per_page' => 10,
					'order' => 'asc', /*'desc',*/
					'orderby' => 'title'
					);
			}
			
			$baselink .= '?%_%';
		}else{
			if( isset( $_GET['tcPage'] )){
				$paged = (int) $_GET['tcPage'];
			}
			if(count( $link ) == 2){
				$baselink .= '?' . $link[1] . '&%_%';
			}else{
				$baselink .= '?%_%';
			}
			$args = array(
						'paged' => $paged,
						'post_type' => /*CRD_Sheet_Music::the_post_type()*/'crd_sheet_music',
						'post_status' => 'publish',
						'posts_per_page' => '10',
						'order' => 'asc', /*'desc',*/
						'orderby' => 'title'
						);
		}

		$the_query = new WP_Query( $args );
		
		if( !empty($the_query) && $the_query->have_posts()){
			$out .= '<table><th>Title</th><th>Artist</th><th>Playlist</th>'; //<th>Key</th>';
			while($the_query->have_posts()){
				
				$the_query->the_post();
				$out .= '<tr>';
				$out .= '<td><a href="'.get_permalink().'">'.get_the_title().'</a></td>';
				$directives_tmp = get_post_meta(get_the_ID(), "_crd_song_meta", false);
				$directives = $directives_default;

				if(is_array($directives_tmp) && array_key_exists(0, $directives_tmp)){
					$directives = array_replace($directives_default, $directives_tmp[0]);
				}

				$tax_args = array( 'taxonomy' => 'playlist' );//array();

				$playlists = wp_get_object_terms(get_the_ID(), "playlist", $tax_args);

				$playlistName = "--";
				if(!empty($playlists)){
					$playlistName = "";
					foreach($playlists as $playlist){
						$link = get_category_link($playlist->term_id);
						$playlistName .= "<a href=".$link.">".$playlist->name."</a></br>";
					}
				}

				if(!empty($directives) && is_array($directives) && (0 < sizeof($directives))){
					
					$out .= '<td>'.$directives/*[0]*/["artist"].'</td>';
					$out .= '<td>'.$playlistName.'</td>';
				}else{
					$out .= '<td>--</td>';//artist		
					$out .= '<td>--</td>';//playlist		
				}
				$out .= '</tr>';
			}
			$out .= '</table>';
		}else{
			$out = "sorry nothing found...";
		}
		if( !empty($the_query) ){
			$out .= paginate_links( array( 
										'base' => $baselink,
										'format' => 'tcPage=%#%',
										'current' => $paged,
										'total' => $the_query->max_num_pages));
		}		
		wp_reset_postdata();
		return $out;
	}
	
    public static function sheet_music_toc ( $atts, $content, $tag ) {
//        $parser = new CRD_Parser();
//        $out = $parser->run( $content );
        $out = CRD_Sheet_Music_TOC::create_abc_menu($atts, $content, $tag);
		$out .= CRD_Sheet_Music_TOC::getAllSheetMusicByChar($atts, $content, $tag);
        $out = '<div class="sheet-music-toc-container">' . $out . "</div>";
        return $out;
    }

}
