<?php
/**
 * @package J2Store
* @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
* @license GNU GPL v3 or later
*/
defined('_JEXEC') or die;
class J2StoreControllerInventories extends F0FController
{
	public function getFilterStates() {
		$app = JFactory::getApplication();
		$state = array();
		$state['filter_order']= $app->input->getString('filter_order','j2store_productquantity_id');
		$state['filter_order_Dir']= $app->input->getString('filter_order_Dir','ASC');
		return $state;
	}

	public function browse()
	{
		$app = JFactory::getApplication();
		$model = $this->getThisModel();
		$state = $this->getFilterStates();
		foreach($state as $key => $value){
			$model->setState($key,$value);
		}
		//$product_types  = $model->getProductTypes();
		//array_unshift($product_types, JText::_('J2STORE_SELECT_OPTION'));
		$products = $model->getStockProductList();
		$product_helper =J2Store::product();
		foreach ($products as $product){
			$product->product = $product_helper->setId($product->j2store_product_id)->getProduct();
		}
		$view = $this->getThisView();
		$view->setModel($model);
		$view->assign('products',$products);
		$view->assign('state', $model->getState());
		//$view->assign('product_types',$product_types);
		return parent::browse();
	}

	public function update_inventory(){
		$app = JFactory::getApplication();
		$quantity = $app->input->getInt('quantity');
		$availability = $app->input->getInt('availability');
		$manage_stock = $app->input->getInt('manage_stock');
		$variant_id = $app->input->getInt('variant_id');
		$json = array();
		if($variant_id > 0){
			F0FTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
			$productquantities = F0FTable::getInstance('Productquantity', 'J2StoreTable')->getClone ();
			$productquantities->load(array('variant_id'=>$variant_id));
			$productquantities->variant_id = $variant_id;
			$productquantities->quantity = $quantity;
			if(!$productquantities->store()){
				$json['error'] = "Problem in Save";
			}
			$variants_table = F0FTable::getInstance('Variant', 'J2StoreTable')->getClone ();
			$variants_table->load($variant_id);
			$variants_table->availability = $availability;
			$variants_table->manage_stock = $manage_stock;
			if(!$variants_table->store()){
				$json['error'] = "Problem in Save";
			}
			if(!$json){
				J2Store::plugin()->event('AfterUpdateInventry',array($variants_table));
			}

		}
		if(!$json){

			$json['success'] = 'index.php?option=com_j2store&view=inventories';
		}
		echo json_encode($json);
		$app->close();
	}
}