<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreModelFiltergroups extends F0FModel {

	protected function onProcessList(&$resultArray)
	{
		foreach($resultArray as &$res){
			$res->filtervalues = F0FModel::getTmpInstance('Filters' ,'J2StoreModel')->group_id($res->j2store_filtergroup_id)->getList();
		}
	}
	protected function onAfterGetItem(&$record)
	{
		if(isset($record->j2store_filtergroup_id))
		$record->filtervalues = F0FModel::getTmpInstance('Filters' ,'J2StoreModel')->group_id($record->j2store_filtergroup_id)->getList();
	}

	public function save($data) {
		$app = JFactory::getApplication ();
		$task = $app->input->getString ( 'task' );
		if ($task == 'saveorder')
			return parent::save ( $data );
		
		if (parent::save ( $data )) {
			if (isset ( $this->otable->j2store_filtergroup_id )) {
				if (isset ( $data ['filter_value'] ) && count ( $data ['filter_value'] )) {

					$status = true;
					foreach ( $data ['filter_value'] as $filtervalue ) {
						$ovTable = F0FTable::getInstance ( 'filter', 'J2StoreTable' )->getClone();
						$ovTable->load ( $filtervalue ['j2store_filter_id'] );
						$filtervalue ['group_id'] = $this->otable->j2store_filtergroup_id;
						if (! $ovTable->save ( $filtervalue )) {
							$status = false;
						}
					}
				} else {
					return true;
				}
			}
		} else {
			return false;
		}
		return true;
	}

}