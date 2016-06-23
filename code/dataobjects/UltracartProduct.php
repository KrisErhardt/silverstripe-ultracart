<?php
class UltracartProduct extends DataObject {
  private static $nested_urls = false;

  // Set up basic database columns
  private static $db = array(
    "UltracartItemID" => "Varchar(255)",
		"URLSegment" => "Varchar(255)",
		"Title" => "Varchar(255)",
		"MenuTitle" => "Varchar(100)",
		"Content" => "HTMLText",
		"MetaDescription" => "Text",
		"ExtraMeta" => "HTMLText('meta, link')",
	);

  private static $has_one = array (
    "Image" => "Image"
  );

  private static $has_many = array(
    "Gallery" => "Image"
  );

  // Set up many-many relationship to
  private static $belongs_many_many = array(
    "Categories" => "UltracartCategoryPage"
  );

  //Fields to show in ModelAdmin table
	private static $summary_fields = array(
		'Title' => 'Title',
		'UltracartItemID' => 'Item ID'
	);

  private static $defaults = array(
		"URLSegment" => "new-product",
		"Title" => "New Product"
	);

	private static $versioning = array(
		"Stage",  "Live"
	);

  //Add an index for URLSegment
	private static $indexes = array(
		'URLSegment' => true
	);

  //Fields to search in ModelAdmin
	private static $searchable_fields = array(
		'Title',
		'Content',
		'UltracartItemID',
		'Categories.ID' => array(
			'title' => 'Category'
		)
	);

  //Setup the fields
	function getCMSFields() {

		//Main Tab
    $baseLink = Controller::join_links (
			Director::absoluteBaseURL(), null
		);

		$urlsegment = SiteTreeURLSegmentField::create("URLSegment", $this->fieldLabel('URLSegment'))
			->setURLPrefix($baseLink)
			->setDefaultURL($this->generateURLSegment(_t(
				'CMSMain.NEWPAGE',
				array('pagetype' => $this->i18n_singular_name())
			)));
		$helpText = (self::config()->nested_urls && count($this->Children())) ? $this->fieldLabel('LinkChangeNote') : '';
		if(!Config::inst()->get('URLSegmentFilter', 'default_allow_multibyte')) {
			$helpText .= $helpText ? '<br />' : '';
			$helpText .= _t('SiteTreeURLSegmentField.HelpChars', ' Special characters are automatically converted or removed.');
		}
		$urlsegment->setHelpText($helpText);

    $imagefield = new UploadField("Image", "Image");
		$imagefield->getValidator()->setAllowedExtensions(array('jpg', 'jpeg', 'png', 'gif'));
		$imagefield->setFolderName('ultracart/products/' . $this->URLSegment);

    $fields = new FieldList(
			$rootTab = new TabSet("Root",
				$tabMain = new Tab('Main',
					new TextField("Title", "Title"),
					$urlsegment,
					new TextField("MenuTitle", "Menu Title"),
					$htmlField = new HTMLEditorField("Content", _t('SiteTree.HTMLEDITORTITLE', "Content", 'HTML editor title')),
					ToggleCompositeField::create('Metadata', _t('SiteTree.MetadataToggle', 'Metadata'),
						array(
							$metaFieldDesc = new TextareaField("MetaDescription", $this->fieldLabel('MetaDescription')),
							$metaFieldExtra = new TextareaField("ExtraMeta",$this->fieldLabel('ExtraMeta'))
						)
					)->setHeadingLevel(4)
				),
        $tabSettings = new Tab('Image Gallery',
          $imagefield
        ),
        $tabSettings = new Tab('Settings',
          new TextField("UltracartItemID", "UltraCart Item ID")
        )
			)
		);
		$htmlField->addExtraClass('stacked');

		return $fields;
	}

  function Parent() {
    if(Director::get_current_page()->ClassName == 'CategoryPage') {
			$Category = Director::get_current_page();
		} elseif ($this->Categories()) {
		  return $this->Categories()->First();
		} else {
		  return null;
		}
  }

  function ParentID() {
    if ($this->Parent()) {
      return $this->Parent()->ID;
    } else {
      return null;
    }
  }

  public function generateURLSegment($title){
		$filter = URLSegmentFilter::create();
		$t = $filter->filter($title);

		// Fallback to generic page name if path is empty (= no valid, convertable characters)
		if(!$t || $t == '-' || $t == '-1') $t = "page-$this->ID";

		// Hook for extensions
		$this->extend('updateURLSegment', $t, $title);

		return $t;
	}

  //Set the URLSegment to be unique on write
	function onBeforeWrite() {
		//If there is no URLSegment set, Generate one from Title
		if((!$this->URLSegment || $this->URLSegment == 'new-product') && $this->Title != 'New Product') {
			$this->URLSegment = SiteTree::generateURLSegment($this->Title);
		}

		//If a URLSegment has been entered manaully, make sure it's valid.
		else if($this->isChanged('URLSegment')) {
			$segment = SiteTree::generateURLSegment($this->URLSegment);

			//If after sanitizing there is no URLSegment, give it a reasonable default
			if(!$segment) {
				$segment = SiteTree::generateURLSegment($this->ClassName.'-'.$this->ID);
			}
			$this->URLSegment = $segment;
		}

		// Ensure that this object has a non-conflicting URLSegment value
		if($object = DataObject::get_one($this->ClassName, "URLSegment='" . $this->URLSegment . "' AND ID !=" . $this->ID)){
			$this->URLSegment = $this->URLSegment.'-'.$this->ID;
		}

		parent::onBeforeWrite();
	}

	///Generate the link for this product
	function Link() {
		//if we are on a category page return that
		if(Director::get_current_page()->ClassName == 'CategoryPage') {
			$Category = Director::get_current_page();
		}

		//Otherwise just grab the first category this product is in
		else {
			$Category = $this->Categories()->First();
		}

		//Check we have a category then return the link
		if($Category) {
			return $Category->absoluteLink() . 'show/' . $this->URLSegment . '/';
		}
	}

	function CanonicalLink() {
		//Return a link based on the first category to use for the canonical tag
		$Category = $this->Categories()->First();

		//Check we have a category then return the link
		if($Category) {
			return $Category->absoluteLink() . 'show/' . $this->URLSegment . '/';
		}
	}

	public function AbsoluteLink() {
		return Director::absoluteURL($this->CanonicalLink());
	}
}
