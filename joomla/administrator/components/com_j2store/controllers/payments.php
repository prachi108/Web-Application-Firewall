<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
class J2storeControllerPayments extends F0FController
{
	public function __construct($config) {
		parent::__construct($config);
		$this->registerTask('apply', 'save');
		$this->registerTask('saveNew', 'save');
	}


	public function save()
	{
		$task = $this->getTask();

		// get the Application Object
		$app=JFactory::getApplication();

 		// get the payment id
		$payment_id=$app->input->getInt('extension_id');

		// if payment id exists
			if($payment_id)
			{
				$data=$app->input->getArray($_POST);

				$paymentdata = array();

				$paymentdata['extension_id']=$payment_id;
				$registry = new JRegistry();

				$registry->loadArray($data);

				$paymentdata['params'] = $registry->toString('JSON');
			try {
					F0FTable::getAnInstance('Payment' ,'J2StoreTable')->save($paymentdata);
				}catch (Exception $e) {
					$msg = $e->getMessage();
				}

					switch($task)
					{
						case 'apply':
							parent::apply();
							break;
						case 'save':
							parent::save();
							break;
						case  'savenew':
							parent::savenew();
							break;
					}


			}


	}
	}