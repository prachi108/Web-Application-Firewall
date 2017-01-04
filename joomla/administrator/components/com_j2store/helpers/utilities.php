<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

/**
 * J2Store helper.
 */

class J2Utilities {

	public static $instance = null;
	protected $state;
	private $_is_cleaned = false;

	public function __construct($properties=null) {

	}

	public static function getInstance(array $config = array())
	{
		if (!self::$instance)
		{
			self::$instance = new self($config);
		}

		return self::$instance;
	}
	
	public function clear_cache() {
		
		//clean it just once.
		if(!$this->_is_cleaned) {
			$cache = JFactory::getCache();
			$cache->clean('com_j2store');
			$cache->clean('com_content');
			$this->_is_cleaned = true;			
		}
		
	}
	
	public function nocache() {
		if(headers_sent()) return false;		
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');
		header('Expires: Wed, 17 Sep 1975 21:32:10 GMT');
		return true;
	}

	public function isJson($string) {
		json_decode($string);
		if(function_exists('json_last_error')) {
			return (json_last_error() == JSON_ERROR_NONE);
		}
		return true;
	}


	/**
	 * Method to convert an object or an array to csv
	 * @param mixed $data array or object
	 * @return string comma seperated value
	 */

	public function to_csv($data) {
		$csv = '';

		//data is set ?
		if(!isset($data)) return $csv;

		$array = array();
		if(is_object($data)) {
			$array = JArrayHelper::fromObject($data);
		} elseif(is_array($data)) {
			$array = $data;
		}else {
			//seems to be a string. So type cast it
			$ids = (array) $data;
		}
		$csv = implode(',', $array);
		return $csv;
	}

	/**
	 * Method to format stock quantity
	 * @param Float|Int $qty An int or a float value can be formated here.
	 * @return mixed
	 */

	public function stock_qty($qty) {
		//allow plugins to modify
		JFactory::getApplication('OnJ2StoreFilterQuantity', array(&$qty));
		return intval($qty);
	}
	
	public function errors_to_string($errors) {
		return $this->toString($errors);
	}
	
	public static function toString($array = null, $inner_glue = '=', $outer_glue = '\n', $keepOuterKey = false)
	{
		$output = array();
	
		if (is_array($array))
		{
			foreach ($array as $key => $item)
			{
				if (is_array($item))
				{
					if ($keepOuterKey)
					{
						$output[] = $key;
					}
					// This is value is an array, go and do it again!
					$output[] = self::toString($item, $inner_glue, $outer_glue, $keepOuterKey);
				}
				else
				{
					$output[] = $item;
				}
			}
		}
	
		return implode($outer_glue, $output);
	}
	
	// Character limit
	public static function characterLimit($str, $limit = 150, $end_char = '...')
	{
		if (JString::trim($str) == '')
			return $str;
	
		// always strip tags for text
		$str = strip_tags(JString::trim($str));
	
		$find = array("/\r|\n/u", "/\t/u", "/\s\s+/u");
		$replace = array(" ", " ", " ");
		$str = preg_replace($find, $replace, $str);
	
		if (JString::strlen($str) > $limit)
		{
			$str = JString::substr($str, 0, $limit);
			return JString::rtrim($str).$end_char;
		}
		else
		{
			return $str;
		}
	
	}
	
	// Cleanup HTML entities
	public static function cleanHtml($text)
	{
		return htmlentities($text, ENT_QUOTES, 'UTF-8');
	}
	
	public function cleanIntArray($array, $db = null) {
		if (! $db)
			$db = JFactory::getDbo ();
		if (is_array ( $array )) {
			$results = array ();
			foreach ( $array as $id ) {
				$clean = ( int ) $id;
				if (! in_array ( $id, $results )) {
					$results [] = $db->q ( $clean );
				}
			}
			return $results;
		} else {
			return $array;
		}
	}
	
	public function getContext($prefix='') {
		$app = JFactory::getApplication();
		$context = array();
		$context[] = 'j2store';
		
		if($app->isSite()) {
			$context[] = 'site';
		}else {
			$context[] = 'admin';
		}
		$context[] = $app->input->getCmd('view', '');
		$context[] = $app->input->getCmd('task', '');
		return implode('.', $context).$prefix;		
	}
	
	public function get_formatted_date($local=true, $options=array()) {
		$tz = JFactory::getConfig()->get('offset');
		$date = JFactory::getDate('now', $tz);
		
		//default to the sql formatted date
		$result = $date->toSql($local);
		
		if(isset($options['formatted']) && $options['formatted']) {
			//format option is set.
			$format = isset($options['format']) ? $options['format'] : 'Y-m-d'; 
			$result = $date->format($format,$local);
		}
		return $result;
	}

	public function generateId($string){
		if(empty( $string )){
			return $string;
		}
		return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
	}

	public function world_currencies() {
		return array (
			'USD' => 'United States Dollar',
			'EUR' => 'Euro Member Countries',
			'GBP' => 'United Kingdom Pound',
			'AUD' => 'Australia Dollar',
			'NZD' => 'New Zealand Dollar',
			'CHF' => 'Switzerland Franc',
			'RUB' => 'Russia Ruble',
            'ALL' => 'Albania Lek',
			'AED' => 'Emirati Dirham',
            'AFN' => 'Afghanistan Afghani',
            'ARS' => 'Argentina Peso',
            'AWG' => 'Aruba Guilder',
            'AZN' => 'Azerbaijan New Manat',
            'BSD' => 'Bahamas Dollar',
            'BBD' => 'Barbados Dollar',
            'BDT' => 'Bangladeshi taka',
            'BYR' => 'Belarus Ruble',
            'BZD' => 'Belize Dollar',
            'BMD' => 'Bermuda Dollar',
            'BOB' => 'Bolivia Boliviano',
            'BAM' => 'Bosnia and Herzegovina Convertible Marka',
            'BWP' => 'Botswana Pula',
            'BGN' => 'Bulgaria Lev',
            'BRL' => 'Brazil Real',
            'BND' => 'Brunei Darussalam Dollar',
            'KHR' => 'Cambodia Riel',
            'CAD' => 'Canada Dollar',
            'KYD' => 'Cayman Islands Dollar',
            'CLP' => 'Chile Peso',
            'CNY' => 'China Yuan Renminbi',
            'COP' => 'Colombia Peso',
            'CRC' => 'Costa Rica Colon',
            'HRK' => 'Croatia Kuna',
            'CUP' => 'Cuba Peso',
            'CZK' => 'Czech Republic Koruna',
            'DKK' => 'Denmark Krone',
            'DOP' => 'Dominican Republic Peso',
            'XCD' => 'East Caribbean Dollar',
            'EGP' => 'Egypt Pound',
            'SVC' => 'El Salvador Colon',
            'EEK' => 'Estonia Kroon',
            'FKP' => 'Falkland Islands (Malvinas) Pound',
            'FJD' => 'Fiji Dollar',
            'GHC' => 'Ghana Cedis',
            'GIP' => 'Gibraltar Pound',
            'GTQ' => 'Guatemala Quetzal',
            'GGP' => 'Guernsey Pound',
            'GYD' => 'Guyana Dollar',
            'HNL' => 'Honduras Lempira',
            'HKD' => 'Hong Kong Dollar',
            'HUF' => 'Hungary Forint',
            'ISK' => 'Iceland Krona',
            'INR' => 'India Rupee',
            'IDR' => 'Indonesia Rupiah',
            'IRR' => 'Iran Rial',
            'IMP' => 'Isle of Man Pound',
            'ILS' => 'Israel Shekel',
            'JMD' => 'Jamaica Dollar',
            'JPY' => 'Japan Yen',
            'JEP' => 'Jersey Pound',
            'KZT' => 'Kazakhstan Tenge',
            'KPW' => 'Korea (North) Won',
            'KRW' => 'Korea (South) Won',
            'KGS' => 'Kyrgyzstan Som',
            'LAK' => 'Laos Kip',
            'LVL' => 'Latvia Lat',
            'LBP' => 'Lebanon Pound',
            'LRD' => 'Liberia Dollar',
            'LTL' => 'Lithuania Litas',
            'MKD' => 'Macedonia Denar',
            'MYR' => 'Malaysia Ringgit',
            'MUR' => 'Mauritius Rupee',
            'MXN' => 'Mexico Peso',
            'MNT' => 'Mongolia Tughrik',
            'MZN' => 'Mozambique Metical',
            'NAD' => 'Namibia Dollar',
            'NPR' => 'Nepal Rupee',
            'ANG' => 'Netherlands Antilles Guilder',

            'NIO' => 'Nicaragua Cordoba',
            'NGN' => 'Nigeria Naira',
            'NOK' => 'Norway Krone',
            'OMR' => 'Oman Rial',
            'PKR' => 'Pakistan Rupee',
            'PAB' => 'Panama Balboa',
            'PYG' => 'Paraguay Guarani',
            'PEN' => 'Peru Nuevo Sol',
            'PHP' => 'Philippines Peso',
            'PLN' => 'Poland Zloty',
            'QAR' => 'Qatar Riyal',
            'RON' => 'Romania New Leu',
            'SHP' => 'Saint Helena Pound',
            'SAR' => 'Saudi Arabia Riyal',
            'RSD' => 'Serbia Dinar',
            'SCR' => 'Seychelles Rupee',
            'SGD' => 'Singapore Dollar',
            'SBD' => 'Solomon Islands Dollar',
            'SOS' => 'Somalia Shilling',
            'ZAR' => 'South Africa Rand',
            'LKR' => 'Sri Lanka Rupee',
            'SEK' => 'Sweden Krona',
            'SRD' => 'Suriname Dollar',
            'SYP' => 'Syria Pound',
            'TWD' => 'Taiwan New Dollar',
            'THB' => 'Thailand Baht',
            'TTD' => 'Trinidad and Tobago Dollar',
            'TRY' => 'Turkey Lira',
            'TRL' => 'Turkey Lira',
            'TVD' => 'Tuvalu Dollar',
            'UAH' => 'Ukraine Hryvna',

            'UYU' => 'Uruguay Peso',
            'UZS' => 'Uzbekistan Som',
            'VEF' => 'Venezuela Bolivar',
            'VND' => 'Viet Nam Dong',
            'YER' => 'Yemen Rial',
            'ZWD' => 'Zimbabwe Dollar'
        );
	}
}

