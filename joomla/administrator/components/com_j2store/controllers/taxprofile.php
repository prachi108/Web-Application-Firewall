<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class J2StoreControllerTaxprofile extends F0FController
{
	function deleteTaxRule() {
		$app = JFactory::getApplication();
		$taxrule_id = $app->input->getInt('taxrule_id');
		$taxrule =F0FTable::getInstance('taxrules','Table');
		$response = array();
		try {
			$taxrule->delete($taxrule_id);
			$response['success'] =JText::_('J2STORE_TAXRULE_DELETED_SUCCESSFULLY');
		}catch (Exception $e) {
			$response['error'] =JText::_('J2STORE_TAXRULE_DELETE_FAILED');
		}
		echo json_encode($response );
		$app->close();

	}
}
?>
