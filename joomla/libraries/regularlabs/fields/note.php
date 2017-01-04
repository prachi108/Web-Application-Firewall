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

require_once dirname(__DIR__) . '/helpers/text.php';
JFormHelper::loadFieldClass('note');

class JFormFieldRL_Note extends JFormFieldNote
{
	public $type = 'Note';

	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$this->element = $element;

		$element['label']                = $this->prepareText($element['label']);
		$element['description']          = $this->prepareText($element['description']);
		$element['translateDescription'] = false;

		return parent::setup($element, $value, $group);
	}

	public function prepareText($string = '')
	{
		$string = trim($string);

		if ($string == '')
		{
			return '';
		}

		switch (true)
		{
			// Old fields using var attributes
			case (JText::_($this->get('var1'))):
				$string = $this->sprintf_old($string);
				break;

			// sprintf format (comma separated)
			case (strpos($string, ',') !== false):
				$string = $this->sprintf($string);
				break;

			// Normal language string
			default:
				$string = JText::_($string);
		}

		return $this->fixLanguageStringSyntax($string);
	}

	public function fixLanguageStringSyntax($string = '')
	{
		$string = trim(RLText::html_entity_decoder($string));
		$string = str_replace('&quot;', '"', $string);
		$string = str_replace('span style="font-family:monospace;"', 'span class="rl_code"', $string);

		return $string;
	}

	public function sprintf($string = '')
	{
		$string = trim($string);

		if (strpos($string, ',') === false)
		{
			return $string;
		}

		$string_parts = explode(',', $string);
		$first_part   = array_shift($string_parts);

		if ($first_part !== strtoupper($first_part))
		{
			return $string;
		}

		$first_part = preg_replace('/\[\[%([0-9]+):[^\]]*\]\]/', '%\1$s', JText::_($first_part));

		array_walk($string_parts, 'JFormFieldRL_Note::jText');

		return vsprintf($first_part, $string_parts);
	}

	public function jText(&$string)
	{
		$string = JText::_($string);
	}

	public function sprintf_old($string = '')
	{
		// variables
		$var1 = JText::_($this->get('var1'));
		$var2 = JText::_($this->get('var2'));
		$var3 = JText::_($this->get('var3'));
		$var4 = JText::_($this->get('var4'));
		$var5 = JText::_($this->get('var5'));

		return JText::sprintf(JText::_(trim($string)), $var1, $var2, $var3, $var4, $var5);
	}

	private function get($val, $default = '')
	{
		if (!isset($this->params[$val]) || (string) $this->params[$val] == '')
		{
			return $default;
		}

		return (string) $this->params[$val];
	}
}
