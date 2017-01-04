<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
class J2StoreModelAppLocalizationdata extends F0FModel
{
	
	/**
	 * Method to truncate and insert the values into requested to table
	 * @param string $tablename
	 * @return boolean
	 */
	function getInstallerTool($tablename){
		$status = false;

		//Get database
		$db = JFactory::getDBO();

		//incase table is metrics
		if($tablename=='metrics'){
			if(!$this->getTruncateTable('lengths')){
				$status = false;
			}

			if(!$this->getTruncateTable('weights')){
				$status = false;
			}

		}else{
			if(!$this->getTruncateTable($tablename)){
				$status = false;
			}
		}
		return $status;
	}

	/**
	 * Method to truncated the table completely
	 * @param string $tablename
	 * @return boolean
	 */
	public function getTruncateTable($tablename){
		$db = JFactory::getDbo();
		$query = "TRUNCATE TABLE ".$db->quoteName('#__j2store_'.$tablename);
		$db->setQuery($query);
		$status = true;
		if (!$db->execute())
		{
			$application->enqueueMessage(JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)), 'error');
			$status  = false;
		}
		if($status){
			if(!$this->getInserted($tablename)){
				$status = false;
			}
		}
		return $status;
	}

	/**
	 * Method to insert the values from the .sql file
	 * @param unknown_type $tablename
	 * @return boolean
	 */
		public function getInserted($tablename){
			$db = JFactory::getDbo();
			$status = true;

			//Force parsing of SQL file since Joomla! does that only in install mode, not in upgrades
			$sql = JPATH_ADMINISTRATOR.'/components/com_j2store/sql/install/mysql/'.$tablename.'.sql';
			$queries = JDatabaseDriver::splitSql(file_get_contents($sql));
			foreach ($queries as $query)
			{
				$query = trim($query);
				if ($query != '' && $query{0} != '#')
				{
					$db->setQuery($query);
					if (!$db->execute())
					{
						$application->enqueueMessage(JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)), 'error');
						$status  = false;
					}
				}
			}
			return $status;

		}
}