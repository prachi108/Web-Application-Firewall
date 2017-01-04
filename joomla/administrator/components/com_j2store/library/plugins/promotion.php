<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/_base.php');

if ( !class_exists( 'J2StorePromotionPlugin' ) )
{

	class J2StorePromotionPlugin extends J2StorePluginBase
	{
		/**
		 * @var $_element  string  Should always correspond with the plugin's filename,
		 *                         forcing it to be unique
		 */
		var $_element = '';

		function __construct( &$subject, $config )
		{
			parent::__construct( $subject, $config );
			$this->loadLanguage( '', JPATH_ADMINISTRATOR );
			$this->loadLanguage( '', JPATH_SITE );
		//	$this->getShopAddress( );
		}



		/************************************
		 * Note to 3pd:
		 *
		 * The methods between here
		 * and the next comment block are
		 * yours to modify by overrriding them in your promotion plugin
		 *
		 ************************************/

		/**
		 * Returns the Promotion Rates.
		 * @param $element the promotion element name
		 * @param $product the product row
		 * @return array
		 */
		public function onJ2StoreGetPromotionRates( $element, $order )
		{
			if ( !$this->_isMe( $element ) )
			{
				return null;
			}

			$rate = array( );
			$rate['name'] = "";
			$rate['code'] = "";
			$rate['price'] = "";
			$rate['extra'] = "";
			$rate['total'] = "";
			$rate['tax'] = "";
			$rate['element'] = $this->_element;
			$rate['error'] = false;
			$rate['errorMsg'] = "";
			$rate['debug'] = "";

			$rates[] = $return;

			return $rates;
		}

		/**
		 * Here you will have to save the promotion rate information
		 *
		 * @param $element the promotion element name
		 * @param $order the order object
		 * @return html
		 */
		public function onJ2StorePostSavePromotion( $element, $order )
		{
			if ( !$this->_isMe( $element ) )
			{
				return null;
			}
		}

		/**
		 * Get a particular promotion rate
		 * @param unknown_type $rate_id
		 */
		public function getPromotionRate( $rate_id )
		{
		}

		/**
		 * Shows the promotion view
		 *
		 * @param $row	the promotion data
		 * @return unknown_type
		 */
		public function onJ2StoreGetPromotionView( $row )
		{
			if ( !$this->_isMe( $row ) )
			{
				return null;
			}
		}

		/**
		 * If you want to show something on the product admin page,
		 * override this function
		 *
		 * @param $product the product row
		 * @return html
		 */
		public function onJ2StoreGetProductView( $product )
		{
			// show something on the product admin page
		}

		/**
		 * If you have to deal with the product data after the save
		 *
		 * @param $product the product row
		 * @return html
		 */
		public function onJ2StoreAfterSaveProducts( $product )
		{
			// Do Something here with the product data
		}

		/************************************
		 * Note to 3pd:
		 *
		 * DO NOT MODIFY ANYTHING AFTER THIS
		 * TEXT BLOCK UNLESS YOU KNOW WHAT YOU
		 * ARE DOING!!!!!
		 *
		 ************************************/

		/**
		 * Tells extension that this is a promotion plugin
		 *
		 * @param $element  string      a valid promotion plugin element
		 * @return boolean	true if it is this particular promotion plugin
		 */
		public function onJ2StoreGetPromotionPlugins( $element )
		{
			$success = false;
			if ( $this->_isMe( $element ) )
			{
				$success = true;
			}
			return $success;
		}

	/**
     * Determines if this promotion option is valid for this order
     *
     * @param $element
     * @param $order
     * @return unknown_type
     */
    function onJ2StoreGetPromotionOptions($element, $order)
    {
        // Check if this is the right plugin
        if (!$this->_isMe($element))
        {
            return null;
        }

        $found = true;
        $geozones = $this->params->get('geozones');

        //return true if we have empty geozones
        if(!empty($geozones))
        {
        	$found = false;

          	$geozones = explode(',', $geozones);
          	$orderGeoZones = $order->getPromotionGeoZones();

          	//loop to see if we have at least one geozone assigned
          	foreach( $orderGeoZones as $orderGeoZone )
          	{
          		if(in_array($orderGeoZone->geozone_id, $geozones))
          		{
          			$found = true;
          			break;
          		}
          	}
        }
        // if this promotion methods should be available for this order, return true
        // if not, return false.
        // by default, all enabled promotion methods are valid, so return true here,
        // but plugins may override this
        return $found;
    }


		/**
		 * Gets the reports namespace for state variables
		 * @return string
		 */
		protected function _getNamespace( )
		{
			$app = JFactory::getApplication( );
			$ns = $app->getName( ) . '::' . 'com.j2store.promotion.' . $this->get( '_element' );
		}

		/**
		 * Get the task for the promotion plugin controller
		 */
		public static function getPromotionTask( )
		{
		 	return JFactory::getApplication()->input->getString( 'promotionTask', '' );

		}

		/**
		 * Get the id of the current promotion plugin
		 */
		public static function getPromotionId( )
		{
			return JFactory::getApplication()->input->getInt( 'sid', '' );
		}

		/**
		 * Get a variable from the JRequest object
		 * @param unknown_type $name
		 */
		public function getPromotionVar( $name )
		{
			$var = JFactory::getApplication()->input->getString( $name, '' );
			return $var;
		}

		
		function checkAddress( $address )
		{
			$this->includeJ2StoreTables( );

			if ( empty( $address->zone_code ) )
			{
				if ( !empty( $address->zone_id ) )
				{
					$table = F0FTable::getInstance( 'Zones', 'Table' );
					$table->load( $address->zone_id );
					$address->zone_code = $table->zone_code;
				}
			}

			if ( empty( $address->country_code ) || empty( $address->country_name ) || empty( $address->country_isocode_2 )
			|| empty( $address->country_isocode_3 ) )
			{
				if ( !empty( $address->country_id ) )
				{
					$table = F0FTable::getInstance( 'Country', 'Table' );
					$table->load( $address->country_id );
					$address->country_name = $table->country_name;
					$address->country_isocode_3 = $table->country_isocode_3;
					$address->country_isocode_2 = $table->country_isocode_2;
					$address->country_code = $table->country_isocode_2;
				}
			}

			return $address;
		}

		function getZone($zone_id) {
			$this->includeJ2StoreTables( );
			$table = F0FTable::getAnInstance('Zone' , 'J2StoreTable');
			if ( !empty( $zone_id ) )
			{
				$table->load( $zone_id );
			}
			return $table;
		}

		function getCountry($country_id) {
			$this->includeJ2StoreTables( );
			$table = F0FTable::getAnInstance('Country' , 'J2StoreTable');
			if(!empty($country_id)) {
				$table->load($country_id);
			}
			return $table;
		}

		/**
		 * Prepares the 'view' tmpl layout
		 * when viewing a report
		 *
		 * @return unknown_type
		 */
		function _renderView( $view = 'view', $vars = null )
		{
			if ( $vars == null ) $vars = new JObject( );
			$html = $this->_getLayout( $view, $vars );

			return $html;
		}

		/**
		 * Prepares variables for the report form
		 *
		 * @return unknown_type
		 */
		function _renderForm($data )
		{
			$vars = new JObject( );
			$html = $this->_getLayout( 'form', $vars );

			return $html;
		}

		/**
		 * Gets the appropriate values from the request
		 *
		 * @return unknown_type
		 */
		function _getState( )
		{
			$state = new JObject( );

			foreach ( $state->getProperties( ) as $key => $value )
			{
				//$new_value = JRequest::getVar( $key );
				$new_value = JFactory::getApplication()->input->getString($key);
				$value_exists = array_key_exists( $key, JRequest::get( 'post' ) );
				if ( $value_exists && !empty( $key ) )
				{
					$state->$key = $new_value;
				}
			}
			return $state;
		}

	}

}
