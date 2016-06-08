<?php class UltracartProductAdmin extends ModelAdmin {

	private static $menu_icon = ULTRACART_BASE . "images/treeicons/ultracart.png";

	private static $managed_models = array(
		'UltracartProduct'
	);

	private static $url_segment = 'ultracart';
	private static $menu_title = 'UltraCart';

}
