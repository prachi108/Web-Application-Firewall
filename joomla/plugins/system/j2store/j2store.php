<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
jimport('joomla.html.parameter');

// Make sure FOF is loaded, otherwise do not run
if (!defined('F0F_INCLUDED'))
{
	include_once JPATH_LIBRARIES . '/f0f/include.php';
}

if (!defined('F0F_INCLUDED') || !class_exists('F0FLess', true))
{
	return;
}

// Do not run if Akeeba Subscriptions is not enabled
JLoader::import('joomla.application.component.helper');

if (!JComponentHelper::isEnabled('com_j2store', true))
{
	return;
}


class plgSystemJ2Store extends JPlugin {

	function __construct( &$subject, $config ){
		parent::__construct( $subject, $config );

		// Timezone fix; avoids errors printed out by PHP 5.3.3+ (thanks Yannick!)
		if (function_exists('date_default_timezone_get') && function_exists('date_default_timezone_set'))
		{
			if (function_exists('error_reporting'))
			{
				$oldLevel = error_reporting(0);
			}
			$serverTimezone	 = @date_default_timezone_get();
			if (empty($serverTimezone) || !is_string($serverTimezone))
				$serverTimezone	 = 'UTC';
			if (function_exists('error_reporting'))
			{
				error_reporting($oldLevel);
			}
			@date_default_timezone_set($serverTimezone);
		}
		//load language
		$this->loadLanguage('com_j2store', JPATH_SITE);
		//if($this->_mainframe->isAdmin())return;


	}

	function onAfterRoute() {

		$mainframe = JFactory::getApplication();
		$document =JFactory::getDocument();
		$baseURL = JURI::root();
		$script = "
		var j2storeURL = '{$baseURL}';
		";
		$document->addScriptDeclaration($script);

		if($mainframe->isSite()) {
			//$this->_addCartJS();
		}

	}

	public function onUserLogin($user, $options = array())
	{
		return $this->doLoginUser($user, $options);
	}

	private function doLoginUser($user, $options=array()) {

		$app = JFactory::getApplication();
		if($app->isAdmin()) return true;

		$session = JFactory::getSession();
		$old_sessionid = $session->get( 'old_sessionid', '', 'j2store' );
		jimport('joomla.user.helper');
		$user['id'] = intval(JUserHelper::getUserId($user['username']));
		if (!class_exists('J2Store')) {
			jimport('joomla.filesystem.file');
			if(JFile::exists(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php')) {
				require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
			}else {
				return;
			}
		}
		//cart
		$helper = J2Store::cart();
		if (!empty($old_sessionid))
		{
			$helper->resetCart( $old_sessionid, $user['id'] );
			//TODO do the same for wish lists
		}
		else
		{
			$helper->updateSession( $user['id'], $session->getId() );
		}

		return true;
	}


	/**
	 * Called when Joomla! is booting up and checks for inventory. Thanks Nicholas (Based on Akeeba Subscriptions)
	 */
	public function onAfterInitialise()
	{
		// Check if we need to run
	 	if (!$this->doIHaveToRun())
		{
			return;
		}
		$this->onJ2StoreCronTask('inventorycontrol');
	}


	public function onJ2StoreCronTask($task, $options = array())
	{
		if ($task != 'inventorycontrol')
		{
			return;
		}

		//check if inventory is enabled
		if (!class_exists('J2Store')) {
			jimport('joomla.filesystem.file');
			if(JFile::exists(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php')) {
				require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
			}else {
				return;
			}
		}
		$config = J2Store::config();
		if($config->get('enable_inventory', 0) != 1 || $config->get('cancel_order', 0) != 1) {
			//inventory not enabled. return.
			return;
		}

		// Get today's date
		JLoader::import('joomla.utilities.date');
		$jNow	 = new JDate();
		$now	 = $jNow->toUnix();

		F0FModel::getTmpInstance('Orders', 'J2StoreModel')->cancel_unpaid_orders();

		// Update the last run info and quit
		$this->setLastRunTimestamp();
	}

	/**
	 * Fetches the com_j2store component's parameters as a JRegistry instance
	 *
	 * @return JRegistry The component parameters
	 */
	private function getComponentParameters()
	{
		JLoader::import('joomla.registry.registry');

		$component = JComponentHelper::getComponent('com_j2store');

		if ($component->params instanceof JRegistry)
		{
			$cparams = $component->params;
		}
		elseif (!empty($component->params))
		{
			$cparams = new JRegistry($component->params);
		}
		else
		{
			$cparams = new JRegistry('{}');
		}

		return $cparams;
	}

	/**
	 * "Do I have to run?" - the age old question. Let it be answered by checking the
	 * last execution timestamp, stored in the component's configuration.
	 */
	private function doIHaveToRun()
	{
		$params		 = $this->getComponentParameters();
		$lastRunUnix = $params->get('plg_j2store_inventory_control_timestamp', 0);
		$dateInfo	 = getdate($lastRunUnix);
		$nextRunUnix = mktime(0, 0, 0, $dateInfo['mon'], $dateInfo['mday'], $dateInfo['year']);
		$nextRunUnix += 24 * 3600;
		$now		 = time();

		return ($now >= $nextRunUnix);
	}

	/**
	 * Saves the timestamp of this plugin's last run
	 */
	private function setLastRunTimestamp()
	{
		$lastRun = time();
		$params	 = $this->getComponentParameters();
		$params->set('plg_j2store_inventory_control_timestamp', $lastRun);

		$db		 = JFactory::getDBO();
		$data	 = $params->toString();

		$query = $db->getQuery(true)
		->update($db->qn('#__extensions'))
		->set($db->qn('params') . ' = ' . $db->q($data))
		->where($db->qn('element') . ' = ' . $db->q('com_j2store'))
		->where($db->qn('type') . ' = ' . $db->q('component'));
		$db->setQuery($query);
		$db->execute();
	}

	public function onJ2StoreAfterUpdateCart($cart_id, $data) {

		$plugin = JPluginHelper::getPlugin('system', 'cache');
		$params = new JRegistry(@$plugin->params);

		$options = array(
				'defaultgroup'	=> 'page',
				'browsercache'	=> $params->get('browsercache', false),
				'caching'		=> false,
		);
		$cache		= JCache::getInstance('page', $options);
		$cache->clean();
	}

	public function onJ2StoreBeforeGetPrice($pricing,$model,&$calculator){
		$app = JFactory::getApplication ();
		$user_id = $app->input->getInt('user_id',0);
		$view = $app->input->get('view','');
		$task = $app->input->get('task','');

		if(!empty( $user_id ) && in_array ( $task, array('displayAdminProduct') ) && in_array ( $view, array('products') )){
			$user = JFactory::getUser ($user_id);
			$group_id = implode(',', JAccess::getGroupsByUser($user->id));
			$calculator->set('group_id',$group_id);
		}

	}

}
