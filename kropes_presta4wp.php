<?php
/*
Plugin Name: Prestashop for Wordpress integration 
Plugin URI: http://work.kropes.cz/wordpress/kropes_presta4wp
Description: Prestashop integration into wordpress (widgets) 
Version: 0.1 
Author: Michal Prokeš 
Author URI: http://work.kropes.cz
*/

require_once('PSWebServiceLibrary.php');


error_reporting(E_ALL);
add_action("widgets_init", array('Presta4wp', 'register'));
add_action('admin_menu', array('Presta4wp','admin_menu'));
add_action('admin_init', array('Presta4wp','admin_init'));
class Presta4wp {
  function controlProducts(){
    echo 'Výpis produktů z home kategorie';
  }
  function widgetProducts($args){
    echo $args['before_widget'];
    echo $args['before_title'] . 'Položky z obchodu' . $args['after_title'];
	try
	{
	  $options = get_option('Presta4wp_options');
	  $ws = new PrestaShopWebservice($options["url"], $options["key"], false);
	  $xml = $ws->get(array('resource' => 'products', 'display'=>'[id,id_default_image,price,condition,link_rewrite,name,description]', 'filter[id_category_default]'=>"[1]", 'filter[active]'=>"[1]" ));
	  // Here in $xml, a SimpleXMLElement object you can parse

	  echo "<ul>";
	  foreach ($xml->products->product as $attName => $r){
		$name = $r->name->xpath("language[@id=6]");
		$name = (string)$name[0];
		$description = $r->description->xpath("language[@id=6]");
		$description = (string)$description[0];
		$link_rewrite = $r->link_rewrite->xpath("language[@id=6]");
		$link_rewrite = (string)$link_rewrite[0];

		$id = (string) $r->id;
		$id_default_image = (string) $r->id_default_image;
		$price = (string) $r->price;
		$condition = (string) $r->condition;


		$prod = array("name"=>$name,"description"=>$description,"id"=>$id,"price"=>$price,"condition"=>$condition,"link_rewrite"=>$link_rewrite);
                echo "<li><h3><a href='$options[url]/$id-$link_rewrite.html'>$name</a></h3><p>$description</p><img src='$options[url]/$id-$id_default_image/$link_rewrite.jpg'><div class='price'>$price Kč</div></li>";
	  }
	  echo "</ul>";

	}
	catch (PrestaShopWebserviceException $ex)
	{
		echo 'Error : '.$ex->getMessage();
	}

    echo $args['after_widget'];
  }


  function options(){
	echo '<div><h2>Prestashop integration</h2><form action="options.php" method="post">';
	settings_fields('Presta4wp');
	do_settings_sections('Presta4wp');
	echo '<p class="submit"><input type="submit" class="button-primary" value="'.__('Save Changes').'" /></p></form></div>';


  }

  function section_text(){
    #echo "Tady bude návod na získání API klíče";
  }

  function setting_key() {
	$options = get_option('Presta4wp_options');
	echo "<input id='Presta4wp_key' name='Presta4wp_options[key]' size='40' type='text' value='{$options['key']}' />";
  }
  function setting_url() {
	$options = get_option('Presta4wp_options');
	echo "<input id='Presta4wp_url' name='Presta4wp_options[url]' size='40' type='text' value='{$options['url']}' />";
  }



  function register(){
    register_sidebar_widget('Prestashop produkty', array('Presta4wp', 'widgetProducts'));
    register_widget_control('Prestashop produkty', array('Presta4wp', 'controlProducts'));
  }

  function admin_menu(){
    add_options_page('Custom Plugin Page', 'Prestashop4wp', 'manage_options', 'Presta4wp', array('Presta4wp','options'));
  }
 
  function admin_init(){
	register_setting( 'Presta4wp', 'Presta4wp_options');
	add_settings_section('Presta4wp_main', 'Main Settings', array('Presta4wp', 'section_text'),'Presta4wp');
	add_settings_field('Presta4wp_key', 'Prestashop API Key', array('Presta4wp','setting_key'), 'Presta4wp', 'Presta4wp_main');
	add_settings_field('Presta4wp_url', 'Prestashop URL', array('Presta4wp','setting_url'), 'Presta4wp', 'Presta4wp_main');
  }
}

?>
