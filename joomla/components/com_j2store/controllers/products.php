<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
use Joomla\Registry\Format\Json;

defined('_JEXEC') or die;
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/productbase.php');
class J2StoreControllerProducts extends J2StoreControllerProductsBase
{

	protected $cacheableTasks = array();
	var $_catids = array();

	public function browse() {
		//first clear cache
		$utility = J2Store::utilities();
		$utility->nocache();
		$utility->clear_cache();

		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		$db = JFactory::getDbo();
		$active	= $app->getMenu()->getActive();
		$menus		= $app->getMenu();
		$pathway	= $app->getPathway();
		$document = JFactory::getDocument();
		$lang = JFactory::getLanguage();

		$manufacturer_ids = $this->input->get('manufacturer_ids', array(), 'ARRAY');
		$vendor_ids = $this->input->get('vendor_ids', array(), 'ARRAY');
		$productfilter_ids = $this->input->get('productfilter_ids', array(), 'ARRAY');

		$ns = 'com_j2store.'.$this->getName();
		$params = F0FModel::getTmpInstance('Products', 'J2StoreModel')->getMergedParams();
		if($params->get('list_show_vote', 0)) {
			$params->set('show_vote', 1);
		}
		$view = $this->getThisView();

		//$model = F0FModel::getTmpInstance('Products', 'J2StoreModel');
		$model = $this->getModel('Products');

		//$model->clearState();
		$view->setModel($model);


		//$states = $this->getFilterStates();
		$states = $this->getSFFilterStates();


		if(!empty($manufacturer_ids)){
			$session->set('manufacturer_ids', $manufacturer_ids, 'j2store');
			$states['manufacturer_id'] = implode(',',$utility->cleanIntArray($manufacturer_ids, $db));
		}else{
			$session->clear('manufacturer_ids', 'j2store');
			$states['manufacturer_id'] = '';
		}
		if(!empty($vendor_ids)){
			$session->set('vendor_ids', $vendor_ids, 'j2store');
			$states['vendor_id']= implode(',',$utility->cleanIntArray($vendor_ids, $db));
			//$model->setState('vendor_id' ,implode(',',$vendor_ids));
		}else{
			$session->clear('vendor_ids', 'j2store');
			$states['vendor_id']= '';
		}
		if(!empty($productfilter_ids)){
			$session->set('productfilter_ids', $productfilter_ids, 'j2store');
			//set filter search condition
			$session->set('list_product_filter_search_logic_rel', $params->get('list_product_filter_search_logic_rel', 'OR'), 'j2store');
			$states['productfilter_id'] = implode(',',$utility->cleanIntArray($productfilter_ids, $db));

			//$model->setState('productfilter_id' ,implode(',',$vendor_ids));
		}else{
			$session->clear('productfilter_ids', 'j2store');
			$session->clear('list_product_filter_search_logic_rel', 'j2store');
			$states['productfilter_id'] ='';
		}

		$itemid = $app->input->get('id', 0, 'int') . ':' . $app->input->get('Itemid', 0, 'int');
		// Get the pagination request variables
		$limit		= $params->get('page_limit');

		$model->setState('list.limit', $limit);
		$limitstart = $app->input->get('limitstart', 0, 'int');

		// In case limit has been changed, adjust limitstart accordingly
		//		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$model->setState('list.start', $limitstart);

		$orderCol = $app->getUserStateFromRequest('com_j2store.product.list.' . $itemid . '.filter_order', 'filter_order', '', 'string');
		if (!in_array($orderCol, $model->get_filter_fields()))
		{
			$orderCol = 'a.ordering';
		}
		$model->setState('list.ordering', $orderCol);

		$listOrder = $app->getUserStateFromRequest('com_j2store.product.list.' . $itemid . '.filter_order_Dir',
				'filter_order_Dir', '', 'cmd');
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}
		$model->setState('list.direction', $listOrder);

		foreach($states as $key => $value){
			$model->setState($key,$value);
		}

		/* $filter_catid = $this->input->getInt('filter_catid',0); */
		$filter_catid = $app->input->getInt('filter_catid',$session->get('filter_catid' ,'','j2store'));

		$catids = $this->input->get('catid',array(),'Array');
		$this->_catids = $catids;
		if(!empty($filter_catid)){
			$model->setState('catids', $filter_catid);
		}elseif(empty($filter_catid)){
			$model->setState('catids', $this->_catids);
		}elseif($params->get('list_show_filter_category_all',1) && empty($filter_catid)){
			$model->setState('catids', $this->_catids[0]);
		}
		// set the depth of the category query based on parameter
		$showSubcategories = $params->get('show_subcategory_content', '0');

		if ($showSubcategories)
		{
			$model->setState('filter.max_category_levels', $params->get('show_subcategory_content', '1'));
			$model->setState('filter.subcategories', true);
		}

		$model->setState('filter.language', JLanguageMultilang::isEnabled());

		$model->setState('enabled', 1);
		$model->setState('visible', 1);
		$search = $this->input->getString('search', '');
		$model->setState('search', $search);
		//set product ids
		$items = $model->getSFProducts();
		$filter_items = $model->getSFAllProducts();
		$filters = array();
		//$filters = $this->getFilters($items);
		$filters = $this->getFilters($filter_items);
		if(count($items)) {
			foreach($items as &$item) {
				//run the content plugins
				$model->executePlugins($item, $params, 'com_content.category.productlist');
			}
			//process the raw items as products
			$this->processProducts($items);

			$pagination = $model->getSFPagination();
			//only do this if it is the default home page
			if($active == $menus->getDefault($lang->getTag())) {
				$post_data = $app->input->getArray($_GET);

	            foreach($post_data as $key=>$value){
	                if(is_array($value)){
	                    foreach($value as $key_i=>$value_i){
	                        //print_r($key_i);
	                        $pagination->setAdditionalUrlParam($key.'['.$key_i.']',$value_i);
	                    }
	                }else{
	                    $pagination->setAdditionalUrlParam($key,$value);
	                }
	            }
			}

			$view->assign('pagination', $pagination);
		}

		$filters['pricefilters'] = $this->getPriceRanges($items);

		//set up document
		// Check for layout override only if this is not the active menu item
		// If it is the active menu item, then the view and category id will match


		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$params->def('page_heading', $params->get('page_title', $menu->title));
		}

		$title = $params->get('page_title', '');


		// Check for empty title and add site name if param is set
		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}
		$document->setTitle($title);

		$meta_description = $params->get('menu-meta_description');
		$document->setDescription($meta_description);

		$keywords = $params->get('menu-meta_keywords');
		$document->setMetaData('keywords', $keywords);

		$robots = $params->get('robots');
		$document->setMetaData('robots', $robots);

		// Set Facebook meta data

		$uri = JURI::getInstance();
		$document->setMetaData('og:title', $document->getTitle());
		$document->setMetaData('og:site_name', $app->get('sitename'));
		$document->setMetaData('og:description', strip_tags($document->getDescription()));
		$document->setMetaData('og:url', $uri->toString());
		$document->setMetaData('og:type', 'product.group');

		$catids = $model->getState('catids', array());
		$category = false;
		if(is_array($catids)) {
			if(count($catids) == 1) {
				$category = $catids[0];
			}
		}elseif(is_numeric($catids)) {
			$category = $catids;
		}
		if ($category) {
			// we have a single category. So we can add a breadcrumb
			$category_data = J2Store::article ()->getCategoryById ( $category );
			if (isset ( $category_data->title ) && $params->get('breadcrumb_category_inclusion', 1)) {
				$pathway = $app->getPathway ();

				$path = array (
						array (
								'title' => $category_data->title,
								'link' => ''
						)
				);
				$path = array_reverse ( $path );

				foreach ( $path as $singlepath ) {
					$pathway->addItem ( $singlepath ['title'], $singlepath ['link'] );
				}
			}
		}

		//add custom styles
		$custom_css = $params->get('custom_css', '');
		$document->addStyleDeclaration(strip_tags($custom_css));

		if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

		// Look for template files in component folders
		$view->addTemplatePath(JPATH_COMPONENT.DS.'templates');
		$view->addTemplatePath(JPATH_COMPONENT.DS.'templates'.DS.'default');

		// Look for overrides in template folder (J2 template structure)
		$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.'templates');
		$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.'templates'.DS.'default');

		// Look for overrides in template folder (Joomla! template structure)
		$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.'default');
		$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store');

		// Look for specific J2 theme files
		if ($params->get('subtemplate'))
		{
			$view->addTemplatePath(JPATH_COMPONENT.DS.'templates'.DS.$params->get('subtemplate'));
			$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.'templates'.DS.$params->get('subtemplate'));
			$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.$params->get('subtemplate'));
		}

		//allow plugins to modify the data
		J2Store::plugin()->event('ViewProductList', array(&$items, &$view, $params, $model));

		$view->assign('products',$items);
		$view->assign('state', $model->getState());
		$view->assign('params',$params);
		$view->assign('filters',$filters);
		$view->assign('filter_catid',$filter_catid);
		$view->assign('currency', J2store::currency());

		$view->assign('active_menu', $menu) ;
		$content ='var j2store_product_base_link ="'. $menu->link.'&Itemid='.$menu->id .'";';
		JFactory::getDocument()->addScriptDeclaration($content);

		$this->display(in_array('browse', $this->cacheableTasks));

		return true;
	}

	public function processProducts(&$items) {

		foreach ($items as &$item) {

			$item->product_short_desc = $item->introtext;
			$item->product_long_desc = $item->fulltext;
			F0FModel::getTmpInstance('Products', 'J2StoreModel')->runMyBehaviorFlag(true)->getProduct($item);
			$item->product_name = $item->title;
		}

	}

	/**
	 * ACL check before editing a record; override to customise
	 *
	 * @return  boolean  True to allow the method to run
	 */
	protected function onBeforeEdit()
	{
		if(parent::onBeforeEdit()) {

			$task = $this->input->getString('task');
			if($task=='edit'){
				$view = $this->getThisView();
				$this->form_prefix = $this->input->getString('form_prefix');
				// Get/Create the model
				if ($model = $this->getThisModel())
				{
					// Push the model into the view (as default)
					$view->setModel($model, true);
				}
				// Set the layout
				$view->setLayout(is_null($this->layout) ? 'default' : $this->layout);
				if($task=='edit'){
					$this->item = $model->runMyBehaviorFlag(true)->getItem();
					$view->item = $this->item;
					$view->setLayout('form');
					$view->addTemplatePath(JPATH_ADMINISTRATOR.'/components/com_j2store/views/product/tmpl/');
					$view->set('form_prefix' ,$this->form_prefix);

					$view->product_types = JHtml::_('select.genericlist', $model->getProductTypes(), $this->form_prefix.'[product_type]', array(), 'value', 'text', $this->item->product_type);

					$view->manufacturers = J2Html::select()->clearState()
					->type('genericlist')
					->name($this->form_prefix.'[manufacturer_id]')
					->value($this->item->manufacturer_id)
					->setPlaceHolders(
							array(''=>JText::_('J2STORE_SELECT_OPTION'))
					)
					->hasOne('Manufacturers')
					->setRelations( array(
							'fields' => array (
									'key' => 'j2store_manufacturer_id',
									'name' => array('company')
							)
					)
					)->getHtml();

					//vendor
					$view->vendors = J2Html::select()->clearState()
					->type('genericlist')
					->name($this->form_prefix.'[vendor_id]')
					->value($this->item->vendor_id)
					->setPlaceHolders(array(''=>JText::_('J2STORE_SELECT_OPTION')))
					->hasOne('Vendors')
					->setRelations(
							array (
									'fields' => array
									(
											'key'=>'j2store_vendor_id',
											'name'=>array('first_name','last_name')
									)
							)
					)->getHtml();

					//tax profiles
					$view->taxprofiles = J2Html::select()->clearState()
					->type('genericlist')
					->name($this->form_prefix.'[taxprofile_id]')
					->value($this->item->taxprofile_id)
					->setPlaceHolders(array(''=>JText::_('J2STORE_NOT_TAXABLE')))
					->hasOne('Taxprofiles')
					->setRelations(
							array (
									'fields' => array (
											'key'=>'j2store_taxprofile_id',
											'name'=>'taxprofile_name'
									)
							)
					)->getHtml();

					$view->item->price_calculator = isset($this->item->price_calculator) && !empty($this->item->price_calculator) ? $this->item->price_calculator : 'standard';

					//pricing options
					$view->pricing_calculator = J2Html::select()->clearState()
					->type('genericlist')
					->name($this->form_prefix.'[pricing_calculator]')
					->value($this->item->price_calculator)
					->setPlaceHolders(J2Store::product()->getPricingCalculators())
					->getHtml();

					$view->product_filters = F0FTable::getAnInstance('ProductFilter', 'J2StoreTable')->getFiltersByProduct($this->item->j2store_product_id);

				}
			}elseif($task =='setproductprice'){
				$this->setproductprice();
			}

			//$this->$task();
			return true;
		}
		return false;
	}

	/**
	 * Method to get Filters and to assing in the browse view
	 */
	public function getFilters($items){

		//filters
		$filters =array();
		$filter_categories =array();
		$params = F0FModel::getTmpInstance('Products', 'J2StoreModel')->getMergedParams();
		//now set the categories
		$filters['filter_categories'] =array();
		if($params->get('list_filter_selected_categories')){
			$filter_categories = $params->get('list_filter_selected_categories');
			$filters['filter_categories'] = F0FModel::getTmpInstance('Products', 'J2StoreModel')->getCategories($filter_categories);
		}

		//to show the product filter for the existing products in the product layout view
		//should not fetch all product filters
		$product_ids = array();
		foreach($items as $item){
			$product_ids[] =$item->j2store_product_id;
		}
		$filters['sorting'] = F0FModel::getTmpInstance('Products', 'J2StoreModel')->getSortFields();
		//$filters['productfilters'] = F0FModel::getTmpInstance('Products', 'J2StoreModel')->getProductFilters($product_ids);

		$filters['productfilters'] = array();
		$product_model = F0FModel::getTmpInstance('Products', 'J2StoreModel');
		//fetch the pfilters when show product filter is enabled
		if($params->get('list_show_product_filter',1)){
			//this option will list all the productfilter added
			if($params->get('list_product_filter_list_type','selected') == 'all'){
				$filters['productfilters'] = F0FTable::getAnInstance('ProductFilter', 'J2StoreTable')->getFilters();
			}elseif($params->get('list_product_filter_list_type','selected') == 'selected'){
				// this option will list productfilter related to the products selected
				$filters['productfilters'] = $product_model->getProductFilters($product_ids);
			}
		}

		//fetch the Manufacturers when Show manufacturer filter is enabled
		if($params->get('list_show_manfacturer_filter',1)){
			if($params->get('list_manufacturer_filter_list_type','selected') == 'all'){
				$filters['manufacturers'] =$product_model->getManufacturers();
			}else{
				$filters['manufacturers'] = $product_model->getManfucaturersByProduct($product_ids);
			}
		}
		//fetch the Vendors when Show vendor filter is enabled
		if($params->get('list_show_vendor_filter',1)){
			if($params->get('list_vendor_filter_list_type','selected') == 'all'){
				$filters['vendors'] = $product_model->getVendors();
			}else{
				$filters['vendors'] =$product_model->getVendorsByProduct($product_ids);
			}
		}
		return $filters;
	}



	public function getPriceRanges($items){

		//get the active menu details
		$menu = JFactory::getApplication()->getMenu()->getActive();
		$params = F0FModel::getTmpInstance('Products', 'J2StoreModel')->getMergedParams();
		$ranges =array();
		$currency = J2Store::currency();
		//price filters
		if(isset($items['0']->pricing->price )) {
			$priceHigh = abs( $items['0']->pricing->price );
		}else {
			$priceHigh = 0;
		}
		//create link to be concatinated
		//get the highest price
		$priceHigh = abs($params->get('list_price_filter_upper_limit', '1000'));
		$link = '';
		$priceLow =0;
		$range =0;
		if(count($items )) {
			foreach($items as $item){

				//$item->pricing->price
				//calculate the price low
				$priceLow = ( count($item) == 1 ) ?  0 : abs( $item[count( $item ) - 1]->pricing->price );
				//calculate the range price high - low price
				$range = ( abs( $priceHigh ) - abs( $priceLow ) )/4;
				//now round the price
				$roundRange = F0FModel::getTmpInstance('Products','J2StoreModel')->_priceRound($range, $params->get( 'list_price_round_digit', '100' ), true);
				//get the lowest price
				$roundPriceLow = F0FModel::getTmpInstance('Products','J2StoreModel')->_priceRound( $priceLow, $params->get( 'list_price_round_digit', '100' ) );
			}
		}
		$ranges['max_price'] = $priceHigh;
		$ranges['min_price'] = $priceLow;
		$ranges['range'] = $range;
		return $ranges;
	}


	public function view() {

		$product_id = $this->input->getInt('id');

		if(!$product_id) {
			$this->setRedirect(JRoute::_('index.php'));
			return;
		}

		//first clear cache
		J2Store::utilities()->nocache();
		J2Store::utilities()->clear_cache();

		$app = JFactory::getApplication();

		$view = $this->getThisView();

		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}
		$ns = 'com_j2store.'.$this->getName();

		$params = F0FModel::getTmpInstance('Products', 'J2StoreModel')->getMergedParams();
		if($params->get('item_show_vote', 0)) {
			$params->set('show_vote', 1);
		}
		$product_helper = J2Store::product();


		//get product
		$product = $product_helper->setId($product_id)->getProduct();
		F0FModel::getTmpInstance('Products', 'J2StoreModel')->runMyBehaviorFlag(true)->getProduct($product);

		if( ($product->visibility !=1) || ($product->enabled !=1) ){
			$this->setRedirect(JRoute::_('index.php'),JText::_('J2STORE_PRODUCT_NOT_ENABLED_CONTACT_SITE_ADMIN_FOR_MORE_DETAILS'),'warning');
			return;
		}

		//run plugin events
		$model->executePlugins($product->source, $params, 'com_content.article.productlist');

		$product->product_short_desc = $model->runPrepareEventOnDescription($product->product_short_desc, $product->product_source_id, $params, 'com_content.article.productlist');
		$product->product_long_desc = $model->runPrepareEventOnDescription($product->product_long_desc, $product->product_source_id, $params, 'com_content.article.productlist');

		//get filters / specs by product
		$filters = F0FModel::getTmpInstance('Products', 'J2StoreModel')->getProductFilters($product->j2store_product_id);

		//upsells
		$up_sells = array();
		if($params->get('item_show_product_upsells', 0) && !empty($product->up_sells)) {
			$up_sells = $product_helper->getUpsells($product);
		}

		//cross sells
		$cross_sells = array();
		if($params->get('item_show_product_cross_sells', 0) && !empty($product->cross_sells)) {
			$cross_sells = $product_helper->getCrossSells($product);
		}

		if(!defined('DS')) {
			define('DS', DIRECTORY_SEPARATOR);
		}
		// Look for template files in component folders
		$view->addTemplatePath(JPATH_SITE.'/components/com_j2store/templates');
		$view->addTemplatePath(JPATH_SITE.'/components/com_j2store/templates/default');

		// Look for overrides in template folder (J2 template structure)
		$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.'templates');
		$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.'templates'.DS.'default');

		// Look for overrides in template folder (Joomla! template structure)
		$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.'default');
		$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store');

		// Look for specific J2 theme files
		if ($params->get('subtemplate'))
		{
			$view->addTemplatePath(JPATH_COMPONENT.DS.'templates'.DS.$params->get('subtemplate'));
			$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.'templates'.DS.$params->get('subtemplate'));
			$view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.$params->get('subtemplate'));
		}

		//set up document
		// Check for layout override only if this is not the active menu item
		// If it is the active menu item, then the view and category id will match

		$active	= $app->getMenu()->getActive();
		$menus		= $app->getMenu();
		$pathway	= $app->getPathway();
		$document = JFactory::getDocument();
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		$params->def('page_heading', $product->product_name);
		$params->set('page_title', $product->product_name);

		$title = $params->get('page_title', '');

		// Check for empty title and add site name if param is set
		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$document->setTitle($title);

		if ($product->source->metadesc)
		{
			$document->setDescription($product->source->metadesc);
		}
		else
		{
			$metaDescItem = preg_replace("#{(.*?)}(.*?){/(.*?)}#s", '', $product->source->introtext.' '.$product->source->fulltext);
			$metaDescItem = strip_tags($metaDescItem);
			$metaDescItem = J2Store::utilities()->characterLimit($metaDescItem, 150);
			$document->setDescription(html_entity_decode($metaDescItem));
		}

		if ($product->source->metakey)
		{
			$document->setMetadata('keywords', $product->source->metakey);
		} else {

			$keywords = $params->get('menu-meta_keywords');
			$document->setMetaData('keywords', $keywords);
		}

		$metadata = json_decode($product->source->metadata);
		if(isset($metadata->robots)) {
			$document->setMetaData('robots', $metadata->robots);
		}else {
			$robots = $params->get('robots');
			$document->setMetaData('robots', $robots);
		}

		// Set Facebook meta data

		$uri = JURI::getInstance();
		$document->setMetaData('og:title', $document->getTitle());
		$document->setMetaData('og:site_name', $app->get('sitename'));
		$document->setMetaData('og:description', strip_tags($document->getDescription()));
		$document->setMetaData('og:url', $uri->toString());
		$document->setMetaData('og:type', 'product');

		if(isset($product->main_image)) {
			$facebookImage = $product->main_image;
		}else {
			$facebookImage = $product->thumb_image;
		}
		if (!empty($facebookImage))
		{
			if (JFile::exists(JPATH_SITE.'/'.$facebookImage))
			{
				$image = substr(JURI::root(), 0, -1).'/'.str_replace(JURI::root(true), '', $facebookImage);
				$document->setMetaData('og:image', $image);
				$document->setMetaData('image', $image);
			}
		}

		//set canonical url

		foreach($document->_links as $key=> $value)
		{
			if(is_array($value))
			{
				if(array_key_exists('relation', $value))
				{
					if($value['relation'] == 'canonical')
					{
						// we found the document link that contains the canonical url
						// change it!
						$canonicalUrl = $uri->toString();

						$document->_links[$canonicalUrl] = $value;
						unset($document->_links[$key]);
						break;
					}
				}
			}
		}
		//$active_menu = $app->getMenu()->getActive();
		$back_link = "";
		$back_link_title = "";
		$item_id = "";
		if(!empty($active)){
			$back_link = $active->link;
			$back_link_title = $active->title;
			$item_id = $active->id;
		}

		if(isset($back_link) && !empty($back_link_title)){
			$view->assign('back_link' , JRoute::_($back_link));
			$view->assign('back_link_title' ,$back_link_title);
		}

		//add a pathway
		$pathway	= $app->getPathway();
		$path = array(array('title' => $product->product_name, 'link' => ''));
		if(isset($product->source->category_title) && $params->get('breadcrumb_category_inclusion', 1)) {
			$path[] =
			array (
					'title' => $product->source->category_title,
					'link' => JRoute::_('index.php?option=com_j2store&view=products&filter_catid='.$product->source->catid.'&Itemid='.$item_id)
			);
			//JRoute::_('index.php?option=com_j2store&view=products&Itemid='.$app->getMenu()->getActive()->id)
		}
		$path = array_reverse($path);

		//get the names already in the path way
		$names = $pathway->getPathwayNames();

		foreach ($path as $item)
		{
			if($params->get('breadcrumb_category_inclusion', 1)) {
				$pathway->addItem($item['title'], $item['link']);
			}else {
				//eliminate if the same names that are already there.
				if (!in_array($item['title'], $names)) {
					$pathway->addItem($item['title'], $item['link']);
				}
			}
		}

		//add class to the module for better styling control.
		$script = '
		if (typeof(jQuery) !== \'undefined\') {
		   jQuery(document).ready(function() {
		      jQuery("body").addClass("j2store-single-product-view view-product-'.$product->j2store_product_id.'  '.$product->source->alias.'");
		  });
		}
		';
		$document->addScriptDeclaration($script);

		//add custom styles
		$custom_css = $params->get('custom_css', '');
		$document->addStyleDeclaration(strip_tags($custom_css));

		//allow plugins to modify the data
		J2Store::plugin()->event('ViewProduct', array(&$product, &$view));


		$view->assign('product', $product);
		$view->assign('filters', $filters);
		$view->assign('up_sells', $up_sells);
		$view->assign('cross_sells', $cross_sells);
		$view->assign('product_helper', $product_helper);
		$view->assign('params', $params);
		$view->assign('currency', J2store::currency());
		$view->setLayout('view');
		$view->display();
	}

	/**
	 * Method to direct to compare layout when
	 * product added to compare
	 */
	function compare(){
		$model = $this->getModel('Products');
		$view = $this->getThisView();
		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}
		$view->setLayout('compare');
		$view->display();
	}


	/**
	 * Method to direct to compare layout when
	 * product added to compare
	 */
	function wishlist(){
		$model = $this->getModel('Products');
		$view = $this->getThisView();
		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}
		$view->setLayout('wishlist');
		$view->display();
	}
}
