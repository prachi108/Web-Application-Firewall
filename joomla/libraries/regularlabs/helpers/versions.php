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

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/text.php';

class RLVersions
{
	public static function render($alias)
	{
		if (!$alias)
		{
			return '';
		}

		$name  = RLFunctions::getNameByAlias($alias);
		$alias = RLFunctions::getAliasByName($alias);

		if (!$version = self::getXMLVersion($alias))
		{
			return '';
		}

		JHtml::_('jquery.framework');

		RLFunctions::script('regularlabs/script.min.js');
		$url    = 'download.regularlabs.com/extensions.xml?j=3&e=' . $alias;
		$script = "
			jQuery(document).ready(function() {
				RegularLabsScripts.loadajax(
					'" . $url . "',
					'RegularLabsScripts.displayVersion( data, \"" . $alias . "\", \"" . str_replace(array('FREE', 'PRO'), '', $version) . "\" )',
					'RegularLabsScripts.displayVersion( \"\" )',
					null, null, null, (60 * 60)
				);
			});
		";
		JFactory::getDocument()->addScriptDeclaration($script);

		return '<div class="alert alert-success" style="display:none;" id="regularlabs_version_' . $alias . '">' . self::getMessageText($alias, $name, $version) . '</div>';
	}

	public static function getMessageText($alias, $name, $version)
	{
		list($url, $onclick) = self::getUpdateLink($alias, $version);

		$href    = $onclick ? '' : 'href="' . $url . '" target="_blank" ';
		$onclick = $onclick ? 'onclick="' . $onclick . '" ' : '';

		$is_pro  = strpos($version, 'PRO') !== false;
		$version = str_replace(array('FREE', 'PRO'), array('', ' <small>[PRO]</small>'), $version);

		$msg = '<div class="text-center">'
			. '<span class="ghosted">'
			. JText::sprintf('RL_NEW_VERSION_OF_AVAILABLE', JText::_($name))
			. '</span>'
			. '<br>'
			. '<a ' . $href . $onclick . ' class="btn btn-large btn-success">'
			. '<span class="icon-upload"></span> '
			. RLText::html_entity_decoder(JText::sprintf('RL_UPDATE_TO', '<span id="regularlabs_newversionnumber_' . $alias . '"></span>'))
			. '</a>';

		if (!$is_pro)
		{
			$msg .= ' <a href="https://www.regularlabs.com/purchase?ext=' . $alias . '" target="_blank" class="btn btn-large btn-primary">'
				. '<span class="icon-basket"></span> '
				. JText::_('RL_GO_PRO')
				. '</a>';
		}

		$msg .= '<br>'
			. '<span class="ghosted">'
			. '[ <a href="https://www.regularlabs.com/' . $alias . '#changelog" target="_blank">'
			. JText::_('RL_CHANGELOG')
			. '</a> ]'
			. '<br>'
			. JText::sprintf('RL_CURRENT_VERSION', $version)
			. '</span>'
			. '</div>';

		return RLText::html_entity_decoder($msg);
	}

	public static function getUpdateLink($alias, $version)
	{
		$is_pro = strpos($version, 'PRO') !== false;

		if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_regularlabsmanager/regularlabsmanager.xml'))
		{
			$url = $is_pro
				? 'https://www.regularlabs.com/' . $alias . '#download'
				: JRoute::_('index.php?option=com_installer&view=update');

			return array($url, '');
		}

		$config = JComponentHelper::getParams('com_regularlabsmanager');

		$key = trim($config->get('key'));

		if ($is_pro && !$key)
		{
			return array('index.php?option=com_regularlabsmanager', '');
		}

		JHtml::_('bootstrap.framework');
		JHtml::_('behavior.modal');
		jimport('joomla.filesystem.file');

		RLFunctions::script('regularlabs/script.min.js');
		JFactory::getDocument()->addScriptDeclaration(
			"
			var NNEM_TIMEOUT = " . (int) $config->get('timeout', 5) . ";
			var NNEM_TOKEN = '" . JSession::getFormToken() . "';
		"
		);
		RLFunctions::script('regularlabsmanager/script.min.js', '6.3.6');

		$url = 'http://download.regularlabs.com?ext=' . $alias . '&j=3';

		if ($is_pro)
		{
			$url .= '&k=' . strtolower(substr($key, 0, 8) . md5(substr($key, 8)));
		}

		return array('', 'RegularLabsManager.openModal(\'update\', [\'' . $alias . '\'], [\'' . $url . '\'], true);');
	}

	public static function getFooter($name, $copyright = 1)
	{
		$html = array();

		$html[] = '<div class="rl_footer_extension">' . self::getFooterName($name) . '</div>';

		if ($copyright)
		{
			$html[] = '<div class="rl_footer_review">' . self::getFooterReview($name) . '</div>';
			$html[] = '<div class="rl_footer_logo">' . self::getFooterLogo() . '</div>';
			$html[] = '<div class="rl_footer_copyright">' . self::getFooterCopyright() . '</div>';
		}

		return '<div class="rl_footer">' . implode('', $html) . '</div>';
	}

	private static function getFooterName($name)
	{
		$name = JText::_($name);

		if (!$version = self::getXMLVersion($name))
		{
			return $name;
		}

		if (strpos($version, 'PRO') !== false)
		{
			return $name . ' v' . str_replace('PRO', '', $version) . ' <small>[PRO]</small>';
		}

		if (strpos($version, 'FREE') !== false)
		{
			return $name . ' v' . str_replace('FREE', '', $version) . ' <small>[FREE]</small>';
		}

		return $name . ' v' . $version;
	}

	private static function getFooterReview($name)
	{
		require_once __DIR__ . '/functions.php';

		$alias = RLFunctions::getAliasByName($name);

		$jed_url = 'http://regl.io/jed-' . $alias . '#reviews';

		return
			RLText::html_entity_decoder(
				JText::sprintf(
					'RL_JED_REVIEW',
					'<a href="' . $jed_url . '" target="_blank">',
					'</a>'
					. ' <a href="' . $jed_url . '" target="_blank" class="stars">'
					. '<span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span>'
					. '</a>'
				)
			);
	}

	private static function getFooterLogo()
	{
		return
			JText::sprintf(
				'RL_POWERED_BY',
				'<a href="https://www.regularlabs.com" target="_blank"><img src="' . JUri::root() . 'media/regularlabs/images/logo.png"></a>'
			);
	}

	private static function getFooterCopyright()
	{
		return JText::_('RL_COPYRIGHT') . ' &copy; ' . date('Y') . ' Regular Labs - ' . JText::_('RL_ALL_RIGHTS_RESERVED');
	}

	public static function getXMLVersion($alias, $urlformat = false, $type = 'component', $folder = 'system')
	{
		require_once __DIR__ . '/functions.php';

		if (!$version = RLFunctions::getXMLValue('version', $alias, $type, $folder))
		{
			return '';
		}

		$version = trim($version);

		if (!$urlformat)
		{
			return $version;
		}

		return $version . '?v=' . strtolower(str_replace(array('FREE', 'PRO'), array('f', 'p'), $version));
	}

	public static function getPluginXMLVersion($alias, $folder = 'system')
	{
		return RLVersions::getXMLVersion($alias, false, 'plugin', $folder);
	}
}
