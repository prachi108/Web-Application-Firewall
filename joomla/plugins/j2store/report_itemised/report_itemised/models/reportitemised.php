<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

class J2StoreModelReportItemised extends F0FModel
{
	/*
	 * @var array
	 */
	var $_data = null;

	/**
	 *
	 * @var integer
	 */
	var $_total = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	var $_pagination = null;



	/**
	 *
	 * @access public
	 * @return array
	 */
	public function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$list = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
			foreach ($list as $item){
				$item->orderitem_attributes = F0FModel::getTmpInstance('OrderitemAttributes','J2StoreModel')->orderitem_id($item->j2store_orderitem_id)->getList();
			}
			$this->_data = $list;
		}

		return $this->_data;
	}


	/**
	 * Get the number of all items
	 *
	 * @return  integer
	 */
	public function getTotal()
	{
		if (is_null($this->total))
		{
			$query = $this->buildCountQuery();

			if ($query === false)
			{
				$subquery = $this->_buildQuery(false);
				$subquery->clear('order');
				$query = $this->_db->getQuery(true)
				->select('COUNT(*)')
				->from("(" . (string) $subquery . ") AS a");
			}

			$this->_db->setQuery((string) $query);

			$this->total = $this->_db->loadResult();
		}

		return $this->total;
	}





	public function getPagination()
	{
		if (empty($this->pagination))
		{
			// Import the pagination library
			JLoader::import('joomla.html.pagination');

			// Prepare pagination values
			$total = $this->getTotal();
			$limitstart = $this->getState('limitstart');
			$limit = $this->getState('limit');

			// Create the pagination object
			$this->pagination = new JPagination($total, $limitstart, $limit);
		}

		return $this->pagination;
	}


	/**
	 * Method to buildQuery
	 * @return Query object
	 */
	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query

		$query = JFactory::getDbo()->getQuery(true);
		$query->select('oi.j2store_orderitem_id,oi.orderitem_name,oi.product_id,oi.orderitem_quantity');
		$query->select('count(oi.product_id) AS count');
		$query->select('product.product_source_id');
		$query->select('cont.id');
		$query->select('category.title AS category_name');
		$query->select('SUM(oi.orderitem_quantity) AS sum');
		$query->from('#__j2store_orderitems AS oi');
		$query->leftJoin('#__j2store_products AS product ON product.j2store_product_id=oi.product_id');
	 	$query->leftJoin('#__content AS cont ON cont.id=product.product_source_id');
		$query->leftJoin('#__categories AS category ON category.id=cont.catid');
		$this->_buildContentWhere($query);
		$query->group('oi.product_id,oi.orderitem_attributes');
		return $query;
	}

	public function buildQuery($overrideLimits = false)
	{
		$query = JFactory::getDbo()->getQuery(true);
		$query->select('oi.*');
		$query->select('count(oi.product_id) AS count');
		$query->select('SUM(oi.orderitem_quantity) AS sum');
		$query->from('#__j2store_orderitems AS oi');
		$query->leftJoin('#__content AS product ON product.id=oi.product_id');
		$query->select('category.title AS category_name');
		$query->leftJoin('#__categories AS category ON category.id=product.catid');
		$this->_buildContentWhere($query);
		$query->group('oi.product_id,oi.orderitem_attributes');
	}

	function _buildContentWhere($query)
	{
		$mainframe = JFactory::getApplication();
		$option = 'com_j2store';
		$ns = $option.'.report';
		$db					=JFactory::getDBO();
		$filter_order		= $mainframe->getUserStateFromRequest( $ns.'filter_order',		'filter_order',		'oi.order_id',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $ns.'filter_order_Dir',	'filter_order_Dir',	'ASC',				'word' );
		$filter_orderstate	= $mainframe->getUserStateFromRequest( $ns.'filter_orderstate',	'filter_orderstate',	'',			'word' );
		$search				= $mainframe->getUserStateFromRequest( $ns.'filter_search',			'filter_search',			'',				'string' );
		if (strpos($search, '"') !== false) {
			$search = str_replace(array('=', '<'), '', $search);
		}
		$search = JString::strtolower($search);

		$where = array();

		if ($search) {
			$where[] = 'LOWER(oi.orderitem_name) LIKE '.$db->Quote( '%'.$db->escape( $search, true ).'%', false ).
			           'OR LOWER(oi.product_id) LIKE '.$db->Quote( '%'.$db->escape( $search, true ).'%', false );
		}

		if($filter_orderstate) {
			if($filter_orderstate == 'Confirmed') {
				$where[] = 'a.order_state = '.$db->Quote($db->escape( $filter_orderstate, true ),false);
			} else if($filter_orderstate == 'Pending') {
				$where[] = 'a.order_state = '.$db->Quote($db->escape( $filter_orderstate, true ),false);
			} else if($filter_orderstate == 'Failed') {
				$where[] = 'a.order_state = '.$db->Quote($db->escape( $filter_orderstate, true ),false);
			}
		}
		foreach($where as $w) {
			$query->where($w);
		}
		if(!empty($filter_order))
		$query->order($filter_order.'  '.$filter_order_Dir);

		$query->order('oi.order_id');
		return;
	}

	function _getOrderID($id) {

			$db = JFactory::getDBO();
			$query = "SELECT order_id FROM #__j2store_orders WHERE id={$id}";
			$db->setQuery($query);
			return $db->loadResult();

	}

	function _getOrderItemIDs($id) {

		//first get the order_id
		$order_id = $this->_getOrderID($id);

		//get the order item ids
		$db = JFactory::getDBO();
		$query = "SELECT orderitem_id FROM #__j2store_orderitems WHERE order_id=".$db->Quote($order_id);
		$db->setQuery($query);
		return $db->loadResultArray();
	}

	/**
	 * Method to get Processed array of data for Export
	 * @param object array $data
	 * return array
	 */
	public function export($data){
		$status;
		$export_data = array();
		foreach($data as $i => $item){
			$export_data[$i]['product_id']= $item->product_id;
			$export_data[$i]['product_name']= $item->orderitem_name;
			$option =array();
			if(isset($item->orderitem_attributes) && $item->orderitem_attributes){
				$string = '';
				foreach($item->orderitem_attributes as $a =>$attr){
					$string .=$attr->orderitemattribute_name.' : '.$attr->orderitemattribute_value;
				}
				$export_data[$i]['item_option'] = $string;
			}
			$export_data[$i]['category_name']= $item->category_name;
			$export_data[$i]['product_qty'] =$item->sum;
			$export_data[$i]['no_of_orders']= $item->count;
		}

		/*  $header = $this->getHeaderfields($export_data);
		require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/csv.php');
		$exporter = new J2StoreCSVExport();

		$exporter->headerAry =  $header;
		$exporter->dataAry = $export_data;
		$exporter->filename = 'j2store_report_itemised_export_';
		$exporter->csv();
		$exporter->download();
		JFactory::getApplication()->close(); */

		return $export_data;
	}

	/**
	 * Method to get Header Fileds for file Export
	 * @return array;
	 */
	public function getHeaderfields($export_data){
	$lang = JFactory::getLanguage()->load('plg_j2store_report_itemised', JPATH_ADMINISTRATOR);
	$data =array();
	$data[]=JText::_("J2STORE_PRODUCT_ID");
	$data[] = JText::_("J2STORE_PRODUCT_NAME");
	$data[] = JText::_("J2STORE_PRODUCT_OPTIONS");
	$data[] = JText::_("JCATEGORY");
	$data[] = JText::_("J2STORE_QUANTITY");
	$data[] = JText::_("J2STORE_REPORTS_ITEMISED_PURCHASES");
	return $data;
	}

}
