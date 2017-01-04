<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/_base.php');
if(!defined('F0F_INCLUDED')) {
	require_once JPATH_LIBRARIES.'/f0f/include.php';
}

class J2StorePaymentPlugin extends J2StorePluginBase
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
	var $_element    = '';
	
	var $_j2version = '';
	
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);		
	}

	/**
	 * Triggered before making the payment
	 * You can perform any modification to the order table variables here. Like setting a surcharge
	 *
	 *
	 * @param $order     object order table object
	 * @return string   HTML to display. Normally an empty one.
	 */
	function _beforePayment( $order )
	{
		// Before the payment
		$html = '';
		return $html;
	}

	/**
	 * Prepares the payment form
	 * and returns HTML Form to be displayed to the user
	 * generally will have a message saying, 'confirm entries, then click complete order'
	 *
	 * Submit button target for onsite payments & return URL for offsite payments should be:
	 * index.php?option=com_j2store&view=billing&task=confirmPayment&orderpayment_type=xxxxxx
	 * where xxxxxxx = $_element = the plugin's filename
	 *
	 * @param $data     array       form post data
	 * @return string   HTML to display
	 */
	function _prePayment( $data )
	{
		// Process the payment

		$vars = new JObject();
		$vars->message = "Preprocessing successful. Double-check your entries.  Then, to complete your order, click Complete Order!";

		$html = $this->_getLayout('prepayment', $vars);
		return $html;
	}

	/**
	 * Processes the payment form
	 * and returns HTML to be displayed to the user
	 * generally with a success/failed message
	 *
	 * IMPORTANT: It is the responsibility of each payment plugin
	 * to tell clear the user's cart (if the payment status warrants it) by using:
	 *
	 * $this->removeOrderItemsFromCart( $order_id );
	 *
	 * @param $data     array       form post data
	 * @return string   HTML to display
	 */
	function _postPayment( $data )
	{
		// Process the payment

		$vars = new JObject();
		$vars->message = "Payment processed successfully.  Hooray!";

		$html = $this->_getLayout('postpayment', $vars);
		return $html;
	}

	/**
	 * Prepares the 'view' tmpl layout
	 * when viewing a payment record
	 *
	 * @param $orderPayment     object       a valid TableOrderPayment object
	 * @return string   HTML to display
	 */
	function _renderView( $orderPayment )
	{
		// Load the payment from _orderpayments and render its html

		$vars = new JObject();
		$vars->full_name        = "";
		$vars->email            = "";
		$vars->payment_method   = $this->_paymentMethods();

		$html = $this->_getLayout('view', $vars);
		return $html;
	}

	/**
	 * Prepares variables for the payment form
	 *
	 * @param $data     array       form post data for pre-populating form
	 * @return string   HTML to display
	 */
	function _renderForm( $data )
	{
		// Render the form for collecting payment info

		$vars = new JObject();
		$vars->full_name        = "";
		$vars->email            = "";
	//	$vars->payment_method   = $this->_paymentMethods();

		$html = $this->_getLayout('form', $vars);
		return $html;
	}

	/**
	 * Verifies that all the required form fields are completed
	 * if any fail verification, set
	 * $object->error = true
	 * $object->message .= '<li>x item failed verification</li>'
	 *
	 * @param $submitted_values     array   post data
	 * @return obj
	 */
	function _verifyForm( $submitted_values )
	{
		$object = new JObject();
		$object->error = false;
		$object->message = '';
		return $object;
	}

	/************************************
	 * Note to 3pd:
	*
	* You shouldn't need to override
	* any of the methods below here
	*
	************************************/

	/**
	 * This method can be executed by a payment plugin after a succesful payment
	 * to perform acts such as enabling file downloads, removing items from cart,
	 * updating product quantities, etc
	 *
	 * @param unknown_type $order_id
	 * @return unknown_type
	 */
	function setOrderPaymentReceived( $order_id )
	{
		//TODO use this method later to update the order table
	}

	/**
	 * Given an order_id, will remove the order's items from the user's cart
	 *
	 * @param unknown_type $order_id
	 * @return unknown_type
	 */
	function removeOrderItemsFromCart( $order_id )
	{
		//TODO Now we clear the total session of the cart. May be this method would fine tune the process
	}

	/**
	 * Tells extension that this is a payment plugin
	 *
	 * @param $element  string      a valid payment plugin element
	 * @return boolean
	 */
	function onJ2StoreGetPaymentPlugins( $element )
	{
		$success = false;
		if ($this->_isMe($element))
		{
			$success = true;
		}
		return $success;
	}

	function onJ2StoreGetPaymentOptions($element, $order)
	{
		// Check if this is the right plugin
		if (!$this->_isMe($element))
		{
			return null;
		}

		$found = true;

		// if this payment method should be available for this order, return true
		// if not, return false.
		// by default, all enabled payment methods are valid, so return true here,
		// but plugins may override this

			$order->setAddress();
			$address = $order->getBillingAddress();
			$geozone_id = $this->params->get('geozone_id', '');

			if(isset($geozone_id) && (int) $geozone_id > 0) {
				//get the geozones
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('gz.*,gzr.*')->from('#__j2store_geozones AS gz')
				->innerJoin('#__j2store_geozonerules AS gzr ON gzr.geozone_id = gz.j2store_geozone_id')
				->where('gz.j2store_geozone_id='.$geozone_id )
				->where('gzr.country_id='.$db->q($address['country_id']).' AND (gzr.zone_id=0 OR gzr.zone_id='.$db->q($address['zone_id']).')');
				$db->setQuery($query);
				$grows = $db->loadObjectList();

				if (!$geozone_id ) {
					$found = true;
				} elseif ($grows) {
					$found = true;
				} else {
					$found = false;
				}
			}

		return $found;
	}



	/**
	 * Wrapper for the internal _renderForm method
	 *
	 * @param $element  string      a valid payment plugin element
	 * @param $data     array       form post data
	 * @return html
	 */
	function onJ2StoreGetPaymentForm( $element, $data )
	{
		if (!$this->_isMe($element))
		{
			return null;
		}

		$html = $this->_renderForm( $data );

		return $html;
	}

	/**
	 * Wrapper for the internal _verifyForm method
	 *
	 * @param $element  string      a valid payment plugin element
	 * @param $data     array       form post data
	 * @return html
	 */
	function onJ2StoreGetPaymentFormVerify( $element, $data )
	{
		if (!$this->_isMe($element))
		{
			return null;
		}

		$html = $this->_verifyForm( $data );

		return $html;
	}

	/**
	 * Wrapper for the internal _renderView method
	 *
	 * @param $element  string      a valid payment plugin element
	 * @param $orderPayment  object      a valid TableOrderPayment object
	 * @return html
	 */
	function onJ2StoreGetPaymentView( $element, $orderPayment )
	{
		if (!$this->_isMe($element))
		{
			return null;
		}

		$html = $this->_renderView( $orderPayment );

		return $html;
	}

	/**
	 * Wrapper for the internal _prePayment method
	 * which performs any necessary actions before payment
	 *
	 * @param $element  string      a valid payment plugin element
	 * @param $data     array       form post data
	 * @return html
	 */
	function onJ2StorePrePayment( $element, $data )
	{
		if (!$this->_isMe($element))
		{
			return null;
		}

		$html = $this->_prePayment( $data );

		return $html;
	}

	/**
	 * Wrapper for the internal _postPayment method
	 * that processes the payment after user submits
	 *
	 * @param $element  string      a valid payment plugin element
	 * @param $data     array       form post data
	 * @return html
	 */
	function onJ2StorePostPayment( $element, $data )
	{
		if (!$this->_isMe($element))
		{
			return null;
		}

		$html = $this->_postPayment( $data );

		return $html;
	}

	/**
	 * Wrapper for the internal _beforePayment method
	 * which performs any necessary actions before payment
	 *
	 * @param $element  string      a valid payment plugin element
	 * @param $order    object      order object
	 * @return html
	 */
	function onJ2StoreBeforePayment( $element, $order)
	{
		if (!$this->_isMe($element))
		{
			return null;
		}

		$html = $this->_beforePayment( $order );

		return $html;
	}
	
	
	public function getVersion() {
		
		if(empty($this->_j2version)) {
			$db = JFactory::getDbo();
			// Get installed version
			$query = $db->getQuery(true);
			$query->select($db->quoteName('manifest_cache'))->from($db->quoteName('#__extensions'))->where($db->quoteName('element').' = '.$db->quote('com_j2store'));
			$db->setQuery($query);
			$registry = new JRegistry;
			$registry->loadString($db->loadResult());
			$this->_j2version = $registry->get('version');
		}
		
		return $this->_j2version;
	}
	
	function getCurrency($order, $convert=false) {
		$results = array();
		$currency_code = $order->currency_code;
		$currency_value = $order->currency_value;
		
		$results['currency_code'] = $currency_code;
		$results['currency_value'] = $currency_value;
		$results['convert'] = $convert;
	
		return $results;
	}

}
