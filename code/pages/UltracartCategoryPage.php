<?php
class UltracartCategoryPage extends Page {

  private static $default_parent = "UltracartStorefrontPage";

  private static $many_many = array(
		'Products' => 'UltracartProduct'
	);

  private static $many_many_extraFields = array(
		'Products' => array(
			'SortOrder'=>'Int'
		)
	);

  public function getCMSFields() {
	  $fields = parent::getCMSFields();

	  // Products Tab
		$config = GridFieldConfig_RelationEditor::create(100);
		$fields->addFieldToTab('Root.Products', new GridField('Products', 'Products', $this->Products(), $config));

	  return $fields;
	}

	public function Products() {
	  return $this->getManyManyComponents('Products')->sort('SortOrder');
	}

}

class UltracartCategoryPage_Controller extends Page_Controller {
  private static $allowed_actions = array (
		'show'
	);

	public function init() {
		parent::init();

		// Require Custom JavaScript
//		Requirements::javascript("mysite/javascript/mootools_scripts/Modal.js");
//		Requirements::javascript("mysite/javascript/mootools_scripts/Ultracart.js");
//		Requirements::javascript("mysite/javascript/mootools_scripts/Checkout.js");
	}

	//Return the list of products for this category
	public function getProductList() {
		return $this->Products()->sort('Title ASC');
	}

	//Get the current product from the URL if appropriate
	public function getCurrentProduct() {
		$Params = $this->getURLParams();

		$URLSegment = Convert::raw2sql($Params['ID']);

		if($URLSegment && $Product = DataObject::get_one('UltracartProduct', "URLSegment = '" . $URLSegment . "'")) {
			return $Product;
		}
	}

	//Shows the Product Detail page
	public function show() {
		//Get the product
		if($Product = $this->getCurrentProduct()) {
			$Data = array(
				'Product' => $Product,
				'MetaTitle' => $Product->Title,
				'ClassName' => 'Product'
			);

			//return our $Data array to use, rendering with ProductPage.ss
			return $this->customise($Data)->renderWith(array('Product', 'Page'));
		}

		else { //Product not found
			return $this->httpError(404, 'Sorry, that product could not be found');
		}
	}

	//Generate breadcrumbs
	public function BreadCrumbs() {
		//Get the default breadcrums
		$Breadcrumbs = parent::Breadcrumbs();


		if($Product = $this->getCurrentProduct()) {

			//Explode them into their individual parts
			$Parts = explode(" &rdquo; ", $Breadcrumbs);

			//Count the parts
			$NumOfParts = count($Parts);

			//Change the last item to a link instead of just text
			$Parts[$NumOfParts-1] = '<a href="' . $this->Link() . '">' . $Parts[$NumOfParts-1] . '</a>';

			//Add our extra piece on the end
			$Parts[$NumOfParts] = $Product->Title;

			//Return the imploded array
			$Breadcrumbs = implode(" &rdquo; ", $Parts);
		}

		return $Breadcrumbs;

	}
}
