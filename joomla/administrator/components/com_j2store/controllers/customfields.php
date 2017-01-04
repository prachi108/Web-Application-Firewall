<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
class J2StoreControllerCustomfields extends F0FController
{


	public function __construct($config = array())
    {
		parent::__construct($config);

		$this->cacheableTasks = array();
      //  $this->registerTask('showspared', 'browse');
	}

	/**
	 * Makes a customfield required
	 */
	public function public_publish()
	{
		// CSRF prevention
		if($this->csrfProtection) {
			if($this->_csrfProtection() === false) return false;
		}
		$this->setpublic(1);
	}

	/**
	 * Makes a customfield not required
	 */
	public function public_unpublish()
	{
		// CSRF prevention
		if($this->csrfProtection) {
			if($this->_csrfProtection() === false) return false;
		}
		$this->setpublic(0);
	}

	/**
	 * Sets the visibility status of a customfields
	 *
	 * @param int $state 0 = not require, 1 = require
	 */
	protected final function setpublic($state = 0)
	{
		$model = $this->getThisModel();

		if(!$model->getId()) $model->setIDsFromRequest();

		$status = $model->visible($state);

		// redirect
		if($customURL = $this->input->getString('returnurl','')) $customURL = base64_decode($customURL);
		$url = !empty($customURL) ? $customURL : 'index.php?option='.$this->component.'&view='.F0FInflector::pluralize($this->view);
		if(!$status)
		{
			$this->setRedirect($url, $model->getError(), 'error');
		}
		else
		{
			$this->setRedirect($url);
		}
		$this->redirect();
	}

}
