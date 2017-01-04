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

class JFormFieldRL_AccessLevel extends RLFormField
{
	public $type = 'AccessLevel';

	protected function getInput()
	{
		$this->params = $this->element->attributes();

		$size      = (int) $this->get('size');
		$multiple  = $this->get('multiple');
		$show_all  = $this->get('show_all');
		$use_names = $this->get('use_names');

		$options = $this->getAccessLevels($use_names);

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

	protected function getAccessLevels($use_names = false)
	{
		$value = $use_names ? 'a.title' : 'a.id';

		$query = $this->db->getQuery(true)
			->select($value . ' as value, a.title as text')
			->from('#__viewlevels AS a')
			->group('a.id')
			->order('a.ordering ASC');
		$this->db->setQuery($query);

		return $this->db->loadObjectList();
	}
}
