<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreModelPromotions extends F0FModel {

	/**
	 * Method to buildQuery to return list of data
	 * @see F0FModel::buildQuery()
	 * @return query
	 */
	public function buildQuery($overrideLimits = false) {

		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$this->getSelectQuery($query);
		$this->getWhereQuery($query);
		return $query;
	}

	/**
	 * Method to getSelect query
	 * @param unknown_type $query
	 */
	protected function getSelectQuery(&$query)
	{
		$query->select("promotion.extension_id,promotion.name,promotion.type,promotion.folder,promotion.element,promotion.params,promotion.enabled,promotion.ordering")
		->from("#__extensions as promotion");
	}

	protected function getWhereQuery(&$query)
	{
		$query->where("promotion.type='plugin'");
		$query->where("promotion.element LIKE 'promotion_%'");
		$query->where("promotion.folder='j2store'");
	}

	protected function onProcessList(&$resultArray){
		foreach($resultArray as &$res){
			$res->view = JText::_('J2STORE_VIEW');
			$xmlfile = JPATH_SITE.'/plugins/j2store/'.$res->element.'/'.$res->element.'.xml';
			$version = '';
			if(JFile::exists($xmlfile)) {
				$xml = JFactory::getXML($xmlfile);
				$res->version =(string)$xml->version;
			}
		}
	}

	public function getPromotionRates(&$order)
	{
		static $rates;

		if (empty($rates) || !is_array($rates))
		{
			$rates = array();
		}

		if (!empty($rates))
		{
			return $rates;
		}

		$app = JFactory::getApplication();
		JPluginHelper::importPlugin ('j2store');

		$plugins = $this->enabled(1)->getList();
		$rates = array();

		// add taxes, even thought they aren't displayed
		$order_tax = 0;

		// add taxes, even thought they aren't displayed
		$order_tax = 0;
		$orderitems = $order->getItems();
		foreach( $orderitems as $item )
		{
			$order->order_subtotal += $item->orderitem_tax;
			$order_tax += $item->orderitem_tax;
		}


		if ($plugins)
		{
			foreach ($plugins as $plugin)
			{
				$promotionOptions = $app->triggerEvent( "onJ2StoreGetPromotionOptions", array( $plugin->element, $order  ) );
				if (in_array(true, $promotionOptions, true))
				{
					$results = $app->triggerEvent( "onJ2StoreGetPromotionRates", array( $plugin->element, $order  ) );

					foreach ($results as $result)
					{
						if(is_array($result))
						{
							foreach( $result as $r )
							{
								$extra = 0;
								// here is where a global handling rate would be added
								//	if ($global_handling = $this->defines->get( 'global_handling' ))
								//	{
								//		$extra = $global_handling;
								//	}
								$r['extra'] += $extra;
								$r['total'] += $extra;
								$rates[] = $r;
							}
						}
					}
				}
			}
		}
		$order->order_subtotal -= $order_tax;
		return $rates;
	}

}
