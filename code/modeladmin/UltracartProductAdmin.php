<?php class UltracartProductAdmin extends ModelAdmin {

	private static $menu_icon = "/ultracart/images/treeicons/ultracart.png";

	private static $managed_models = array(
		'UltracartStorefrontPage',
		'UltracartCategoryPage',
		'UltracartProduct'
	);

	private static $url_segment = 'ultracart';
	private static $menu_title = 'UltraCart';

}
