<?php
/*
Plugin Name: Prestashop for Wordpress integration 
Plugin URI: http://work.kropes.cz/wordpress/kropes_presta4wp
Description: Prestashop integration into wordpress (widgets) 
Version: 0.1 
Author: Michal Prokeš 
Author URI: http://work.kropes.cz
*/



#error_reporting(E_ALL);
add_action('admin_menu', array('Presta4wp','admin_menu'));
add_action('admin_init', array('Presta4wp','admin_init'));
add_action('widgets_init', array('Presta4wp','widgets_init'));
add_action('wp_enqueue_scripts', array('Presta4wp','wp_enque_scripts'));

class Presta4wp {
  function options(){
	echo '<div><h2>Prestashop integration</h2><form action="options.php" method="post">';
	settings_fields('Presta4wp');
	do_settings_sections('Presta4wp');
	echo '<p class="submit"><input type="submit" class="button-primary" value="'.__('Save Changes').'" /></p></form></div>';


  }

  function wp_enque_scripts(){
    wp_enqueue_script( 'jquery.jcarousel', plugins_url('js/jquery.jcarousel.min.js',__FILE__ ),array('jquery'));
    wp_enqueue_script( 'jquery.jcarousel.init', plugins_url('js/jquery.jcarousel.init.js',__FILE__),array('jquery.jcarousel'));
    wp_enqueue_style( 'jquery.jcarousel', plugins_url('css/skins/tango/skin.css',__FILE__));

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

  function admin_menu(){
    add_options_page('Custom Plugin Page', 'Prestashop4wp', 'manage_options', 'Presta4wp', array('Presta4wp','options'));
  }
 
  function admin_init(){
	register_setting( 'Presta4wp', 'Presta4wp_options');
	add_settings_section('Presta4wp_main', 'Main Settings', array('Presta4wp', 'section_text'),'Presta4wp');
	add_settings_field('Presta4wp_key', 'Prestashop API Key', array('Presta4wp','setting_key'), 'Presta4wp', 'Presta4wp_main');
	add_settings_field('Presta4wp_url', 'Prestashop URL', array('Presta4wp','setting_url'), 'Presta4wp', 'Presta4wp_main');
  }

  function widgets_init(){
    require_once('KropesPrestaProductsWidget.php');
    require_once('KropesPrestaProductsSliderWidget.php');


    register_widget('KropesPrestaProductsWidget');
    register_widget('KropesPrestaProductsSliderWidget');
  }
}

?>
