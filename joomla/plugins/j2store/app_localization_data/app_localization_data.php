<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/app.php');
class plgJ2StoreApp_localization_data extends J2StoreAppPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
    var $_element   = 'app_localization_data';

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
    	$ns = $option.'.app.'.$this->_element;
    	$html = "";
    	JToolBarHelper::title(JText::_('J2STORE_APP').'-'.JText::_('PLG_J2STORE_'.strtoupper($this->_element)),'j2store-logo');

	   	$vars = new JObject();
	   	$this->includeCustomModel('AppLocalizationdata');
    	$this->includeCustomTables();
    	$model = F0FModel::getTmpInstance('AppLocalizationdata', 'J2StoreModel');
    	$id = $app->input->getInt('id', '0');
    	$vars->id = $id;
    	$form = array();
    	$form['action'] = "index.php?option=com_j2store&view=app&task=view&id={$id}";
    	$vars->form = $form;
    	$html = $this->_getLayout('default', $vars);
    	return $html;
    }

}

