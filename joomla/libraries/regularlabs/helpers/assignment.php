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

class RLAssignment
{
	public $request    = null;
	public $date       = null;
	public $db         = null;
	public $selection  = null;
	public $params     = null;
	public $assignment = null;
	public $article    = null;

	public function __construct($request = null, $date = null)
	{
		if (is_null($request))
		{
			$request = new stdClass;
		}

		if (is_null($date))
		{
			$tz   = new DateTimeZone(JFactory::getApplication()->getCfg('offset'));
			$date = JFactory::getDate()->setTimeZone($tz);
		}

		$this->request = $request;
		$this->date    = $date;
		$this->db      = JFactory::getDbo();
	}

	public function init()
	{
	}

	public function initAssignment($assignment, $article = 0)
	{
		$this->selection  = $assignment->selection;
		$this->params     = $assignment->params;
		$this->assignment = $assignment->assignment;
		$this->article    = $article;
	}

	public function pass($pass = true, $assignment = null)
	{
		$assignment = $assignment ?: $this->assignment;

		return $pass ? ($assignment == 'include') : ($assignment == 'exclude');
	}

	public function passSimple($values = '', $caseinsensitive = false, $assignment = null, $selection = null)
	{
		$values     = $this->makeArray($values);
		$assignment = $assignment ?: $this->assignment;
		$selection  = $selection ?: $this->selection;

		$pass = false;
		foreach ($values as $value)
		{
			if ($caseinsensitive)
			{
				if (in_array(strtolower($value), array_map('strtolower', $selection)))
				{
					$pass = true;
					break;
				}

				continue;
			}

			if (in_array($value, $selection))
			{
				$pass = true;
				break;
			}
		}

		return $this->pass($pass, $assignment);
	}

	public function passInRange($value = '', $assignment = null, $selection = null)
	{
		$assignment = $assignment ?: $this->assignment;

		if (empty($value))
		{
			return $this->pass(false, $assignment);
		}

		$selections = $this->makeArray($selection ?: $this->selection);

		$pass = false;
		foreach ($selections as $selection)
		{
			if (empty($selection))
			{
				continue;
			}

			if (strpos($selection, '-') === false)
			{
				if ((int) $value == (int) $selection)
				{
					$pass = true;
					break;
				}

				continue;
			}

			list($min, $max) = explode('-', $selection, 2);

			if ((int) $value >= (int) $min && (int) $value <= (int) $max)
			{
				$pass = true;
				break;
			}
		}

		return $this->pass($pass, $assignment);
	}

	public function passItemByType(&$pass, $type = '', $data = null)
	{
		$pass_type = !empty($data) ? $this->{'pass' . $type}($data) : $this->{'pass' . $type}();

		if ($pass_type == null)
		{
			return true;
		}

		$pass = $pass_type;

		return $pass;
	}

	public function passByPageTypes($option, $selection = array(), $assignment = 'all', $add_view = false, $get_task = false, $get_layout = true)
	{
		if ($this->request->option != $option)
		{
			return $this->pass(false, $assignment);
		}

		if ($get_task && $this->request->task && $this->request->task != $this->request->view && $this->request->task != 'default')
		{
			$pagetype = ($add_view ? $this->request->view . '_' : '') . $this->request->task;

			return $this->passSimple($pagetype, $selection, $assignment);
		}

		if ($get_layout && $this->request->layout && $this->request->layout != $this->request->view && $this->request->layout != 'default')
		{
			$pagetype = ($add_view ? $this->request->view . '_' : '') . $this->request->layout;

			return $this->passSimple($pagetype, $selection, $assignment);
		}

		return $this->passSimple($this->request->view, $selection, $assignment);
	}

	function getMenuItemParams($id = 0)
	{
		$hash = md5('getMenuItemParams_' . $id);

		if (RLCache::has($hash))
		{
			return RLCache::get($hash);
		}

		$query = $this->db->getQuery(true)
			->select('m.params')
			->from('#__menu AS m')
			->where('m.id = ' . (int) $id);
		$this->db->setQuery($query);
		$params = $this->db->loadResult();

		$parameters = RLParameters::getInstance();

		return RLCache::set(
			$hash,
			$parameters->getParams($params)
		);
	}

	function getParentIds($id = 0, $table = 'menu', $parent = 'parent_id', $child = 'id')
	{
		if (!$id)
		{
			return array();
		}

		$hash = md5('getParentIds_' . $id . '_' . $table . '_' . $parent . '_' . $child);

		if (RLCache::has($hash))
		{
			return RLCache::get($hash);
		}

		$parent_ids = array();

		while ($id)
		{
			$query = $this->db->getQuery(true)
				->select('t.' . $parent)
				->from('#__' . $table . ' as t')
				->where('t.' . $child . ' = ' . (int) $id);
			$this->db->setQuery($query);
			$id = $this->db->loadResult();

			// Break if no parent is found or parent already found before for some reason
			if (!$id || in_array($id, $parent_ids))
			{
				break;
			}

			$parent_ids[] = $id;
		}

		return RLCache::set(
			$hash,
			$parent_ids
		);
	}

	function makeArray($array = '', $delimiter = ',', $trim = 1)
	{
		if (empty($array))
		{
			return array();
		}

		$hash = md5('makeArray_' . json_encode($array) . '_' . $delimiter . '_' . $trim);

		if (RLCache::has($hash))
		{
			return RLCache::get($hash);
		}

		$array = $this->mixedDataToArray($array, $delimiter);

		if (empty($array))
		{
			return $array;
		}

		if (!$trim)
		{
			return $array;
		}

		foreach ($array as $k => $v)
		{
			if (!is_string($v))
			{
				continue;
			}

			$array[$k] = trim($v);
		}

		return RLCache::set(
			$hash,
			$array
		);
	}

	private function mixedDataToArray($array = '', $onlycommas = 0)
	{
		if (!is_array($array))
		{
			$delimiter = ($onlycommas || strpos($array, '|') === false) ? ',' : '|';

			return explode($delimiter, $array);
		}

		if (empty($array))
		{
			return $array;
		}

		if (isset($array['0']) && is_array($array['0']))
		{
			return $array['0'];
		}

		if (count($array) === 1 && strpos($array['0'], ',') !== false)
		{
			return explode(',', $array['0']);
		}

		return $array;
	}

	public function passContentIds()
	{
		if (empty($this->selection))
		{
			return null;
		}

		return in_array($this->request->id, $this->selection);
	}

	public function passContentKeywords($fields = array('title', 'introtext', 'fulltext'), $text = '')
	{
		if (empty($this->params->content_keywords))
		{
			return null;
		}

		if (!$text)
		{
			$item = $this->getItem($fields);

			foreach ($fields as $field)
			{
				if (!isset($item->{$field}))
				{
					return false;
				}

				$text = trim($text . ' ' . $item->{$field});
			}
		}

		if (empty($text))
		{
			return false;
		}

		$this->params->content_keywords = $this->makeArray($this->params->content_keywords);

		foreach ($this->params->content_keywords as $keyword)
		{
			if (!preg_match('#\b' . preg_quote($keyword, '#') . '\b#si', $text))
			{
				continue;
			}

			return true;
		}
	}

	public function passMetaKeywords($field = 'metakey', $keywords = '')
	{
		if (empty($this->params->meta_keywords))
		{
			return null;
		}

		if (!$keywords)
		{
			$item = $this->getItem($field);

			if (!isset($item->metakey) || empty($item->metakey))
			{
				return false;
			}

			$keywords = $item->metakey;
		}

		if (empty($keywords))
		{
			return false;
		}

		if (is_string($keywords))
		{
			$keywords = str_replace(' ', ',', $keywords);
		}

		$keywords = $this->makeArray($keywords);

		$this->params->meta_keywords = $this->makeArray($this->params->meta_keywords);

		foreach ($this->params->meta_keywords as $keyword)
		{
			if (!$keyword || !in_array(trim($keyword), $keywords))
			{
				continue;
			}

			return true;
		}
	}

	public function passAuthors($field = 'created_by', $author = '')
	{
		if (empty($this->params->authors))
		{
			return null;
		}

		if (!$author)
		{
			$item = $this->getItem($field);

			if (!isset($item->{$field}))
			{
				return false;
			}

			$author = $item->{$field};
		}

		if (empty($author))
		{
			return false;
		}

		$this->params->authors = $this->makeArray($this->params->authors);

		return in_array($author, $this->params->authors);
	}

	public function getItem($fields = array())
	{
		return null;
	}
}
