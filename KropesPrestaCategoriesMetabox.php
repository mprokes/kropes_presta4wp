<?php
/**
 * Calls the class on the post edit screen
 */
function call_KropesPrestaCategoriesMetabox() 
{
    return new KropesPrestaCategoriesMetabox();
}
if ( is_admin() ){
    add_action( 'load-post.php', 'call_KropesPrestaCategoriesMetabox' );
    add_action( 'load-post-new.php', 'call_KropesPrestaCategoriesMetabox' );
}

/** 
 * The Class
 */
class KropesPrestaCategoriesMetabox
{
    const LANG = 'presta4wp';

    public function __construct()
    {
        add_action( 'add_meta_boxes', array( &$this, 'add_category_meta_box' ) );
	add_action( 'save_post', array(&$this,'save_post'), 10, 2 );
    }

    /**
     * Adds the meta box container
     */
    public function add_category_meta_box()
    {
        add_meta_box( 
             'presta4wp_category_meta_box_name'
            ,__( 'Související kategorie z eshopu', self::LANG )
            ,array( &$this, 'render_meta_box_content' )
            ,'page' 
            ,'advanced'
            ,'high'
        );
    }

    /* Save the meta box's post metadata. */
    function save_post( $post_id, $post ) {


	/* Verify the nonce before proceeding. */
	if ( !isset( $_POST['presta4wp_nonce'] ) || !wp_verify_nonce( $_POST['presta4wp_nonce'], basename( __FILE__ ) ) )
		return $post_id;

	/* Get the post type object. */
	$post_type = get_post_type_object( $post->post_type );

	/* Check if the current user has permission to edit the post. */
	if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
		return $post_id;

	/* Get the meta key. */
	foreach(array('presta4wp_title','presta4wp_phrase') AS $meta_key){

		/* Get the posted data and sanitize it for use as an HTML class. */
		$new_meta_value = ( isset( $_POST[$meta_key] ) ?  $_POST[$meta_key]  : '' );

		/* Get the meta value of the custom field key. */
		$meta_value = get_post_meta( $post_id, $meta_key, true );


		/* If the new meta value does not match the old value, update it. */
		update_post_meta( $post_id, $meta_key, $new_meta_value );
	}


    } 


    /**
     * Render Meta Box content
     */
    public function render_meta_box_content($post) 
    {
	wp_nonce_field( basename( __FILE__ ), 'presta4wp_nonce' ); 
        echo '<div>Máte zájem o <input name="presta4wp_title" id="presta4wp_title" value="'. esc_attr( get_post_meta( $post->ID, 'presta4wp_title', true ) ).'"> např. MED?</div>';
        echo '<div>Doplňková věta: <input size=50 name="presta4wp_phrase" id="presta4wp_phrase" value="'.esc_attr( get_post_meta( $post->ID, 'presta4wp_phrase', true ) ).'"> např. Vyberte si z těchto typů medů</div>';
    }
}
?>
