<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/shippingcontroller.php');
class J2StoreControllerShippingStandard extends J2StoreControllerShippingPlugin
{

	var $_element   = 'shipping_standard';

	/**
	 * constructor
	 */
	function __construct()
	{
		$app = JFactory::getApplication();
		$values = $app->input->getArray($_POST);
		parent::__construct();
		F0FModel::addIncludePath(JPATH_SITE.'/plugins/j2store/'.$this->_element.'/'.$this->_element.'/models');
		F0FModel::addTablePath(JPATH_SITE.'/plugins/j2store/'.$this->_element.'/'.$this->_element.'/tables');
		JFactory::getLanguage()->load('plg_j2store_'.$this->_element, JPATH_ADMINISTRATOR);
		$this->registerTask( 'newMethod', 'newMethod' );
		$this->registerTask('apply', 'save');
	}

	/**
	 * Gets the plugin's namespace for state variables
	 * @return string
	 */
	function getNamespace()
	{
		$app = JFactory::getApplication();
		$ns = $app->getName().'::'.'com.j2store.plugin.shipping.standard';
		return $ns;
	}

	function newMethod(){

		return $this->view();
	}


	function publish() {

		$return = 0;
		$post = JFactory::getApplication()->input->getArray($_POST);
		$this->includeCustomTables();
		$table = F0FTable::getInstance('ShippingMethods', 'J2StoreTable');
		$table->load($post['smid']);
		if($table->j2store_shippingmethod_id == $post['smid']) {
			if($table->published == 1) {
				$table->published = 0;
			}elseif($table->published == 0) {
				$table->published = 1;
				$return = 1;
			}
			$table->store();
		}
		echo $return;
		JFactory::getApplication()->close();
	}

	function save(){

		$app = JFactory::getApplication();
		$sid = $app->input->getInt('j2store_shippingmethod_id');
		$values = $app->input->getArray($_POST);
		$model  = F0FModel::getTmpInstance('ShippingMethods','J2StoreModel');
		require_once JPATH_SITE.'/plugins/j2store/shipping_standard/shipping_standard/tables/shippingmethod.php';
		$model->addIncludePath(JPATH_SITE.'/plugins/j2store/shipping_standard/shipping_standard/tables');
		$this->includeCustomTables();
		$table = F0FTable::getInstance('ShippingMethod', 'J2StoreTable');
		$table->bind($values);
		try {
			$table->save($values);
			$link = $this->baseLink();
			$this->messagetype 	= 'message';
			$this->message  	= JText::_('J2STORE_ALL_CHANGES_SAVED');
		} catch(Exception $e) {
			$link = $this->baseLink().'&shippingTask=view&sid='.$sid;
			$this->messagetype 	= 'error';
			$this->message 		= JText::_('J2STORE_SAVE_FAILED').$e->getMessage();
		}
		if($this->getTask() =='apply') $link = $this->baseLink().'&shippingTask=view&sid='.$table->j2store_shippingmethod_id;
		$redirect = JRoute::_( $link, false );
		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}


	function setRates()
	{

		$app = JFactory::getApplication();
		$this->includeCustomModel('ShippingRates');
		$sid = $app->input->getInt('sid');
		$lists =array();
		$this->includeCustomTables();
		$row = F0FTable::getAnInstance('ShippingMethod', 'J2StoreTable');
		$row->load($sid);
		$model  = F0FModel::getAnInstance('ShippingRates', 'J2StoreModel');

		//set the shipping method id
		$model->set('filter_shippingmethod' , $sid);
		$items = $model->getList();
		//form
		$form = array();
		$form['action'] = $this->baseLink();

		JToolBarHelper::title(JText::_('J2STORE_SHIPM_SHIPPING_METHODS'),'j2store-logo');
		// view

		$view = $this->getView( 'ShippingMethods', 'html' );
		$view->setModel( $model, true );
		$view->addTemplatePath(JPATH_SITE.'/plugins/j2store/'.$this->_element.'/'.$this->_element.'/tmpl');
	 	$view->set('row', $row);
		$view->items = $items;
		 $view->set( 'total', $model->getTotal() );
		$view->set( 'pagination', $model->getPagination());
		//$view->lists = $lists ;
		$view->form2 = $form;
		$view->baseLink = $this->baseLink();
		$view->setLayout('setrates');
		$view->display();

	}



	function cancel(){
		$redirect = $this->baseLink();
		$redirect = JRoute::_( $redirect, false );

		$this->setRedirect( $redirect, '', '' );
	}

	function view()
	{

		$app = JFactory::getApplication();

		require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/select.php');
		JToolBarHelper::title(JText::_('J2STORE_SHIPM_SHIPPING_METHODS'),'j2store-logo');

		$id = $app->input->getInt('id', '0');
		$sid = $app->input->getInt('sid', '0');
		$this->includeCustomModel('ShippingMethods');
		$this->includeCustomTables();
		//$model = JModelLegacy::getInstance('ShippingMethods', 'J2StoreModel');
		$model = F0FModel::getTmpInstance('ShippingMethods', 'J2StoreModel');
		$tpath =JPATH_SITE.'/plugins/j2store/'.$this->_element.'/'.$this->_element.'/tables';

		$model->addTablePath($tpath);
		$shippingmethod_table = $model->getTable('ShippingMethod' , 'J2StoreTable');
		$shippingmethod_table->load($sid);
		$data = array();
		$data ['published'] = JHTML::_('select.booleanlist',  'published', 'class=""', $shippingmethod_table->published );

		$data ['taxclass'] =  J2StoreHelperSelect::taxclass($shippingmethod_table->tax_class_id, 'tax_class_id');
		$data ['shippingtype'] =  J2StoreHelperSelect::shippingtype($shippingmethod_table->shipping_method_type, 'shipping_method_type', '', 'shipping_method_type', false );

		$options=array();
		$options[]= JHtml::_('select.option', 'no', JText::_('JNO'));
		$options[]= JHtml::_('select.option', 'store', JText::_('J2STORE_SHIPPING_STORE_ADDRESS'));
		$data ['address_override'] = JHtmlSelect::genericlist($options, 'address_override', array(), 'value', 'text', $shippingmethod_table->address_override);

		// Form
		$form = array();
		$form['action'] = $this->baseLink();
		$form['shippingTask'] = 'save';
		//We are calling a view from the ShippingMethods we isn't actually the same  controller this has, however since all it does is extend the base view it is
		// all good, and we don't need to remake getView()

		$view = $this->getView( 'ShippingMethods','html');
		$view->hidemenu = true;
		$view->hidestats = true;

		$view->setModel( $model, true );
		$view->assign('item', $shippingmethod_table);
		$view->assign('data', $data );
		$view->assign('form2', $form);
		$view->setLayout('view');
		$view->display();
	}

	/**
	 * Deletes a shipping method
	 */
	function delete()
	{
		$error = false;
		$this->messagetype	= '';
		$this->message 		= '';
		$app = JFactory::getApplication();
		$model  = F0FModel::getTmpInstance( 'Shippingmethods' ,'J2StoreModel');
		require_once JPATH_SITE.'/plugins/j2store/shipping_standard/shipping_standard/tables/shippingmethod.php';
		$model->addIncludePath(JPATH_SITE.'/plugins/j2store/shipping_standard/shipping_standard/tables');
		//$this->includeCustomTables();

		$row =F0FTable::getAnInstance('ShippingMethod', 'J2StoreTable');
		$cids = $app->input->get('cid', array (), 'array');
		if(count($cids) ) {
			foreach ($cids as $cid)
			{
				if (!$row->delete($cid))
				{
					$this->message .= $row->getError();
					$this->messagetype = 'notice';
					$error = true;
				}
			}

			if ($error)
			{
				$this->message = JText::_('J2STORE_ERROR') . " - " . $this->message;
			}
			else
			{
				$this->message = JText::_('J2STORE_ITEMS_DELETED');
			}
		} else {
			$this->messagetype = 'warning';
			$this->message = JText::_('J2STORE_SELECT_ITEM_TO_DELETE');
		}

		$this->redirect = $this->baseLink();
		$this->setRedirect( $this->redirect, $this->message, $this->messagetype );
	}

	/**
	 * Creates a shipping rate and redirects
	 *
	 * @return unknown_type
	 */
	function createrate()
	{
		$app = JFactory::getApplication();
		$data = $app->input->getArray($_POST);
		$model  = $this->getModel( 'shippingrates');
		$row = $model->getTable();
		$this->message = JText::_('J2STORE_SHIPPING_METHOD_RATE_SAVE_SUCCESS');
		$this->messagetype = 'message';
		if (!$row->save($data['jform']) )	{
			$this->messagetype  = 'notice';
			$this->message      = JText::_('J2STORE_SAVE_FAILED')." - ".$row->getError();
		}
		$redirect = $this->baseLink()."&shippingTask=setrates&sid={$row->shipping_method_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );
		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}

	/**
	 * Saves the properties for all prices in list
	 *
	 * @return unknown_type
	 */
	function saverates()
	{
		$app = JFactory::getApplication();
		$data = $app->input->getArray($_POST);
		$sid = $app->input->getInt('sid');
		$error = false;
		$this->messagetype  = '';
		$this->message      = '';
		/* $this->includeCustomModel('shippingrates');
		$this->includeCustomTables();
 */
		$model  = $this->getModel('shippingrates');
		$row = $model->getTable();

		$this->message = JText::_('J2STORE_SHIPPING_METHOD_RATE_SAVE_SUCCESS');
		$this->messagetype = 'message';
		foreach ($data['standardrates'] as $item)
		{
			$row->load($item['j2store_shippingrate_id']);
			if (!$row->save($item))
			{	$this->message= $row->getError();
				$this->messagetype = 'notice';
				$error = true;
			}
		}

		if($error)	$this->message = JText::_('J2STORE_ERROR') . " - " . $this->message;

		$redirect = $this->baseLink()."&shippingTask=setrates&sid={$sid}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );
		$this->setRedirect( $redirect, $this->message, $this->messagetype );

	}

	/**
	 * Deletes a shipping rate and redirects
	 *
	 * @return unknown_type
	 */
	function deleterate()
	{
		$model  = $this->getModel( 'shippingrates');
		$sid = JFactory::getApplication()->input->getInt('sid');
		$cids = JFactory::getApplication()->input->get('cid', array(0), 'array');

		$this->message = '';
		foreach ($cids as $cid)
		{
			$row = $model->getTable();
			$row->load( $cid );
			try {
				$row->delete();
				$this->message = JText::_('J2STORE_SHIPPING_METHOD_RATE_DELETE_SUCCESS');
				$this->messagetype = 'message';
			} catch (Exception $e)
			{
				$this->message .= $e->getMessage();
				$this->messagetype = 'notice';
				$error = true;
			}
		}
		if($error)	$this->message = JText::_('J2STORE_ERROR') . " - " . $this->message;
		$redirect = $this->baseLink()."&shippingTask=setrates&sid={$sid}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );
		$this->setRedirect( $redirect, $this->message, $this->messagetype );
	}
}
