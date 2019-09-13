<?php

//Register Meta Box
function rm_register_meta_box() {
    add_meta_box( 'book-meta-box-info', esc_html__( 'Book Info', 'library' ), 'book_info_meta_box_callback', 'livre', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'rm_register_meta_box');
 
//Add field
function book_info_meta_box_callback( $meta_id ) { 
    wp_nonce_field(basename(__FILE__), "meta-box-nonce"); ?> 
    <table style="width:100%">
        <tr>
            <th>Quantity</th>
            <td><input name="meta-box-book-quantity" type="number" id="meta-box-book-quantity" value="<?php echo get_post_meta($meta_id->ID, "meta_book_quantity", true); ?>" size="30"></td>
        </tr>
        <tr>
            <th>Quantity available</th>
            <td><input name="meta-box-book-quantity-available" type="number" id="meta-box-book-quantity-available" value="<?php echo get_post_meta($meta_id->ID, "meta_book_quantity_available", true); ?>" size="30" ></td>
        </tr>
        <tr> <?php
        $blogusers = get_users();
        // Array of WP_User objects.
        foreach ( $blogusers as $user ) {
            $check_rents = get_user_meta( $user->ID, 'meta_book_rent', true );
            $i = 0;
            foreach ($check_rents as $check_rent) {
                if ( $check_rent[ID] == $meta_id->ID ) {
                    $i++;
                    ?></br><?php
                    echo $user->ID;
                    ?> - <?php
                    echo $check_rent[ID];
                    ?> - <?php           
                    "Rent until ";
                    echo date("m/d/Y h:i:s A T", $check_rent[Time]);
                    ?> - <?php
                    echo $i;
                }
            }
        } ?>
        </tr>
        <tr>
            <th>Number of page</th>
            <td><input name="meta-box-book-page" type="number" id="meta-box-book-page" value="<?php echo get_post_meta($meta_id->ID, "meta_book_page", true); ?>" size="30"></td>
        </tr>
        <tr>
            <th>ISBN-10</th>
            <td><input name="meta-box-book-isbn-10" type="text" id="meta-box-book-isbn-10" value="<?php echo get_post_meta($meta_id->ID, "meta_book_isbn_10", true); ?>" size="30"></td>
        </tr>
        <tr>
            <th>ISBN-13</th>
            <td><input name="meta-box-book-isbn-13" type="text" id="meta-box-book-isbn-13" value="<?php echo get_post_meta($meta_id->ID, "meta_book_isbn_13", true); ?>" size="30"></td>
        </tr>
    </table>
<?php }

function save_book_info_meta_box($post_id, $post, $update) {
    if (!isset($_POST["meta-box-nonce"]) || !wp_verify_nonce($_POST["meta-box-nonce"], basename(__FILE__)))
    return $post_id;
    if(!current_user_can("edit_post", $post_id))
        return $post_id;
    if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
        return $post_id;
    $slug = "livre";
    if($slug != $post->post_type)
        return $post_id;
    if( ! isset( $_POST['meta-box-book-isbn-13'] ) )
    return; 
    
    update_post_meta( $post_id, "meta_book_isbn_13", $_POST['meta-box-book-isbn-13'] );

    if( ! isset( $_POST['meta-box-book-isbn-10'] ) )
    return; 
    
    update_post_meta( $post_id, "meta_book_isbn_10", $_POST['meta-box-book-isbn-10'] );

    if( ! isset( $_POST['meta-box-book-quantity'] ) )
    return; 
    
    update_post_meta( $post_id, "meta_book_quantity", $_POST['meta-box-book-quantity'] );

    
    if( ! isset( $_POST['meta-box-book-quantity-available'] ) )
    return; 
    
    update_post_meta( $post_id, "meta_book_quantity_available", $_POST['meta-box-book-quantity-available'] );

    
    if( ! isset( $_POST['meta-box-book-page'] ) )
    return; 
    
    update_post_meta( $post_id, "meta_book_page", $_POST['meta-box-book-page'] );
}
add_action("save_post", "save_book_info_meta_box", 10, 3);

$meta_box_cover_upload = new meta_box_cover_upload();
class meta_box_cover_upload {
	function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'setup_box_cover' ) );
		add_action( 'save_post', array( $this, 'save_box_cover' ), 10, 2 );
    }
    
	function setup_box_cover() {
		add_meta_box( 'meta_box_cover_id', __( 'Cover Image', 'some-meta-box-cover' ), array( $this, 'meta_box_contents_cover' ), 'livre', 'side' );
    }
    
	function meta_box_contents_cover() {
		wp_enqueue_media();
        
        wp_nonce_field( 'nonce_action', 'nonce_name' );
        
        $name = esc_attr( 'meta-box-media-cover_' );
        $value = $rawvalue = get_post_meta( get_the_id(), $name, true );
        $attachment_title = get_the_title($value);
        
        echo '<div id="metabox_music_upload_wrapper">';
            echo '<div id="metabox_album_upload">';
                $image = ! $rawvalue ? '' : wp_get_attachment_image( $rawvalue, 'full', false, array('style' => 'max-width:100%;height:auto;') );
                echo "<div class='image-preview'>$image</div>";
            echo '</div>';
            echo '<div id="metabox_audio_upload">';
                echo '<div id="metabox_cover_link">';
                    echo '<a href="';
                    echo get_edit_post_link( $rawvalue );
                    echo '">Edit image</a>';
                echo '</div>';
                echo '</br>';
                echo "<input type='hidden' id='$name-value'  class='small-text'       name='meta-box-media[$name]'            value='$value' />";
                echo "<input type='button' id='$name'        class='button meta-box-upload-button'        value='Upload' />";
                echo "<input type='button' id='$name-remove' class='button meta-box-upload-button-remove' value='Remove' />";
            echo '</div>';
        echo '</div>';
	}
	function save_box_cover( $post_id, $post ) {
		if ( ! isset( $_POST['nonce_name'] ) ) //make sure our custom value is being sent
			return;
		if ( ! wp_verify_nonce( $_POST['nonce_name'], 'nonce_action' ) ) //verify intent
			return;
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) //no auto saving
			return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) //verify permissions
			return;
        if ( ! isset( $_POST['meta-box-media'] ) ) //make sure our custom value is being sent
			return; 
		$new_value = array_map( 'intval', $_POST['meta-box-media'] ); //sanitize
		foreach ( $new_value as $k => $v ) {
			update_post_meta( $post_id, $k, $v ); //save
		}
	}
}

?>