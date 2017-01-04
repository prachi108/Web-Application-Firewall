<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/

defined('_JEXEC') or die();

class J2StoreTableCustomfield extends F0FTable
{
	/**
	 * The event which runs before deleting a record
	 *
	 * @param   integer  $oid  The PK value of the record to delete
	 *
	 * @return  boolean  True to allow the deletion
	 */
	protected function onBeforeDelete($oid)
	{

		$db = JFactory::getDbo();
		$status = true;
		// Load the post record
		$item = clone $this;
		$item->load($oid);

		if($item->field_core != 1) {

				//first delete the column in the address table
				$query = 'ALTER TABLE #__j2store_addresses DROP '.$item->field_namekey;
				$db->setQuery($query);
				try {
					if($db->query()){
						$status = true;
					}
				} catch (Exception $e) {
					$status = false;
				}
			}
		return $status;

	}
	function visible($cid = null, $publish = 1)
	{
		JArrayHelper::toInteger($cid);
		$publish = (int)$publish;
		$k = $this->_tbl_key; //get table key

		$query = $this->_db->getQuery(true)

		->update($this->_db->qn($this->_tbl))
		->set($this->_db->qn('field_required') . ' = ' . (int)$publish);

	     $cids = $this->_db->qn($k) . ' = ' .implode(' OR ' . $this->_db->qn($k) . ' = ', $cid);
		 $query->where('(' . $cids . ')');

		$this->_db->setQuery((string)$query);
		if (!$this->_db->execute())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}
		return true;
	}
}
