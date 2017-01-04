<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/appcontroller.php');
class J2StoreControllerAppLocalization_data extends J2StoreAppController
{
	var $_element   = 'app_localization_data';

	/**
	 * constructor
	 */
	function __construct()
	{
		parent::__construct();
		$this->includeCustomModels();
		$this->includeCustomTables();		
	}

	public function insertTableValues(){

		$model = $this->getModel('AppLocalizationdata' ,'J2StoreModel');
		$tablename = $this->input->getString('table');
		$msgType ='message';
		$msg = JText::_('J2STORE_TABLE_VALUE_INSERTED_SUCCESSFULLY');
		try{
			$model->getInstallerTool($tablename);
		}catch(Exception $e){
			$msgType ='warning';
			$msg = JText::_('J2STORE_TABLE_VALUE_INSERTION_ERROR');
		}
		$this->setRedirect($this->baseLink(), $msg, $msgType);
	}
}
