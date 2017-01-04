<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
//require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/reportcontroller.php');
class J2StoreControllerReportItemised extends F0FController
{
	var $_element   = 'report_itemised';

	/**
	 * constructor
	 */
	function __construct()
	{
		parent::__construct();
		F0FModel::addIncludePath(JPATH_SITE.'/plugins/j2store/report_itemised/report_itemised/models');
		F0FTable::addIncludePath(JPATH_SITE.'/plugins/j2store/report_itemised/report_itemised/tables');
	}

	function exportItems(){
		$app = JFactory::getApplication();
		$id = $app->input->getInt('id');

		F0FModel::addIncludePath(JPATH_SITE.'/plugins/j2store/report_itemised/report_itemised/models');
		$model = F0FModel::getTmpInstance('ReportItemised', 'J2StoreModel');
		$data = $model->getData();
		$filename = $model->export($data);
		die;
		$app->close();
	}



}
