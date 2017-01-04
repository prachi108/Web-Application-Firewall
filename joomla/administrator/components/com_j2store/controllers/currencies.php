<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreControllerCurrencies extends F0FController
{
	protected function onBeforeBrowse() {
		$model = F0FModel::getTmpInstance('Currencies', 'J2StoreModel');
		$model->updateCurrencies(false);
		
		return parent::onBeforeBrowse();
	}
}