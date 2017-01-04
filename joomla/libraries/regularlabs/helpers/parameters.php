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

class RLParameters
{
	public static $instance = null;

	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new static;
		}

		return self::$instance;
	}

	function getParams($params, $path = '', $default = '')
	{
		$hash = md5('getParams_' . json_encode($params) . '_' . $path . '_' . $default);

		if (RLCache::has($hash))
		{
			return RLCache::get($hash);
		}

		$xml = $this->loadXML($path, $default);

		if (empty($params))
		{
			return RLCache::set(
				$hash,
				(object) $xml
			);
		}

		if (!is_object($params))
		{
			$params = json_decode($params);
			if (is_null($xml))
			{
				$xml = new stdClass;
			}
		}
		elseif (method_exists($params, 'toObject'))
		{
			$params = $params->toObject();
		}

		if (!$params)
		{
			return RLCache::set(
				$hash,
				(object) $xml
			);
		}

		if (empty($xml))
		{
			return RLCache::set(
				$hash,
				$params
			);
		}

		foreach ($xml as $key => $val)
		{
			if (isset($params->{$key}) && $params->{$key} != '')
			{
				continue;
			}

			$params->{$key} = $val;
		}

		return RLCache::set(
			$hash,
			$params
		);
	}

	function getComponentParams($name, $params = '')
	{
		$name = 'com_' . preg_replace('#^com_#', '', $name);

		$hash = md5('getComponentParams_' . $name . '_' . json_encode($params));

		if (RLCache::has($hash))
		{
			return RLCache::get($hash);
		}

		if (empty($params))
		{
			$params = JComponentHelper::getParams($name);
		}

		return RLCache::set(
			$hash,
			$this->getParams($params, JPATH_ADMINISTRATOR . '/components/' . $name . '/config.xml')
		);
	}

	function getModuleParams($name, $admin = 1, $params = '')
	{
		$name = 'mod_' . preg_replace('#^mod_#', '', $name);

		$hash = md5('getModuleParams_' . $name . '_' . json_encode($params));

		if (RLCache::has($hash))
		{
			return RLCache::get($hash);
		}

		if (empty($params))
		{
			$params = null;
		}

		return RLCache::set(
			$hash,
			$this->getParams($params, ($admin ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/modules/' . $name . '/' . $name . '.xml')
		);
	}

	function getPluginParams($name, $type = 'system', $params = '')
	{
		$hash = md5('getPluginParams_' . $name . '_' . $type . '_' . json_encode($params));

		if (RLCache::has($hash))
		{
			return RLCache::get($hash);
		}

		if (empty($params))
		{
			$plugin = JPluginHelper::getPlugin($type, $name);
			$params = (is_object($plugin) && isset($plugin->params)) ? $plugin->params : null;
		}

		return RLCache::set(
			$hash,
			$this->getParams($params, JPATH_PLUGINS . '/' . $type . '/' . $name . '/' . $name . '.xml')
		);
	}

	private function loadXML($path, $default = '')
	{
		$hash = md5('loadXML_' . $path . '_' . $default);

		if (RLCache::has($hash))
		{
			return RLCache::get($hash);
		}

		jimport('joomla.filesystem.file');
		if (!$path
			|| !JFile::exists($path)
			|| !$file = JFile::read($path)
		)
		{
			return RLCache::set(
				$hash,
				array()
			);
		}

		$xml = array();

		$xml_parser = xml_parser_create();
		xml_parse_into_struct($xml_parser, $file, $fields);
		xml_parser_free($xml_parser);

		$default = $default ? strtoupper($default) : 'DEFAULT';
		foreach ($fields as $field)
		{
			if ($field['tag'] != 'FIELD'
				|| !isset($field['attributes'])
				|| (!isset($field['attributes']['DEFAULT']) && !isset($field['attributes'][$default]))
				|| !isset($field['attributes']['NAME'])
				|| $field['attributes']['NAME'] == ''
				|| $field['attributes']['NAME']['0'] == '@'
				|| !isset($field['attributes']['TYPE'])
				|| $field['attributes']['TYPE'] == 'spacer'
			)
			{
				continue;
			}

			if (isset($field['attributes'][$default]))
			{
				$field['attributes']['DEFAULT'] = $field['attributes'][$default];
			}

			if ($field['attributes']['TYPE'] == 'textarea')
			{
				$field['attributes']['DEFAULT'] = str_replace('<br>', "\n", $field['attributes']['DEFAULT']);
			}

			$xml[$field['attributes']['NAME']] = $field['attributes']['DEFAULT'];
		}

		return RLCache::set(
			$hash,
			$xml
		);
	}

	function getObjectFromXML(&$xml)
	{
		$hash = md5('getObjectFromXML_' . json_encode($xml));

		if (RLCache::has($hash))
		{
			return RLCache::get($hash);
		}

		if (!is_array($xml))
		{
			$xml = array($xml);
		}

		$object = new stdClass;
		foreach ($xml as $item)
		{
			$key = $this->_getKeyFromXML($item);
			$val = $this->_getValFromXML($item);

			if (isset($object->{$key}))
			{
				if (!is_array($object->{$key}))
				{
					$object->{$key} = array($object->{$key});
				}
				$object->{$key}[] = $val;
			}
			$object->{$key} = $val;
		}

		return RLCache::set(
			$hash,
			$object
		);
	}

	function _getKeyFromXML(&$xml)
	{
		if (!empty($xml->_attributes) && isset($xml->_attributes['name']))
		{
			$key = $xml->_attributes['name'];
		}
		else
		{
			$key = $xml->_name;
		}

		return $key;
	}

	function _getValFromXML(&$xml)
	{
		if (!empty($xml->_attributes) && isset($xml->_attributes['value']))
		{
			$val = $xml->_attributes['value'];
		}
		else if (empty($xml->_children))
		{
			$val = $xml->_data;
		}
		else
		{
			$val = new stdClass;
			foreach ($xml->_children as $child)
			{
				$k = $this->_getKeyFromXML($child);
				$v = $this->_getValFromXML($child);

				if (isset($val->{$k}))
				{
					if (!is_array($val->{$k}))
					{
						$val->{$k} = array($val->{$k});
					}
					$val->{$k}[] = $v;
				}
				else
				{
					$val->{$k} = $v;
				}
			}
		}

		return $val;
	}
}
