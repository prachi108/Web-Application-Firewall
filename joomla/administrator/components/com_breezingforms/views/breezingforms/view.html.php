<?php
/**
 * @package     BreezingForms
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

defined('_JEXEC') or die;

class BreezingformsViewBreezingforms extends JViewLegacy
{
	protected $modules = null;

	public function display($tpl = null)
	{
                
                JToolbarHelper::title('BreezingForms');
                JFactory::getDocument()->setTitle("BreezingForms");
                parent::display($tpl);
	}
}
