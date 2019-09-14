<?php
/* Enqueue Script */
add_action( 'wp_enqueue_scripts', 'wp_libray_ajax_scripts' );
/**
 * Scripts
 */
function wp_libray_ajax_scripts() {
	/* Plugin DIR URL */
	$url = trailingslashit( plugin_dir_url( __FILE__ ) );
	//
	if ( is_user_logged_in() ) {
        wp_register_script( 'wp-get-rent-ajax-scripts', $url . "js/ajax.get.rent.js", array( 'jquery' ), '1.0.0', true );
		wp_localize_script( 'wp-get-rent-ajax-scripts', 'get_rent_ajax_url', admin_url( 'admin-ajax.php' ) );
        wp_enqueue_script( 'wp-get-rent-ajax-scripts' );	

		wp_register_script( 'wp-rent-ajax-scripts', $url . "js/ajax.rent.js", array( 'jquery' ), '1.0.0', true );
		wp_localize_script( 'wp-rent-ajax-scripts', 'rent_ajax_url', admin_url( 'admin-ajax.php' ) );
        wp_enqueue_script( 'wp-rent-ajax-scripts' );	

		wp_register_script( 'wp-scan-ajax-scripts', $url . "js/ajax.scan.js", array( 'jquery' ), '1.0.0', true );
		wp_localize_script( 'wp-scan-ajax-scripts', 'scan_ajax_url', admin_url( 'admin-ajax.php' ) );
        wp_enqueue_script( 'wp-scan-ajax-scripts' );	
        
        wp_register_script( 'wp-return-ajax-scripts', $url . "js/ajax.return.js", array( 'jquery' ), '1.0.0', true );
		wp_localize_script( 'wp-return-ajax-scripts', 'return_ajax_url', admin_url( 'admin-ajax.php' ) );
        wp_enqueue_script( 'wp-return-ajax-scripts' );	
        
        wp_register_script( 'wp-user-ajax-scripts', $url . "js/ajax.user.js", array( 'jquery' ), '1.0.0', true );
		wp_localize_script( 'wp-user-ajax-scripts', 'user_ajax_url', admin_url( 'admin-ajax.php' ) );
		wp_enqueue_script( 'wp-user-ajax-scripts' );	
	}
}

add_action( 'wp_ajax_get_rent_book', 'ajax_get_rent_book' );
add_action( 'wp_ajax_nopriv_get_rent_book', 'ajax_get_rent_book' );
function ajax_get_rent_book($post) {

    $object_id = $_POST['object_id'];

    $blogusers = get_users();
    // Array of WP_User objects.
    foreach ( $blogusers as $user ) {
        $check_rents = get_user_meta( $user->ID, 'meta_book_rent', true );
        $i = 0;
        foreach ($check_rents as $check_rent) {
            if ( $check_rent[ID] == $object_id ) {
                $html[] = '</br>';
                $html[] .= $user->ID;
                $html[] .= ' - ';
                $html[] .= $check_rent[ID];
                $html[] .= ' - ';            
                $html[] .= "Rent until " . date("m/d/Y h:i:s A T", $check_rent[Time]);
                $html[] .= ' - ';    
                $html[] .= '<div class="return_book" style="display: inline;" data="' . $i . '" data-userid="' . $user->ID . '" data-bookid="' . $object_id . '"><i class="material-icons md-24">clear</i></div>';
            }
            $i++;
        }
    }

    $arr = implode("", $html);

    return wp_send_json ($arr); 
}


add_action( 'wp_ajax_rent_book', 'ajax_rent_book' );
add_action( 'wp_ajax_nopriv_rent_book', 'ajax_rent_book' );
function ajax_rent_book($post) {
    $object_id = $_POST['object_id'];
    
    $quantity_available = get_post_meta($object_id['bookid'], "meta_book_quantity_available", true);
    if ($quantity_available > 0) {
        $html[] = "Rent until " . date("jS F, Y", strtotime('+2 weeks'));
    }
    $book_[ID] = $object_id['bookid'];
    if ($quantity_available > 0) {
        $book_[Time] = strtotime('+2 weeks 6 hours');
    }

    $get_book_quantity_available = get_post_meta( $object_id['bookid'], 'meta_book_quantity_available', true );
    if ($get_book_quantity_available > 0) {
        $current_rent = get_user_meta( $object_id['userid'], 'meta_book_rent', true );
        if ( $current_rent ) {
            array_push($current_rent, $book_);
            update_user_meta( $object_id['userid'], 'meta_book_rent', $current_rent );
        } else {
            delete_user_meta( $object_id['userid'], 'meta_book_rent' );
            add_user_meta( $object_id['userid'], 'meta_book_rent', [$book_] );
        }
        update_post_meta( $object_id['bookid'], 'meta_book_quantity_available', ($get_book_quantity_available - 1));
    } else {
        $html[] .= "There is no more copy avalable";
    }
    
    $arr = implode("", $html);

    return wp_send_json ( $arr ); 
}

add_action( 'wp_ajax_scan_book', 'ajax_scan_book' );
add_action( 'wp_ajax_nopriv_scan_book', 'ajax_scan_book' );
function ajax_scan_book($post) {

    $object_id = $_POST['object_id'];

    $get_book_10_args = array( 
        'post_type' => 'livre',
        'posts_per_page' => -1,
        'meta_key' => 'meta_book_isbn_10',
        'meta_value' => $object_id['isbn']
    ); 

    if ($object_id['isbn'] != "") {
        $books_10 = get_posts( $get_book_10_args );
    }

    $get_book_13_args = array( 
        'post_type' => 'livre',
        'posts_per_page' => -1,
        'meta_key' => 'meta_book_isbn_13',
        'meta_value' => $object_id['isbn']
    ); 

    
    if ($object_id['isbn'] != "") {
        $books_13 = get_posts( $get_book_13_args );
    }
    
    $user_isbn = get_user_by('id', $object_id['isbn']);
    if ($user_isbn) {
        return wp_send_json ( [-1, $object_id['isbn']] );
    }

    $user = get_user_by('id', $object_id['userid']);

    if ($books_13 || $books_10 && !$user) {
        foreach ( $books_10 as $book ) {
            $quantity_available = get_post_meta($book->ID, "meta_book_quantity_available", true);
            $name = esc_attr( 'meta-box-media-cover_' );
            $value = $rawvalue = get_post_meta( $book->ID, $name, true );
            $html[] = $book->post_title;
            $html[] .= '</br>';
            $html[] .= $quantity_available;
            $image = ! $rawvalue ? '' : wp_get_attachment_image( $rawvalue, array('225', '300'), false, array('style' => 'max-width:100%;height:auto;') );
            $html[] .= "<div class='image-preview'>$image</div>";
            $book_[ID] = $book->ID;
        }
        foreach ( $books_13 as $book ) {
            $quantity_available = get_post_meta($book->ID, "meta_book_quantity_available", true);
            $name = esc_attr( 'meta-box-media-cover_' );
            $value = $rawvalue = get_post_meta( $book->ID, $name, true );
            $html[] = $book->post_title;
            $html[] .= '</br>';
            $html[] .= $quantity_available;
            $image = ! $rawvalue ? '' : wp_get_attachment_image( $rawvalue, array('225', '300'), false, array('style' => 'max-width:100%;height:auto;') );
            $html[] .= "<div class='image-preview'>$image</div>";
            $book_[ID] = $book->ID;
        }
    }

    if ( $books_10 && $user ) {
        foreach ( $books_10 as $book ) {
            $quantity_available = get_post_meta($book->ID, "meta_book_quantity_available", true);
            $name = esc_attr( 'meta-box-media-cover_' );
            $value = $rawvalue = get_post_meta( $book->ID, $name, true );
            $html[] = $book->post_title;
            $html[] .= '</br>';
            $html[] .= $quantity_available;
            $image = ! $rawvalue ? '' : wp_get_attachment_image( $rawvalue, array('225', '300'), false, array('style' => 'max-width:100%;height:auto;') );
            $html[] .= "<div class='image-preview'>$image</div>";
            $html[] .= '<div id="rent_btn" data-userid="' . $object_id['userid'] . '" data-bookid="' . $book->ID . '">Rent</div>';
            $book_[ID] = $book->ID;
        }
    } elseif ( $books_13 && $user ) {
        foreach ( $books_13 as $book ) {
            $quantity_available = get_post_meta($book->ID, "meta_book_quantity_available", true);
            $name = esc_attr( 'meta-box-media-cover_' );
            $value = $rawvalue = get_post_meta( $book->ID, $name, true );
            $html[] = $book->post_title;
            $html[] .= '</br>';
            $html[] .= $quantity_available;
            $image = ! $rawvalue ? '' : wp_get_attachment_image( $rawvalue, array('225', '300'), false, array('style' => 'max-width:100%;height:auto;') );
            $html[] .= "<div class='image-preview'>$image</div>";
            $html[] .= '<div id="rent_btn" data-userid="' . $object_id['userid'] . '" data-bookid="' . $book->ID . '">Rent</div>';
            $book_[ID] = $book->ID;
        }
    } 

    $html[] .= '<div class="rent_wrapper">';
    $html[] .= '</div>';

    if (!$books_13 && !$books_10) {
        $html[] .= "Book not found";
    }

    $arr = implode("", $html);

    wp_reset_postdata();
    return wp_send_json ( $arr ); 
}


add_action( 'wp_ajax_return_book', 'ajax_return_book' );
add_action( 'wp_ajax_nopriv_return_book', 'ajax_return_book' );
function ajax_return_book($post) {

    $object_id = $_POST['object_id'];

    $current_rent = get_user_meta( $object_id['user_id'], 'meta_book_rent', true );
    $get_book_quantity_available = get_post_meta(  $object_id['book_id'], 'meta_book_quantity_available', true );

    if ( $current_rent ) {
        unset($current_rent[$object_id['rent_id']]); // remove item at index 0
        $foo2 = array_values($current_rent); // 'reindex' array

        update_user_meta( $object_id['user_id'], "meta_book_rent", $foo2);
        update_post_meta( $object_id['book_id'], "meta_book_quantity_available", ($get_book_quantity_available + 1));
    }

    $arr = implode("", $html);

    return wp_send_json ($arr); 

}

add_action( 'wp_ajax_user_info', 'ajax_user_info' );
add_action( 'wp_ajax_nopriv_user_info', 'ajax_user_info' );
function ajax_user_info($post) {

    $object_id = $_POST['object_id'];

    $user = get_user_by('id', $object_id);

    $html[] = $user->first_name;
    $html[] .= ' ';
    $html[] .= $user->last_name;
    $html[] .= '</br>';
    $html[] .= $user->user_email;
    $html[] .= '</br>';

    $arr = implode("", $html);

    return wp_send_json ($arr); 
}
?>