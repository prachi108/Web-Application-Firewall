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

class RLHtml
{
	static function selectlist(&$options, $name, $value, $id, $size = 0, $multiple = 0, $simple = 0)
	{
		if (empty($options))
		{
			return '<fieldset class="radio">' . JText::_('RL_NO_ITEMS_FOUND') . '</fieldset>';
		}

		require_once __DIR__ . '/parameters.php';
		$parameters = RLParameters::getInstance();
		$params     = $parameters->getPluginParams('regularlabs');

		if (!is_array($value))
		{
			$value = explode(',', $value);
		}

		if (count($value) == 1 && strpos($value['0'], ',') !== false)
		{
			$value = explode(',', $value['0']);
		}

		$count = 0;
		if ($options != -1)
		{
			foreach ($options as $option)
			{
				$count++;
				if (isset($option->links))
				{
					$count += count($option->links);
				}
				if ($count > $params->max_list_count)
				{
					break;
				}
			}
		}

		if ($options == -1 || $count > $params->max_list_count)
		{
			if (is_array($value))
			{
				$value = implode(',', $value);
			}
			if (!$value)
			{
				$input = '<textarea name="' . $name . '" id="' . $id . '" cols="40" rows="5">' . $value . '</textarea>';
			}
			else
			{
				$input = '<input type="text" name="' . $name . '" id="' . $id . '" value="' . $value . '" size="60">';
			}

			return '<fieldset class="radio"><label for="' . $id . '">' . JText::_('RL_ITEM_IDS') . ':</label>' . $input . '</fieldset>';
		}

		if (!$multiple)
		{
			$first_level = isset($options['0']->level) ? $options['0']->level : 0;
			foreach ($options as &$option)
			{
				if (!isset($option->level))
				{
					continue;
				}
				$repeat       = ($option->level - $first_level > 0) ? $option->level - $first_level : 0;
				$option->text = str_repeat(' - ', $repeat) . $option->text;
			}

			$html = JHtml::_('select.genericlist', $options, $name, 'class="inputbox"', 'value', 'text', $value);

			return self::handlePreparedStyles($html);
		}

		$size = (int) $size ?: 300;

		if ($simple)
		{
			$attr = 'style="width: ' . $size . 'px"';
			$attr .= $multiple ? ' multiple="multiple"' : '';

			$html = JHtml::_('select.genericlist', $options, $name, trim($attr), 'value', 'text', $value, $id);

			return self::handlePreparedStyles($html);
		}

		require_once __DIR__ . '/functions.php';

		RLFunctions::loadLanguage('com_modules', JPATH_ADMINISTRATOR);

		RLFunctions::script('regularlabs/multiselect.min.js');
		RLFunctions::stylesheet('regularlabs/multiselect.min.css');

		$html = array();

		$html[] = '<div class="well well-small rl_multiselect" id="' . $id . '">';
		$html[] = '
			<div class="form-inline rl_multiselect-controls">
				<span class="small">' . JText::_('JSELECT') . ':
					<a class="rl_multiselect-checkall" href="javascript:;">' . JText::_('JALL') . '</a>,
					<a class="rl_multiselect-uncheckall" href="javascript:;">' . JText::_('JNONE') . '</a>,
					<a class="rl_multiselect-toggleall" href="javascript:;">' . JText::_('RL_TOGGLE') . '</a>
				</span>
				<span class="width-20">|</span>
				<span class="small">' . JText::_('RL_EXPAND') . ':
					<a class="rl_multiselect-expandall" href="javascript:;">' . JText::_('JALL') . '</a>,
					<a class="rl_multiselect-collapseall" href="javascript:;">' . JText::_('JNONE') . '</a>
				</span>
				<span class="width-20">|</span>
				<span class="small">' . JText::_('JSHOW') . ':
					<a class="rl_multiselect-showall" href="javascript:;">' . JText::_('JALL') . '</a>,
					<a class="rl_multiselect-showselected" href="javascript:;">' . JText::_('RL_SELECTED') . '</a>
				</span>
				<span class="rl_multiselect-maxmin">
				<span class="width-20">|</span>
				<span class="small">
					<a class="rl_multiselect-maximize" href="javascript:;">' . JText::_('RL_MAXIMIZE') . '</a>
					<a class="rl_multiselect-minimize" style="display:none;" href="javascript:;">' . JText::_('RL_MINIMIZE') . '</a>
				</span>
				</span>
				<input type="text" name="rl_multiselect-filter" class="rl_multiselect-filter input-medium search-query pull-right" size="16"
					autocomplete="off" placeholder="' . JText::_('JSEARCH_FILTER') . '" aria-invalid="false" tabindex="-1">
			</div>

			<div class="clearfix"></div>

			<hr class="hr-condensed">';

		$o = array();
		foreach ($options as $option)
		{
			$option->level = isset($option->level) ? $option->level : 0;
			$o[]           = $option;
			if (isset($option->links))
			{
				foreach ($option->links as $link)
				{
					$link->level = $option->level + (isset($link->level) ? $link->level : 1);
					$o[]         = $link;
				}
			}
		}

		$html[]    = '<ul class="rl_multiselect-ul" style="max-height:300px;min-width:' . $size . 'px;overflow-x: hidden;">';
		$prevlevel = 0;

		foreach ($o as $i => $option)
		{
			if ($prevlevel < $option->level)
			{
				// correct wrong level indentations
				$option->level = $prevlevel + 1;

				$html[] = '<ul class="rl_multiselect-sub">';
			}
			else if ($prevlevel > $option->level)
			{
				$html[] = str_repeat('</li></ul>', $prevlevel - $option->level);
			}
			else if ($i)
			{
				$html[] = '</li>';
			}

			$labelclass = trim('pull-left ' . (isset($option->labelclass) ? $option->labelclass : ''));

			$html[] = '<li>';

			$item = '<div class="' . trim('rl_multiselect-item pull-left ' . (isset($option->class) ? $option->class : '')) . '">';
			if (isset($option->title))
			{
				$labelclass .= ' nav-header';
			}

			if (isset($option->title) && (!isset($option->value) || !$option->value))
			{
				$item .= '<label class="' . $labelclass . '">' . $option->title . '</label>';
			}
			else
			{
				$selected = in_array($option->value, $value) ? ' checked="checked"' : '';
				$disabled = (isset($option->disable) && $option->disable) ? ' readonly="readonly" style="visibility:hidden"' : '';

				$item .= '<input type="checkbox" class="pull-left" name="' . $name . '" id="' . $id . $option->value . '" value="' . $option->value . '"' . $selected . $disabled . '>
					<label for="' . $id . $option->value . '" class="' . $labelclass . '">' . $option->text . '</label>';
			}
			$item .= '</div>';
			$html[] = $item;

			if (!isset($o[$i + 1]) && $option->level > 0)
			{
				$html[] = str_repeat('</li></ul>', (int) $option->level);
			}
			$prevlevel = $option->level;
		}
		$html[] = '</ul>';
		$html[] = '
			<div style="display:none;" class="rl_multiselect-menu-block">
				<div class="pull-left nav-hover rl_multiselect-menu">
					<div class="btn-group">
						<a href="#" data-toggle="dropdown" class="dropdown-toggle btn btn-micro">
							<span class="caret"></span>
						</a>
						<ul class="dropdown-menu">
							<li class="nav-header">' . JText::_('COM_MODULES_SUBITEMS') . '</li>
							<li class="divider"></li>
							<li class=""><a class="checkall" href="javascript:;"><span class="icon-checkbox"></span> ' . JText::_('JSELECT') . '</a>
							</li>
							<li><a class="uncheckall" href="javascript:;"><span class="icon-checkbox-unchecked"></span> ' . JText::_('COM_MODULES_DESELECT') . '</a>
							</li>
							<div class="rl_multiselect-menu-expand">
								<li class="divider"></li>
								<li><a class="expandall" href="javascript:;"><span class="icon-plus"></span> ' . JText::_('RL_EXPAND') . '</a></li>
								<li><a class="collapseall" href="javascript:;"><span class="icon-minus"></span> ' . JText::_('RL_COLLAPSE') . '</a></li>
							</div>
						</ul>
					</div>
				</div>
			</div>';
		$html[] = '</div>';

		$html = implode('', $html);

		return self::handlePreparedStyles($html);
	}

	static function selectlistsimple(&$options, $name, $value, $id, $size = 0, $multiple = 0)
	{
		return self::selectlist($options, $name, $value, $id, $size, $multiple, 1);
	}

	static private function handlePreparedStyles($string)
	{
		$regex = '#>((?:\s*-\s*)*)\[\[\:(.*?)\:\]\]#si';

		if (@preg_match($regex . 'u', $string))
		{
			$regex .= 'u';
		}

		if (!preg_match($regex . 'u', $string))
		{
			return $string;
		}

		return preg_replace($regex, ' style="\2">\1', $string);
	}
}
