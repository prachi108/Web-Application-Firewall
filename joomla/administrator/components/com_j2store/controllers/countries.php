<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreControllerCountries extends F0FController
{

	/**
	 * Method
	 * @return boolean
	 */
	function elements(){
		$geozone_id = $this->input->getInt('geozone_id');
		$model = F0FModel::getTmpInstance('Countries','J2StoreModel');
		$filter =array();
		$filter['limit'] = $this->input->getInt('limit',20);
		$filter['limitstart'] = $this->input->getInt('limitstart');
		$filter['search'] = $this->input->getString('search','');
		$filter['country_name'] = $this->input->getString('country_name','');
		foreach($filter as $key => $value){
			$model->setState($key,$value);
		}

		if(isset($geozone_id) && $geozone_id){
			$view = $this->getThisView();
			$state = $model->getState();
			$view->setModel($this->getThisModel(),true);
			$view->assign('countries',$model->enabled(1)->country_name($filter['country_name'])->getItemList());
			$view->assign('pagination',$model->getPagination());
			$view->assign('geozone_id',$geozone_id);
			$view->assign('state',$model->getState());
			$view->setLayout('modal');
			$view->display();
		}else{
			return false;
		}
	}
}
