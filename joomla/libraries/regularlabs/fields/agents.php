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

class JFormFieldRL_Agents extends RLFormField
{
	public $type = 'Agents';

	protected function getInput()
	{
		$this->params = $this->element->attributes();

		$group = $this->get('group', 'os');

		if (!is_array($this->value))
		{
			$this->value = explode(',', $this->value);
		}

		$agents = array();
		switch ($group)
		{
			/* OS */
			case 'os':
				$agents[] = array('Windows (' . JText::_('JALL') . ')', 'Windows');
				$agents[] = array('Windows 10', 'Windows nt 10.0');
				$agents[] = array('Windows 8', 'Windows nt 6.2');
				$agents[] = array('Windows 7', 'Windows nt 6.1');
				$agents[] = array('Windows Vista', 'Windows nt 6.0');
				$agents[] = array('Windows Server 2003', 'Windows nt 5.2');
				$agents[] = array('Windows XP', 'Windows nt 5.1');
				$agents[] = array('Windows 2000 sp1', 'Windows nt 5.01');
				$agents[] = array('Windows 2000', 'Windows nt 5.0');
				$agents[] = array('Windows NT 4.0', 'Windows nt 4.0');
				$agents[] = array('Windows Me', 'Win 9x 4.9');
				$agents[] = array('Windows 98', 'Windows 98');
				$agents[] = array('Windows 95', 'Windows 95');
				$agents[] = array('Windows CE', 'Windows ce');
				$agents[] = array('Mac OS (' . JText::_('JALL') . ')', '#(Mac OS|Mac_PowerPC|Macintosh)#');
				$agents[] = array('Mac OSX (' . JText::_('JALL') . ')', 'Mac OS X');
				$agents[] = array('Mac OSX El Capitan', 'Mac OS X 10.11');
				$agents[] = array('Mac OSX Yosemite', 'Mac OS X 10.10');
				$agents[] = array('Mac OSX Mavericks', 'Mac OS X 10.9');
				$agents[] = array('Mac OSX Mountain Lion', 'Mac OS X 10.8');
				$agents[] = array('Mac OSX Lion', 'Mac OS X 10.7');
				$agents[] = array('Mac OSX Snow Leopard', 'Mac OS X 10.6');
				$agents[] = array('Mac OSX Leopard', 'Mac OS X 10.5');
				$agents[] = array('Mac OSX Tiger', 'Mac OS X 10.4');
				$agents[] = array('Mac OSX Panther', 'Mac OS X 10.3');
				$agents[] = array('Mac OSX Jaguar', 'Mac OS X 10.2');
				$agents[] = array('Mac OSX Puma', 'Mac OS X 10.1');
				$agents[] = array('Mac OSX Cheetah', 'Mac OS X 10.0');
				$agents[] = array('Mac OS (classic)', '#(Mac_PowerPC|Macintosh)#');
				$agents[] = array('Linux', '#(Linux|X11)#');
				$agents[] = array('Open BSD', 'OpenBSD');
				$agents[] = array('Sun OS', 'SunOS');
				$agents[] = array('QNX', 'QNX');
				$agents[] = array('BeOS', 'BeOS');
				$agents[] = array('OS/2', 'OS/2');
				break;

			/* Browsers */
			case 'browsers':
				if ($this->get('simple') && $this->get('simple') !== 'false')
				{

					$agents[] = array('Chrome', 'Chrome');
					$agents[] = array('Firefox', 'Firefox');
					$agents[] = array('Edge', 'Edge');
					$agents[] = array('Internet Explorer', 'MSIE');
					$agents[] = array('Opera', 'Opera');
					$agents[] = array('Safari', 'Safari');
					break;
				}

				$agents[] = array('Chrome (' . JText::_('JALL') . ')', 'Chrome');
				$agents[] = array('Chrome 51-60', '#Chrome/(5[1-9]|60)\.#');
				$agents[] = array('Chrome 41-50', '#Chrome/(4[1-9]|50)\.#');
				$agents[] = array('Chrome 31-40', '#Chrome/(3[1-9]|40)\.#');
				$agents[] = array('Chrome 21-30', '#Chrome/(2[1-9]|30)\.#');
				$agents[] = array('Chrome 11-20', '#Chrome/(1[1-9]|20)\.#');
				$agents[] = array('Chrome 1-10', '#Chrome/([1-9]|10)\.#');
				$agents[] = array('Firefox (' . JText::_('JALL') . ')', 'Firefox');
				$agents[] = array('Firefox 41-50', '#Firefox/(4[1-9]|50)\.#');
				$agents[] = array('Firefox 31-40', '#Firefox/(3[1-9]|40)\.#');
				$agents[] = array('Firefox 21-30', '#Firefox/(2[1-9]|30)\.#');
				$agents[] = array('Firefox 11-20', '#Firefox/(1[1-9]|20)\.#');
				$agents[] = array('Firefox 1-10', '#Firefox/([1-9]|10)\.#');
				$agents[] = array('Edge (' . JText::_('JALL') . ')', 'Edge');
				$agents[] = array('Edge 12', 'Edge/12');
				$agents[] = array('Internet Explorer (' . JText::_('JALL') . ')', 'MSIE');
				$agents[] = array('Internet Explorer Edge', 'MSIE Edge'); // missing MSIE is added to agent string in assingnments/agents.php
				$agents[] = array('Internet Explorer 11', 'MSIE 11'); // missing MSIE is added to agent string in assingnments/agents.php
				$agents[] = array('Internet Explorer 10.6', 'MSIE 10.6');
				$agents[] = array('Internet Explorer 10.0', 'MSIE 10.0');
				$agents[] = array('Internet Explorer 10', 'MSIE 10.');
				$agents[] = array('Internet Explorer 9', 'MSIE 9.');
				$agents[] = array('Internet Explorer 8', 'MSIE 8.');
				$agents[] = array('Internet Explorer 7', 'MSIE 7.');
				$agents[] = array('Internet Explorer 1-6', '#MSIE [1-6]\.#');
				$agents[] = array('Opera (' . JText::_('JALL') . ')', 'Opera');
				$agents[] = array('Opera 31-40', '#Opera/(3[1-9]|40)\.#');
				$agents[] = array('Opera 21-30', '#Opera/(2[1-9]|30)\.#');
				$agents[] = array('Opera 11-20', '#Opera/(1[1-9]|20)\.#');
				$agents[] = array('Opera 1-10', '#Opera/([1-9]|10)\.#');
				$agents[] = array('Safari (' . JText::_('JALL') . ')', 'Safari');
				//$agents[] = array('Safari 10', '#Version/10\..*Safari/#');
				$agents[] = array('Safari 9', '#Version/9\..*Safari/#');
				$agents[] = array('Safari 8', '#Version/8\..*Safari/#');
				$agents[] = array('Safari 7', '#Version/7\..*Safari/#');
				$agents[] = array('Safari 6', '#Version/6\..*Safari/#');
				$agents[] = array('Safari 5', '#Version/5\..*Safari/#');
				$agents[] = array('Safari 4', '#Version/4\..*Safari/#');
				$agents[] = array('Safari 1-3', '#Version/[1-3]\..*Safari/#');
				break;

			/* Mobile browsers */
			case 'mobile':
				$agents[] = array(JText::_('JALL'), 'mobile');
				$agents[] = array('Android', 'Android');
				$agents[] = array('Android Chrome', '#Android.*Chrome#');
				$agents[] = array('Blackberry', 'Blackberry');
				$agents[] = array('IE Mobile', 'IEMobile');
				$agents[] = array('iPad', 'iPad');
				$agents[] = array('iPhone', 'iPhone');
				$agents[] = array('iPod Touch', 'iPod');
				$agents[] = array('NetFront', 'NetFront');
				$agents[] = array('Nokia', 'NokiaBrowser');
				$agents[] = array('Opera Mini', 'Opera Mini');
				$agents[] = array('Opera Mobile', 'Opera Mobi');
				$agents[] = array('UC Browser', 'UC Browser');
				break;
		}

		$options = array();
		foreach ($agents as $agent)
		{
			$option    = JHtml::_('select.option', $agent['1'], $agent['0']);
			$options[] = $option;
		}

		$size = (int) $this->get('size');

		require_once dirname(__DIR__) . '/helpers/html.php';

		return RLHtml::selectlistsimple($options, $this->name, $this->value, $this->id, $size, 1);
	}
}
