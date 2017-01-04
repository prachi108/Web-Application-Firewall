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

class JFormFieldRL_RedShop extends RLFormGroupField
{
	public $type = 'RedShop';

	protected function getInput()
	{
		if ($error = $this->missingFilesOrTables(array('categories' => 'category', 'products' => 'product')))
		{
			return $error;
		}

		return $this->getSelectList();
	}

	function getCategories()
	{
		$query = $this->db->getQuery(true)
			->select('COUNT(c.category_id)')
			->from('#__redshop_category AS c')
			->where('c.published > -1');
		$this->db->setQuery($query);
		$total = $this->db->loadResult();

		if ($total > $this->max_list_count)
		{
			return -1;
		}

		$query->clear('select')
			->select('c.category_id as id, x.category_parent_id AS parent_id, c.category_name AS title, c.published')
			->join('LEFT', '#__redshop_category_xref AS x ON x.category_child_id = c.category_id')
			->group('c.category_id')
			->order('c.ordering, c.category_name');
		$this->db->setQuery($query);
		$items = $this->db->loadObjectList();

		return $this->getOptionsTreeByList($items);
	}

	function getProducts()
	{
		$query = $this->db->getQuery(true)
			->select('COUNT(p.product_id)')
			->from('#__redshop_product AS p')
			->where('p.published > -1');
		$this->db->setQuery($query);
		$total = $this->db->loadResult();

		if ($total > $this->max_list_count)
		{
			return -1;
		}

		$query->clear('select')
			->select('p.product_id as id, p.product_name AS name, p.product_number as number, c.category_name AS cat, p.published')
			->join('LEFT', '#__redshop_product_category_xref AS x ON x.product_id = p.product_id')
			->join('LEFT', '#__redshop_category AS c ON c.category_id = x.category_id')
			->group('p.product_id')
			->order('p.product_name, p.product_number');
		$this->db->setQuery($query);
		$list = $this->db->loadObjectList();

		return $this->getOptionsByList($list, array('number', 'cat'));
	}
}
