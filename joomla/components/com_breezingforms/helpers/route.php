<?php
/**
 * BreezingForms - A Joomla Forms Application
 * @version 1.8
 * @package BreezingForms
 * @copyright (C) 2008-2012 by Markus Bopp
 * @license Released under the terms of the GNU General Public License
 **/

defined('_JEXEC') or die;

abstract class BreezingformsHelperRoute
{
	protected static $lookup = array();

	public static function getFormRoute($id)
	{
            $found_menu = '&found_menu=false';
            $itemid = JRequest::getVar('Itemid', 0);
            $the_id = explode(':', $id);
            $menu = 'Itemid';
            if(JFactory::getConfig()->get('sef')){
                $menu = 'menuitemid';
                $db = JFactory::getDbo();
                $db->setQuery("Select `name` From #__facileforms_forms Where id = " . intval($the_id[0]));
                $formname = $db->loadResult();
                if($formname){
                    $db->setQuery("Select id From #__menu Where published = 1 And link = 'index.php?option=com_breezingforms&view=form' And ( params Like ".$db->Quote('%ff_com_name='.$formname.'%')." Or params Like ".$db->Quote('%"ff_com_name":"'.$formname.'"%')." )");
                    $_itemid = $db->loadResult();
                    if($_itemid){
                       $itemid = $_itemid;
                       $menu = 'Itemid';
                       $found_menu = '&found_menu=true';
                    }
                }
            }
            return 'index.php?option=com_breezingforms&ff_form='.$the_id[0].'&title='.$the_id[1].'&'.$menu.'='.$itemid.'&ff_applic=com_tags'.$found_menu;
	}
}
