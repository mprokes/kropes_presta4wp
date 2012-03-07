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
	foreach(array('presta4wp_title','presta4wp_phrase','presta4wp_categories') AS $meta_key){

		/* Get the posted data and sanitize it for use as an HTML class. */
		$new_meta_value = ( isset( $_POST[$meta_key] ) ?  $_POST[$meta_key]  : '' );
		if(is_array($new_meta_value)){
		  $new_meta_value=implode(',',$new_meta_value);
		}

		/* Get the meta value of the custom field key. */
		$meta_value = get_post_meta( $post_id, $meta_key, true );


		/* If the new meta value does not match the old value, update it. */
		update_post_meta( $post_id, $meta_key, $new_meta_value );
	}


    } 

	function buildTree($categories) {

	    $childs = array();

	    foreach($categories as &$item){
		$childs[$item['id_parent']][$item['id']] = &$item;
	    }


	    foreach($categories as &$item) if (isset($childs[$item['id']]))
		$item['childs'] = $childs[$item['id']];

	    return $childs[0];
	}

    public function echoTree($list,$catSel){
	$ret = "<ul class='ul-square'>";
	foreach((array)$list AS $r){
            $ret .= "<li><input type='checkbox' name='presta4wp_categories[".$r['id']."]' value='".$r['id']."' ".(in_array($r['id'],$catSel) ? 'checked' : '' ).">".$r['name'].$this->echoTree($r['childs'],$catSel)."</li>";
        }
	$ret .= "</ul>";
	return $ret;
    }



    /**
     * Render Meta Box content
     */
    public function render_meta_box_content($post) 
    {
	wp_nonce_field( basename( __FILE__ ), 'presta4wp_nonce' ); 
        echo '<div>Máte zájem o <input name="presta4wp_title" id="presta4wp_title" value="'. esc_attr( get_post_meta( $post->ID, 'presta4wp_title', true ) ).'"> např. MED?</div>';
        echo '<div>Doplňková věta: <input size=50 name="presta4wp_phrase" id="presta4wp_phrase" value="'.esc_attr( get_post_meta( $post->ID, 'presta4wp_phrase', true ) ).'"> např. Vyberte si z těchto typů medů</div>';
	try
	{
	  $options = get_option('Presta4wp_options');
	  $ws = new PrestaShopWebservice($options["url"], $options["key"], false);
	  $xml = $ws->get(array('resource' => 'categories', 'display'=>'[id,id_parent,link_rewrite,name,description]', 'filter[active]'=>"[1]" ));
	  // Here in $xml, a SimpleXMLElement object you can parse


	  $catlist = array();
	  foreach ($xml->categories->category as $attName => $r){
		$name = $r->name->xpath("language[@id=6]");
		$name = (string)$name[0];
		$description = $r->description->xpath("language[@id=6]");
		$description = (string)$description[0];
		$link_rewrite = $r->link_rewrite->xpath("language[@id=6]");
		$link_rewrite = (string)$link_rewrite[0];

		$id = (string) $r->id;
		$id_parent = $r->id_parent ? (int) $r->id_parent : 0;

		
		$catlist[$id] = array("name"=>$name,"description"=>$description,"id"=>$id,"price"=>$price,"condition"=>$condition,"link_rewrite"=>$link_rewrite,"id_parent"=>$id_parent);

	  }
	$catlist = $this->buildTree($catlist);
	
	$catSel = explode(',',(string)get_post_meta( $post->ID, 'presta4wp_categories', true ));

	echo $this->echoTree($catlist,$catSel);
	  
	}
	catch (PrestaShopWebserviceException $ex)
	{
		echo 'Error : '.$ex->getMessage();
	}


    }
}
?>
