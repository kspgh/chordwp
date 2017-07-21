<?php

class CRD_Sheet_Music {

    public static function register() {
        $labels = array(
            'name'               => 'Sheet Music',
            'singular_name'      => 'Sheet Music',
            'menu_name'          => 'Sheet Music',
            'name_admin_bar'     => 'Sheet Music',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Music',
            'new_item'           => 'New Music',
            'edit_item'          => 'Edit Music',
            'view_item'          => 'View Music',
            'all_items'          => 'All Music',
            'search_items'       => 'Search Music',
            'parent_item_colon'  => 'Parent Music:',
            'not_found'          => 'No sheet music found.',
            'not_found_in_trash' => 'No sheet music found in trash.'
        );

        $args = array(
            'public'      => true,
            'labels'      => $labels,
            'description' => 'Share your music',
            'taxonomies'  => array( 'post_tag', 'category' ),
            'supports'    => array( 'title', 'editor', 'comments', 'excerpt', 'thumbnail'),
            'has_archive' => true,
            'rewrite'     => array( 'slug' => 'music' ),
            'menu_icon'   => 'dashicons-format-audio',
        );

        register_post_type( 'crd_sheet_music', $args );

        add_action( 'add_meta_boxes', array( 'CRD_Sheet_Music', 'add_song_box' ) );
        add_action( 'save_post', array( 'CRD_Sheet_Music', 'save_song_box' ) );
    }

    public static function add_to_taxonomies( $query ) {
        if( is_category() || is_tag() && empty( $query->query_vars['suppress_filters'] ) ) {
            $post_types = get_post_types();
            $query->set( 'post_type', $post_types );
        }

        return $query;
    }

    public static function add_song_box() {
        add_meta_box (
            'crd_song_box',                                 // id
            'Song',                                         // title
            array( 'CRD_Sheet_Music', 'song_box_content' ), // callback to echo content
            'crd_sheet_music',                              // screen id
            'normal',                                       // context
            'high'
        );
    }

    public static function song_box_content() {
        global $post;

        // Create nonce for saving song
        $content = get_post_meta( $post->ID, '_crd_song', true );
        $nonce_value = wp_create_nonce( 'crd_save_song' );
        $nonce = '<input type="hidden" name="crd_save_song_nonce" id="crd_save_song_nonce" value="' . $nonce_value. '" />';

        // Create textarea for song content
        $html = '<textarea name="crd_song" cols="40" rows="10" style="width: 100%">';
        $html .= $content;
        $html .= '</textarea>';
        $html .= $nonce;

        // Location setting
        $location = get_post_meta( $post->ID, '_crd_song_location', true );
        $location = empty( $location ) ? 'top' : $location;
        $location_bottom = 'bottom' == $location ? ' checked="checked" ' : '';
        $location_top    = 'bottom' != $location ? ' checked="checked" ' : '';

        $html .= '<p><strong>Show at:</strong> <br>';
        $html .= '<input type="radio" name="crd_song_location" id="crd_song_location_top" value="top" ' . $location_top . ' /> Top of post';
        $html .= ' &nbsp; ';
        $html .= '<input type="radio" name="crd_song_location" id="crd_song_location_botom" value="bottom" ' . $location_bottom . '/> Bottom of post';
        $html .= '</p>';

        echo $html ;
    }

    public static function save_song_box( $post_id ) {

		// $post_id and $post are required
		if ( empty( $post_id ) ) {
            CRD_Log::write( "Not saving song box because post is empty: $post_id" );
			return;
		}

		// Dont' save meta boxes for revisions or autosaves
		if ( defined( 'DOING_AUTOSAVE' ) ) {
            CRD_Log::write( 'Not saving song box because this is a post revision' );
			return;
		}

        // Make sure saving sheet music post
        if ( ! wp_verify_nonce( $_POST['crd_save_song_nonce'], 'crd_save_song' ) ) {
            CRD_Log::write( 'Failed nonce verification' );
            return $post_id;
        }

        // Check permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            CRD_Log::write( 'Failed permissions verification' );
            return $post_id;
        }

        // Save the song
        if ( isset( $_POST['crd_song']) && ! empty( $_POST['crd_song'] ) ) {
            CRD_Log::write( 'Updating post meta' );
            update_post_meta( $post_id, '_crd_song', $_POST['crd_song'] );
            update_post_meta( $post_id, '_crd_song_location', $_POST['crd_song_location'] );
        }
        else {
            CRD_Log::write( 'Deleting post meta' );
            delete_post_meta( $post_id, '_crd_song' );
            delete_post_meta( $post_id, '_crd_song_location' );
        }
    }

    public static function render_song( $content ) {
        global $post;

        $post_type = get_post_type();

        if ( 'crd_sheet_music' ==  $post_type ) {
            $location = get_post_meta( $post->ID, '_crd_song_location', true );
            $song = get_post_meta( $post->ID, '_crd_song', true );
            $song = '[chordwp]' . $song . '[/chordwp]';
            $song_html = do_shortcode( $song );

            if ( has_filter('crd_the_song') ) {
                $song_html = apply_filters( 'crd_the_song', $song_html );
            }

            if ( 'top' == $location ) {
                $content = $song_html . $content;
            }
            else {
                $content = $content . $song_html;
            }

        }


        return $content;
    }

}
