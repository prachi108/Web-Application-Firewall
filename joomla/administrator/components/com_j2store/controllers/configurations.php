<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
class J2StoreControllerConfigurations extends F0FController {

	public function __construct($config) {

		parent::__construct($config);
		$this->registerTask('apply', 'save');
		$this->registerTask('saveNew', 'save');
		$this->registerTask('populatedata','save');
	}

	public function execute($task) {
		if(in_array($task, array('browse', 'read', 'edit', 'add'))) {
			$task = 'add';
		}
		return parent::execute($task);
	}

	/**
	 * Method to cancel(non-PHPdoc)
	 * @see F0FController::cancel()
	 */
	public function cancel(){
		$app = JFactory::getApplication();
		$url  ='index.php?option=com_j2store&view=cpanels';
		$app->redirect($url,'',$msgType='');
	}

	/**
	 * Method to save data
	 * (non-PHPdoc)
	 * @see F0FController::save()
	 */
	public function save(){

		//security check
		JSession::checkToken() or die( 'Invalid Token' );

		$app = JFactory::getApplication();
		$model = $this->getModel('configurations');
		$data = $app->input->getArray($_POST);
		$task = $this->getTask();

		$token = JSession::getFormToken();

		unset($data['option']);
		unset($data['task']);
		unset($data['view']);
		unset($data[$token]);

		if($task == 'populatedata') {
			$this->getPopulatedData($data);
		}

		$db = JFactory::getDbo();
		$config = J2Store::config();
		$query = 'REPLACE INTO #__j2store_configurations (config_meta_key,config_meta_value) VALUES ';

		jimport('joomla.filter.filterinput');
		$filter = JFilterInput::getInstance(null, null, 1, 1);
		$conditions = array();
		foreach ($data as $metakey=>$value) {
			if(is_array($value)) {
				$value = implode(',', $value);
			}
			//now clean up the value
			if($metakey == 'store_billing_layout' || $metakey == 'store_shipping_layout' || $metakey == 'store_payment_layout') {
				$value = $app->input->get($metakey, '', 'raw');
				$clean_value = $filter->clean($value, 'html');

			} else {
				$clean_value = $filter->clean($value, 'string');
			}
			$config->set($metakey, $clean_value);
			$conditions[] = '('.$db->q(strip_tags($metakey)).','.$db->q($clean_value).')';
		}

		$query .= implode(',',$conditions);

		try {
			$db->setQuery($query);
			$db->execute();
			//update currencies
			F0FModel::getTmpInstance('Currencies', 'J2StoreModel')->updateCurrencies(false);
			$msg = JText::_('J2STORE_CHANGES_SAVED');
		}catch (Exception $e) {
			$msg = $e->getMessage();
			$msgType='Warning';
		}

		switch($task){
			case 'apply':
				$url  ='index.php?option=com_j2store&view=configuration';
				break;
			case 'populatedata':
				$url  ='index.php?option=com_j2store&view=configuration';
				break;
			case 'save':
				$url  ='index.php?option=com_j2store&view=cpanels';
				break;
		}
		$this->setRedirect($url,$msg,$msgType);
	}

	function getPopulatedData(&$data){

			$data['store_billing_layout']='<div class="row-fluid">
		<div class="span6">[first_name] [last_name] [email] [phone_1] [phone_2] [company] [tax_number]</div>
		<div class="span6">[address_1] [address_2] [city] [zip] [country_id] [zone_id]</div>
		</div>';
			$data['store_shipping_layout'] ='<div class="row-fluid">
		<div class="span6">[first_name] [last_name] [phone_1] [phone_2] [company]</div>
		<div class="span6">[address_1] [address_2] [city] [zip] [country_id] [zone_id]</div>
		</div>';

			$app = JFactory::getApplication();
			$app->input->set('store_billing_layout', $data['store_billing_layout']);
			$app->input->set('store_shipping_layout', $data['store_shipping_layout']);

	}

	function testemail(){
		$app = JFactory::getApplication();
		//get the config class obj
		$config = JFactory::getConfig();
		$json = array();
		$email = $app->input->getString('admin_email','');

		if(isset($email) && empty($email)){
			$json['error'] = JText::_('J2STORE_TEST_ADMIN_EMAIL_FIELD_EMPTY');
		}else{
			$admin_emails = explode(',',$email);
			//get the mailer class object
			$mailer = JFactory::getMailer();
			foreach($admin_emails as $admin_email){
				$mailer->addRecipient($admin_email);
			}
			$sitename = $config->get('sitename');
			$subject = JText::sprintf("J2STORE_TEST_ADMIN_EMAIL_SUBJECT",$sitename);
			$body = JText::sprintf("J2STORE_TEST_ADMIN_EMAIL_BODY",$sitename);
			$mailer->setSubject($subject );
			$mailer->setBody($body);
			$mailer->IsHTML(1);
			$mailfrom = $config->get('mailfrom');
			$fromname = $config->get('fromname');
			$mailer->setSender(array( $mailfrom, $fromname ));

			if($mailer->send()){
				$json['success'] = JText::_('J2STORE_TEST_ADMIN_EMAIL_SUCCESS');
			}else{
				$json['error'] = JText::_('J2STORE_TEST_ADMIN_EMAIL_SUCCESS');
			}
		}
		echo json_encode($json);
		$app->close();
	}
}


