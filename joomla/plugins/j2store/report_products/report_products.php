<?php
/**
 * --------------------------------------------------------------------------------
 * Report Plugin - Products
 * --------------------------------------------------------------------------------
 * @package     Joomla 3.x
 * @subpackage  J2 Store
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2015 J2Store . All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://j2store.org
 * --------------------------------------------------------------------------------
 *
 * */
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/report.php');
class plgJ2StoreReport_Products extends J2StoreReportPlugin
{

	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
	var $_element   = 'report_products';
	
	function __construct($subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage('plg_j2store_'.$this->_element, JPATH_ADMINISTRATOR);
	}
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
		JToolBarHelper::title(JText::_('J2STORE_REPORT').'-'.JText::_('PLG_J2STORE_'.strtoupper($this->_element)),'j2store-logo');
		JToolbarHelper::back('J2STORE_BACK','index.php?option=com_j2store&view=reports');

		$vars = new JObject();
		$this->includeCustomModel('Reportproducts');
		$this->includeCustomTables();
		$model = F0FModel::getTmpInstance('ReportProducts','J2StoreModel');
		$model = $this->getProductList ($model);
		$vars->state = $model->getState();
		$vars->total = $model->getTotal();
		$vars->orderStatus = $this->getOrderStatus();
		$vars->orderDateType = $this->getOrderDateType();
		$vars->shippingmethod = $this->getShippingMethod();
		$vars->paymentmethod = $this->getPaymentMethod();
		$vars->filtertype = $this->getFilterType();
		$vars->manufacture = $this->getManufacture();
		$vars->vendor = $this->getVendor();
		$vars->vat = $this->getVat();	
		$lists = $model->getList();
		$product = array();
		foreach ($lists as $prod){
			if(array_key_exists($prod->variant_id, $product)){
				$product[$prod->variant_id]->orderitem_quantity += $prod->orderitem_quantity;
				$product[$prod->variant_id]->orderitem_per_item_tax += $prod->orderitem_per_item_tax;
				$product[$prod->variant_id]->orderitem_tax += $prod->orderitem_tax;
				$product[$prod->variant_id]->orderitem_discount += $prod->orderitem_discount;
				$product[$prod->variant_id]->orderitem_discount_tax += $prod->orderitem_discount_tax;
				$product[$prod->variant_id]->orderitem_price += $prod->orderitem_price;
				$product[$prod->variant_id]->orderitem_option_price += $prod->orderitem_option_price;
				$product[$prod->variant_id]->orderitem_finalprice += $prod->orderitem_finalprice;
				$product[$prod->variant_id]->orderitem_finalprice_with_tax += $prod->orderitem_finalprice_with_tax;
				$product[$prod->variant_id]->orderitem_finalprice_without_tax += $prod->orderitem_finalprice_without_tax;
				$product[$prod->variant_id]->orderitem_weight_total += $prod->orderitem_weight_total;
				$product[$prod->variant_id]->sold = $prod->sold;
				if($prod->j2store_orderstatus_id == 1){
					$product[$prod->variant_id]->sold_current += $prod->orderitem_quantity;
				}
				$product[$prod->variant_id]->order_list[] = $prod->order_id;
			}else{
				$prod->order_list[] = $prod->order_id;
				$product[$prod->variant_id]= $prod;
				if($prod->j2store_orderstatus_id == 1){
					$product[$prod->variant_id]->sold_current = $prod->orderitem_quantity;
				}else{
					$product[$prod->variant_id]->sold_current = 0;
				}

			}
		}
		$product_amount = array();
		$product_name = array();
		foreach($product as $prod){
			$product_amount[] = round($prod->orderitem_finalprice_with_tax);
			$product_name[] = $prod->orderitem_name;
		}
		$vars->product_amount = $product_amount;
		$vars->product_name = $product_name;
		$vars->products = $product;

		//$vars->list = $lists;
		$vars->pagination = $model->getSFPagination($vars->products);//$model->getPagination();
		$vars->params = JComponentHelper::getParams('com_j2store');				
		$id = $app->input->getInt('id', '0');

		$vars->id = $id;
		$form = array();
		$form['action'] = "index.php?option=com_j2store&view=report&task=view&id={$id}";
		$vars->form = $form;
		$html = $this->_getLayout('default', $vars);
		return $html;	
	}

	function getProductList($model){
		$app = JFactory::getApplication();
		$option = 'com_j2store';
		$ns = $option.'.reportproducts';
		$data = $app->input->getArray($_REQUEST);
		$model->setState('filter_search', $app->input->getString('filter_search'));
		if(isset($data['filter_orderstatus'])){
			$model->setState('filter_orderstatus', $data['filter_orderstatus']);
		}
		$model->setState('filter_order', $app->input->getString('filter_order'));
		$model->setState('filter_order_Dir', $app->input->getString('filter_order_Dir'));
		//filer for date
		$model->setState('filter_datetype',$app->input->getString('filter_datetype'));
		$model->setState('filter_order_from_date',$app->input->getString('filter_order_from_date'));
		$model->setState('filter_order_to_date',$app->input->getString('filter_order_to_date'));
		$model->setState('filter_shippingmethod',$app->input->getString('filter_shippingmethod'));
		$model->setState('filter_paymentmethod',$app->input->getString('filter_paymentmethod'));
		$model->setState('filter_display_type',$app->input->getString('filter_display_type'));
		$model->setState('filter_coupon_search',$app->input->getString('filter_coupon_search'));
		$model->setState('filter_manufacture',$app->input->getString('filter_manufacture'));
		$model->setState('filter_vendor',$app->input->getString('filter_vendor'));
		$model->setState('filter_taxsearch',$app->input->getString('filter_taxsearch'));
		$model->setState('filter_postcodesearch',$app->input->getString('filter_postcodesearch'));
		$model->setState('filter_order_from_qty',$app->input->getString('filter_order_from_qty'));
		$model->setState('filter_order_to_qty',$app->input->getString('filter_order_to_qty'));
		$model->setState('filter_vat',$app->input->getString('filter_vat'));
		// Get the pagination request variables
		$limit		= $app->getUserStateFromRequest( 'global.list.limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$limitstart	= $app->getUserStateFromRequest( $ns.'.limitstart', 'limitstart', 0, 'int' );

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$model->setState('list.limit', $limit);
		$model->setState('list.start', $limitstart);

		return $model;
	}


	//export csv
	function onJ2StoreGetReportExported($row){
		$app = JFactory::getApplication();
		$this->includeCustomModel('ReportProducts');
		if (!$this->_isMe($row))
		{
			return null;
		}
		$model = F0FModel::getTmpInstance('ReportProducts','J2StoreModel');
		$model = $this->getProductList ( $model );
		$items = $model->getList();

		$product = array();
		foreach ($items as $prod){
			if(array_key_exists($prod->variant_id, $product)){
				$product[$prod->variant_id]->orderitem_quantity += $prod->orderitem_quantity;
				$product[$prod->variant_id]->orderitem_per_item_tax += $prod->orderitem_per_item_tax;
				$product[$prod->variant_id]->orderitem_tax += $prod->orderitem_tax;
				$product[$prod->variant_id]->orderitem_discount += $prod->orderitem_discount;
				$product[$prod->variant_id]->orderitem_discount_tax += $prod->orderitem_discount_tax;
				$product[$prod->variant_id]->orderitem_price += $prod->orderitem_price;
				$product[$prod->variant_id]->orderitem_option_price += $prod->orderitem_option_price;
				$product[$prod->variant_id]->orderitem_finalprice += $prod->orderitem_finalprice;
				$product[$prod->variant_id]->orderitem_finalprice_with_tax += $prod->orderitem_finalprice_with_tax;
				$product[$prod->variant_id]->orderitem_finalprice_without_tax += $prod->orderitem_finalprice_without_tax;
				$product[$prod->variant_id]->orderitem_weight_total += $prod->orderitem_weight_total;
				$product[$prod->variant_id]->sold = $prod->sold;
				if($prod->j2store_orderstatus_id == 1){
					$product[$prod->variant_id]->sold_current += $prod->orderitem_quantity;
				}
				$product[$prod->variant_id]->order_list[] = $prod->order_id;
			}else{
				$prod->order_list[] = $prod->order_id;
				$product[$prod->variant_id]= $prod;
				if($prod->j2store_orderstatus_id == 1){
					$product[$prod->variant_id]->sold_current = $prod->orderitem_quantity;
				}else{
					$product[$prod->variant_id]->sold_current = 0;
				}

			}
		}
		$name = JText::_('PLG_J2STORE_PRODUCT_NAME');
		$quantity = JText::_('J2STORE_REPORT_TOTAL_QUANTITY');
		$discount_text = JText::_('J2STORE_REPORT_PRODUCT_DISCOUNT');
		$without_tax = JText::_('J2STORE_REPORT_PRODUCT_WITHOUT_TAX');
		$with_tax =JText::_('J2STORE_REPORT_PRODUCT_WITH_TAX');
		$total_text = JText::_('J2STORE_TOTAL');


		$currency = J2Store::currency ();
		$export = array();
		$qty_total = 0;
		$discount_total = 0;
		$total_without_tax = 0;
		$total_with_tax = 0;

		foreach ($product as $item){
			$sample = new stdClass();
			$sample->$name = $item->orderitem_name;
			$sample->$quantity = $item->orderitem_quantity;
			$sample->$discount_text = $currency->format($item->orderitem_discount+$item->orderitem_discount_tax);
			$sample->$without_tax = $currency->format($item->orderitem_finalprice_without_tax);
			$sample->$with_tax = $currency->format($item->orderitem_finalprice_with_tax);
			$export[]=$sample;
			$qty_total += $item->orderitem_quantity;
			$discount_total += $item->orderitem_discount+$item->orderitem_discount_tax;
			$total_without_tax +=  $item->orderitem_finalprice_without_tax;
			$total_with_tax +=  $item->orderitem_finalprice_with_tax;
		}
		$final_data = new stdClass();
		$final_data->$name = $total_text;
		$final_data->$quantity = $qty_total;
		$final_data->$discount_text = $currency->format($discount_total);
		$final_data->$without_tax = $currency->format($total_without_tax);
		$final_data->$with_tax = $currency->format($total_with_tax);
		$export[]=$final_data;

		return $export;
	}
	//get vendor filter
	function getVendor(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query = "SELECT v.*,a.* FROM #__j2store_vendors as v
				LEFT JOIN #__j2store_addresses a ON v.address_id = a.j2store_address_id";
		$db->setQuery($query);
		$row = $db->loadObjectList();
		$data =array();
		$data[] = JText::_('PLG_J2STORE_VENDOR_SELECT');
		foreach($row as $item){
	
			$data[$item->j2store_vendor_id] = JText::_($item->first_name.' '.$item->last_name);
		}
		return $data;
	}
	//get Manufature
	function getManufacture(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query = "SELECT m.*,a.* FROM #__j2store_manufacturers as m
				LEFT JOIN #__j2store_addresses a ON m.address_id = a.j2store_address_id";
		$db->setQuery($query);
		$row = $db->loadObjectList();
		$data =array();
		$data[] = JText::_('PLG_J2STORE_MANUFACTURE_SELECT');
		foreach($row as $item){
	
			$data[$item->j2store_manufacturer_id] = JText::_($item->company);
		}
		return $data;
	}
	/**
	 * Method to get order status
	 */
	public function getOrderStatus(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("*")->from("#__j2store_orderstatuses");
		$db->setQuery($query);
		$row = $db->loadObjectList();
		$data =array();
		foreach($row as $item){
				
			$data[$item->j2store_orderstatus_id] = JText::_($item->orderstatus_name);
		}
		return $data;
	}
	//search order by days type
	public function getOrderDateType(){
		$data = array(
				'select' =>JText::_('J2STORE_DAY_TYPES'),
				'today' => JText::_('J2STORE_TODAY'),
				'this_week' => JText::_('J2STORE_THIS_WEEK'),
				'this_month' => JText::_('J2STORE_THIS_MONTH'),
				'this_year' => JText::_('J2STORE_THIS_YEAR'),
				'last_7day' => JText::_('J2STORE_LAST_7_DAYS'),
				'last_month' => JText::_('J2STORE_LAST_MONTH'),
				'last_year' => JText::_('J2STORE_LAST_YEAR'),
				'custom' => JText::_('J2STORE_CUSTOM')
		);
		return $data;
	}
	//tax type
	public function getVat(){
		$data = array(
				'select' =>JText::_('J2STORE_TAX_TYPE'),
				'with_tax' => JText::_('J2STORE_WITH_TAX'),
				'without_tax' => JText::_('J2STORE_WITHOUT_TAX')
		);
		return $data;
	}
	//search fiter type
	public function getFilterType(){
		$data = array(
				'order' => 'By Order',
				'category' => 'By Category',
				'product' => 'By Product'
		);
		return $data;
	}
	
	//get shipping method
	public function getShippingMethod(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query = "SELECT * FROM #__extensions
				WHERE type='plugin' AND folder = 'j2store'
				AND element like 'shipping_%'";
		$db->setQuery($query);
		$row = $db->loadObjectList();
		$j2store_shipping = array(
				'shipping_standard',
				'shipping_postcode',
				'shipping_additional',
				'shipping_incremental',
				'shipping_flatrate_advanced'
		);
		$data =array();
		$data[] = JText::_('PLG_J2STORE_ORDER_SHIP_SELECT');
		foreach($row as $item){
			if(in_array($item->element, $j2store_shipping)){
				$query = "SELECT * FROM #__j2store_shippingmethods";
				$db->setQuery($query);
				$ship_methods = $db->loadObjectList();
				//print_r($ship_methods);exit;
				foreach ($ship_methods as $ship_method){
					if(!in_array($ship_method->shipping_method_name, $data)){
						$data[$item->element.'-'.$ship_method->shipping_method_name] = JText::_($ship_method->shipping_method_name);
					}
				}
			}else{
				$data[$item->element] = JText::_($item->name);
			}
				
		}
		return $data;
	}
	//get payment methods
	public function getPaymentMethod(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query = "SELECT * FROM #__extensions
				WHERE type='plugin' AND folder = 'j2store'
				AND element like 'payment_%'";
		$db->setQuery($query);
		$row = $db->loadObjectList();
		$data =array();
		$data[] = JText::_('PLG_J2STORE_ORDER_PAYMENT_SELECT');
		foreach($row as $item){
	
			$data[$item->element] = JText::_($item->name);
		}
		return $data;
	}
	
}