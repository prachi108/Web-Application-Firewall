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

require_once dirname(__DIR__) . '/helpers/functions.php';
require_once dirname(__DIR__) . '/helpers/field.php';
require_once dirname(__DIR__) . '/helpers/html.php';

class JFormFieldRL_SimpleCategories extends RLFormField
{
	public $type = 'SimpleCategories';

	protected function getInput()
	{
		JHtml::_('jquery.framework');
		RLFunctions::script('regularlabs/script.min.js');
		RLFunctions::script('regularlabs/toggler.min.js');

		$this->params = $this->element->attributes();

		$size = (int) $this->get('size');
		$attr = $this->get('onchange') ? ' onchange="' . $this->get('onchange') . '"' : '';

		$categories = $this->getOptions();
		$options    = parent::getOptions();

		if ($this->get('show_none', 1))
		{
			$options[] = JHtml::_('select.option', '', '- ' . JText::_('JNONE') . ' -', 'value', 'text');
		}

		if ($this->get('show_new', 1))
		{
			$options[] = JHtml::_('select.option', '-1', '- ' . JText::_('RL_NEW_CATEGORY') . ' -', 'value', 'text', false);
		}

		$options = array_merge($options, $categories);

		if (!$this->get('show_new', 1))
		{
			return JHtml::_('select.genericlist',
				$options,
				$this->name,
				trim($attr),
				'value',
				'text',
				$this->value,
				$this->id
			);
		}

		RLFunctions::script('regularlabs/simplecategories.min.js');

		$html = array();

		$html[] = '<div class="rl_simplecategory">';

		$html[] = '<input type="hidden" class="rl_simplecategory_value" id="' . $this->id . '" name="' . $this->name . '" value="' . $this->value . '" checked="checked">';

		$html[] = '<div class="rl_simplecategory_select">';
		$html[] = RLHtml::selectlistsimple(
			$options,
			$this->getName($this->fieldname . '_select'),
			$this->value,
			$this->getId('', $this->fieldname . '_select'),
			$size,
			false
		);
		$html[] = '</div>';

		$html[] = '<div id="' . rand(1000000, 9999999) . '___' . $this->fieldname . '_select.-1" class="rl_toggler rl_toggler_nofx" style="display:none;">';
		$html[] = '<div class="rl_simplecategory_new">';
		$html[] = '<input type="text" id="' . $this->id . '_new" value="" placeholder="' . JText::_('RL_NEW_CATEGORY_ENTER') . '">';
		$html[] = '</div>';
		$html[] = '</div>';

		$html[] = '</div>';

		return implode('', $html);
	}

	protected function getOptions()
	{
		$table = $this->get('table');

		if (!$table)
		{
			return array();
		}

		// Get the user groups from the database.
		$query = $this->db->getQuery(true)
			->select(array(
				$this->db->quoteName('category', 'value'),
				$this->db->quoteName('category', 'text'),
			))
			->from($this->db->quoteName('#__' . $table))
			->where($this->db->quoteName('category') . ' != ' . $this->db->quote(''))
			->group($this->db->quoteName('category'))
			->order($this->db->quoteName('category') . ' ASC');
		$this->db->setQuery($query);

		return $this->db->loadObjectList();
	}
}
