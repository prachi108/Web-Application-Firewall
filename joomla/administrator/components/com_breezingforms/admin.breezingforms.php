<?php
/**
* BreezingForms - A Joomla Forms Application
* @version 1.8
* @package BreezingForms
* @copyright (C) 2008-2012 by Markus Bopp
* @license Released under the terms of the GNU General Public License
**/
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

if(!function_exists('bf_b64enc')){
    
    function bf_b64enc($str){
        $base = 'base';
        $sixty_four = '64_encode';
        return call_user_func($base.$sixty_four, $str);
    }

}

if(!function_exists('bf_b64dec')){
    function bf_b64dec($str){
        $base = 'base';
        $sixty_four = '64_decode';
        return call_user_func($base.$sixty_four, $str);
    }
}

require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_breezingforms'.DS.'libraries'.DS.'crosstec'.DS.'classes'.DS.'BFJoomlaConfig.php');

jimport('joomla.version');
$version = new JVersion();

function bf_getTableFields($tables, $typeOnly = true)
{
        jimport('joomla.version');
        $version = new JVersion();
        
        if(version_compare($version->getShortVersion(), '3.0', '<')){
           return JFactory::getDBO()->getTableFields($tables); 
        }
        
        $results = array();

        settype($tables, 'array');

        foreach ($tables as $table)
        {
            try{
                $results[$table] = JFactory::getDbo()->getTableColumns($table, $typeOnly);
            }catch(Exception $e){  }
        }

        return $results;
}

$option = JRequest::getCmd('option');
$task = JRequest::getCmd('task');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');


if(version_compare($version->getShortVersion(), '1.6', '>=')){

    if ( !JFactory::getUser()->authorise('core.manage', 'com_breezingforms')) 
    {
        return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
    }
}

// ffexports / packages cleanup
// SECURITY UPDATE 2016-02-16

// purge ajax save
$sourcePath = JPATH_SITE . DS . 'components' . DS . 'com_breezingforms' . DS . 'exports'.DS;
if (@file_exists($sourcePath) && @is_readable($sourcePath) && @is_dir($sourcePath) && $handle = @opendir($sourcePath)) {
    while (false !== ($file = @readdir($handle))) {
        if($file!="." && $file!=".."&& $file!="index.html") {
            @JFile::delete($sourcePath.$file);
        }
    }
    @closedir($handle);
}

$sourcePath = JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_breezingforms' . DS . 'packages'.DS;
if (@file_exists($sourcePath) && @is_readable($sourcePath) && @is_dir($sourcePath) && $handle = @opendir($sourcePath)) {
    while (false !== ($file = @readdir($handle))) {
        if($file!="." && $file!=".." && $file!="index.html" && $file!="stdlib.english.xml") {
            @JFile::delete($sourcePath.$file);
        }
    }
    @closedir($handle);
}

// SECURITY UPDATE END

// 1.7.5 to 1.8 cleanup

if(JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_breezingforms'.DS.'install.secimage.php')){
    JFile::delete(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_breezingforms'.DS.'install.secimage.php');
}

if(JFile::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_breezingforms'.DS.'uninstall.secimage.php')){
    JFile::delete(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_breezingforms'.DS.'uninstall.secimage.php');
}

if(!JFolder::exists(JPATH_SITE.DS.'media'.DS.'breezingforms')){
    JFolder::create(JPATH_SITE.DS.'media'.DS.'breezingforms');
}

if(!JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'index.html')){
    JFile::copy(
            JPATH_SITE.DS.'components'.DS.'com_breezingforms'.DS.'index.html', 
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'index.html'
    );
}

#### MAIL TEMPLATES

if(!JFolder::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'mailtpl')){
    JFolder::copy(
            JPATH_ADMINISTRATOR.DS.'components'.DS.'com_breezingforms'.DS.'mailtpl'.DS, 
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'mailtpl'.DS
    );
}

#### PDF TEMPLATES

if(!JFolder::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'pdftpl')){
    JFolder::copy(
            JPATH_ADMINISTRATOR.DS.'components'.DS.'com_breezingforms'.DS.'pdftpl'.DS, 
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'pdftpl'.DS
    );
}

JFolder::create(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'pdftpl'.DS.'fonts');

#### DOWNLOAD TEMPLATES

if(!JFolder::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'downloadtpl')){
    JFolder::copy(
            JPATH_SITE.DS.'components'.DS.'com_breezingforms'.DS.'downloadtpl'.DS, 
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'downloadtpl'.DS
    );
}

#### PAYMENT CACHE

if(!JFolder::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'payment_cache')){
    JFolder::create(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'payment_cache');
    
}

if(!JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'payment_cache'.DS.'.htaccess')){
    $def = 'deny from all';
    JFile::write(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'payment_cache'.DS.'.htaccess', $def);
}



#### UPLOADS

if(!JFolder::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'uploads')){
    JFolder::create(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'uploads');
    JFile::copy(
            JPATH_SITE.DS.'components'.DS.'com_breezingforms'.DS.'uploads'.DS.'index.html', 
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'uploads'.DS.'index.html'
    );
}

// Default upload folder is now htaccess protected 2016-02-16

if(!JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'uploads'.DS.'.htaccess')){
    $def = 'deny from all';
    JFile::write(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'uploads'.DS.'.htaccess', $def);
}

#### THEMES

if(!JFolder::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes')){
    JFolder::copy(
            JPATH_SITE.DS.'components'.DS.'com_breezingforms'.DS.'themes'.DS.'quickmode'.DS, 
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'quickmode'.DS
    );
    JFolder::move(
           JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'quickmode'.DS,
           JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS
    );
}

if(!JFolder::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes-bootstrap')){
    JFolder::copy(
            JPATH_SITE.DS.'components'.DS.'com_breezingforms'.DS.'themes'.DS.'quickmode-bootstrap'.DS, 
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'quickmode-bootstrap'.DS
    );
    JFolder::move(
           JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'quickmode-bootstrap'.DS,
           JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes-bootstrap'.DS
    );
}

if(!JFolder::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'images')){
    JFolder::copy(
            JPATH_SITE.DS.'components'.DS.'com_breezingforms'.DS.'themes'.DS.'quickmode'.DS.'images'.DS, 
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'images'.DS
    );
}

if(!JFolder::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'images'.DS.'icons-png'.DS)){
    JFolder::copy(
            JPATH_SITE.DS.'components'.DS.'com_breezingforms'.DS.'themes'.DS.'quickmode'.DS.'images'.DS.'icons-png'.DS, 
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'images'.DS.'icons-png'.DS
    );
}

if(!JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'jq.mobile.1.4.4.min.css')){
    JFile::copy(
            JPATH_SITE.DS.'components'.DS.'com_breezingforms'.DS.'themes'.DS.'quickmode'.DS.'jq.mobile.1.4.4.min.css', 
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'jq.mobile.1.4.4.min.css'
    );
}

if(!JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'jq.mobile.1.4.4.icons.min.css')){
    JFile::copy(
            JPATH_SITE.DS.'components'.DS.'com_breezingforms'.DS.'themes'.DS.'quickmode'.DS.'jq.mobile.1.4.4.icons.min.css', 
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'jq.mobile.1.4.4.icons.min.css'
    );
}

if(!JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'ajax-loader.gif')){
    JFile::copy(
            JPATH_SITE.DS.'components'.DS.'com_breezingforms'.DS.'themes'.DS.'quickmode'.DS.'ajax-loader.gif', 
            JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'ajax-loader.gif'
    );
}

#### DELETE SYSTEM THEMES FILES FROM MEDIA FOLDER (the ones in the original themes path are being used)

if(JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'system.css')){
    JFile::delete(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'system.css');
}

if(JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'system.ie7.css')){
    JFile::delete(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'system.ie7.css');
}

if(JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'system.ie6.css')){
    JFile::delete(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'system.ie6.css');
}

if(JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'system.ie.css')){
    JFile::delete(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes'.DS.'system.ie.css');
}

if(JFile::exists(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes-bootstrap'.DS.'system.css')){
    JFile::delete(JPATH_SITE.DS.'media'.DS.'breezingforms'.DS.'themes-bootstrap'.DS.'system.css');
}

/**
 * 
 * SAME CHECKS FOR CAPTCHA AS IN FRONTEND, SINCE THEY DONT SHARE THE SAME SESSION
 * 
 */

if(JRequest::getBool('bfReCaptcha')){

	@ob_end_clean();
        require_once(JPATH_SITE.'/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
	require_once(JPATH_SITE.'/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');
        $db = JFactory::getDBO();
        $db->setQuery( "Select * From #__facileforms_forms Where id = " . $db->Quote( JRequest::getInt('form',-1) ) );
	$list = $db->loadObjectList();
	if(count($list) == 0){
		exit;
	}
	$form = $list[0];
	$areas = Zend_Json::decode($form->template_areas);
        foreach($areas As $area){
		foreach($area['elements'] As $element){

                    if($element['bfType'] == 'ReCaptcha'){
                        if(!function_exists('recaptcha_check_answer')){
                            require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/recaptcha/recaptchalib.php');
                        }
                        
                        $publickey = $element['pubkey']; // you got this from the signup page
                        $privatekey = $element['privkey'];

                        $resp = recaptcha_check_answer ($privatekey,
                                                        $_SERVER["REMOTE_ADDR"],
                                                        isset( $_POST["recaptcha_challenge_field"] ) ? $_POST["recaptcha_challenge_field"] : '' ,
                                                        isset($_POST["recaptcha_response_field"]) ? $_POST["recaptcha_response_field"] : '' );

                        JFactory::getSession()->set('bfrecapsuccess',false);
                        if ($resp->is_valid) {
                            echo 'success';
                            JFactory::getSession()->set('bfrecapsuccess',true);
                        }
                        else
                        {
                            die ("The reCAPTCHA wasn't entered correctly. Go back and try it again." .
                               "(reCAPTCHA said: " . $resp->error . ")");
                        }
                        exit;
                    }
                }
        }
	
	exit;

} else if(JRequest::getBool('checkCaptcha')){
	
	ob_end_clean();
        
	require_once(JPATH_SITE . '/components/com_breezingforms/images/captcha/securimage.php');
	$securimage = new Securimage();
	if(!$securimage->check(str_replace('?','',JRequest::getVar('value', '')))){
		echo 'capResult=>false';
	} else {
		echo 'capResult=>true';
	}
	exit;
	
}

$mainframe = JFactory::getApplication();

$cache = JFactory::getCache('com_content');
$cache->clean();

// since joomla 1.6.2, load some behaviour to get the core.js files loaded
if (version_compare($version->getShortVersion(), '1.6', '>=')) {
    JHtml::_('behavior.framework', true);
}

if (version_compare($version->getShortVersion(), '3.0', '>=')) {
    // force jquery to be loaded after mootools but before any other js (since J! 3.4)
    JHtml::_('jquery.framework');
}

JHtml::_('behavior.tooltip');

// purge ajax save
$sourcePath = JPATH_SITE . DS . 'media' . DS . 'breezingforms' . DS . 'ajax_cache'.DS;
if (@file_exists($sourcePath) && @is_readable($sourcePath) && @is_dir($sourcePath) && $handle = @opendir($sourcePath)) {
    while (false !== ($file = @readdir($handle))) {
        if($file!="." && $file!="..") {
            $parts = explode('_', $file);
            if(count($parts)==3 && $parts[0] == 'ajaxsave') {
                if (@JFile::exists($sourcePath.$file) && @is_readable($sourcePath.$file)) {
                    $fileCreationTime = @filectime($sourcePath.$file);
                    $fileAge = time() - $fileCreationTime;
                    if($fileAge >= 86400) {
                        @JFile::delete($sourcePath.$file);
                    }
                }
            }
        }
    }
    @closedir($handle);
}

/**
 * DB UPGRADE BEGIN
 */
$tables = bf_getTableFields( JFactory::getDBO()->getTableList() );
if(isset($tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms'])){
    /**
     * New as of 1.7.3
     */
    
    // workaround for joomla bug (bf plugin in articles), introduced since joomla 3.1.5
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'breezingforms'])){
        JFactory::getDBO()->setQuery("CREATE TABLE IF NOT EXISTS `#__breezingforms` (`id` int(11) NOT NULL, `language` varchar(255) NOT NULL)");
        JFactory::getDBO()->query();
    }
    
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mb_alt_mailfrom'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mb_alt_mailfrom` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `alt_mailfrom` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mb_alt_fromname'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mb_alt_fromname` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `alt_fromname` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mb_custom_mail_subject'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mb_custom_mail_subject` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `custom_mail_subject` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mb_emailntf'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mb_emailntf` tinyint( 1 ) NOT NULL DEFAULT 1 AFTER `emailntf` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mb_emaillog'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mb_emaillog` tinyint( 1 ) NOT NULL DEFAULT 1 AFTER `emaillog` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mb_emailxml'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mb_emailxml` tinyint( 1 ) NOT NULL DEFAULT 0 AFTER `emailxml` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['email_type'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `email_type` tinyint( 1 ) NOT NULL DEFAULT 0 AFTER `mb_emailxml` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mb_email_type'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mb_email_type` tinyint( 1 ) NOT NULL DEFAULT 0 AFTER `email_type` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['email_custom_template'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `email_custom_template` TEXT AFTER `mb_email_type` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mb_email_custom_template'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mb_email_custom_template` TEXT AFTER `email_custom_template` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['email_custom_html'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `email_custom_html` tinyint( 1 ) NOT NULL DEFAULT 0 AFTER `mb_email_custom_template` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mb_email_custom_html'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mb_email_custom_html` tinyint( 1 ) NOT NULL DEFAULT 0 AFTER `email_custom_html` ");
        JFactory::getDBO()->query();
    }
    /////
    // New as of 1.7.2
    /////
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['alt_mailfrom'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `alt_mailfrom` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `id` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['alt_fromname'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `alt_fromname` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `alt_mailfrom` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mailchimp_email_field'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mailchimp_email_field` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `alt_fromname` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mailchimp_checkbox_field'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mailchimp_checkbox_field` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `mailchimp_email_field` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mailchimp_api_key'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mailchimp_api_key` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `mailchimp_checkbox_field` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mailchimp_list_id'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mailchimp_list_id` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `mailchimp_api_key` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mailchimp_double_optin'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mailchimp_double_optin` TINYINT( 1 ) NOT NULL DEFAULT 1 AFTER `mailchimp_list_id` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mailchimp_mergevars'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mailchimp_mergevars` TEXT AFTER `mailchimp_double_optin` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mailchimp_text_html_mobile_field'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mailchimp_text_html_mobile_field` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `mailchimp_mergevars` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mailchimp_send_errors'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mailchimp_send_errors` TINYINT( 1 ) NOT NULL DEFAULT 0 AFTER `mailchimp_text_html_mobile_field` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mailchimp_update_existing'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mailchimp_update_existing` TINYINT( 1 ) NOT NULL DEFAULT 0 AFTER `mailchimp_send_errors` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mailchimp_replace_interests'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mailchimp_replace_interests` TINYINT( 1 ) NOT NULL DEFAULT 0 AFTER `mailchimp_update_existing` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mailchimp_send_welcome'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mailchimp_send_welcome` TINYINT( 1 ) NOT NULL DEFAULT 0 AFTER `mailchimp_replace_interests` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mailchimp_default_type'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mailchimp_default_type` VARCHAR( 255 ) NOT NULL DEFAULT 'text' AFTER `mailchimp_send_welcome` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mailchimp_delete_member'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mailchimp_delete_member` TINYINT( 1 ) NOT NULL DEFAULT 0 AFTER `mailchimp_default_type` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mailchimp_send_goodbye'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mailchimp_send_goodbye` TINYINT( 1 ) NOT NULL DEFAULT 1 AFTER `mailchimp_delete_member` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mailchimp_send_notify'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mailchimp_send_notify` TINYINT( 1 ) NOT NULL DEFAULT 1 AFTER `mailchimp_send_goodbye` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['mailchimp_unsubscribe_field'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `mailchimp_unsubscribe_field` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `mailchimp_send_notify` ");
        JFactory::getDBO()->query();
    }
    // salesforce
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['salesforce_token'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `salesforce_token` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `mailchimp_unsubscribe_field` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['salesforce_username'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `salesforce_username` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `salesforce_token` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['salesforce_password'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `salesforce_password` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `salesforce_username` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['salesforce_type'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `salesforce_type` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `salesforce_password` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['salesforce_fields'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `salesforce_fields` TEXT AFTER `salesforce_type` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['salesforce_enabled'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `salesforce_enabled` TINYINT( 1 ) NOT NULL DEFAULT 0 AFTER `salesforce_fields` ");
        JFactory::getDBO()->query();
    }
    // dropbox
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['dropbox_email'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `dropbox_email` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `salesforce_fields` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['dropbox_password'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `dropbox_password` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `dropbox_email` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['dropbox_folder'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `dropbox_folder` TEXT AFTER `dropbox_password` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['dropbox_submission_enabled'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `dropbox_submission_enabled` TINYINT( 1 ) NOT NULL DEFAULT 0 AFTER `dropbox_folder` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['dropbox_submission_types'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `dropbox_submission_types` VARCHAR( 255 ) NOT NULL DEFAULT 'pdf' AFTER `dropbox_submission_enabled` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['tags_content'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `tags_content` text NOT NULL AFTER `dropbox_submission_types` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['tags_content_template'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `tags_content_template` mediumtext NOT NULL AFTER `tags_content` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['tags_content_template_default_element'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `tags_content_template_default_element` int(11) NOT NULL DEFAULT '0' AFTER `tags_content_template` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['tags_content_default_category'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `tags_content_default_category` int(11) NOT NULL DEFAULT '0' AFTER `tags_content_template_default_element` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['tags_content_default_state'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `tags_content_default_state` int(11) NOT NULL DEFAULT '1' AFTER `tags_content_default_category` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['tags_content_default_access'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `tags_content_default_access` int(11) NOT NULL DEFAULT '1' AFTER `tags_content_default_state` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['tags_content_default_language'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `tags_content_default_language` VARCHAR( 7 ) NOT NULL DEFAULT '*' AFTER `tags_content_default_access` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['tags_content_default_featured'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `tags_content_default_featured` int(11) NOT NULL DEFAULT '0' AFTER `tags_content_default_language` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['tags_content_default_publishup'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `tags_content_default_publishup` VARCHAR( 255 ) NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `tags_content_default_featured` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['tags_content_default_publishdown'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `tags_content_default_publishdown` VARCHAR( 255 ) NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `tags_content_default_publishup` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['tags_form'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `tags_form` text NOT NULL AFTER `tags_content_default_publishdown` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['autoheight'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `autoheight` TINYINT( 1 ) NOT NULL DEFAULT 0 AFTER `tags_form` ");
        JFactory::getDBO()->query();
    }
    if(!isset( $tables[BFJoomlaConfig::get('dbprefix').'facileforms_forms']['filter_state'] )){
        JFactory::getDBO()->setQuery("ALTER TABLE `#__facileforms_forms` ADD `filter_state` TEXT NOT NULL");
        JFactory::getDBO()->query();
    }
}

if(version_compare($version->getShortVersion(), '3.1', '>=')){

    JFactory::getDbo()->setQuery("Select type_id From #__content_types Where type_alias = 'com_breezingforms.form'");
    $tag_typeid = JFactory::getDbo()->loadResult();
    if(!$tag_typeid){
        $contenttype['type_id']		= 0;
        $contenttype['type_title']	= 'BreezingForms';
        $contenttype['type_alias']	= 'com_breezingforms.form';
        $contenttype['table']		= '';
        $contenttype['rules']		= '';
        $contenttype['router']		= 'BreezingformsHelperRoute::getFormRoute';
        $contenttype['field_mappings']	= '';
        $table = JTable::getInstance('Contenttype', 'JTable');
        $table->save($contenttype);
    }
}
/**
 * DB UPGRADE END
 */

require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/classes/BFTabs.php');
require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/classes/BFText.php');
require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/classes/BFTableElements.php');
require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/functions/helpers.php');
require_once(JPATH_SITE . '/administrator/components/com_breezingforms/libraries/crosstec/constants.php');

jimport('joomla.version');
$version = new JVersion();

if(version_compare($version->getShortVersion(), '1.6', '>=')){

JSubMenuHelper::addEntry(
                        BFText::_('COM_BREEZINGFORMS_MANAGERECS'),
                        'index.php?option=com_breezingforms&act=managerecs', JRequest::getVar('act','') == 'managerecs' || JRequest::getVar('act','') == 'recordmanagement' || JRequest::getVar('act','') == '');

JSubMenuHelper::addEntry(
                        BFText::_('COM_BREEZINGFORMS_MANAGEFORMS'),
                        'index.php?option=com_breezingforms&act=manageforms', JRequest::getVar('act','') == 'manageforms' || JRequest::getVar('act','') == 'easymode' || JRequest::getVar('act','') == 'quickmode');

JSubMenuHelper::addEntry(
                        BFText::_('COM_BREEZINGFORMS_MANAGESCRIPTS'),
                        'index.php?option=com_breezingforms&act=managescripts', JRequest::getVar('act','') == 'managescripts');

JSubMenuHelper::addEntry(
                        BFText::_('COM_BREEZINGFORMS_MANAGEPIECES'),
                        'index.php?option=com_breezingforms&act=managepieces', JRequest::getVar('act','') == 'managepieces');

JSubMenuHelper::addEntry(
                        BFText::_('COM_BREEZINGFORMS_INTEGRATOR'),
                        'index.php?option=com_breezingforms&act=integrate', JRequest::getVar('act','') == 'integrate');

JSubMenuHelper::addEntry(
                        BFText::_('COM_BREEZINGFORMS_MANAGEMENUS'),
                        'index.php?option=com_breezingforms&act=managemenus', JRequest::getVar('act','') == 'managemenus');

JSubMenuHelper::addEntry(
                        BFText::_('COM_BREEZINGFORMS_CONFIG'),
                        'index.php?option=com_breezingforms&act=configuration', JRequest::getVar('act','') == 'configuration');

JSubMenuHelper::addEntry(
                        BFText::_('Docs & Support'),
                        'http://crosstec.org/en/support/breezingforms-documentation.html' );

JSubMenuHelper::addEntry(
                        '<span style="color: red; font-weight: bold;">'.BFText::_('Get BreezingForms Pro').'</span>',
                        'https://crosstec.org/en/downloads/breezingforms-for-joomla.html' );

}

$_POST    = bf_stripslashes_deep($_POST);
$_GET     = bf_stripslashes_deep($_GET);
$_REQUEST = bf_stripslashes_deep($_REQUEST);

$db = JFactory::getDBO();

/*
 * Temporary section end
 */

global $errors, $errmode;
global $ff_mospath, $ff_admpath, $ff_compath, $ff_request;
global $ff_mossite, $ff_admsite, $ff_admicon, $ff_comsite;
global $ff_config, $ff_compatible, $ff_install;

$my = JFactory::getUser();

if (!isset($ff_compath)) { // joomla!
	
	jimport('joomla.version');
        $version = new JVersion();

        if(version_compare($version->getShortVersion(), '1.6', '<')){
            if ($my->usertype != 'Super Administrator' && $my->usertype != 'Administrator') {
                    JFactory::getApplication()->enqueueMessage(BFText::_('COM_BREEZINGFORMS_NOT_AUTHORIZED'));
                    JFactory::getApplication()->redirect( 'index.php' );
            } // if
        }

	// get paths
	$comppath = '/components/com_breezingforms';
	$ff_admpath = dirname(__FILE__);
	$ff_mospath = str_replace('\\','/',dirname(dirname(dirname($ff_admpath))));
	$ff_admpath = str_replace('\\','/',$ff_admpath);
	$ff_compath = $ff_mospath.$comppath;

	require_once($ff_admpath.'/toolbar.facileforms.php');
} // if

$errors = array();
$errmode = 'die';   // die or log

// compatibility check
if (!$ff_compatible) {
	echo '<h1>'.BFText::_('COM_BREEZINGFORMS_INCOMPATIBLE').'</h1>';
	exit;
} // if

// load ff parameters
$ff_request = array();
reset($_REQUEST);
while (list($prop, $val) = each($_REQUEST))
	if (is_scalar($val) && substr($prop,0,9)=='ff_param_')
		$ff_request[$prop] = $val;

if ($ff_install) {
	$act = 'installation';
	$task = 'step2';
} // if

$ids = JRequest::getVar( 'ids', array());

switch($act) {
	case 'installation':
		require_once($ff_admpath.'/admin/install.php');
		break;
	case 'configuration':
		require_once($ff_admpath.'/admin/config.php');
		break;
	case 'managemenus':
		require_once($ff_admpath.'/admin/menu.php');
		break;
	case 'manageforms':
		require_once($ff_admpath.'/admin/form.php');
		break;
	case 'editpage':
		require_once($ff_admpath.'/admin/element.php');
		break;
	case 'managescripts':
		require_once($ff_admpath.'/admin/script.php');
		break;
	case 'managepieces':
		require_once($ff_admpath.'/admin/piece.php');
		break;
	case 'run':
		require_once($ff_admpath.'/admin/run.php');
		break;
	case 'easymode':
		require_once($ff_admpath.'/admin/easymode.php');
		break;
	case 'quickmode':
		require_once($ff_admpath.'/admin/quickmode.php');
		break;
	case 'quickmode_editor':
		require_once($ff_admpath.'/admin/quickmode-editor.php');
		break;
	case 'integrate':
		require_once($ff_admpath.'/admin/integrator.php');
		break;
	case 'recordmanagement':
		require_once($ff_admpath.'/admin/recordmanagement.php');
		break;
	default:
		require_once($ff_admpath.'/admin/recordmanagement.php');
		break;
} // switch

// some general purpose functions for admin

function isInputElement($type)
{
	switch ($type) {
		case 'Static Text/HTML':
		case 'Rectangle':
		case 'Image':
		case 'Tooltip':
		case 'Query List':
		case 'Regular Button':
		case 'Graphic Button':
		case 'Icon':
			return false;
		default:
			break;
	} // switch
	return true;
} // isInputElement

function isVisibleElement($type)
{
	switch ($type) {
		case 'Hidden Input':
			return false;
		default:
			break;
	} // switch
	return true;
} // isVisibleElement

function _ff_query($sql, $insert = 0)
{
	global $database, $errors;
	$database = JFactory::getDBO();
	$id = null;
	$database->setQuery($sql);
	$database->query();
	if ($database->getErrorNum()) {
		if (isset($errmode) && $errmode=='log')
			$errors[] = $database->getErrorMsg();
		else
			die($database->stderr());
	} // if
	if ($insert) $id = $database->insertid();
	return $id;
} // _ff_query

function _ff_select($sql)
{
	global $database, $errors;
	$database = JFactory::getDBO();
	$database->setQuery($sql);
	$rows = $database->loadObjectList();
	if ($database->getErrorNum()) {
		if (isset($errmode) && $errmode=='log')
			$errors[] = $database->getErrorMsg();
		else
			die($database->stderr());
	} // if
	
	return $rows;
} // _ff_select

function _ff_selectValue($sql)
{
	global $database, $errors;
	$database = JFactory::getDBO();
	$database->setQuery($sql);
	$value = $database->loadResult();
	if ($database->getErrorNum()) {
		
			die($database->stderr());
	} // if
	return $value;
} // _ff_selectValue

function protectedComponentIds()
{
    jimport('joomla.version');
    $version = new JVersion();

    if(version_compare($version->getShortVersion(), '1.6', '>=')){

        $rows = _ff_select(
		"select id, parent_id As parent from #__menu ".
		"where ".
		" link in (".
			"'index.php?option=com_breezingforms&act=managerecs',".
			"'index.php?option=com_breezingforms&act=managemenus',".
			"'index.php?option=com_breezingforms&act=manageforms',".
			"'index.php?option=com_breezingforms&act=managescripts',".
			"'index.php?option=com_breezingforms&act=managepieces',".
			"'index.php?option=com_breezingforms&act=share',".
			"'index.php?option=com_breezingforms&act=integrate',".
			"'index.php?option=com_breezingforms&act=configuration'".
		") ".
		"order by id"
	);

    }else{

	$rows = _ff_select(
		"select id, parent from #__components ".
		"where `option`='com_breezingforms' ".
		"and admin_menu_link in (".
			"'option=com_breezingforms&act=managerecs',".
			"'option=com_breezingforms&act=managemenus',".
			"'option=com_breezingforms&act=manageforms',".
			"'option=com_breezingforms&act=managescripts',".
			"'option=com_breezingforms&act=managepieces',".
			"'option=com_breezingforms&act=share',".
			"'option=com_breezingforms&act=integrate',".
			"'option=com_breezingforms&act=configuration'".
		") ".
		"order by id"
	);

    }
    
    $parent = 0;
    $ids = array();
    if (count($rows))
        foreach ($rows as $row) {
            if ($parent == 0) {
                $parent = 1;
                if(isset($row->parent)){
                    $ids[] = intval($row->parent);
                }
            } // if
            $ids[] = intval($row->id);
        } // foreach
 return implode($ids, ',');
} // protectedComponentIds

function addComponentMenu($row, $parent, $copy = false)
{
	$db = JFactory::getDBO();
	$admin_menu_link = '';
	if ($row->name!='') {
		$admin_menu_link =
			'option=com_breezingforms'.
			'&act=run'.
			'&ff_name='.htmlentities($row->name, ENT_QUOTES, 'UTF-8');
		if ($row->page!=1) $admin_menu_link .= '&ff_page='.htmlentities($row->page, ENT_QUOTES, 'UTF-8');
		if ($row->frame==1) $admin_menu_link .= '&ff_frame=1';
		if ($row->border==1) $admin_menu_link .= '&ff_border=1';
		if ($row->params!='') $admin_menu_link .= $row->params;
	} // if
	if ($parent==0) $ordering = 0; else $ordering = $row->ordering;

        jimport('joomla.version');
        $version = new JVersion();

        if(version_compare($version->getShortVersion(), '3.0', '<') && version_compare($version->getShortVersion(), '1.6', '>=')){

            $parent = $parent == 0 ? 1 : $parent;

            $db->setQuery("Select component_id From #__menu Where link = 'index.php?option=com_breezingforms' And parent_id = 1");
            $result = $db->loadResult();
            if($result){
                
                return _ff_query(
                    "insert into #__menu (".
                            "`title`, alias, menutype, parent_id, ".
                            "link,".
                            "ordering, level, component_id, client_id, img, lft, rgt".
                    ") ".
                    "values (".$db->Quote( ($copy ? 'Copy of ' : '') . $row->title . ($copy ? ' ('.md5(session_id().microtime().mt_rand(0,  mt_getrandmax())).')' : '')).", ".$db->Quote( ($copy ? 'Copy of ' : '') . $row->title . ($copy ? ' ('.md5(session_id().microtime().mt_rand(0,  mt_getrandmax())).')' : '')).", 'menu', $parent, ".
                            "'index.php?$admin_menu_link',".
                            "'$ordering', 1, ".intval($result).", 1, 'components/com_breezingforms/images/$row->img',( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet )".
                    ")",
                    true
                );
            }else{
                die("BreezingForms main menu item not found!");
            }
        } else if(version_compare($version->getShortVersion(), '3.0', '>=')){
            $parent = $parent == 0 ? 1 : $parent;

            $db->setQuery("Select component_id From #__menu Where link = 'index.php?option=com_breezingforms' And parent_id = 1");
            $result = $db->loadResult();
            if($result){
                
                return _ff_query(
                    "insert into #__menu (".
                            "`title`, alias, menutype, parent_id, ".
                            "link,".
                            "level, component_id, client_id, img, lft, rgt".
                    ") ".
                    "values (".$db->Quote( ($copy ? 'Copy of ' : '') . $row->title . ($copy ? ' ('.md5(session_id().microtime().mt_rand(0,  mt_getrandmax())).')' : '')).", ".$db->Quote( ($copy ? 'Copy of ' : '') . $row->title . ($copy ? ' ('.md5(session_id().microtime().mt_rand(0,  mt_getrandmax())).')' : '')).", 'menu', $parent, ".
                            "'index.php?$admin_menu_link',".
                            "1, ".intval($result).", 1, 'components/com_breezingforms/images/$row->img',( Select mlftrgt From (Select max(mlft.rgt)+1 As mlftrgt From #__menu As mlft) As tbone ),( Select mrgtrgt From (Select max(mrgt.rgt)+2 As mrgtrgt From #__menu As mrgt) As filet )".
                    ")",
                    true
                );
            }else{
                die("BreezingForms main menu item not found!");
            }
        }
        // if older JVersion
	return _ff_query(
		"insert into #__components (".
			"id, name, link, menuid, parent, ".
			"admin_menu_link, admin_menu_alt, `option`, ".
			"ordering, admin_menu_img, iscore, params".
		") ".
		"values (".
			"'', ".$db->Quote($row->title).", '', 0, $parent, ".
			"'$admin_menu_link', ".$db->Quote($row->title).", 'com_breezingforms', ".
			"'$ordering', '$row->img', 1, ''".
		")",
		true
	);
} // addComponentMenu

function updateComponentMenus($copy = false)
{
	// remove unprotected menu items
	$protids = protectedComponentIds();
	if(trim($protids)!=''){

            jimport('joomla.version');
            $version = new JVersion();

            if(version_compare($version->getShortVersion(), '1.6', '>=')){
                _ff_query(
			"delete from #__menu ".
			"where `link` Like 'index.php?option=com_breezingforms&act=run%' ".
			"and id not in ($protids)"
		);
            }else{
		_ff_query(
			"delete from #__components ".
			"where `option`='com_breezingforms' ".
			"and id not in ($protids)"
		);
            }
	} 
	
	// add published menu items
	$rows = _ff_select(
		"select ".
			"m.id as id, ".
			"m.parent as parent, ".
			"m.ordering as ordering, ".
			"m.title as title, ".
			"m.img as img, ".
			"m.name as name, ".
			"m.page as page, ".
			"m.frame as frame, ".
			"m.border as border, ".
			"m.params as params, ".
			"m.published as published ".
		"from #__facileforms_compmenus as m ".
			"left join #__facileforms_compmenus as p on m.parent=p.id ".
		"where m.published=1 ".
			"and (m.parent=0 or p.published=1) ".
		"order by ".
			"if(m.parent,p.ordering,m.ordering), ".
			"if(m.parent,m.ordering,-1)"
	);
	$parent = 0;
	if (count($rows)) foreach ($rows as $row) {

                jimport('joomla.version');
                $version = new JVersion();

                if(version_compare($version->getShortVersion(), '1.6', '>=')){

                    JFactory::getDBO()->setQuery("Select id From #__menu Where `alias` = " . JFactory::getDBO()->Quote($row->title));

                    if(JFactory::getDBO()->loadResult()){
                        return BFText::_('COM_BREEZINGFORMS_MENU_ITEM_EXISTS');
                    }

                    if ($row->parent==0 || $row->parent==1){
                            $parent = addComponentMenu($row, 1, $copy);
                    }else{
                            addComponentMenu($row, $parent, $copy);
                    }
                }else{
                    if ($row->parent==0){
                            $parent = addComponentMenu($row, 0);
                    }else{
                            addComponentMenu($row, $parent);
                    }
                }
	} // foreach

        return '';
} // updateComponentMenus

function dropPackage($id)
{
	// drop package settings
	_ff_query("delete from #__facileforms_packages where id = ".JFactory::getDBO()->Quote($id)."");

	// drop backend menus
	$rows = _ff_select("select id from #__facileforms_compmenus where package = ".JFactory::getDBO()->Quote($id)."");
	if (count($rows)) foreach ($rows as $row)
		_ff_query("delete from #__facileforms_compmenus where id=$row->id or parent=$row->id");
	updateComponentMenus();

	// drop forms
	$rows = _ff_select("select id from #__facileforms_forms where package = ".JFactory::getDBO()->Quote($id)."");
	if (count($rows)) foreach ($rows as $row) {
		_ff_query("delete from #__facileforms_elements where form = $row->id");
		_ff_query("delete from #__facileforms_forms where id = $row->id");
	} // if

	// drop scripts
	_ff_query("delete from #__facileforms_scripts where package =  ".JFactory::getDBO()->Quote($id)."");

	// drop pieces
	_ff_query("delete from #__facileforms_pieces where package =  ".JFactory::getDBO()->Quote($id)."");
} // dropPackage

function savePackage($id, $name, $title, $version, $created, $author, $email, $url, $description, $copyright)
{
	$db = JFactory::getDBO();
	$cnt = _ff_selectValue("select count(*) from #__facileforms_packages where id=".JFactory::getDBO()->Quote($id)."");
	if (!$cnt) {
		
		_ff_query(
			"insert into #__facileforms_packages ".
					"(id, name, title, version, created, author, ".
					 "email, url, description, copyright) ".
			"values (".$db->Quote($id).", ".$db->Quote($name).", ".$db->Quote($title).", ".$db->Quote($version).", ".$db->Quote($created).", ".$db->Quote($author).",
					".$db->Quote($email).", ".$db->Quote($url).", ".$db->Quote($description).", ".$db->Quote($copyright).")"
		);
	} else {
		_ff_query(
			"update #__facileforms_packages ".
				"set name=".$db->Quote($name).", title=".$db->Quote($title).", version=".$db->Quote($version).", created=".$db->Quote($created).", author=".$db->Quote($author).", ".
				"email=".$db->Quote($email).", url=".$db->Quote($url).", description=".$db->Quote($description).", copyright=".$db->Quote($copyright). " 
			where id =  ".$db->Quote($id)
		);
	} // if
} // savePackage

function relinkScripts(&$oldscripts)
{
	if (count($oldscripts))
		foreach ($oldscripts as $row) {
			$newid = _ff_selectValue("select max(id) from #__facileforms_scripts where name = ".JFactory::getDBO()->Quote($row->name)."");
			if ($newid) {
				_ff_query("update #__facileforms_forms set script1id=$newid where script1id=$row->id");
				_ff_query("update #__facileforms_forms set script2id=$newid where script2id=$row->id");
				_ff_query("update #__facileforms_elements set script1id=$newid where script1id=$row->id");
				_ff_query("update #__facileforms_elements set script2id=$newid where script2id=$row->id");
				_ff_query("update #__facileforms_elements set script3id=$newid where script3id=$row->id");
			} // if
		} // foreach
} // relinkScripts

function relinkPieces(&$oldpieces)
{
	if (count($oldpieces))
		foreach ($oldpieces as $row) {
			$newid = _ff_selectValue("select max(id) from #__facileforms_pieces where name = ".JFactory::getDBO()->Quote($row->name)."");
			if ($newid) {
				_ff_query("update #__facileforms_forms set piece1id=$newid where piece1id=$row->id");
				_ff_query("update #__facileforms_forms set piece2id=$newid where piece2id=$row->id");
				_ff_query("update #__facileforms_forms set piece3id=$newid where piece3id=$row->id");
				_ff_query("update #__facileforms_forms set piece4id=$newid where piece4id=$row->id");
			} // if
		} // foreach
} // relinkPieces
?>