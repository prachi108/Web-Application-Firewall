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

require_once dirname(__DIR__) . '/helpers/groupfield.php';

class JFormFieldRL_Zoo extends RLFormGroupField
{
	public $type = 'Zoo';

	protected function getInput()
	{
		if ($error = $this->missingFilesOrTables(array('applications' => 'application', 'categories' => 'category', 'items' => 'item')))
		{
			return $error;
		}

		return $this->getSelectList();
	}

	function getCategories()
	{
		$query = $this->db->getQuery(true)
			->select('COUNT(c.id)')
			->from('#__zoo_category AS c')
			->where('c.published > -1');
		$this->db->setQuery($query);
		$total = $this->db->loadResult();

		if ($total > $this->max_list_count)
		{
			return -1;
		}

		$options = array();
		if ($this->get('show_ignore'))
		{
			if (in_array('-1', $this->value))
			{
				$this->value = array('-1');
			}
			$options[] = JHtml::_('select.option', '-1', '- ' . JText::_('RL_IGNORE') . ' -', 'value', 'text', 0);
			$options[] = JHtml::_('select.option', '-', '&nbsp;', 'value', 'text', 1);
		}

		$query->clear()
			->select('a.id, a.name')
			->from('#__zoo_application AS a')
			->order('a.name, a.id');
		$this->db->setQuery($query);
		$apps = $this->db->loadObjectList();

		foreach ($apps as $i => $app)
		{
			$query->clear()
				->select('c.id, c.parent AS parent_id, c.name AS title, c.published')
				->from('#__zoo_category AS c')
				->where('c.application_id = ' . (int) $app->id)
				->where('c.published > -1')
				->order('c.ordering, c.name');
			$this->db->setQuery($query);
			$items = $this->db->loadObjectList();

			if ($i)
			{
				$options[] = JHtml::_('select.option', '-', '&nbsp;', 'value', 'text', 1);
			}

			// establish the hierarchy of the menu
			// TODO: use node model
			$children = array();

			if ($items)
			{
				// first pass - collect children
				foreach ($items as $v)
				{
					$pt   = $v->parent_id;
					$list = @$children[$pt] ? $children[$pt] : array();
					array_push($list, $v);
					$children[$pt] = $list;
				}
			}

			// second pass - get an indent list of the items
			$list = JHtml::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);

			// assemble items to the array
			$options[] = JHtml::_('select.option', 'app' . $app->id, '[' . $app->name . ']', 'value', 'text', 0);
			foreach ($list as $item)
			{
				$item->treename = '  ' . str_replace('&#160;&#160;- ', '  ', $item->treename);
				$item->treename = RLText::prepareSelectItem($item->treename, $item->published);
				$option         = JHtml::_('select.option', $item->id, $item->treename, 'value', 'text', 0);
				$option->level  = 1;
				$options[]      = $option;
			}
		}

		return $options;
	}

	function getItems()
	{
		$query = $this->db->getQuery(true)
			->select('COUNT(i.id)')
			->from('#__zoo_item AS i')
			->where('i.state > -1');
		$this->db->setQuery($query);
		$total = $this->db->loadResult();

		if ($total > $this->max_list_count)
		{
			return -1;
		}

		$query->clear('select')
			->select('i.id, i.name, a.name as cat, i.state as published')
			->join('LEFT', '#__zoo_application AS a ON a.id = i.application_id')
			->group('i.id')
			->order('i.name, i.priority, i.id');
		$this->db->setQuery($query);
		$list = $this->db->loadObjectList();

		return $this->getOptionsByList($list, array('cat', 'id'));
	}
}
