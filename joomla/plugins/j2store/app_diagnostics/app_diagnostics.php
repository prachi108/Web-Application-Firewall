<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/app.php');
class plgJ2StoreApp_diagnostics extends J2StoreAppPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
    var $_element   = 'app_diagnostics';

    /**
     * Overriding
     *
     * @param $options
     * @return unknown_type
     */
    function onJ2StoreGetAppView( $row )
    {

	   	if (!$this->_isMe($row))
    	{
    		return null;
    	}

    	$html = $this->viewList();


    	return $html;
    }

    /**
     * Validates the data submitted based on the suffix provided
     * A controller for this plugin, you could say
     *
     * @param $task
     * @return html
     */
    function viewList()
    {
    	$app = JFactory::getApplication();
    	$option = 'com_j2store';
    	$ns = $option.'.tool';
    	$html = "";
    	JToolBarHelper::title(JText::_('J2STORE_APP').'-'.JText::_('PLG_J2STORE_'.strtoupper($this->_element)),'j2store-logo');
    	JToolBarHelper::back('J2STORE_BACK_TO_DASHBOARD', 'index.php?option=com_j2store');

	   	$vars = new JObject();
	   	$this->includeCustomModel('AppDiagnostics');

    	$this->includeCustomTables();
    	//$model = F0FModel::getTmpInstance('ToolDiagnostics', 'J2StoreModel');


    	$vars->info = $this->getInfo();


    	$id = $app->input->getInt('id', '0');
    	$vars->id = $id;
    	$form = array();
    	$form['action'] = "index.php?option=com_j2store&view=app&task=view&id={$id}";
    	$vars->form = $form;
    	$html = $this->_getLayout('default', $vars);
    	return $html;
    }


    public function getInfo()
    {

    	$info = array();
    	$version = new JVersion;
    	$platform = new JPlatform;
    	$db = JFactory::getDbo();

    	if (isset($_SERVER['SERVER_SOFTWARE']))
    	{
    		$sf = $_SERVER['SERVER_SOFTWARE'];
    	}
    	else
    	{
    		$sf = getenv('SERVER_SOFTWARE');
    	}

     	$info['php']			= php_uname();
    	$info['dbversion']	= $db->getVersion();
    	$info['dbcollation']	= $db->getCollation();
     	$info['phpversion']	= phpversion();
    	$info['server']		= $sf;
    	$info['sapi_name']	= php_sapi_name();
    	$info['version']		= $version->getLongVersion();
    	$info['platform']		= $platform->getLongVersion();
    	$info['useragent']	= isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
		$info['j2store_version'] = $this->getJ2storeVerion();
		$info['is_pro'] = J2Store::isPro();
		$info['curl'] =  $this->_isCurl();
		$info['json'] =  $this->_isJson();
		$config = JFactory::getConfig();
		$info['error_reporting'] =$config->get('error_reporting');
		$caching = $config->get('caching');
		$info['caching'] = ($caching) ? JText::_('J2STORE_ENABLED') : JText::_('J2STORE_DISABLED') ;		 
		$cache_plugin = JPluginHelper::isEnabled('system', 'cache');
		$info['plg_cache_enabled'] = $cache_plugin;
		$info['memory_limit'] = ini_get('memory_limit');
    	return $info;
    }

    function _isCurl(){
    	return (function_exists('curl_version')) ?  JText::_('J2STORE_ENABLED'):  JText::_('J2STORE_DISABLED') ;
    }

    function _isJson(){
    	return (function_exists('json_encode')) ?  JText::_('J2STORE_ENABLED'):  JText::_('J2STORE_DISABLED') ;
    }
   
    public function getJ2storeVerion(){
	    $version ='';
	    $db = JFactory::getDbo();
    	$query = $db->getQuery(true);
	    $query->select($db->quoteName('manifest_cache'))->from($db->quoteName('#__extensions'))->where($db->quoteName('element').' = '.$db->quote('com_j2store'));
	    $db->setQuery($query);
	    $result = $db->loadResult();
	    if($result) {
	    	$manifest = json_decode($result);
	    	$version = $manifest->version;
	    }
		return $version;
    }
}

