<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
class J2StoreControllerOptions extends F0FController
{

	public function deleteoptionvalue(){

		$app = JFactory::getApplication();
		$option_value_id = $app->input->getInt('optionvalue_id');
		$optionValue = F0FTable::getInstance('Optionvalue','J2StoreTable')->getClone();
		$optionValue->load($option_value_id);

		$json  =array();
		$msg_type = "success";
		$msg = JText::_('J2STORE_OPTION_VALUE_DELETED_SUCCESSFULLY');
		$msg_header ='Message';
		$json['success'] = true;
		if(!$optionValue->delete()){
			$json['success'] = false;
			$msg_type = "warning";
			$msg = JText::_('J2STORE_OPTION_VALUE_DELETE_ERROR');
			$msg_header ='Warning';
		}

		$html = "<div class='alert alert-$msg_type'>";
		$html .="<h4 class='alert-heading'>". $msg_header."</h4>";
		$html .="<p>" .$msg."</p></div>";
		$json['html'] = $html;
		echo json_encode($json);
    	$app->close();
	}


	public function getOptions() {
		$app = JFactory::getApplication();
		$q = $app->input->post->get('q', '','string');
		$json = array();
		$model = $this->getThisModel('options');
		$result = $model->getOptions($q);
		$product_type = $app->input->getString('product_type');
		if($product_type =='configurable'){
			$json['options'] = $result;
		//get parent
			$json['pa_options']= $model->getParent($q);
		}else{
			$json = $result;
		}
		echo json_encode($json);
		$app->close();
	}



}
