<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();
class J2StoreControllerEmailtemplates extends F0FController {
	
	/**
	 * ACL check before allowing someone to browse
	 *
	 * @return  boolean  True to allow the method to run
	 */
	protected function onBeforeBrowse() {
		
		if(parent::onBeforeBrowse()) {
			
			jimport('joomla.filesystem.file');
			//make sure we have a default.php template
			$filename = 'default.php';
			$tplpath = JPATH_ADMINISTRATOR.'/components/com_j2store/views/emailtemplate/tpls';
			$defaultphp = $tplpath.'/default.php';
			$defaulttpl = $tplpath.'/default.tpl';
			
			if(!JFile::exists(JPath::clean($defaultphp))) {				
				//file does not exist. so we need to rename				
				JFile::copy($defaulttpl, $defaultphp);
			}
			
			return true;
		}
		
		return false;
		
	}
	
	
	function sendtest() {
		$app = JFactory::getApplication ();
		$template_id = $app->input->getInt ( 'id', 0 );
		$msgType = 'warning';
		if ($template_id) {
			$model = $this->getModel ( 'Emailtemplates' );
			try {
				$email = $model->sendTestEmail ( $template_id );
				if ($email == false) {
					$msg = JText::sprintf ( 'J2STORE_EMAILTEMPLATE_TEST_EMAIL_ERROR' );
				} else {
					$msg = JText::sprintf ( 'J2STORE_EMAILTEMPLATE_TEST_EMAIL_SENT', $email );
					$msgType = 'message';
				}
			} catch ( Exception $e ) {
				$msg = $e->getMessage ();
			}
			$url = 'index.php?option=com_j2store&view=emailtemplate&id=' . $template_id;
		} else {
			$msg = JText::_ ( 'J2STORE_EMAILTEMPLATE_NO_EMAIL_TEMPLATE_FOUND' );
			$url = 'index.php?option=com_j2store&view=emailtemplates';
		}
		$this->setRedirect ( $url, $msg, $msgType );
	}
}