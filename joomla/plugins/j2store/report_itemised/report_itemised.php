<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/report.php');
class plgJ2StoreReport_itemised extends J2StoreReportPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
    var $_element   = 'report_itemised';

    /**
     * Overriding
     *
     * @param $options
     * @return unknown_type
     */
    function onJ2StoreGetReportView( $row )
    {
	   	if (!$this->_isMe($row))
    	{
    		return null;
    	}

    	$html = $this->viewList();


    	return $html;
    }

    /**
     * Validates the data submitted based on the suffix provided
     * A controller for this plugin, you could say
     *
     * @param $task
     * @return html
     */
    function viewList()
    {
    	$app = JFactory::getApplication();
    	$option = 'com_j2store';
    	$ns = $option.'.report';
    	$html = "";
    	JToolBarHelper::title(JText::_('J2STORE_REPORT').'-'.JText::_('PLG_J2STORE_'.strtoupper($this->_element)),'j2store-logo');

	   	$vars = new JObject();
	   	$this->includeCustomModel('Reportitemised');
    	$this->includeCustomTables();

    	$model = F0FModel::getTmpInstance('ReportItemised', 'J2StoreModel');
    	$model->setState('limit',$app->input->getInt('limit',0));
    	$model->setState('limitstart',$app->input->getInt('limitstart',0));
    	$model->setState('filter_search', $app->input->getString('filter_search'));
    	$model->setState('filter_orderstatus', $app->input->getString('filter_orderstatus'));
    	$model->setState('filter_order', $app->input->getString('filter_order'));
    	$model->setState('filter_order_Dir', $app->input->getString('filter_order_Dir'));


    	$list = $model->getData();
    	//$list = $model->getList();
		$vars->state=$model->getState();
    	$vars->list = $list;
    	$vars->total = $model->getTotal();
    	$vars->pagination = $model->getPagination();
    	$vars->orderStatus =F0FModel::getTmpInstance('OrderStatuses','J2StoreModel')->enabled(1)->getList();
    	$id = $app->input->getInt('id', '0');
    	$vars->id = $id;
    	$form = array();
    	$form['action'] = "index.php?option=com_j2store&view=report&task=view&id={$id}";
    	$vars->form = $form;
    	$html = $this->_getLayout('default', $vars);
    	return $html;
    }


    function onJ2StoreGetReportExported($row){
    	$app = JFactory::getApplication();

    	$ignore_column =array('sum','count','orderitem_quantity','product_source_id','id');
    	$this->includeCustomModel('Reportitemised');
    	if (!$this->_isMe($row))
    	{
    		return null;
    	}
    	$model = F0FModel::getTmpInstance('ReportItemised', 'J2StoreModel');
    	$items = $model->getData();
  		foreach($items as &$item){
			$item->orderitem_options ='';
			if(isset($item->orderitem_attributes) && $item->orderitem_attributes){
				foreach($item->orderitem_attributes as $attr){
					unset($item->orderitem_attributes);
					$item->orderitem_options.=$attr->orderitemattribute_name .' : '.$attr->orderitemattribute_value;
				}
			}
			$item->qty = $item->sum;
			$item->total_purchase = $item->count;

			foreach($ignore_column as $key =>$value){
				unset($item->$value);
			}
  		}
	   	return $items;
    }





}

