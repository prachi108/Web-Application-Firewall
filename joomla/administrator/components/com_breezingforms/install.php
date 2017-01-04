<?php
/**
* BreezingForms - A Joomla Forms Application
* @version 1.8
* @package BreezingForms
* @copyright (C) 2008-2012 by Markus Bopp
* @license Released under the terms of the GNU General Public License
**/
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

function com_install(){

    if(!version_compare(PHP_VERSION, '5.1.2', '>=')){
 
	echo '<b style="color:red">WARNING: YOU ARE RUNNING PHP VERSION "'.PHP_VERSION.'". BREEZINGFORMS WON\'T WORK WITH THIS VERSION. PLEASE UPGRADE TO AT LEAST PHP 5.1.2, SORRY BUT YOU BETTER UNINSTALL THIS COMPONENT NOW!</b>';
    }
    
    // adjust component menu
    jimport('joomla.version');
    $version = new JVersion();

    if (version_compare($version->getShortVersion(), '1.6', '>=')) {

        JFactory::getDBO()->setQuery(
                "update #__menu set `alias` = 'BreezingForms' " .
                "where `link`='index.php?option=com_breezingforms'"
        );
        JFactory::getDBO()->query();
        JFactory::getDBO()->setQuery(
                "update #__menu set `alias` = 'Manage Records', img='components/com_breezingforms/images/js/ThemeOffice/checkin.png' " .
                "where `link`='index.php?option=com_breezingforms&act=managerecs'"
        );
        JFactory::getDBO()->query();
        JFactory::getDBO()->setQuery(
                "update #__menu set `alias` = 'Manage Backend Menus', img='components/com_breezingforms/images/js/ThemeOffice/mainmenu.png' " .
                "where `link`='index.php?option=com_breezingforms&act=managemenus'"
        );
        JFactory::getDBO()->query();
        JFactory::getDBO()->setQuery(
                "update #__menu set `alias` = 'Manage Forms', img='components/com_breezingforms/images/js/ThemeOffice/content.png' " .
                "where `link`='index.php?option=com_breezingforms&act=manageforms'"
        );
        JFactory::getDBO()->query();
        JFactory::getDBO()->setQuery(
                "update #__menu set `alias` = 'Manage Scripts', img='components/com_breezingforms/images/js/ThemeOffice/controlpanel.png' " .
                "where `link`='index.php?option=com_breezingforms&act=managescripts'"
        );
        JFactory::getDBO()->query();
        JFactory::getDBO()->setQuery(
                "update #__menu set `alias` = 'Manage Pieces', img='components/com_breezingforms/images/js/ThemeOffice/controlpanel.png' " .
                "where `link`='index.php?option=com_breezingforms&act=managepieces'"
        );
        JFactory::getDBO()->query();
        JFactory::getDBO()->setQuery(
                "update #__menu set `alias` = 'Integrator', img='components/com_breezingforms/images/js/ThemeOffice/controlpanel.png' " .
                "where `link`='index.php?option=com_breezingforms&act=integrate'"
        );
        JFactory::getDBO()->query();
        JFactory::getDBO()->setQuery(
                "update #__menu set `alias` = 'Configuration', img='components/com_breezingforms/images/js/ThemeOffice/config.png' " .
                "where `link`='index.php?option=com_breezingforms&act=configuration'"
        );
        JFactory::getDBO()->query();
    } else {

        JFactory::getDBO()->setQuery("update #__components set admin_menu_link='' where `option`='com_breezingforms' and parent=0");
        JFactory::getDBO()->query();

        // assign nice icons to facileforms
        JFactory::getDBO()->setQuery(
                "update #__components set admin_menu_img='components/com_breezingforms/images/js/ThemeOffice/checkin.png' " .
                "where admin_menu_link='option=com_breezingforms&act=managerecs'"
        );
        JFactory::getDBO()->query();
        JFactory::getDBO()->setQuery(
                "update #__components set admin_menu_img='components/com_breezingforms/images/js/ThemeOffice/mainmenu.png' " .
                "where admin_menu_link='option=com_breezingforms&act=managemenus'"
        );
        JFactory::getDBO()->query();
        JFactory::getDBO()->setQuery(
                "update #__components set admin_menu_img='components/com_breezingforms/images/js/ThemeOffice/content.png' " .
                "where admin_menu_link='option=com_breezingforms&act=manageforms'"
        );
        JFactory::getDBO()->query();
        JFactory::getDBO()->setQuery(
                "update #__components set admin_menu_img='components/com_breezingforms/images/js/ThemeOffice/controlpanel.png' " .
                "where admin_menu_link='option=com_breezingforms&act=managescripts'"
        );
        JFactory::getDBO()->query();
        JFactory::getDBO()->setQuery(
                "update #__components set admin_menu_img='components/com_breezingforms/images/js/ThemeOffice/controlpanel.png' " .
                "where admin_menu_link='option=com_breezingforms&act=managepieces'"
        );
        JFactory::getDBO()->query();
        JFactory::getDBO()->setQuery(
                "update #__components set admin_menu_img='components/com_breezingforms/images/js/ThemeOffice/config.png' " .
                "where admin_menu_link='option=com_breezingforms&act=integrate'"
        );
        JFactory::getDBO()->query();
        JFactory::getDBO()->setQuery(
                "update #__components set admin_menu_img='components/com_breezingforms/images/js/ThemeOffice/config.png' " .
                "where admin_menu_link='option=com_breezingforms&act=configuration'"
        );
        JFactory::getDBO()->query();

        // fix broken menuitems
        JFactory::getDBO()->setQuery(
                "select min(id) from #__components " .
                "where `parent`=0 and `option`='com_breezingforms'"
        );
        $id = JFactory::getDBO()->loadResult();
        if ($id)
            JFactory::getDBO()->setQuery(
                    "update #__menu " .
                    "set componentid=" . JFactory::getDBO()->Quote($id) . ", link='index.php?option=com_breezingforms' " .
                    "where type='components' and params like 'ff_com_name=%'"
            );
            JFactory::getDBO()->query();
    }
    
}