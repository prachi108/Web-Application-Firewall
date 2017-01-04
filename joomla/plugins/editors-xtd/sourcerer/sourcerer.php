<?php
/**
 * @package         Sourcerer
 * @version         6.3.6
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2016 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

/**
 * Button Plugin that places a Sourcerer code block into the text
 */
class PlgButtonSourcerer extends JPlugin
{
	/**
	 * Display the button
	 *
	 * @return array A two element array of ( imageName, textToInsert )
	 */
	function onDisplay($name)
	{
		jimport('joomla.filesystem.file');

		// return if system plugin is not installed
		if (!JFile::exists(JPATH_PLUGINS . '/system/' . $this->_name . '/' . $this->_name . '.php'))
		{
			return;
		}

		// return if Regular Labs Library plugin is not installed
		if (!JFile::exists(JPATH_PLUGINS . '/system/regularlabs/regularlabs.php'))
		{
			return;
		}

		// load the admin language file
		require_once JPATH_LIBRARIES . '/regularlabs/helpers/functions.php';
		RLFunctions::loadLanguage('plg_' . $this->_type . '_' . $this->_name);

		// Load plugin parameters
		require_once JPATH_LIBRARIES . '/regularlabs/helpers/parameters.php';
		$parameters = RLParameters::getInstance();
		$params     = $parameters->getPluginParams($this->_name);

		// Include the Helper
		require_once JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/helper.php';
		$class  = get_class($this) . 'Helper';
		$helper = new $class($params);

		return $helper->render($name);
	}
}
