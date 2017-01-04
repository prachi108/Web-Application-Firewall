<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/


/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
//F0FTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
class J2User
{

	public static $instance;

	public static function getInstance($properties=null) {

		if (!self::$instance)
		{
			self::$instance = new self($properties);
		}

		return self::$instance;
	}

	function addCustomer($post) {
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$user = JFactory::getUser();


		//first save data to the address table
		$row = F0FTable::getAnInstance('Address', 'J2StoreTable');

		//set the id so that it updates the record rather than changing
		if (!$row->bind($post)) {
			$row->setError($row->getError());
			return false;
		}

		if($user->id) {
			$row->user_id = $user->id;
		}

		$row->type = 'billing';

		if (!$row->store()) {
			$row->setError($row->getError());
			return false;
		}

		return $row->id;

	}

	/**
	 *
	 * @param $string
	 * @return unknown_type
	 */
	function usernameExists( $string )
	{
		// TODO Make this use ->load()

		$success = false;
		$database = JFactory::getDBO();
		$query = "SELECT * FROM #__users WHERE username = ".$database->quoteuote($string)." LIMIT 1";
		$database->setQuery($query);
		$result = $database->loadObject();
		if ($result) {
			$success = true;
		}
		return $success;
	}

	/**
	 *
	 * @param $string
	 * @return unknown_type
	 */
	function emailExists( $string, $table='users'  ) {
		switch($table)
		{
			case  'users':
			default     :
				$table = '#__users';
		}

		$success = false;
		$database = JFactory::getDBO();

		$query = "SELECT * FROM $table WHERE email = ".$database->quote($string)." LIMIT 1";
		$database->setQuery($query);
		$result = $database->loadObject();
		if ($result) {
			$success = true;
		}
		return $result;
	}


	/**
	 * Returns yes/no
	 * @param mixed Boolean
	 * @param mixed Boolean
	 * @return array
	 */
	function createNewUser( $details, &$msg )
	{
		$instance = JUser::getInstance();

		jimport('joomla.application.component.helper');
		$config = JComponentHelper::getParams('com_users');
		// Default to Registered.
		$defaultUserGroup = $config->get('new_usertype', 2);
		if(version_compare(JVERSION, '3.2.1', 'ge')) {
			$md5_pass = JUserHelper::hashPassword($details['password']);
		}else {
			$md5_pass = md5($details['password']);
		}

		$acl = JFactory::getACL();

		$instance->set('id'         , 0);
		$instance->set('name'           , $details['name']);
		$instance->set('username'       , $details['email']);
		$instance->set('password' 		, $md5_pass );
		$instance->set('email'          , $details['email']);  // Result should contain an email (check)
		$instance->set('usertype'       , 'deprecated');
		$instance->set('groups'     , array($defaultUserGroup));

		//If autoregister is set let's register the user
		$autoregister = isset($options['autoregister']) ? $options['autoregister'] :  $config->get('autoregister', 1);

		if ($autoregister) {
			if (!$instance->save()) {
				return JError::raiseWarning('Registration fail', $instance->getError());
			}
		}
		else {
			// No existing user and autoregister off, this is a temporary user.
			$instance->set('tmp_user', true);
		}

		$useractivation='0';

		// Send registration confirmation mail
		$this->_sendMail( $instance, $details, $useractivation );

		return $instance;
	}

	/**
	 * Returns yes/no
	 * @param array [username] & [password]
	 * @param mixed Boolean
	 *
	 * @return array
	 */
	function login( $credentials, $remember=true, $return='' ) {

		$mainframe  = JFactory::getApplication();

		if (strpos( $return, 'http' ) !== false && strpos( $return, JURI::base() ) !== 0) {
			$return = '';
		}

		// $credentials = array();
		// $credentials['username'] = JRequest::getVar('username', '', 'method', 'username');
		// $credentials['password'] = JRequest::getString('passwd', '', 'post', JREQUEST_ALLOWRAW);

		$options = array();
		$options['remember'] = (boolean)$remember;
		//$options['return'] = $return;

		//preform the login action
		$success = $mainframe->login($credentials);

		if ( $return ) {
			$mainframe->redirect( $return );
		}

		return $success;
	}

	/**
	 * Returns yes/no
	 * @param mixed Boolean
	 * @return array
	 */
	function logout( $return='' ) {
		$mainframe  = JFactory::getApplication();

		//preform the logout action//check to see if user has a joomla account
		//if so register with joomla userid
		//else create joomla account
		$success = $mainframe->logout();

		if (strpos( $return, 'http' ) !== false && strpos( $return, JURI::base() ) !== 0) {
			$return = '';
		}

		if ( $return ) {
			$mainframe->redirect( $return );
		}

		return $success;
	}

	/**
	 * Unblocks a user
	 *
	 * @param int $user_id
	 * @param int $unblock
	 * @return boolean
	 */
	function unblockUser($user_id, $unblock = 1)
	{
		$user =JFactory::getUser( (int)$user_id );

		if ($user->get('id')) {
			$user->set('block', !$unblock);

			if (  ! $user->save()) {
				return false;
			}

			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Returns yes/no
	 * @param object
	 * @param mixed Boolean
	 * @return array
	 */
	function _sendMail( &$user, $details, $useractivation ) {
		$mainframe  = JFactory::getApplication();
		$config = JFactory::getConfig();

		$name 		= $user->get('name');
		if(empty($name)) {
			$name 		= $user->get('email');
		}
		$email 		= $user->get('email');
		$username 	= $user->get('username');
		$activation	= $user->get('activation');
		$password 	= $details['password2']; // using the original generated pword for the email

		$usersConfig 	= JComponentHelper::getParams( 'com_users' );
		// $useractivation = $usersConfig->get( 'useractivation' );
		$sitename 		=$config->get( 'sitename' );
		$mailfrom 		= $config->get( 'mailfrom' );
		$fromname 		= $config->get( 'fromname' );
		$siteURL		= JURI::base();

		$subject 	= JText::sprintf('J2STORE_ACCOUNT_DETAILS', $name, $sitename);
		$subject 	= html_entity_decode($subject, ENT_QUOTES);

		$send_password = $usersConfig->get('sendpassword', 0);

		if ( $useractivation == 1 ){
			$message = JText::sprintf( 'J2STORE_SEND_MSG_ACTIVATE', $name, $sitename, $siteURL."index.php?option=com_users&task=activate&activation=".$activation, $siteURL, $email, $password);
		} else {
			if($send_password) {
				$message = JText::sprintf('J2STORE_SEND_MSG', $name, $sitename, $siteURL, $email, $password );
			}else {
				$message = JText::sprintf('J2STORE_SEND_MSG_NOPW', $name, $sitename, $siteURL, $email );
			}
		}

		$message = html_entity_decode($message, ENT_QUOTES);

		$success = $this->_doMail($mailfrom, $fromname, $email, $subject, $message);

		return $success;
	}

	/**
	 *
	 * @return unknown_type
	 */
	function _doMail( $from, $fromname, $recipient, $subject, $body, $actions=NULL, $mode=NULL, $cc=NULL, $bcc=NULL, $attachment=NULL, $replyto=NULL, $replytoname=NULL )
	{
		$success = false;

		$message =JFactory::getMailer();
		$message->addRecipient( $recipient );
		$message->setSubject( $subject );
		$message->setBody( $body );
		$sender = array( $from, $fromname );
		$message->setSender($sender);
		try {
			$sent = $message->send();
		}catch(Exception $e) {
			//do nothing. Joomla has botched up the entire mail system from 3.5.1
		}

		if ($sent == '1') {
			$success = true;
		}

		return $success;
	}

	/**
	 * Method to validate the password based on the password rules set under user manager options 
	 * @param string $password 			password
	 * @param string $confirm_password 	confirm password
	 * @param array  $json 				json as a reference
	 * */
	function validatePassword($password,$confirm_password,&$json){
		$config = J2Store::config();
		$minimumLength    =  4;
		$minimumIntegers  =  0;
		$minimumSymbols   =  0;
		$minimumUppercase =  0;
		$is_joomla_validate = $config->get('allow_password_validation',0);
		if($is_joomla_validate){
			$params = JComponentHelper::getParams('com_users');
			if (!empty($params))
			{
				$minimumLengthp    = $params->get('minimum_length');
				$minimumIntegersp  = $params->get('minimum_integers');
				$minimumSymbolsp   = $params->get('minimum_symbols');
				$minimumUppercasep = $params->get('minimum_uppercase');
				empty($minimumLengthp) ? : $minimumLength = (int) $minimumLengthp;
				empty($minimumIntegersp) ? : $minimumIntegers = (int) $minimumIntegersp;
				empty($minimumSymbolsp) ? : $minimumSymbols = (int) $minimumSymbolsp;
				empty($minimumUppercasep) ? : $minimumUppercase = (int) $minimumUppercasep;
			}

			$valueLength = strlen($password);

			// We set a maximum length to prevent abuse since it is unfiltered.
			if ($valueLength > 4096)
			{
				$json['error']['password'] = JText::_('J2STORE_PASSWORD_TOO_LONG');//JFactory::getApplication()->enqueueMessage(JText::_('COM_USERS_MSG_PASSWORD_TOO_LONG'), 'warning');
			}

			// We don't allow white space inside passwords
			$valueTrim = trim($password);

			if (strlen($valueTrim) != $valueLength)
			{
				$json['error']['password'] = JText::_('J2STORE_SPACES_IN_PASSWORD');
			}

		}

		// Minimum number of integers required
		if (!empty($minimumIntegers))
		{
			$nInts = preg_match_all('/[0-9]/', $password, $imatch);

			if ($nInts < $minimumIntegers)
			{
				$json['error']['password'] = JText::plural('J2STORE_NOT_ENOUGH_INTEGERS_N', $minimumIntegers);
			}
		}

		// Minimum number of symbols required
		if (!empty($minimumSymbols))
		{
			$nsymbols = preg_match_all('[\W]', $password, $smatch);

			if ($nsymbols < $minimumSymbols)
			{
				$json['error']['password'] = JText::plural('J2STORE_NOT_ENOUGH_SYMBOLS_N', $minimumSymbols);
			}
		}

		// Minimum number of upper case ASII characters required
		if (!empty($minimumUppercase))
		{
			$nUppercase = preg_match_all("/[A-Z]/", $password, $umatch);

			if ($nUppercase < $minimumUppercase)
			{
				$json['error']['password'] = JText::plural('J2STORE_NOT_ENOUGH_UPPERCASE_LETTERS_N', $minimumUppercase);
			}
		}

		// Minimum length option
		if (!empty($minimumLength))
		{
			if (strlen((string) $password) < $minimumLength)
			{
				$json['error']['password'] = JText::plural('J2STORE_PASSWORD_TOO_SHORT_N', $minimumLength);
			}
		}

		if(empty($password)){
			$json['error']['password'] = JText::_('J2STORE_PASSWORD_REQUIRED');
		}
		if(empty($confirm_password)){
			$json['error']['confirm'] = JText::_('J2STORE_PASSWORD_REQUIRED');
		}
		if ($password != $confirm_password) {
			$json['error']['confirm'] = JText::_('J2STORE_PASSWORDS_DOESTNOT_MATCH');
		}
	}

}