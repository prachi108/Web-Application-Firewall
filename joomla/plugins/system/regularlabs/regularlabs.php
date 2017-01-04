<?php
/**
 * @package         Regular Labs Library
 * @version         16.11.9943
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2016 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

if (JFactory::getApplication()->isAdmin() && JFile::exists(JPATH_LIBRARIES . '/regularlabs//helpers/functions.php'))
{
	// load the Regular Labs Library language file
	require_once JPATH_LIBRARIES . '/regularlabs/helpers/functions.php';
	RLFunctions::loadLanguage('plg_system_regularlabs');
}

// If controller.php exists, assume this is K2 v3
define('RL_K2_VERSION', JFile::exists(JPATH_ADMINISTRATOR . '/components/com_k2/controller.php') ? 3 : 2);

class PlgSystemRegularLabs extends JPlugin
{
	public function onAfterRoute()
	{
		if (!JFile::exists(JPATH_LIBRARIES . '/regularlabs/helpers/functions.php'))
		{
			JFactory::getApplication()->enqueueMessage('The Regular Labs Library folder is missing or incomplete: ' . JPATH_LIBRARIES . '/regularlabs', 'error');

			return;
		}

		$this->updateDownloadKey();

		$this->loadSearchHelper();

		$this->renderQuickPage();
	}

	public function onAfterRender()
	{
		$this->combineAdminMenu();

		$this->addAdminHelpMenuItem();
	}

	private function renderQuickPage()
	{
		if (!JFactory::getApplication()->input->getInt('rl_qp', 0))
		{
			return;
		}

		require_once __DIR__ . '/helpers/quickpage.php';
		$helper = new PlgSystemRegularLabsQuickPageHelper;

		$helper->render();
	}

	private function updateDownloadKey()
	{
		// Save the download key from the Regular Labs Extension Manager config to the update sites
		if (
			JFactory::getApplication()->isSite()
			|| JFactory::getApplication()->input->get('option') != 'com_config'
			|| JFactory::getApplication()->input->get('task') != 'config.save.component.apply'
			|| JFactory::getApplication()->input->get('component') != 'com_regularlabsmanager'
		)
		{
			return;
		}

		$form = JFactory::getApplication()->input->post->get('jform', array(), 'array');

		if (!isset($form['key']))
		{
			return;
		}

		$key = $form['key'];

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->update('#__update_sites')
			->set($db->quoteName('extra_query') . ' = ' . $db->quote(''))
			->where($db->quoteName('location') . ' LIKE ' . $db->quote('http://download.regularlabs.com%'));
		$db->setQuery($query);
		$db->execute();

		$query->clear()
			->update('#__update_sites')
			->set($db->quoteName('extra_query') . ' = ' . $db->quote('k=' . $key))
			->where($db->quoteName('location') . ' LIKE ' . $db->quote('http://download.regularlabs.com%'))
			->where($db->quoteName('location') . ' LIKE ' . $db->quote('%&pro=1%'));
		$db->setQuery($query);
		$db->execute();
	}

	private function loadSearchHelper()
	{
		// Only in frontend search component view
		if (!JFactory::getApplication()->isSite() || JFactory::getApplication()->input->get('option') != 'com_search')
		{
			return;
		}

		$classes = get_declared_classes();

		if (in_array('SearchModelSearch', $classes) || in_array('searchmodelsearch', $classes))
		{
			return;
		}

		require_once JPATH_LIBRARIES . '/regularlabs/helpers/search.php';
	}

	private function combineAdminMenu()
	{
		if (JFactory::getApplication()->isSite()
			|| JFactory::getDocument()->getType() != 'html'
			|| !$this->params->get('combine_admin_menu', 0)
		)
		{
			return;
		}

		require_once __DIR__ . '/helpers/adminmenu.php';
		$helper = new PlgSystemRegularLabsAdminMenuHelper();

		$helper->combine();
	}

	private function addAdminHelpMenuItem()
	{
		if (JFactory::getApplication()->isSite()
			|| JFactory::getDocument()->getType() != 'html'
			|| !$this->params->get('show_help_menu', 1)
		)
		{
			return;
		}

		$html = JFactory::getApplication()->getBody();

		if ($html == '')
		{
			return;
		}

		$shop_item = '(<li>\s*<a [^>]*class="[^"]*menu-help-)shop("\s[^>]*)href="https://shop\.joomla\.org"([^>]*>)[^<]*(</a>s*</li>)';

		$html = preg_replace(
			'#' . $shop_item . '#s',
			'\0<li class="divider"><span></span></li>\1dev\2href="https://www.regularlabs.com"\3Regular Labs Extensions\4',
			$html
		);

		// Just in case something fails
		if ($html == '')
		{
			return;
		}

		JFactory::getApplication()->setBody($html);
	}
}

