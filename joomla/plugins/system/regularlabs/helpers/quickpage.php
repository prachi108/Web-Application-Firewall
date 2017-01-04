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

require_once JPATH_LIBRARIES . '/regularlabs/helpers/functions.php';

/**
 * Regular Labs Quick Page stuff (rl_qp=1 in url)
 */
class PlgSystemRegularLabsQuickPageHelper
{
	function render()
	{
		$url = JFactory::getApplication()->input->getString('url', '');

		if ($url)
		{
			echo RLFunctions::getByUrl($url);

			die;
		}

		$allowed = array(
			'administrator/components/com_dbreplacer/ajax.php',
			'administrator/modules/mod_addtomenu/popup.php',
			'media/rereplacer/images/popup.php',
			'plugins/editors-xtd/articlesanywhere/popup.php',
			'plugins/editors-xtd/conditionalcontent/popup.php',
			'plugins/editors-xtd/contenttemplater/data.php',
			'plugins/editors-xtd/contenttemplater/popup.php',
			'plugins/editors-xtd/dummycontent/popup.php',
			'plugins/editors-xtd/modals/popup.php',
			'plugins/editors-xtd/modulesanywhere/popup.php',
			'plugins/editors-xtd/sliders/data.php',
			'plugins/editors-xtd/sliders/popup.php',
			'plugins/editors-xtd/snippets/popup.php',
			'plugins/editors-xtd/sourcerer/popup.php',
			'plugins/editors-xtd/tabs/data.php',
			'plugins/editors-xtd/tabs/popup.php',
			'plugins/editors-xtd/tooltips/popup.php',
		);

		$file   = JFactory::getApplication()->input->getString('file', '');
		$folder = JFactory::getApplication()->input->getString('folder', '');

		if ($folder)
		{
			$file = implode('/', explode('.', $folder)) . '/' . $file;
		}

		if (!$file || in_array($file, $allowed) === false)
		{
			die;
		}

		jimport('joomla.filesystem.file');

		if (JFactory::getApplication()->isSite())
		{
			JFactory::getApplication()->setTemplate('../administrator/templates/isis');
		}

		$_REQUEST['tmpl'] = 'component';
		JFactory::getApplication()->input->set('option', 'com_content');

		switch (JFactory::getApplication()->input->getCmd('format', 'html'))
		{
			case 'json' :
				$format = 'application/json';
				break;

			default:
			case 'html' :
				$format = 'text/html';
				break;
		}

		header('Content-Type: ' . $format . '; charset=utf-8');
		JHtml::_('bootstrap.framework');
		JFactory::getDocument()->addScript(JUri::root(true) . '/administrator/templates/isis/js/template.js');
		JFactory::getDocument()->addStyleSheet(JUri::root(true) . '/administrator/templates/isis/css/template.css');

		RLFunctions::stylesheet('regularlabs/popup.min.css');

		$file = JPATH_SITE . '/' . $file;

		$html = '';
		if (JFile::exists($file))
		{
			ob_start();
			include $file;
			$html = ob_get_contents();
			ob_end_clean();
		}

		JFactory::getDocument()->setBuffer($html, 'component');

		$app = new RLApplication();
		$app->render();

		$html = JFactory::getApplication()->toString(JFactory::getApplication()->getCfg('gzip'));
		$html = preg_replace('#\s*<' . 'link [^>]*href="[^"]*templates/system/[^"]*\.css[^"]*"[^>]*( /)?>#s', '', $html);
		$html = preg_replace('#(<' . 'body [^>]*class=")#s', '\1reglab-popup ', $html);
		$html = str_replace('<' . 'body>', '<' . 'body class="reglab-popup"', $html);

		echo $html;

		die;
	}
}

class RLApplication
{
	public function render()
	{
		$app      = JFactory::getApplication();
		$document = JFactory::getDocument();
		$app->loadDocument($document);

		$params = array(
			'template'  => $app->get('theme'),
			'file'      => $app->get('themeFile', 'index.php'),
			'params'    => $app->get('themeParams'),
			'directory' => self::getThemesDirectory(),
		);

		// Parse the document.
		$document->parse($params);

		// Trigger the onBeforeRender event.
		JPluginHelper::importPlugin('system');
		$app->triggerEvent('onBeforeRender');

		$caching = false;

		if ($app->isSite() && $app->get('caching') && $app->get('caching', 2) == 2 && !JFactory::getUser()->get('id'))
		{
			$caching = true;
		}

		// Render the document.
		$data = $document->render($caching, $params);

		// Set the application output data.
		$app->setBody($data);

		// Trigger the onAfterRender event.
		$app->triggerEvent('onAfterRender');

		// Mark afterRender in the profiler.
		// Causes issues, so commented out.
		// JDEBUG ? $app->profiler->mark('afterRender') : null;
	}

	static function getThemesDirectory()
	{
		if (JFactory::getApplication()->get('themes.base'))
		{
			return JFactory::getApplication()->get('themes.base');
		}

		if (defined('JPATH_THEMES'))
		{
			return JPATH_THEMES;
		}

		if (defined('JPATH_BASE'))
		{
			return JPATH_BASE . '/themes';
		}

		return __DIR__ . '/themes';
	}
}
