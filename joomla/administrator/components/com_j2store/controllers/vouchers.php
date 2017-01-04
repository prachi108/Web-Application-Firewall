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
jimport('joomla.application.component.controllerform');


class J2StoreControllerVouchers extends F0FController
{

	/**
	 * Method to send Voucher Email
	 */
	public function send(){
		$app = JFactory::getApplication();
		$cids = $app->input->get('cid',array(),'');		
		if(count($cids)) {
			$model = $this->getModel('Vouchers');
			if($model->sendVouchers($cids) === false) {
				$msg = JText::_('J2STORE_VOUCHERS_SENDING_FAILED');
				$msgType = 'warning';
			}else {
				$msg = JText::_('J2STORE_VOUCHERS_SENDING_SUCCESSFUL');
				$msgType = 'message';
			}
		}	
		$this->setRedirect('index.php?option=com_j2store&view=vouchers' ,$msg, $msgType);
	}
	
	public function history() {
		
		$app = JFactory::getApplication();
		$cid = $app->input->get('cid', array(), 'array');
		//take the first one
		$id = isset($cid[0]) ? $cid[0] : 0;
		if($id > 0) {
			
			$view = $this->getThisView();
			
			if ($model = $this->getThisModel())
			{
				// Push the model into the view (as default)
				$view->setModel($model, true);
			}
			$voucher = F0FTable::getAnInstance('Voucher', 'J2StoreTable');
			$voucher->load($id);
			$view->assign('voucher', $voucher);
			$voucher_history = $model->getVoucherHistory($id);
			$view->assign('vouchers', $voucher_history);
			$view->assign('params', J2Store::config());
		}
		$view->setLayout('history');
		$view->display();
		
	}
}