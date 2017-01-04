<?php
/**
 * @package     BreezingForms
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

if(!defined('DS')){
    define('DS', DIRECTORY_SEPARATOR);
}

class com_breezingformsInstallerScript
{
        /**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent) 
	{
            // adjust component menu
            jimport('joomla.version');
            $version = new JVersion();

            if (version_compare($version->getShortVersion(), '3.0', '>=')) {

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
            }
	}
        
        /**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent) 
	{
        }
 
	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent) 
	{
            
            jimport('joomla.filesystem.file');

    
            jimport('joomla.version');
            $version = new JVersion();

            if(version_compare($version->getShortVersion(), '3.0', '>=')){
                $db = JFactory::getDBO();
                $db->setQuery("Delete From #__menu Where `link` Like 'index.php?option=com_breezingforms&act=%'");
                $db->query();
                $db->setQuery("Delete From #__menu Where `alias` Like 'BreezingForms' And `path` Like 'breezingforms'");
                $db->query();
            }

            if(JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'facileforms.config.php')){
                JFile::delete(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'facileforms.config.php');
            }

            if (JFile::exists(JPATH_SITE . "/components/com_sh404sef/sef_ext/com_breezingforms.php")){
                JFile::delete(JPATH_SITE . "/components/com_sh404sef/sef_ext/com_breezingforms.php");
            }

            if(JFile::exists(JPATH_SITE . '/ff_secimage.php'))JFile::delete( JPATH_SITE . '/ff_secimage.php');
            if(JFile::exists(JPATH_SITE . '/templates/system/ff_secimage.php'))JFile::delete( JPATH_SITE . '/templates/system/ff_secimage.php');
            if(JFile::exists(JPATH_SITE . "/administrator/components/com_joomfish/contentelements/breezingforms_elements.xml"))JFile::delete( JPATH_SITE . "/administrator/components/com_joomfish/contentelements/breezingforms_elements.xml");
            if(JFile::exists(JPATH_SITE . "/administrator/components/com_joomfish/contentelements/translationFformFilter.php"))JFile::delete( JPATH_SITE . "/administrator/components/com_joomfish/contentelements/translationFformFilter.php");
            if(JFile::exists(JPATH_SITE . "/administrator/components/com_joomfish/contentelements/translationFformoptions_emptyFilter.php"))JFile::delete( JPATH_SITE . "/administrator/components/com_joomfish/contentelements/translationFformoptions_emptyFilter.php");


            $db = JFactory::getDBO();
            $db->setQuery("Select id From `#__menu` Where `alias` = 'root'");
            if(!$db->loadResult()){
                $db->setQuery("INSERT INTO `#__menu` VALUES(1, '', 'Menu_Item_Root', 'root', '', '', '', '', 1, 0, 0, 0, 0, '0000-00-00 00:00:00', 0, 0, '', 0, '', 0, ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ), 0, '*', 0)");
                $db->query();
            }
	}
 
	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent) 
	{
            $db = JFactory::getDBO();
            $db->setQuery("Select id From `#__menu` Where `alias` = 'root'");
            if(!$db->loadResult()){
                $db->setQuery("INSERT INTO `#__menu` VALUES(1, '', 'Menu_Item_Root', 'root', '', '', '', '', 1, 0, 0, 0, 0, '0000-00-00 00:00:00', 0, 0, '', 0, '', 0, ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ), 0, '*', 0)");
                $db->query();
            }
	}
 
	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent) 
	{
            $db = JFactory::getDBO();
            
            $db->setQuery("Select id From `#__menu` Where `alias` = 'root'");
            if(!$db->loadResult()){
                $db->setQuery("INSERT INTO `#__menu` VALUES(1, '', 'Menu_Item_Root', 'root', '', '', '', '', 1, 0, 0, 0, 0, '0000-00-00 00:00:00', 0, 0, '', 0, '', 0, ( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ), 0, '*', 0)");
                $db->query();
            }
            
            $db->setQuery("Select update_site_id From #__update_sites Where `name` = 'BreezingForms' And `type` = 'extension'");
            $site_id = $db->loadResult();
            
            if( $site_id ){
                
                $db->setQuery("Delete From #__update_sites Where update_site_id = " . $db->quote($site_id));
                $db->execute();
                $db->setQuery("Delete From #__update_sites_extensions Where update_site_id = " . $db->quote($site_id));
                $db->execute();
                $db->setQuery("Delete From #__updates Where update_site_id = " . $db->quote($site_id));
                $db->execute();
            }
	}
}

