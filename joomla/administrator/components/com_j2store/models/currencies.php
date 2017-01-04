<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/utilities.php';
class J2StoreModelCurrencies extends F0FModel {

	/**
	 * This method runs before the $data is saved to the $table. Return false to
	 * stop saving.
	 *
	 * @param   array     &$data   The data to save
	 * @param   F0FTable  &$table  The table to save the data to
	 *
	 * @return  boolean  Return false to prevent saving, true to allow it
	 */
	protected function onBeforeSave(&$data, &$table)
	{
		if(!empty($data['currency_code'])) {
	 		$data['currency_numeric_code'] = J2Store::currency()->getCurrenciesNumericCode($data['currency_code']);
	 		if(empty($data['j2store_currency_id'])){
	 			$app = JFactory::getApplication();
	 			$currency = F0FTable::getAnInstance('Currency', 'J2StoreTable');
	 			$currency->load(array(
	 				'currency_code' => $data['currency_code']
	 			));
	 			if($currency->j2store_currency_id > 0){
	 				$message = JText::_("J2STORE_CURRENCY_CODE_ALREADY_EXIST");
	 				$app->enqueueMessage($message,'error');	 				
	 				return false;
	 			}
	 		}
		}
		return true;
	}

	protected function onAfterSave(&$table) {

		$store = J2Store::storeProfile();
		if($store->get('config_currency_auto', 1)) {
			$this->updateCurrencies(false);
		}
	}

	public function updateCurrencies($force = false) {
		if (extension_loaded('curl')) {
			$data = array();
			$db = JFactory::getDbo();
			$store = J2Store::config();
			
			//sanity check
			if($store->get('config_currency_auto', 1) != 1) return; 
			
			//update the default currency
			$query = $db->getQuery(true);
			$query->update('#__j2store_currencies')->set('currency_value ='.$db->q('1.00000'))
			->set('modified_on='.$db->q(date('Y-m-d H:i:s')))
			->where('currency_code='.$db->q($store->get('config_currency')));
			$db->setQuery($query);
			try {
				$db->execute();
			}catch(Exception $e) {
				$this->setError($e->getMessage());
			}

			
			$query = $db->getQuery(true);

			$query->select('*')->from('#__j2store_currencies')->where('currency_code !='.$db->q($store->get('config_currency')));

			if($force) {
				$nullDate = JFactory::getDbo( )->getNullDate( );
				$query->where('(modified_on='.  $db->q(date('Y-m-d H:i:s', strtotime('-1 day'))).' OR modified_on='.$db->q($nullDate).')');
			}
			$db->setQuery($query);
			$rows = $db->loadAssocList();
			foreach ($rows as $result) {
				$data[] = $store->get('config_currency') . $result['currency_code'] . '=X';
			}

			$curl = curl_init();

			curl_setopt($curl, CURLOPT_URL, 'http://download.finance.yahoo.com/d/quotes.csv?s=' . implode(',', $data) . '&f=sl1&e=.csv');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

			$content = curl_exec($curl);

			curl_close($curl);

			$lines = explode("\n", trim($content));

			foreach ($lines as $line) {
				$currency = JString::substr($line, 4, 3);
				$value = JString::substr($line, 11, 6);

				if ((float)$value) {
					$query = $db->getQuery(true);
					$query->update('#__j2store_currencies')->set('currency_value ='.$db->q((float)$value))
					->set('modified_on='.$db->q(date('Y-m-d H:i:s')))
					->where('currency_code='.$db->q($currency));
					$db->setQuery($query);
					$db->query();
				}
			}		
			
		}
	}
	
	public function create_currency_by_code($code, $symbol) {
		//no records found. Dumb default data
		$item = F0FTable::getAnInstance('Currency', 'J2StoreTable');
		$item->reset();
		$item->j2store_currency_id = 0;
		$values = array();
		$values['j2store_currency_id '] = null;
		$values['currency_title'] = $code;
		$values['currency_code'] = $code;
		$values['currency_position'] = 'pre';
		$values['currency_symbol'] = $symbol;
		$values['currency_num_decimals'] = '2';
		$values['currency_decimal'] = '.';
		$values['currency_thousands'] = ',';
		$values['currency_value'] = '1.00000'; //default currency is one always
		$values['enabled'] = 1;
		$item->save($values);		
	}
	
	public function getTableFields()
	{
		$tableName = $this->getTable()->getTableName();
		static $sets;
	
		if ( !is_array( $sets) )
		{
			$sets= array( );
		}
	
		if(!isset($sets[$tableName])) {
	
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$sets[$tableName] = $this->getDbo()->getTableColumns($tableName, true);
			}
			else
			{
				$fieldsArray = $this->getDbo()->getTableFields($tableName, true);
				$sets[$tableName] = array_shift($fieldsArray);
			}
		}
		return $sets[$tableName];
	}

}