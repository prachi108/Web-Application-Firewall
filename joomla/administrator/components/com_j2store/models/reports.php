<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreModelReports extends F0FModel {

	/**
	 * Method to buildQuery to return list of data
	 * @see F0FModel::buildQuery()
	 * @return query
	 */
	public function buildQuery($overrideLimits = false) {

		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$this->getSelectQuery($query);
		$this->getWhereQuery($query);
		return $query;
	}

	/**
	 * Method to getSelect query
	 * @param unknown_type $query
	 */
	protected function getSelectQuery(&$query)
	{
		$query->select("report.extension_id,report.name,report.type,report.folder,report.element,report.params,report.enabled,report.ordering")
		->from("#__extensions as report");
	}

	protected function getWhereQuery(&$query)
	{
		$query->where("report.type='plugin'");
		$query->where("report.element LIKE 'report_%'");
		$query->where("report.folder='j2store'");
	}

	protected function onProcessList(&$resultArray){
		foreach($resultArray as &$res){
			$res->create_text = JText::_('J2STORE_CREATE');
			$res->view_text = JText::_('J2STORE_VIEW');
			$xmlfile = JPATH_SITE.'/plugins/j2store/'.$res->element.'/'.$res->element.'.xml';
			$version = '';
			if(JFile::exists($xmlfile)) {
				$xml = JFactory::getXML($xmlfile);
				$res->version =(string)$xml->version;
			}
		}
	}

}
