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

require_once dirname(__DIR__) . '/helpers/field.php';

class JFormFieldRL_GroupLevel extends RLFormField
{
	public $type = 'GroupLevel';

	protected function getInput()
	{
		$this->params = $this->element->attributes();

		$size      = (int) $this->get('size');
		$multiple  = $this->get('multiple');
		$show_all  = $this->get('show_all');
		$use_names = $this->get('use_names');

		$options = $this->getUserGroups($use_names);

		if ($show_all)
		{
			$option          = new stdClass;
			$option->value   = -1;
			$option->text    = '- ' . JText::_('JALL') . ' -';
			$option->disable = '';
			array_unshift($options, $option);
		}

		require_once dirname(__DIR__) . '/helpers/html.php';

		return RLHtml::selectlist($options, $this->name, $this->value, $this->id, $size, $multiple);
	}

	protected function getUserGroups($use_names = false)
	{
		$value = $use_names ? 'a.title' : 'a.id';

		$query = $this->db->getQuery(true)
			->select($value . ' as value, a.title as text, a.parent_id AS parent')
			->from('#__usergroups AS a')
			->select('COUNT(DISTINCT b.id) AS level')
			->join('LEFT', '#__usergroups AS b ON a.lft > b.lft AND a.rgt < b.rgt')
			->group('a.id')
			->order('a.lft ASC');
		$this->db->setQuery($query);

		return $this->db->loadObjectList();
	}
}
