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

require_once __DIR__ . '/cache.php';

class RLHelper
{
	static function getPluginHelper(&$plugin, $params = null)
	{
		$hash = md5('getPluginHelper_' . $plugin->get('_type') . '_' . $plugin->get('_name') . '_' . json_encode($params));

		if (RLCache::has($hash))
		{
			return RLCache::get($hash);
		}

		if (!$params)
		{
			require_once __DIR__ . '/parameters.php';
			$params = RLParameters::getInstance()->getPluginParams($plugin->get('_name'));
		}

		require_once JPATH_PLUGINS . '/' . $plugin->get('_type') . '/' . $plugin->get('_name') . '/helper.php';
		$class = get_class($plugin) . 'Helper';

		return RLCache::set(
			$hash,
			new $class($params)
		);
	}

	static function isCategoryList($context)
	{
		$hash = md5('isCategoryList_' . $context);

		if (RLCache::has($hash))
		{
			return RLCache::get($hash);
		}

		// Return false if it is not a category page
		if ($context != 'com_content.category' || JFactory::getApplication()->input->get('view') != 'category')
		{
			return RLCache::set($hash, false);
		}

		// Return false if it is not a list layout
		if (JFactory::getApplication()->input->get('layout') && JFactory::getApplication()->input->get('layout') != 'list')
		{
			return RLCache::set($hash, false);
		}

		// Return true if it IS a list layout
		return RLCache::set($hash, true);
	}

	static function processArticle(&$article, &$context, &$helper, $method, $params = array())
	{
		if (!empty($article->description))
		{
			call_user_func_array(array($helper, $method), array_merge(array(&$article->description), $params));
		}

		if (!empty($article->title))
		{
			call_user_func_array(array($helper, $method), array_merge(array(&$article->title), $params));
		}

		if (!empty($article->created_by_alias))
		{
			call_user_func_array(array($helper, $method), array_merge(array(&$article->created_by_alias), $params));
		}

		if (self::isCategoryList($context))
		{
			return;
		}

		// Process texts
		if (!empty($article->text))
		{
			call_user_func_array(array($helper, $method), array_merge(array(&$article->text), $params));

			return;
		}

		if (!empty($article->introtext))
		{
			call_user_func_array(array($helper, $method), array_merge(array(&$article->introtext), $params));
		}

		if (!empty($article->fulltext))
		{
			call_user_func_array(array($helper, $method), array_merge(array(&$article->fulltext), $params));
		}
	}
}
