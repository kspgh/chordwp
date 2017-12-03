<?php

class CRD_Playlist_Taxonomy {

    public static function register() {
		$args = array(
					'label' => 'Playlist',
					'public' => true, 
					'show_ui' => true,
					'hierarchical' => true );
					
		register_taxonomy('playlist', 'crd_sheet_music' /*CRD_Sheet_Music::the_post_type()*/, $args );
		
    }


}
