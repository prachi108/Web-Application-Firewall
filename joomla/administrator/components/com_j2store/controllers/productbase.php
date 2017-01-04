<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
class J2StoreControllerProductsBase extends F0FController
{

	protected $view = 'Products';

	/**
	 * Method to change the product type
	 * here the  product child tables will be deleted
	 *
	 */

	public function getFilterStates() {
		$app = JFactory::getApplication();
		$state = array();
		$state['search'] = $app->input->getString('search','');
		$state['product_type']= $app->input->getString('product_type','');
		$state['visible'] = $app->input->getString('visible',null);
		$state['vendor_id'] = $app->input->getInt('vendor_id',0);
		$state['manufacturer_id'] = $app->input->getString('manufacturer_id',0);
		$state['productid_from']= $app->input->getString('productid_from','');
		$state['productid_to']= $app->input->getString('productid_to','');
		$state['pricefrom']= $app->input->getString('pricefrom','');
		$state['priceto']= $app->input->getString('priceto','');
		$state['since']= $app->input->getString('since','');
		$state['until']= $app->input->getString('until','');
		$state['taxprofile_id']= $app->input->getString('taxprofile_id','');
		$state['shippingmethod']=$app->input->getString('shippingmethod','');
		$state['filter_order']= $app->input->getString('filter_order','j2store_product_id');
		$state['filter_order_Dir']= $app->input->getString('filter_order_Dir','ASC');
		$state['sku']= $app->input->getString('sku','');
		$state['sortby']= $app->input->getString('sortby','');
		$state['productfilter_id']= $app->input->getString('productfilter_id',0);
		return $state;
	}


	public function changeProductType(){
		$app = JFactory::getApplication();
		$data = $app->input->getArray($_POST);
		$product_id 	=  $data['product_id'];
		$product_type 	=  $data['product_type'];

		$json =array();
		if(isset($data['product_id']) && $data['product_id']) {
			//allow plugins to run their events
			$json = J2Store::plugin()->eventWithArray('ChangeProductType', array($product_id, $product_type));
				
			if(!F0FTable::getAnInstance('Product','J2StoreTable')->delete($product_id)){
				$json['success'] = false;
			}
			
			if(!$json) {
				$json['success'] = true;
			}
		}
		echo json_encode($json);
		$app->close();
	}

	/**
	 * Method to get Related products
	 *
	 */
	public function getRelatedProducts(){

		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$q = $app->input->post->get('q', '','string');
		$ignore_product_id = $app->input->getInt('product_id');
		$json = array();
		$model = $this->getModel('Products');
		$model->setState('search', $q);
		$query = $db->getQuery(true);
		$query->select('#__j2store_products.j2store_product_id')->from("#__j2store_products as #__j2store_products");
		$query->join ( 'LEFT', '#__j2store_variants AS v ON v.product_id=#__j2store_products.j2store_product_id' );
		$query->select('v.sku');
		$query->where('v.is_master=1');
		$query->where('#__j2store_products.enabled=1');
		$query->where('#__j2store_products.visibility=1');
		$query->where('#__j2store_products.j2store_product_id !=' .$db->q($ignore_product_id));

		J2Store::plugin()->importCatalogPlugins();
		JFactory::getApplication()->triggerEvent('onJ2StoreAfterProductListQuery',array(&$query ,&$model));
		//echo $query;
		$db->setQuery($query);
		$items = $db->loadObjectList();
		$result = array();
		if(isset($items) && !empty($items)){
			foreach ($items as $key=>$item) {
				  if($item->product_name){
					$result[$key]['j2store_product_id'] = $item->j2store_product_id;
					$product_name = $item->product_name;

					if(!empty($item->sku)){
						$product_name .= "(".$item->sku.")";
					}
					$result[$key]['product_name'] = $product_name;
			  	}
			}
		}
		$json['products'] = $result;
		echo json_encode($json);
		$app->close();
	}

	/**
	 * Method to set productoptionvalues
	 */
	function setproductoptionvalues()
	{
		$app = JFactory::getApplication();
		$model = $this->getModel('Productoptions');
		$view = $this->getThisView();

		$product_id = $app->input->getInt('product_id', 0);

		$product = J2Store::product()->setId($product_id)->getProduct();
		//get product option id
		$productoption_id = $app->input->getInt('productoption_id', 0);

		//set productoption id
		//to get Item
		//$model->setId($productoption_id);
		$product_option = F0FModel::getTmpInstance('ProductOption' ,'J2StoreModel')->getItem($productoption_id);
		//get all the optionvalues matching the option id
		$option_values =  F0FModel::getTmpInstance('Optionvalues','J2StoreModel')->option_id($product_option->option_id)->getList();

		//lets load all the product optionvalues
		$product_optionvalues = $model->getTmpInstance('Productoptionvalues','J2StoreModel')
		->productoption_id($productoption_id)
		->getList();

		if(isset($product_option->parent_id) && !empty($product_option->parent_id)){
			$parentopvalues =$model->getParentOptionValues($product_option->parent_id, $product_option->product_id);
			$view->set('parent_optionvalues',$parentopvalues );

		}
		$view->addTemplatePath(JPATH_ADMINISTRATOR.'/components/com_j2store/views/product/tmpl/');
		$view->set('product_option',$product_option);
		$view->set('productoption_id',$productoption_id);
		$view->set('option_values',$option_values);
		$view->set('product_id',$product_id);
		$view->set('product',$product);
		$view->set('prefix','jform[poption_value]');
		$view->set('product_optionvalues',$product_optionvalues);
		$view = $this->display();
	}


	/**
	 * Method to setparent optionvalues
	 */
	public function setparentoptionvalues(){

		$app = JFactory::getApplication();
		$model = $this->getModel('Productoptions');
		$view = $this->getThisView();

		//get product option id
		$productoption_id = $app->input->getInt('productoption_id', 0);

		//set productoption id
		//to get Item
		$product_option = F0FModel::getTmpInstance('ProductOption' ,'J2StoreModel')->getItem($productoption_id);

		$product_optionvalues = $model->getTmpInstance('Productoptionvalues','J2StoreModel')
		->productoption_id($productoption_id)
		->getList();

		$view->set('product_optionvalues' ,$product_optionvalues);
		$parentopvalues =array();

		if(isset($product_option->parent_id) && !empty($product_option->parent_id))
			$parentopvalues =$model->getParentOptionValues($product_option->parent_id,$product_option->product_id);
		$view->addTemplatePath(JPATH_ADMINISTRATOR.'/components/com_j2store/views/product/tmpl/');
		$view->set('product_option',$product_option);
		$view->set('parent_optionvalues',$parentopvalues);
		$view->set('productoption_id',$productoption_id);
		$view->set('parent_id',$product_option->parent_id);
		$view->set('prefix','jform[poption_value]');
		$view = $this->display();

	}

	/**
	 * Method to save parentproduct optionvalue
	 * product option type != select || dropdown || radio || checkbox
	 */
	public function saveparentproductoptionvalue(){
		$app = JFactory::getApplication();
		$data = $app->input->getArray($_POST);
		$url = "index.php?option=com_j2store&view=products&task=setparentoptionvalues&productoption_id=".$data['productoption_id']."&layout=parentproductopvalues&tmpl=component";
		$msgType = 'Message';
		$msg = JText::_('J2STORE_PRODUCT_PARENT_OPTION_VALUE_SAVED');
		$poptionvalue = F0FTable::getAnInstance('Productoptionvalue','J2StoreTable');
		$data['parent_optionvalue'] = implode(',',$data['parent_optionvalue']);

		if(!$poptionvalue->save($data)){
			$msg .= $poptionvalue->getError();
			$msgType = 'Warning';
		}
		$app->redirect($url,$msg,$msgType);

	}



	/**
	 * Method to create product optionvalues
	 */
	public function createproductoptionvalue(){
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$data = $app->input->getArray($_POST);

		if( isset($data['product_optionvalue_attribs'])){
			$data['product_optionvalue_attribs'] =$db->escape ($data['product_optionvalue_attribs']);
		}

		if(isset($data['parent_optionvalue']) && !empty($data['parent_optionvalue'])){
			if(isset($data['parent_optionvalue'])){
				$data['parent_optionvalue'] = implode(',',$data['parent_optionvalue']);
			}
		}

		$product_option = F0FTable::getAnInstance('ProductOption','J2StoreTable');
		$product_option->load($data['productoption_id']);
		$poptionvalue = F0FTable::getAnInstance('Productoptionvalue','J2StoreTable');
		//$url = "index.php?option=com_j2store&view=products&task=setproductoptionvalues&productoption_id=".$data['productoption_id']."&layout=productoptionvalues&tmpl=component";
		$url ="index.php?option=com_j2store&view=products&task=setproductoptionvalues&product_id=".$data['product_id']."&productoption_id=".$data['productoption_id']."&layout=productoptionvalues&tmpl=component";
		$msgType ="Message";
		$msg = JText::_('J2STORE_PRODUCT_OPTION_SAVED_SUCCESSFULLY');
		
		if(isset( $product_option->is_variant ) && $product_option->is_variant){
			$data['product_optionvalue_price'] = 0;
		}
		if(!$poptionvalue->save($data)){
			$msgType ="Warning";
			$msg = $poptionvalue->getError();
		}
		$app->redirect($url,$msg,$msgType);
	}

	/**
	 * Method to save all  productoptionvalue
	 */
	public function saveproductoptionvalue(){
		$app = JFactory::getApplication();
		$error = false;
		$data = $app->input->getArray($_POST);
		$poption_id =$data['productoption_id'];
		$product_id =$data['product_id'];
		//$url = "index.php?option=com_j2store&view=products&task=setproductoptionvalues&productoption_id=".$poption_id."&layout=productoptionvalues&tmpl=component";
		$url ="index.php?option=com_j2store&view=products&task=setproductoptionvalues&product_id=".$product_id."&productoption_id=".$poption_id."&layout=productoptionvalues&tmpl=component";
		$msgType ="Message";
		$msg = '';
		
		$product_option = F0FTable::getAnInstance('ProductOption','J2StoreTable');
		$product_option->load($poption_id);
		foreach($data['jform']['poption_value'] as $povalue_item){
			$poptionvalue = F0FTable::getAnInstance('Productoptionvalue','J2StoreTable')->getClone ();
			if(isset($povalue_item['parent_optionvalue'])){
				$povalue_item['parent_optionvalue'] = implode(',',$povalue_item['parent_optionvalue']);
			}

			if( isset($povalue_item['product_optionvalue_attribs']) && !empty($povalue_item['product_optionvalue_attribs'])){
				$povalue_item['product_optionvalue_attribs']=$povalue_item['product_optionvalue_attribs'];
			}
			if(isset( $product_option->is_variant ) && $product_option->is_variant){
				$povalue_item['product_optionvalue_price'] = 0;
			}

			if(!$poptionvalue->save($povalue_item)){
				$msg .= $poptionvalue->getError();
				$msgType = 'notice';
				$error = true;
			}
		}
		if ($error)
		{
			$msg = JText::_('J2STORE_ERROR') . " - " . $this->message;
		}
		else
		{
			$msg = JText::_('J2STORE_PRODUCT_OPTION_SAVED_SUCCESSFULLY');
		}

		$app->redirect($url,$msg,$msgType);
	}

	/**
	 * Method to delete product option value
	 */
	public function deleteProductOptionvalues(){
		$product_id = $this->input->getInt('product_id');
		$product = F0FTable::getAnInstance('Product' ,'J2StoreTable');
		$product->load($product_id);
		$productoption_id = $this->input->get('productoption_id');
		$cids = $this->input->get('cid', array(), 'array');
		$message = JText::_('J2STORE_ITEMS_DELETED');
		$msgType="message";
		$url = "index.php?option=com_j2store&view=products&task=setproductoptionvalues&product_id=".$product_id."&productoption_id=".$productoption_id."&layout=productoptionvalues&tmpl=component" ;
		if(isset($cids) && count($cids)){
			foreach($cids as $cid){
				$poptionvalue = F0FTable::getAnInstance('Productoptionvalue','J2StoreTable');
				$poptionvalue->load($cid);
				try{
					$poptionvalue->delete($cid);
				}Catch(Exception $e){
					$message = $e->getMessage();
					//throw new Exception($e->getMessage());
					if($product->product_type=='variable'){
						$message = JText::_('J2STORE_DELETE_VARIANT_OPTION_ERROR_MSG');
					}
					$msgType = 'notice';
				}
			}
		}
		JFactory::getApplication()->redirect( $url, $message, $msgType );
	}


	/**
	 * Method to remove product options
	 */
	public function removeProductOption(){
		$poption_id = $this->input->getInt('pao_id');
		$product_type = $this->input->getint('product_type','');
		$poptionValue = F0FTable::getInstance('Productoption','J2StoreTable');
		$result = array();
		$result['success'] = false;
		try{
			if($poptionValue->delete($poption_id)){
				$result['success'] = true;
			}
		}Catch(Exception $e){
			$result['error'] = JText::_('J2STORE_DELETE_PRODUCT_OPTION_ERROR_MSG');
			if(isset($product_type) && $product_type=='variable'){
				$result['error'] = JText::_('J2STORE_DELETE_VARIANT_OPTION_ERROR_MSG');
			}
		}

		echo json_encode($result);
		JFactory::getApplication()->close();
	}
	function setDefault() {
		$app = JFactory::getApplication();
		$cid = $this->input->get('cid', array(), 'array');
		$task = $this->input->getString('optiontask');
		$pov_id = $cid[0];
		$json = array();
		$json['success'] =true;
		$product_id = $app->input->getInt('product_id');
		$productoption_id = $app->input->getInt('productoption_id');
		if($product_id && $productoption_id && $pov_id) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)->update('#__j2store_product_optionvalues')->set('product_optionvalue_default=0')
			->where('productoption_id='.$db->q($productoption_id));
			$db->setQuery($query)->execute();
			$row =	F0FTable::getAnInstance('ProductOptionvalue','J2StoreTable');
			$row->load($pov_id);
			if($task == 'unsetDefault') {
				$row->product_optionvalue_default=0;
			} else {
				$row->product_optionvalue_default=1;
			}
			if(!$row->store()){
				$json['success'] =false;
			}
		}
		echo json_encode($json);
		$app->close();

	}


	/**
	 * Method to create new product price
	 */
	public function createproductprice(){
		$data =  $this->input->getArray($_POST);
		$variant_id = $this->input->getInt('variant_id');
		$product_id = $this->input->getInt('product_id');
		$nullDate =  JFactory::getDbo()->getNullDate();

		/* $data['date_from'] =  (isset($data['date_from']) && $data['date_from'])  ? $data['date_from'] : JFactory::getDbo()->getNullDate();
			$data['date_to'] =  (isset($data['date_to']) && $data['date_to'])  ? $data['date_from'] : JFactory::getDbo()->getNullDate(); */

		if ( empty( $data['date_from'] ) || $data['date_from']== $nullDate )
		{
			$data['date_from'] = $nullDate;
		}

		if ( empty( $data['date_to'] ) || $data['date_to']== $nullDate )
		{
			$data['date_to'] = $nullDate;
		}

		$variant = F0FTable::getAnInstance('Variant','J2StoreTable');
		$variant->load(array('product_id' => $product_id));

		$price = F0FTable::getAnInstance('ProductPrice','J2StoreTable');

		$msg =JText::_('J2STORE_PRODUCT_PRICE_SAVED_SUCCESSFULLY');
		$msgType = "Message";
		$url ="index.php?option=com_j2store&view=products&task=setproductprice&product_id=".$product_id."&variant_id=".$variant_id."&layout=productpricing&tmpl=component";
		$data['variant_id'] = $variant_id;
		if(!$price->save($data)){
			$msg =JText::_('J2STORE_PRODUCT_PRICE_ERROR_IN_SAVING_PRICE');
			$msgType = "Warning";
		}
		JFactory::getApplication()->redirect($url,$msg,$msgType);
	}

	/**
	 * Method to setproductprice layout tmpl
	 */
	function setproductprice(){

		//get variant id
		$variant_id = $this->input->getInt('variant_id', 0);
		$prices = array();
		$groups = array();
		$model = F0FModel::getTmpInstance('ProductPrices', 'J2StoreModel');

		if($variant_id) {
			$model->setState('variant_id', $variant_id);
			$prices = $model->getList();
			$groups = JHtmlUser::groups();
		}

		$view = $this->getThisView();
		$view->setModel($model, true);
		$view->addTemplatePath(JPATH_ADMINISTRATOR.'/components/com_j2store/views/product/tmpl/');
		$view->setLayout('productpricing');
		$view->set('variant_id',$variant_id);
		$view->set('groups',$groups);
		$view->set('prices',$prices);
		$view->display();
	}

	/**
	 * Method to save product prices
	 *
	 */
	function saveproductprices(){
		$variant_id =  $this->input->getInt('variant_id', null);
		if(!$variant_id || empty($variant_id)) return;
		$items = $this->input->getArray($_POST);
		$url ="index.php?option=com_j2store&view=products&task=setproductprice&variant_id=".$variant_id."&layout=productpricing&tmpl=component";
		$msg =JText::_('J2STORE_PRODUCT_PRICE_SAVED_SUCCESSFULLY');
		$msgType="Message";
		foreach($items['jform']['prices'] as $item){

			$nullDate = JFactory::getDbo( )->getNullDate( );
			if ( empty( $item['date_from'] ) || $item['date_from']== $nullDate )
			{
				$item['date_from'] = $nullDate;
			}

			if ( empty( $item['date_to'] ) || $item['date_to']== $nullDate )
			{
				$item['date_to'] = $nullDate;
			}

			/* 		$item['date_from'] =  (isset($item['date_from']) && $item['date_from'])  ? $item['date_from'] : JFactory::getDbo()->getNullDate();
				$item['date_to'] =  (isset($item['date_to']) && $item['date_to'])  ? $item['date_from'] : JFactory::getDbo()->getNullDate();
			*/
			$productprice= F0FTable::getAnInstance('productprice','J2StoreTable');
			if($productprice->load($item['j2store_productprice_id'])) {
				//$productprice->load(1);
				if(!$productprice->save($item)){
					$msg = $productprice->getError();
					$msgType="Warning";
				}
			}
		}
		JFactory::getApplication()->redirect($url,$msg,$msgType);
	}



	/**
	 * Method to get Files
	 */
	function getFiles() {
		$app = JFactory::getApplication();
		$model = $this->getModel('Products');

		//@TODO get configuration from configuration view
		$params = J2Store::config();
		$savefolder = $params->get('attachmentfolderpath');
		jimport('joomla.filesystem.folder');
		$html = '';
		$path = JPath::clean(JPATH_ROOT.'/'.$savefolder);
		if(empty($savefolder) || !JFolder::exists($path) ) {
			$html .= JText::_('J2STORE_ERROR_ATTACHMENT_PATH_OUTSIDE_ROOT');
			$html .='<br>';
			$html .= JText::sprintf('J2STORE_MSG_WEB_ROOT', JPATH_ROOT);
			$html .='<br>';
			$html .= JText::sprintf('J2STORE_MSG_GIVEN_ATTACHMENT_PATH', $savefolder);
			$html .='<br>';
			echo $html;
			$app->close();
		}
		$dir = $app->input->getString('dir');
		$dir = urldecode($dir);

		if($dir) {
			$model->setState('folder', $dir);
		}

		//if(file_exists($root . $dir) ) {
		$files = (array)$model->getFilesData();
		$folders = (array)$model->getFolders();		
		natcasesort($files);
		natcasesort($folders);
		J2Store::plugin()->event('GetExternalFiles', array($model,&$files,&$folders));
		if( count($files) || count($folders)) { /* The 2 accounts for . and .. */
			$html .= "<ul class=\"jqueryFileTree\" style=\"\">";
			// All dirs
			foreach( $folders as $file ) {
				//if( file_exists($root . $dir . $file) && $file != '.' && $file != '..' && is_dir($root . $dir . $file) ) {
				$html .= "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($dir . $file) . "/\">" . htmlentities($file) . "</a></li>";
				//	}
			}
			$html .= J2Store::plugin()->eventWithHtml('DisplayExternalFile',array(&$files,$dir));
			// All files
			foreach( $files as $file ) {
				//	if( file_exists($root . $dir . $file) && $file != '.' && $file != '..' && !is_dir($root . $dir . $file) ) {
				$ext = preg_replace('/^.*\./', '', $file);
				$html .="<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . htmlentities($dir . $file) . "\">" . htmlentities($file) . "</a></li>";
				//	}
			}
			$html .= "</ul>";
		} else {
			$html .= JText::_('J2STORE_ERROR_IN_PATH');
		}
		//		}
		echo $html;
		$app->close();
	}

	/***
	 * Method to delete files
	*/
	function deleteFiles(){
		$app = JFactory::getApplication();
		$variant_id = $app->input->getInt('variant_id');
		$product_id = $app->input->getInt('product_id');
		$file_id = $app->input->getInt('productfile_id');
		$model = $this->getThisModel('products');
		$url ="index.php?option=com_j2store&view=products&task=setproductfiles&variant_id=".$variant_id."&product_id=".$product_id."&layout=productfiles&tmpl=component";
		$msg = JText::_('J2STORE_PRODUCT_FILE_DELETED_SUCCESSFULLY');
		$msgType = 'message';
		if(isset($product_id) && !empty($product_id)){
			if(!$model->deleteProductFile($file_id,$product_id)){
				$msgType = 'warning';
				$msg = JText::_('J2STORE_PRODUCT_FILE_DELETION_ERROR');
			}
		}
		JFactory::getApplication()->redirect($url,$msg ,$msgType);
	}

	/**
	 * Method to generate variants for variable product
	 */
	function generateVariants() {

		$model = $this->getModel('Products', 'J2StoreModel');
		$json = $model->generateVariants();
		echo json_encode($json);
		JFactory::getApplication()->close();
	}

	/**
	 * Method to set variant
	 */
	function setvariant(){
		$app = JFactory::getApplication();
		$model = F0FModel::getAnInstance('Variants' ,'J2StoreModel');
		$view = $this->getThisView();
		$variant_id = $app->input->getInt('variant_id');
		$item = $model->getItem($variant_id);
		$product_id = $item->product_id;
		$product_item = F0FModel::getAnInstance('Products', 'J2StoreModel')->getItem($product_id);
		$view = $this->getThisView();
		$view->set('item',$item);
		$view->addTemplatePath(JPATH_ADMINISTRATOR.'/components/com_j2store/views/product/tmpl/');
		$view->set('product_item',$product_item);
		$view->set('form_prefix' , 'jform[j2store][attribs]');
		$view = $this->display();
	}

	/**
	 * Method to save variant
	 */
	function savevariant(){
		$variant_id = $this->input->getInt('variant_id', 0);
		if($variant_id) {
			$data = $this->input->getArray($_POST);
			$item  = $data['jform']['j2store']['attribs'];
			$quantity_item =  $data['jform']['j2store']['attribs']['quantity'];
			$quantity_item['variant_id'] = $variant_id;
			$variant = F0FTable::getAnInstance('Variant','J2StoreTable');
			$variant->load($variant_id);
			$quantity = F0FTable::getAnInstance('Productquantity','J2StoreTable');
			$msgType="Message";
			if($variant->save($item)){
				if(!$quantity->save($quantity_item)){
					$msg = $variant->getError();
					$msgType="Warning";
				}
			}
			$url = "index.php?option=com_j2store&view=products&task=setvariant&variant_id=".$variant_id."&layout=variant_form&tmpl=component";
			$msg = JText::_('J2STORE_PRODUCT_SAVE_SUCCESS');

		}else {
			$msg = JText::_('J2STORE_PRODUCT_SAVE_FAILED');
			$url = "index.php?option=com_j2store&view=products";
		}
		JFactory::getApplication()->redirect($url,$msg ,$msgType);
	}

	function deletevariant(){
		$variant_id = $this->input->getInt('variant_id');
		$variant = F0FTable::getAnInstance('Variant','J2StoreTable');
		$success = true;
		if($variant->load($variant_id)){
			if(!$variant->delete($variant_id)){
				$success= false;
			}
		}
		echo json_encode($success);
		JFactory::getApplication()->close();
	}

	/**
	 * Method to regenerate variants
	 */
	function regenerateVariants() {

		$json = array ();
		$product_id = $this->input->getInt ( 'product_id', 0);
		if ($product_id) {
			$product = F0FTable::getAnInstance ( 'Product', 'J2StoreTable' );
			if($product->load ( $product_id )) {
				$variants = F0FModel::getTmpInstance ( 'Variants', 'J2StoreModel' )->product_id ( $product_id )->is_master ( 0 )->getList ();
				// first delete all variants.
				foreach ( $variants as $variant ) {
					F0FTable::getAnInstance ( 'Variant', 'J2StoreTable' )->delete ( $variant->j2store_variant_id );
				}
				$model = $this->getModel ( 'Products', 'J2StoreModel' );
				$model->generateVariants ();
				$json ['success'] = true;
			}else {
				$json['success'] = false;
			}

		}
		echo json_encode ( $json );
		JFactory::getApplication ()->close ();
	}

	/**
	 * Method to delete all variants
	 */
	function deleteAllVariants() {
		$product_id = $this->input->getInt ( 'product_id', 0 );
		if ($product_id) {
			$product = F0FTable::getAnInstance ( 'Product', 'J2StoreTable' );
			if($product->load ( $product_id )) {
				$variants = F0FModel::getTmpInstance ( 'Variants', 'J2StoreModel' )->product_id ( $product_id )->is_master ( 0 )->getList ();
				$json = array ();
				$json ['success'] = true;
				$variantTable = F0FTable::getAnInstance ( 'Variant', 'J2StoreTable' );
				foreach ( $variants as $variant ) {
					try {
						$variantTable->delete ( $variant->j2store_variant_id );
					} catch ( Exception $e ) {
						echo $e->getMessage ();
					}
				}
			}

		}
		echo json_encode ( $json );
		JFactory::getApplication ()->close ();
	}

	/**
	 * Method to remove product price
	 */
	function removeproductprice() {
		$variant_id = $this->input->getInt ( 'variant_id' );
		$price_id = $this->input->getInt ( 'price_id' );
		$cids = $this->input->get ( 'cid', array (), 'array' );
		$price = F0FTable::getAnInstance ( 'Productprice', 'J2StoreTable' );
		$msg = JText::_ ( 'J2STORE_PRODUCT_PRICE_DELETED_SUCCESSFULLY' );
		$msgType = "Message";
		$url = "index.php?option=com_j2store&view=products&task=setproductprice&variant_id=" . $variant_id . "&layout=productpricing&tmpl=component";
		foreach ( $cids as $cid ) {
			if (! $price->delete ( $cid )) {
				$msg = JText::_ ( 'J2STORE_PRODUCT_PRICE_DELETE_ERROR' );
				$msgType = "Warning";
			}
		}
		JFactory::getApplication ()->redirect ( $url, $msg, $msgType );
	}

	/**
	 * Method to set Default variant to the variant type products
	 */
	function setDefaultVariant() {
		$app = JFactory::getApplication();
		$vid = $this->input->getInt('v_id');
		$product_id = $this->input->getint('product_id');

		$status = $app->input->getString('status');
		$json =array();
		$json['success']=false;
		if($vid && $product_id) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			// Fields to update.
			$fields = array($db->qn('isdefault_variant'). ' = 0');

			// Conditions for which records should be updated.
			$conditions = array(
					$db->qn('product_id') . ' ='.$db->q($product_id),

			);

			$query->update($db->quoteName('#__j2store_variants'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$db->query();
			$row  = F0FTable::getAnInstance('Variant' ,'J2StoreTable' );
			$variant  = F0FTable::getAnInstance('Variant' ,'J2StoreTable' );
			$variant->load($vid);
			if($status == 'unsetDefault') {
				$variant->isdefault_variant=0;
			} else {
				$variant->isdefault_variant=1;
			}
			if($row->save($variant)){
				$json['success']=true;
			}
		}
		echo json_encode($json);
		$app->close();

	}

	public function setproductfiles(){
		//get variant id
		$variant_id = $this->input->getInt('variant_id');
		$product_id =$this->input->getInt('product_id');
		$model = $this->getModel('Products' ,'J2StoreModel');
		$view = $this->getThisView();
		if($product_id){
			$productFiles = F0FModel::getTmpInstance('ProductFiles','J2StoreModel')->product_id($product_id)->getList();
		}
		$view->setModel($model, true);
		$view->addTemplatePath(JPATH_ADMINISTRATOR.'/components/com_j2store/views/product/tmpl/');
		$app = JFactory::getApplication();
		$view->addTemplatePath(JPATH_ADMINISTRATOR.'/templates/'.$app->getTemplate().'/html/com_j2store/product/');
		$view->setLayout('productfiles');
		$view->set('product_id',$product_id);
		$view->set('productfiles',$productFiles);
		$view = $this->display();
	}

	/**
	 * Method to created Product files
	 * based on the Product id
	 */
	public function createproductfile(){
		$data =  $this->input->getArray($_POST);
		$product_id = $this->input->getInt('product_id');
		$product_file = F0FTable::getAnInstance('ProductFile','J2StoreTable');
		$msg =JText::_('J2STORE_PRODUCT_FILE_SAVED_SUCCESSFULLY');
		$msgType = "Message";
		$url ="index.php?option=com_j2store&view=products&task=setproductfiles&product_id=".$product_id."&layout=productfiles&tmpl=component";
		if($product_id){
			$data['product_id'] = $product_id;
			if(!$product_file->save($data)){
				$msg =$product_file->getError();
				$msgType = "Warning";
			}
		}else{
			$msgType = "Warning";
			$msg =JText::_('J2STORE_PRODUCT_FILE_ERROR_IN_SAVING_FILE');
		}
		JFactory::getApplication()->redirect($url,$msg,$msgType);
	}

	/**
	 * Method to save Product files
	 */
	public function saveproductfiles(){
		$data = $this->input->getArray($_POST);
		$app = JFactory::getApplication();
		$product_id = $this->input->getInt('product_id');
		$product_files = $data['product_files'];
		$productfile = F0FTable::getAnInstance('ProductFile','J2StoreTable');
		$url ="index.php?option=com_j2store&view=products&task=setproductfiles&product_id=".$product_id."&layout=productfiles&tmpl=component";
		$msg = JText::_('J2STORE_PRODUCT_FILE_SAVED_SUCCESSFULLY');
		$msgType = 'message';
		foreach($product_files as $file){
			$file['product_id'] = (isset($file['product_id'] ) && $file['product_id']) ? $file['product_id'] : $product_id;
			if(!$productfile->save($file)){
				$msgType = 'warning';
				$msg = $productfile->getError();
			}
		}
		JFactory::getApplication()->redirect($url,$msg ,$msgType);


	}

	function setProducts(){
		//get variant id
		$model = F0FModel::getTmpInstance('Products', 'J2StoreModel');

		$filter_category = $this->input->getInt('filter_category');

		$limit = $this->input->getInt('limit',0);
		$limitstart = $this->input->getInt('limitstart',0);

		$model->setState('filter_category',$filter_category);
		$model->setState('limit',$limit);
		$model->setState('limitstart',$limitstart);
		$model->setState('enabled',1);
		$model->setState('visible', 1);

		$items = $model->getProductList();
		$catarray = array();
		foreach($items as $item){
			$product =  J2Product::getInstance()
			->setId($item->j2store_product_id)
			->getProduct();
			$item->product_name = $product->product_name;
			$item->catid = $product->source->catid;
		}
		$testproduct = array();
		foreach($items as $product){
			if(empty($filter_category)){
				$testproduct[]= $product;
			}elseif(isset($filter_category) && !empty($filter_category) && $filter_category == $product->catid){
				$testproduct[]= $product;
			}
		}
		$categories = JHtmlCategory::options('com_content');
		$layout = $this->input->getString('layout');
		$view = $this->getThisView();
		$view->setModel($model, true);
		$view->set('state',$model->getState());
		$view->set('pagination',$model->getPagiantion());
		$view->set('total',$model->getTotal());
		$view->set('productitems',$testproduct);
		$view->set('categories',$categories);
		$view->setLayout('elements');
		$view->display();
	}

	function update() {
		//first clear cache
		J2Store::utilities()->nocache();

		$model = $this->getThisModel();
		$model->getName();
		$json = $model->updateProduct();

		if($json === false) {
			$json = array();
			$json['errormsg'] = implode('/n', $model->getErrors());
			$json['error'] = 1;
		}
		echo json_encode($json);
		JFactory::getApplication()->close();
	}


	/*
	 *  product files
	* */

	function setfiles()
	{

		$app = JFactory::getApplication();
		$model = $this->getModel('Productfiles');
		//$context = 'com_j2store.productfiles';
	/* 	$filter_order		= $app->getUserStateFromRequest( $context.'filter_order',		'filter_order',		'a.ordering',	'cmd' );
		$filter_order_Dir	= $app->getUserStateFromRequest( $context.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );
 */
		// table ordering
/* 		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_order;
 */
		$total		= $model->getTotal();
		$pagination = $model->getPagination();

		//$id = JRequest::getVar('id', 0, 'get', 'int');
		$id = $app->input->getInt('id');

		//set states
		$model->setState('product.id',$id);

		// get items from the table
		$items = $model->getItems();
		$row = J2StoreItem::getArticle($id);

		$files	 = $model->getFiles();
		$error = $model->getError();

		$view   = $this->getView( 'productfiles', 'html' );
		$view->set( '_controller', 'products' );
		$view->set( '_view', 'products' );
		$view->set( '_action', "index.php?option=com_j2store&view=products&task=setfiles&tmpl=component&id=".$id);
		$view->setModel( $model, true );
		$view->assign( 'state', $model->getState() );
		$view->assign( 'row', $row );
		$view->assign( 'items', $items );
		$view->assign( 'files', $files );

		$view->assign( 'error', $error);
		$view->assign( 'total', $total);
		$view->assign( 'pagination', $pagination);
		$view->assign( 'product_id', $id );
		//$view->assign( 'product_id', $id );

		$view->setLayout( 'default' );
		$view->display();
	}
	function setpaimport() {
		$app = JFactory::getApplication ();
		$model = $this->getThisModel ();
		$view = $this->getThisView ();
		$view->setModel ( $model, true );

		$product_id = $app->input->getInt ( 'product_id', 0 );
		if ($product_id) {
			$product_type = $app->input->getString ( 'product_type', 'simple' );
			$filter_sku = $app->input->getString ( 'filter_sku', '' );
			$filter_id = $app->input->getString ( 'filter_pid', '' );
			$search_term = '';
			$product_list = array ();
			
			$db = JFactory::getDbo ();
			$query = $db->getQuery ( true )->select ( 'pa.product_id' )->from ( '#__j2store_product_options AS pa' )
			->where ( 'pa.product_id !=' . $db->q ( $product_id ) )
			->group ( 'pa.product_id' )
			->join ( 'LEFT', '#__j2store_products AS p ON pa.product_id=p.j2store_product_id' )
			->join ( 'LEFT', '#__j2store_variants AS v ON v.product_id=p.j2store_product_id' )
			->where ( 'p.product_type = ' . $db->q ( $product_type ) );
	
			$search_term = ( !empty($filter_sku) ) ? 'v.sku LIKE ' . $db->q ( '%'. $filter_sku .'%') : '';	

			if ( !empty($filter_id) ) {
				$search_term .= ( !empty($search_term) ) ? ' OR ' : '';		
				$search_term .= 'p.j2store_product_id=' .$filter_id ;	
			}
			if (!empty($search_term)) {
				$query->where( $search_term ) ;
			}

			$db->setQuery ( $query );
			try {
				$product_list = $db->loadObjectList ();
			} catch ( Exception $e ) {
				$error = $e->getMessage ();
			}
			
			$products = array ();
			foreach ( $product_list as $item ) {
				// run the getItem again
				unset ( $product );
				$product = F0FModel::getTmpInstance ( 'Products', 'J2StoreModel' )->runMyBehaviorFlag ( true )->getItem ( $item->product_id );
				$products [] = $product;
			}
			
			if ( empty($filter_id) && empty($filter_sku) ) {
				$products = array ();
			}
			$view->assign ( 'model', $model );
			$view->assign ( 'state', $model->getState () );
			$view->assign ( 'products', $products );
			$row = F0FModel::getTmpInstance ( 'Products', 'J2StoreModel' )->getItem ( $product_id );
			$view->assign ( 'row', $row );
			$view->assign ( 'productHelper', J2Store::product () );
			$view->assign ( 'currency', J2Store::currency () );
			
			$view->assign ( 'product_id', $product_id );
		}
		$view->setLayout ( 'paimport' );
		$view->display ();
	}

	function importattributes(){

		$app = JFactory::getApplication();
		$error = false;
		$this->messagetype  = '';
		$this->message      = '';
		$cids = $app->input->get('cid', array(), 'array');
		$product_id = $app->input->getInt('product_id', 0);
		$model = $this->getThisModel();
		$product_helper = J2Store::product();
		$row = $product_helper->setId($product_id)->getProduct();
	//	$row = $model->getItem($product_id);
//		$row= $model->getProduct($row);
		
		if(empty($cids) || count($cids) < 1) {
			$error = true;
			$this->message .= JText::_('J2STORE_PAI_SELECT_PRODUCT_TO_IMPORT');
			$this->messagetype = 'notice';
		} else {
			//get the model
			$poption_model = $this->getModel('ProductOptions','J2StoreModel');
			foreach($cids as $cid) {
				if(!$poption_model->importAttributeFromProduct($row,$cid, $product_id)){
					$this->message .= $model->getError();
					$this->messagetype = 'error';
				}
			}

		}
		if ($error)
		{
			$this->message = JText::_('J2STORE_ERROR') . " - " . $this->message;
		}
		else
		{
			$this->message = JText::_('J2STORE_PAI_SELECT_ATTRIBUTES_IMPORTED');
			$this->messageType = 'message';
		}

		$redirect = "index.php?option=com_j2store&view=products&task=setpaimport&product_type={$row->product_type}&product_id={$product_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, $this->message, $this->messagetype );

	}

	function searchproductfilters() {
		$app = JFactory::getApplication();
		$q = $this->input->post->getString('q');
		$json = array();
		$result = F0FTable::getAnInstance('ProductFilter', 'J2StoreTable')->searchFilters($q);
		echo json_encode($result);
		$app->close();
	}

	function deleteproductfilter() {
		$app = JFactory::getApplication();
		$filter_id = $this->input->post->get('filter_id');
		$product_id = $this->input->post->get('product_id');
		$json = array();
		$result = F0FTable::getAnInstance('ProductFilter', 'J2StoreTable')->deleteFilter($filter_id, $product_id);
		if($result) {
			$msg = JText::_('J2STORE_PRODUCT_FILTER_DELETE_SUCCESSFUL');
		}else {
			$msg = JText::_('J2STORE_PRODUCT_FILTER_DELETE_ERROR');
		}
		echo json_encode ( array (
				'success' => $result,
				'msg' => $msg
		) );
		$app->close();
	}


	public function getSFFilterStates() {
		$app = JFactory::getApplication();
		$state = array();
		$state['search'] = $app->input->getString('search','');
		$state['product_type']= $app->input->getString('product_type','');
		$state['visible'] = $app->input->getString('visible',null);
		$state['vendor_id'] = $app->input->getInt('vendor_id','');
		$state['manufacturer_id'] = $app->input->getString('manufacturer_id','');
		$state['productid_from']= $app->input->getString('productid_from','');
		$state['productid_to']= $app->input->getString('productid_to','');
		$state['pricefrom']= $app->input->getString('pricefrom','');
		$state['priceto']= $app->input->getString('priceto','');
		$state['since']= $app->input->getString('since','');
		$state['until']= $app->input->getString('until','');
		$state['taxprofile_id']= $app->input->getString('taxprofile_id','');
		$state['shippingmethod']=$app->input->getString('shippingmethod','');
		$state['filter_order']= $app->input->getString('filter_order','j2store_product_id');
		$state['filter_order_Dir']= $app->input->getString('filter_order_Dir','ASC');
		$state['sku']= $app->input->getString('sku','');
		$state['sortby']= $app->input->getString('sortby','');
		$state['productfilter_id']= $app->input->getString('productfilter_id','');
		return $state;
	}


	/**
	 * Method to list of variants
	 * based on the ajax request
	 */
	public function getVariantListAjax(){
		$app = JFactory::getApplication();
		$params = J2Store::config();
		$json = array();
		$product_id = $app->input->get('product_id');
		$global_config = JFactory::getConfig();
		$limit = $global_config->get('list_limit',20);
		if(!empty($product_id)){
			$limitstart = $app->input->get('limitstart');
			$model = $this->getThisModel();
			$model->setId($product_id);
			$item = $model->runMyBehaviorFlag(true)->getItem();
			$variantModel = F0FModel::getTmpInstance('Variants', 'J2StoreModel');
			$variantModel->setState('product_type', $item->product_type);
			$variant_list = $variantModel->product_id($product_id)->limit($limit)->limitstart($limitstart)->is_master(0)->getList();
			$variant_pagination = $variantModel->getPagination();
			$lengths =$variantModel->getDimesions('lengths', 'j2store_length_id','length_title');
			$weights = $variantModel->getDimesions('weights', 'j2store_weight_id','weight_title');
			$controller = F0FController::getTmpInstance ( 'com_j2store', 'Products' );
			$view = $controller->getView ( 'Product', 'Html', 'J2StoreView' );
			if ($model = $controller->getModel ( 'Products', 'J2StoreModel' )) {
				// Push the model into the view (as default)
				$view->setModel ( $model, true );
			}
			$view->assign('product',  $item );
			$view->assign('weights', $weights);
			$view->assign('lengths', $lengths);
			$view->assign('variant_list' , $variant_list);
			$view->assign( 'params', $params );
			$view->assign('form_prefix','jform[attribs][j2store]');
			$view->assign('variant_pagination',$variant_pagination);
			$view->setLayout('form_ajax_avoptions');
			ob_start ();
			$view->display ( );
			$html = ob_get_contents ();
			ob_end_clean ();
			$json['html'] = $html;
		}
		echo json_encode($json);
		$app->close();
	}

	/**
	 * display admin product popup
	 *   */
	function displayAdminProduct(){
		$app = JFactory::getApplication();
		$data = $app->input->getArray($_POST);
	
		$product = F0FTable::getInstance('Product' ,'J2StoreTable')->getClone();;
		if(isset($data['product_id']) && $data['product_id'] && $product->get_product_by_id($data['product_id'])){
			$model = F0FModel::getTmpInstance('Products', 'J2StoreModel');
			$model->setId($data['product_id']);
			$product_data = $model->getItem();						
			$html = '';
			if(isset($product_data->product_name)){
				$html = "<h3>".$product_data->product_name."</h3>";
			}			 
			$html .= $product->get_product_html();
			$html .= '<input type="hidden" name="user_id" id="user_id" value="'.$data['user_id'].'"/>';
			$html .= '<input type="hidden" name="oid" id="oid" value="'.$data['oid'].'"/>';
			$html .= '<input type="hidden" name="product_id" id="product_id" value="'.$data['product_id'].'"/>';
			echo $html;
			$document = JFactory::getDocument();
			$headData = $document->getHeadData();
			$scripts = $headData['scripts'];
			unset($scripts['/j2store/j2store_v3/develop/j3_point2/media/j2store/js/j2store.js']);
			$headData['scripts'] = $scripts;
			$document->setHeadData($headData);
			$document->addScript(JUri::root(true).'/media/j2store/js/j2store_admin.js');
		}
	}
}