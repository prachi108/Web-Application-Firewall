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

class RLLicenses
{
	public static function render($name, $check_pro = false)
	{
		if (!$name)
		{
			return '';
		}

		require_once __DIR__ . '/functions.php';

		$alias = RLFunctions::getAliasByName($name);
		$name  = RLFunctions::getNameByAlias($name);

		if ($check_pro && self::isPro($alias))
		{
			return '';
		}

		require_once __DIR__ . '/text.php';

		return
			'<div class="alert rl_licence">'
			. JText::sprintf('RL_IS_FREE_VERSION', $name)
			. '<br>'
			. JText::_('RL_FOR_MORE_GO_PRO')
			. '<br>'
			. '<a href="https://www.regularlabs.com/purchase?ext=' . $alias . '" target="_blank" class="btn btn-small btn-primary">'
			. ' <span class="icon-basket"></span>'
			. RLText::html_entity_decoder(JText::_('RL_GO_PRO'))
			. '</a>'
			. '</div>';
	}

	private static function isPro($element)
	{
		require_once __DIR__ . '/functions.php';

		if (!$version = RLFunctions::getXMLValue('version', $element))
		{
			return false;
		}

		return (stripos($version, 'PRO') !== false);
	}
}
