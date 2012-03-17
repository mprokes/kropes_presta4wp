<?php
/*
Plugin Name: Kropes Prestashop for Wordpress 
Description: Prestashop integration into wordpress (widgets) 
Version: 0.9.3
Author: Michal Prokeš 
Author URI: http://kropes.cz
*/



#error_reporting(E_ALL);

require_once('PSWebServiceLibrary.php');
add_action('admin_menu', array('Presta4wp','admin_menu'));
add_action('admin_init', array('Presta4wp','admin_init'));
add_action('widgets_init', array('Presta4wp','widgets_init'));
add_action('wp_enqueue_scripts', array('Presta4wp','wp_enque_scripts'));

require_once("KropesPrestaCategoriesMetabox.php");


/**
 * Presta4wp plugin class
 */
class Presta4wp {

  /**
   * Options page for this plugin
   */
  function options(){
	echo '<div><h2>Kropes Prestashop for Wordpress</h2><form action="options.php" method="post">';
	settings_fields('Presta4wp');
	do_settings_sections('Presta4wp');
	echo '<p class="submit"><input type="submit" class="button-primary" value="'.__('Save Changes').'" /></p></form></div>';


  }

  /**
   *  Script and styles for this plugin
   */
  function wp_enque_scripts(){
    wp_enqueue_script( 'jquery.jcarousel', plugins_url('js/jquery.jcarousel.min.js',__FILE__ ),array('jquery'));
    wp_enqueue_script( 'jquery.jcarousel.init', plugins_url('js/jquery.jcarousel.init.js',__FILE__),array('jquery.jcarousel'));
    wp_enqueue_style( 'jquery.jcarousel', plugins_url('css/skins/zbych/skin.css',__FILE__));

  }

  /**
   * Descriptioén how to get API key 
   */
  function section_main_text(){
    echo "V administraci prestashop zvolte Tools->Webservice a <ul>";
    echo "<li>Create new API key with permissions: categories/GET, languages/GET, products/GET</li>";
    echo "<li>Enable PrestaShop Webservice: Yes</li>";
    echo "</ul>";
  }

  /**
   * Description of widgets settings
   */
  function section_widgets_text(){
    echo "Zvolte html vzory pro řádky (li) jednotlivých widgetů";
  }




  /**
   * API key input
   */
  function setting_key() {
	$options = get_option('Presta4wp_options');
	echo "<input id='Presta4wp_key' name='Presta4wp_options[key]' size='40' type='text' value='{$options['key']}' />";
  }

  /** 
   * Prestashop URL
   */
  function setting_url() {
	$options = get_option('Presta4wp_options');
	echo "<input id='Presta4wp_url' name='Presta4wp_options[url]' size='40' type='text' value='{$options['url']}' />";
  }

  /**
   * LI template for Products Slider Widget
   */
  function setting_psliderli() {
	$options = get_option('Presta4wp_options');
	$options['psliderli'] = $options['psliderli'] ? $options['psliderli'] : '<li class="product_list">
<div class="center_block">
<a href="{$link}" class="product_img_link" title="{$name}"><img src="{$img}" alt="{$name}" /></a>
<h3><span class="new">Novinka</span><a href="{$link}" title="{$name}">{$name}</a></h3>
<p class="product_desc"><a href="{$link}" title="{$name}">{$description}</a></p>
</div>
<div class="right_block">
<span class="price" style="display: inline;">{$price}</span>
<a class=zbych_add_to_cart_button" rel="ajax_id_product_{$id}" href="{$url}/cs/cart?add&id_product={$id}" title="Přidat do košíku">Přidat do košíku</a>
<a class="zbych_detail_button" href="{$link}" title="Zobrazit">Zobrazit</a>
</div>
</li>';
	echo "<textarea id='Presta4wp_psliderli' name='Presta4wp_options[psliderli]' >{$options['psliderli']}</textarea>";
	echo '<div>Proměnné: {$name} {$link} {$img} {$url} {$price} {$description} {$id}</div>';
  }

  /**
   * LI template for Products List Widget
   */
  function setting_pli() {
	$options = get_option('Presta4wp_options');
	$options['pli'] = $options['pli'] ? $options['pli'] : '<li>
<div class="thumb"><a href="{$link}" class="img_background"><img width=45 height=45 src="{$img}"></a></div>
<div class="zbozi"><a href="{$link}">{$name}</a></div>
<div class="desc"><a href="{$link}">{$description}</a></div>
<div class="price"><a href="{$link}">Cena {$price},- Kč</a></div>
</li>';
	echo "<textarea id='Presta4wp_pli' name='Presta4wp_options[pli]' >{$options['pli']}</textarea>";
	echo '<div>Proměnné: {$name} {$link} {$img} {$url} {$price} {$description} {$id}</div>';
  }


  /**
   * Admin menu with plugin options page
   */
  function admin_menu(){
    add_options_page('Prestashop integration Settings', 'Kropes Presta4wp', 'manage_options', 'Presta4wp', array('Presta4wp','options'));
  }
 

  /**
   * Admin hooks initialization 
   */
  function admin_init(){
	register_setting( 'Presta4wp', 'Presta4wp_options');
	add_settings_section('Presta4wp_main', 'Prestashop', array('Presta4wp', 'section_main_text'),'Presta4wp');
	add_settings_field('Presta4wp_key', 'Prestashop API Key', array('Presta4wp','setting_key'), 'Presta4wp', 'Presta4wp_main');
	add_settings_field('Presta4wp_url', 'Prestashop URL', array('Presta4wp','setting_url'), 'Presta4wp', 'Presta4wp_main');

	add_settings_section('Presta4wp_widgets', 'Wordpress Widgety', array('Presta4wp', 'section_widgets_text'),'Presta4wp');
	add_settings_field('Presta4wp_psliderli', 'Product Slider Widget', array('Presta4wp','setting_psliderli'), 'Presta4wp', 'Presta4wp_widgets');
	add_settings_field('Presta4wp_pli', 'Product List Widget', array('Presta4wp','setting_pli'), 'Presta4wp', 'Presta4wp_widgets');
  }


  /**
   * Widgets initialization
   */
  function widgets_init(){
    require_once('KropesPrestaProductsWidget.php');
    require_once('KropesPrestaProductsSliderWidget.php');


    register_widget('KropesPrestaProductsWidget');
    register_widget('KropesPrestaProductsSliderWidget');
  }
}


/**
 * Function for template to include post related categorie from shop
 */
function kropes_presta4wp_assoc_categories($post_id){

  $title = get_post_meta( $post_id, 'presta4wp_title', true );
  $phrase = get_post_meta( $post_id, 'presta4wp_phrase', true );
  $categories = get_post_meta( $post_id, 'presta4wp_categories', true );
  $categories = $categories ? explode(',',$categories) : array();
  if($title || $phrase || count($categories)>0){
	$options = get_option('Presta4wp_options');
?>
<aside class="vypis_kategorii">
<?php if($title){ echo "<h2>Máte zájem o $title?</h2>"; } ?>
<h3>Navštivte náš <a href="/eshop">e-shop!</a> <?php if($phrase){ echo "$phrase"; } ?></h3>


<?php if(count($categories)>0) : ?>
<?php
	  $ws = new PrestaShopWebservice($options["url"], $options["key"], false);
	  $xml = $ws->get(array('resource' => 'categories', 'display'=>"[id,name]", 'filter[active]'=>"[1]", "filter[id]"=>"[".implode('|',(array)$categories)."]" ));


?>
<ul class="category_slider jcarousel jcarousel-skin-zbych">
  <?php foreach((array)$categories AS $c) : ?>
	<?php
	  $name = $xml->xpath('categories/category[id='.$c.']/name/language[@id=6]');
          $name = (string)$name[0];
	?>
	<li>
		<a class="img" href="/eshop/<?php echo $c; ?>-category"><img class="category_img" src="<?php echo $options['url']."/c/$c-home/image.jpg"; ?>"></a>
		<h4><?php echo $name; ?></h4>
	</li>
   <?php endforeach; ?>
</ul>

<a id="kolomy-next" class="jcarousel-skin-zbych jcarousel-next jcarousel-next-horizontal "></a>
<a id="kolomy-prev" class="jcarousel-skin-zbych jcarousel-prev jcarousel-prev-horizontal "></a> 
<?php endif; ?>


<div style="clear: both"></div>
</aside> <!-- .vypis_kategorii !-->
<?
  }
}

?>
