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
 * Plugin that replaces Sourcerer code with its HTML / CSS / JavaScript / PHP equivalent
 */
class PlgSystemSourcerer extends JPlugin
{
	public function __construct(&$subject, $config)
	{
		$this->_pass = false;
		parent::__construct($subject, $config);
	}

	public function onAfterRoute()
	{
		$this->_pass = false;

		jimport('joomla.filesystem.file');
		if (JFile::exists(JPATH_LIBRARIES . '/regularlabs/helpers/protect.php'))
		{
			require_once JPATH_LIBRARIES . '/regularlabs/helpers/protect.php';
			// return if page should be protected
			if (RLProtect::isProtectedPage('', true, array()))
			{
				return;
			}
		}

		// load the admin language file
		require_once JPATH_LIBRARIES . '/regularlabs/helpers/functions.php';
		RLFunctions::loadLanguage('plg_' . $this->_type . '_' . $this->_name);

		// return if Regular Labs Library plugin is not installed
		if (!JFile::exists(JPATH_PLUGINS . '/system/regularlabs/regularlabs.php'))
		{
			if (!JFactory::getApplication()->isAdmin() || JFactory::getApplication()->input->get('option') == 'com_login')
			{
				return;
			}

			$msg = JText::_('SRC_REGULAR_LABS_LIBRARY_NOT_INSTALLED')
				. ' ' . JText::sprintf('SRC_EXTENSION_CAN_NOT_FUNCTION', JText::_('SOURCERER'));
			$mq  = JFactory::getApplication()->getMessageQueue();
			foreach ($mq as $m)
			{
				if ($m['message'] == $msg)
				{
					$msg = '';
					break;
				}
			}

			if ($msg)
			{
				JFactory::getApplication()->enqueueMessage($msg, 'error');
			}

			return;
		}

		// return if current page is an admin page
		if ($this->isAdmin())
		{
			return;
		}

		// load the site language file
		require_once JPATH_LIBRARIES . '/regularlabs/helpers/functions.php';
		RLFunctions::loadLanguage('plg_' . $this->_type . '_' . $this->_name, JPATH_SITE);

		// Load plugin parameters
		require_once JPATH_LIBRARIES . '/regularlabs/helpers/parameters.php';
		$parameters = RLParameters::getInstance();
		$params     = $parameters->getPluginParams($this->_name);

		// Include the Helper
		require_once JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/helper.php';
		$class         = get_class($this) . 'Helper';
		$this->_helper = new $class ($params);

		$this->_pass = true;
	}

	private function isAdmin()
	{
		if (!JFile::exists(JPATH_LIBRARIES . '/regularlabs/helpers/protect.php'))
		{
			return JFactory::getApplication()->isAdmin();
		}

		require_once JPATH_LIBRARIES . '/regularlabs/helpers/protect.php';

		return RLProtect::isAdmin();
	}

	public function onContentPrepare($context, &$article)
	{
		if ($this->_pass)
		{
			$this->_helper->onContentPrepare($article, $context);
		}
	}

	public function onAfterDispatch()
	{
		if ($this->_pass)
		{
			$this->_helper->onAfterDispatch();
		}
	}

	public function onAfterRender()
	{
		if ($this->_pass)
		{
			$this->_helper->onAfterRender();
		}
	}
}
