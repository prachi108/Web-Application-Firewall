<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreControllerCoupons extends F0FController
{

	/**
	 * Method to set view to add products
	 *
	 */
	function setProducts(){
		//get variant id
		$model = F0FModel::getTmpInstance('Products', 'J2StoreModel');
		$limit = $this->input->getInt('limit',20);
		$limitstart = $this->input->getInt('limitstart',0);

		//sku search
		$search = $this->input->getString('search','');
		$model->setState('search',$search);
		$model->setState('limit',$limit);
		$model->setState('limitstart',$limitstart);
		$model->setState('enabled',1);
		$items = $model->getProductList();
		$layout = $this->input->getString('layout');
		$view = $this->getThisView('Coupons');
		$view->setModel($model, true);
		$view->set('state',$model->getState());
		$view->set('pagination',$model->getPagination());
		$view->set('total',$model->getTotal());
		$view->set('productitems',$items);
		$view->setLayout($layout);
		$view->display();
	}



	public function history() {
		$app = JFactory::getApplication();
		$coupon_id = $app->input->getInt('coupon_id');
		$view = $this->getThisView();
		if($coupon_id > 0) {
			if ($model = $this->getThisModel())
			{
				// Push the model into the view (as default)
				$view->setModel($model, true);
			}
			$coupon = F0FTable::getAnInstance('Coupon', 'J2StoreTable');
			$coupon->load($coupon_id);
			$view->assign('coupon', $coupon);
			$coupon_history = $model->getCouponHistory();
			$view->assign('coupon_history', $coupon_history);
			$view->assign('params', J2Store::config());
			$view->assign('currency', J2Store::currency());

		}
		$view->setLayout('history');
		$view->display();
	}
}