<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreModelManufacturers extends F0FModel {

	protected function onProcessList(&$resultArray)
	{
		foreach($resultArray as &$res){

			$res->name = $res->first_name .' ' .$res->last_name;
		}
	}
	public function buildQuery($overrideLimits = false)
	{
		$db = JFactory::getDbo();
		$query  = $db->getQuery(true)
		->select('#__j2store_manufacturers.*')->from("#__j2store_manufacturers as #__j2store_manufacturers")
		->select($db->qn('#__j2store_addresses').'.j2store_address_id')
		->select($db->qn('#__j2store_addresses').'.first_name')
		->select($db->qn('#__j2store_addresses').'.last_name')
		->select($db->qn('#__j2store_addresses').'.address_1')
		->select($db->qn('#__j2store_addresses').'.address_2')
		->select($db->qn('#__j2store_addresses').'.email')
		->select($db->qn('#__j2store_addresses').'.city')
		->select($db->qn('#__j2store_addresses').'.zip')
		->select($db->qn('#__j2store_addresses').'.zone_id')
		->select($db->qn('#__j2store_addresses').'.country_id')
		->select($db->qn('#__j2store_addresses').'.phone_1')
		->select($db->qn('#__j2store_addresses').'.phone_2')
		->select($db->qn('#__j2store_addresses').'.fax')
		->select($db->qn('#__j2store_addresses').'.type')
		->select($db->qn('#__j2store_addresses').'.company')
		->select($db->qn('#__j2store_addresses').'.tax_number')
		->leftJoin('#__j2store_addresses ON #__j2store_addresses.j2store_address_id = #__j2store_manufacturers.address_id');
		$this->buildOrderbyQuery($query);
		return $query;
	}

	public function buildOrderbyQuery(&$query){
		$state = $this->getState();
		$app = JFactory::getApplication();
		$filter_order_Dir = $app->input->getString('filter_order_Dir','asc');
		$filter_order = $app->input->getString('filter_order','company');
		if($filter_order =='j2store_manufacturer_id' || $filter_order =='enabled' || $filter_order =='ordering'){
			$query->order('#__j2store_manufacturers.'.$filter_order.' '.$filter_order_Dir);
		}elseif(in_array($filter_order ,array('company' ,'city'))){
			$query->order('#__j2store_addresses.'.$filter_order.' '.$filter_order_Dir);
		}
	}

	public function onBeforeSave(&$data, &$table){
		$app = JFactory::getApplication();
		$addressTable = F0FTable::getInstance('Address','J2storeTable');
		$addressTable->load($data['address_id']);
		$addressTable->save($data);
		$data['address_id'] = $addressTable->j2store_address_id;
		return true;
	}

	public function getManufacturersList($brand_ids){

		$db = JFactory::getDbo();
		$query = $this->buildQuery($overrideLimits = false);
		$query->where('#__j2store_manufacturers.j2store_manufacturer_id IN ('. $brand_ids. ')');
		$db->setQuery($query);

		$results =  $db->loadObjectList();

		return $results;
	}

}
