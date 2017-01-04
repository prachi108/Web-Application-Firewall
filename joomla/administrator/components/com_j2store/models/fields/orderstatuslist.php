<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
/* class JFormFieldFieldtypes extends JFormField */

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2html.php';
class JFormFieldOrderstatusList extends JFormFieldList {

	protected $type = 'OrderstatusList';

	public function getRepeatable()
	{
		$html ='';
		if($this->item->orderstatus_id != '*'){
			$orderstatus = F0FTable::getAnInstance('Orderstatus','J2StoreTable');
			$orderstatus->load($this->item->orderstatus_id);
			$html ='<label class="label">'.JText::_($orderstatus->orderstatus_name);
			if(isset($orderstatus->orderstatus_cssclass) && $orderstatus->orderstatus_cssclass){
				$html ='<label class="label  '.$orderstatus->orderstatus_cssclass.'">'.JText::_($orderstatus->orderstatus_name);
			}

		}else{
			$html ='<label class="label label-success">'.JText::_('J2STORE_ALL');
		}
		$html .='</label>';
		return $html;
	}


	public function getInput() {

		$model = F0FModel::getTmpInstance('Orderstatuses','J2StoreModel');
		$orderlist = $model->getItemList();
		$attr = '';
		// Get the field options.
				// Initialize some field attributes.
		$attr['class']= !empty($this->class) ? $this->class: '';
		$attr ['size']= !empty($this->size) ?$this->size : '';
		$attr ['multiple']= $this->multiple ? 'multiple': '';
		$attr ['required']= $this->required ? true:false;
		$attr ['autofocus']= $this->autofocus ? 'autofocus' : '';
		// Initialize JavaScript field attributes.
		$attr ['onchange']= $this->onchange ?  $this->onchange : '';

		//generate country filter list
		$orderstatus_options = array();
		$orderstatus_options['*'] =  JText::_('JALL');
		foreach($orderlist as $row) {
			$orderstatus_options[$row->j2store_orderstatus_id] =  JText::_($row->orderstatus_name);
		}
		return J2Html::select()->clearState()
						->type('genericlist')
						->name($this->name)
						->attribs($attr)
						->value($this->value)
						->setPlaceHolders($orderstatus_options)
						->getHtml();
	}
}
