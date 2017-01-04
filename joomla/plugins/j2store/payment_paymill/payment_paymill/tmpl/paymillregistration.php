<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
/* class JFormFieldFieldtypes extends JFormField */
class JFormFieldPaymillRegistration extends JFormField 
{
	protected $type = 'paymillregistration';
	
	public function getInput() {
		
		$html = '';
		$html .= '<p>'.$this->getTitle();
		$html .= ' <a href="https://app.paymill.com/user/register?referrer=j2store" target="_blank" >'.JText::_('J2STORE_REGISTER').'</a>';
		$html .= '</p>';
		return  $html;
	}
	
	public function getLabel() {
		return '';
	}
	
}