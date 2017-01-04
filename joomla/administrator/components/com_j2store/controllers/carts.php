<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreControllerCarts extends F0FController
{
	/**
	 * add product to order item
	 *   */
	function addOrderitems(){
		$app = JFactory::getApplication();
		$data = $this->input->getArray($_POST);
		
		$json = array();
		//if($app->input->getInt('user_id',0)){
		$model = $this->getModel('Cartadmins', 'J2StoreModel')->getClone();
		$result = $model->addAdminCartItem();
		
		if(isset($result['success']) && $result['success']){
			$result['message'] = JText::_("J2STORE_ITEM_ADDED_SUCCESS");
		}
		//print_r($result);exit;
		echo json_encode($result);
		$app->close();
		
	}
	
	/**
	 * apply coupon
	 *   */
	function applyCoupon() {
		$json = array();
		//first clear cache
		J2Store::utilities()->nocache();
		J2Store::utilities()->clear_cache();
		$app = JFactory::getApplication();
		$layout =  $app->input->getString('layout','summary');
		$id = $app->input->getInt('oid', '');
		$session = JFactory::getSession();
		$model = F0FModel::getTmpInstance('Cartadmins', 'J2StoreModel');
		//coupon
		$post_coupon = $this->input->getString('coupon', '');
		//first time applying? then set coupon to session
		if (isset($post_coupon) && !empty($post_coupon)) {
			$session->set('coupon', $post_coupon, 'j2store');			
		}
		$order_id = $app->input->getInt('oid', '');		
		$url = 'index.php?option=com_j2store&view=orders&task=saveAdminOrder&layout=payment_shipping_methods&next_layout=summary&oid='.$id;
		$json['success']=1;
		$json['redirect']= $url;
		echo json_encode($json);
		$app->close();
	}
	
	/**
	 * remove coupon
	 *   */
	function removeCoupon() {
		$json = array();
		//first clear cache
		J2Store::utilities()->nocache();
		J2Store::utilities()->clear_cache();
		$app = JFactory::getApplication();		
		$model = $this->getModel('Cartadmins' ,'J2StoreModel');
		//coupon
		$id = $app->input->getInt('oid', '');
		$order_id = $app->input->getInt('order_id', '');
		$session = JFactory::getSession();
		if($session->has('coupon', 'j2store')) {
			$session->clear('coupon', 'j2store');						
		}
		
		$discount_table = F0FTable::getInstance('Orderdiscount', 'J2StoreTable')->getClone();
		$discount_table->load(array(
				'order_id' => $order_id,
				'discount_type' => "coupon"
		));
		if($discount_table->j2store_orderdiscount_id){
			$discount_table->delete();			
		}			
		$json['success']=1;
		$url = 'index.php?option=com_j2store&view=orders&task=saveAdminOrder&layout=payment_shipping_methods&next_layout=summary&oid='.$id;
		$json['redirect']= $url;
		echo json_encode($json);
		$app->close();
	}
	
	/**
	 * apply voucher
	 *   */
	function applyVoucher() {
	
		//first clear cache
		J2Store::utilities()->nocache();
		J2Store::utilities()->clear_cache();
		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		$model = F0FModel::getTmpInstance('Cartadmins', 'J2StoreModel');
		//coupon
		$voucher = $app->input->getString('voucher', '');
		//first time applying? then set coupon to session
		if (isset($voucher) && !empty($voucher)) {
			$session->set('voucher', $voucher, 'j2store');
		}
	
		$order_id = $app->input->getInt('oid', '');		
		$url = 'index.php?option=com_j2store&view=orders&task=saveAdminOrder&layout=payment_shipping_methods&next_layout=summary&oid='.$order_id;
		$json = array();
		$json['success']=1;
		$json['redirect']= $url;
		echo json_encode($json);
		$app->close();
	}
	
	/**
	 * remove voucher
	 *   */
	function removeVoucher() {
	
		//first clear cache
		J2Store::utilities()->nocache();
		J2Store::utilities()->clear_cache();
		$app = JFactory::getApplication();
		$model = $this->getModel('Cartadmins' ,'J2StoreModel');
		//coupon
		$session = JFactory::getSession();
		if($session->has('voucher', 'j2store')) {
			$session->clear('voucher', 'j2store');						
		}
		$id = $app->input->getInt('oid', '');
		$order_id = $app->input->getInt('order_id', '');		
		$discount_table = F0FTable::getInstance('Orderdiscount', 'J2StoreTable')->getClone();
		$discount_table->load(array(
				'order_id' => $order_id,
				'discount_type' => "voucher"
		));
		if($discount_table->j2store_orderdiscount_id){
			$discount_table->delete();
		}		
		$url = 'index.php?option=com_j2store&view=orders&task=saveAdminOrder&layout=payment_shipping_methods&next_layout=summary&oid='.$id;
		$json = array();
		$json['redirect']= $url;
		$json['success']=1;
		echo json_encode($json);
		$app->close();
		
	}
	
	function update() {
	
		//first clear cache
		J2Store::utilities()->clear_cache();
		J2Store::utilities()->nocache();
		$app = JFactory::getApplication();
		$model = $this->getModel('Cartadmins','J2StoreModel');
		$result = $model->update();		
		$json = array();
		if(!empty($result['error'])) {
			$json['error'] = $result['error'];
		} else {
			$json['success'] = JText::_('J2STORE_CART_UPDATED_SUCCESSFULLY');
		}
		$id = $app->input->getInt('oid', '');
		$url = 'index.php?option=com_j2store&view=orders&task=saveAdminOrder&layout=items&next_layout=items&oid='.$id;
		echo json_encode($json);
		$app->close();
		//$this->setRedirect($url, $msg, 'notice');
	}
}