<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/


/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

if(!class_exists('J2Store')) {
	require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
}

class J2StoreRouterHelper
{
	static function getAndPop(&$query, $key, $default = null)
	{
		if(isset($query[$key]))
		{
			$value = $query[$key];
			unset($query[$key]);
			return $value;
		}
		else
		{
			return $default;
		}
	}

	/**
	 * Finds a menu whose query parameters match those in $qoptions
	 * @param array $qoptions The query parameters to look for
	 * @param array $params The menu parameters to look for
	 * @return null|object Null if not found, or the menu item if we did find it
	 */
	static public function findMenu($qoptions = array(), $params = null)
	{
		static $joomla16 = null;

		if(is_null($joomla16)) {
			$joomla16 = version_compare(JVERSION,'1.6.0','ge');
		}

		// Convert $qoptions to an object
		if(empty($qoptions) || !is_array($qoptions)) $qoptions = array();

		$menus =JMenu::getInstance('site');
		$menuitem = $menus->getActive();
		// First check the current menu item (fastest shortcut!)
		if(is_object($menuitem)) {
			if(self::checkMenu($menuitem, $qoptions, $params)) {
				return $menuitem;
			}
		}


		//print_r($menus->getItems(array('language'), $languages) );
		foreach($menus->getMenu() as $item)
		{
			if($joomla16) {
				if(self::checkMenu($item, $qoptions, $params)) return $item;
			} elseif($item->published)
			{
				if(self::checkMenu($item, $qoptions, $params)) return $item;
			}
		}


		return null;
	}

	/**
	 * Checks if a menu item conforms to the query options and parameters specified
	 *
	 * @param object $menu A menu item
	 * @param array $qoptions The query options to look for
	 * @param array $params The menu parameters to look for
	 * @return bool
	 */
	static public function checkMenu($menu, $qoptions, $params = null)
	{
		$query = $menu->query;
		foreach($qoptions as $key => $value)
		{
			if(is_null($value)) continue;
			if(!isset($query[$key])) return false;
			if($query[$key] != $value) return false;
		}

		if(!is_null($params))
		{
			$menus =JMenu::getInstance('site');
			$check =  $menu->params instanceof JRegistry ? $menu->params : $menus->getParams($menu->id);

			foreach($params as $key => $value)
			{
				if(is_null($value)) continue;
				if( $check->get($key) != $value ) return false;
			}
		}

		$lang = JFactory::getLanguage();
		if($lang->getTag() == $menu->language) {
			return true;
		}if($menu->language == '*') {
			return true;
		} else {
			return false;
		}

		return true;
	}

	static public function preconditionSegments($segments)
	{
		$newSegments = array();
		if(!empty($segments)) foreach($segments as $segment)
		{
			if(strstr($segment,':'))
			{
				$segment = str_replace(':','-',$segment);
			}
			if(is_array($segment)) {
				$newSegments[] = implode('-', $segment);
			} else {
				$newSegments[] = $segment;
			}
		}
		return $newSegments;
	}

	static public function findMenuOrders($qoptions) {

		$menus =JMenu::getInstance('site');
		$menu_id = null;
		foreach($menus->getMenu() as $item)
		{
			if(isset($item->query['view']) && $item->query['view']=='orders') {
				if(self::checkMenuOrders($item, $qoptions)) {
					$menu_id =$item->id;
				}
			}

		}
		return $menu_id;
	}


	public static function checkMenuOrders($menu, $qoptions) {
		//echo 'ram';
		$lang = JFactory::getLanguage();
		if($lang->getTag() == $menu->language) {
			return true;
		}if($menu->language == '*') {
			return true;
		} else {
			return false;
		}
	}

	public static function findProductMenu($qoptions) {

		$menus =JMenu::getInstance('site');
		$menu = null;
		$other_tasks = array('compare','wishlist');
		foreach($menus->getMenu() as $item)
		{
			if(isset($item->query['view']) && $item->query['view']=='products') {
				if (isset($item->query['task']) && !empty($item->query['task']) && in_array($item->query['task'] , $other_tasks) && ($item->query['task'] == $qoptions['task']) ){
					$menu =$item;
					break;
				}
				if(self::checkMenuProducts($item, $qoptions)) {
					$menu =$item;
					//break on first found menu
					break;
				}
			}

		}
		return $menu;

	}

	public static function checkMenuProducts($menu, $qoptions) {
		$lang = JFactory::getLanguage();
		//first check the category
		if(isset($qoptions['id'])) {
			$cat_id = self::getProductCategory($qoptions['id']);
			$categories = array();
			if(isset($menu->query['catid'])) {			
				$categories = $menu->query['catid'];			
			}			
			if(in_array($cat_id, $categories)) {
				//seems we have a match
				if($lang->getTag() == $menu->language) {
					return true;
				}if($menu->language == '*') {
					return true;
				} else {
					return false;
				}
			}
		}
		return false;

	}

	public static function getProductCategory($id) {

		//first load the product to get the id.
		$product = F0FTable::getAnInstance('Product', 'J2StoreTable');

		if($product->load($id)) {
			if($product->product_source == 'com_content') {
				$article = J2Store::article()->getArticle($product->product_source_id);
				return $article->catid;
			} else {
				return '';
			}
		} else {
			return '';
		}
	}


	public static function getItemAlias($id) {
		//first load the product to get the id.
		$product = F0FTable::getAnInstance('Product', 'J2StoreTable');

		if($product->load($id)) {
			if($product->product_source == 'com_content') {
				$article = J2Store::article()->getArticle($product->product_source_id);
				return $article->alias;
			}else {
				return '';
			}
		}else {
			return '';
		}
	}

	/**
	 * Method to get the Product ID by article alias
	 * @param $segment mixed Either ID or a string containing the ID
	 * @return mixed Product ID or false on finding none
     */
	public static function getArticleByAlias($segment) {
		$explode_results = explode(':', $segment);
		$article = new stdClass();
		if(isset($explode_results[0]) && is_numeric($explode_results[0])) {
			// The url is generated by version prior to 3.2.6.
			// We already have the product id. So it is sufficient to return the product id.
			// but for safar side. Let us query by alias again
			if(isset($explode_results[1]) && !empty($explode_results[1])) {
				$segment = $explode_results[1];
			}else {
				return $explode_results[0];
			}
		}
		//new router. Load article by alias
		$article = J2Store::article()->getArticleByAlias($segment);

		if(isset($article->id)) {
			$product = F0FTable::getAnInstance('Product', 'J2StoreTable');
			$product->get_product_by_source('com_content', $article->id);
			if($product->j2store_product_id) {
				return $product->j2store_product_id;
			}else {
				return false;
			}
		}else {
			return false;
		}
	}

}