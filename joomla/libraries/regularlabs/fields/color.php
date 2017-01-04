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

jimport('joomla.form.formfield');

require_once dirname(__DIR__) . '/helpers/functions.php';

class JFormFieldRL_Color extends JFormField
{
	public $type = 'Color';

	protected function getInput()
	{
		$field = new RLFieldColor;

		return $field->getInput($this->name, $this->id, $this->value, $this->element->attributes());
	}
}

class RLFieldColor
{
	function getInput($name, $id, $value, $params)
	{
		$this->name   = $name;
		$this->id     = $id;
		$this->value  = $value;
		$this->params = $params;

		$class    = trim('rl_color minicolors ' . $this->get('class'));
		$disabled = $this->get('disabled') ? ' disabled="disabled"' : '';

		RLFunctions::script('regularlabs/color.min.js');
		RLFunctions::stylesheet('regularlabs/color.min.css');

		$this->value = strtolower(strtoupper(preg_replace('#[^a-z0-9]#si', '', $this->value)));

		return '<input type="text" name="' . $this->name . '" id="' . $this->id . '" class="' . $class . '" value="' . $this->value . '"' . $disabled . '>';
	}

	private function get($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}
