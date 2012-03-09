<?php
/**
 * Adds Presta4wp_Widget widget.
 */

require_once('PSWebServiceLibrary.php');

class KropesPrestaProductsSliderWidget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'kropes_presta_products_slider_widget', // Base ID
			'Kropes Prestashop Products Slider', // Name
			array( 'description' => __( 'Vypíše produkty z kategorie HOME', 'presta4wp' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		// BODY
	try
	{
	  $options = get_option('Presta4wp_options');
	  $ws = new PrestaShopWebservice($options["url"], $options["key"], false);
	  $xml = $ws->get(array('resource' => 'categories', 'id'=>"1", 'filter[active]'=>"[1]" ));
	  // Here in $xml, a SimpleXMLElement object you can parse


	  $filtrid=array();
	  foreach ($xml->category->associations->products->product as $attName => $r){
		$filtrid[] = (int) $r->id;
          }

	  $xml = $ws->get(array('resource' => 'products', 'filter[id]'=>"[".implode('|',$filtrid)."]", 'display'=>'full', 'filter[active]'=>"[1]" ));
	  echo "<ul class='jcarousel jcarousel-skin-tango'>";
	  foreach ($xml->products->product as $attName => $r){
		$name = $r->name->xpath("language[@id=6]");
		$name = (string)$name[0];
		$description = $r->description_short->xpath("language[@id=6]");
		$description = (string)$description[0];
		$link_rewrite = $r->link_rewrite->xpath("language[@id=6]");
		$link_rewrite = (string)$link_rewrite[0];

		$id = (string) $r->id;
		$id_default_image = (string) $r->id_default_image;
		$price = (string) $r->price;
		$condition = (string) $r->condition;

		
		$prod = array("name"=>$name,"description"=>$description,"id"=>$id,"price"=>$price,"condition"=>$condition,"link_rewrite"=>$link_rewrite);


		$html = $options['psliderli'];
		$html = preg_replace('/{\\$name}/',$name,$html);
		$html = preg_replace('/{\\$link}/',"$options[url]/$id-$link_rewrite.html",$html);
		$html = preg_replace('/{\\$img}/',"$options[url]/$id-$id_default_image-home/$link_rewrite.jpg",$html);
		$html = preg_replace('/{\\$id}/',$id,$html);
		$html = preg_replace('/{\\$price}/',$price,$html);
		$html = preg_replace('/{\\$description}/', wp_strip_all_tags($description),$html);
		$html = preg_replace('/{\\$url}/',$options['url'],$html);

		echo $html;

	  }
	  echo "</ul>";

	}
	catch (PrestaShopWebserviceException $ex)
	{
		echo 'Error : '.$ex->getMessage();
	}

		// BODY
		echo $after_widget;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( $instance ) {
			$title = esc_attr( $instance[ 'title' ] );
		}
		else {
			$title = __( 'New title', 'presta4wp' );
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<?php 
	}



} // class Foo_Widget
