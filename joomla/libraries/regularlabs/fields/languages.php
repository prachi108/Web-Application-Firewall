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

class JFormFieldRL_Languages extends RLFormField
{
	public $type = 'Languages';

	protected function getInput()
	{
		$this->params = $this->element->attributes();

		$size     = (int) $this->get('size');
		$multiple = $this->get('multiple');
		$client   = $this->get('client', 'SITE');

		jimport('joomla.language.helper');
		$langs   = JLanguageHelper::createLanguageList($this->value, constant('JPATH_' . strtoupper($client)), true);
		$options = array();

		foreach ($langs as $lang)
		{
			if (!$lang['value'])
			{
				continue;
			}

			$option        = new stdClass;
			$option->value = $lang['value'];
			$option->text  = $lang['text'] . ' [' . $lang['value'] . ']';
			$options[]     = $option;
		}

		require_once dirname(__DIR__) . '/helpers/html.php';

		return RLHtml::selectlistsimple($options, $this->name, $this->value, $this->id, $size, $multiple);
	}
}
