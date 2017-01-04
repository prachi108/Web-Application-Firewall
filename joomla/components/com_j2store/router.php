<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();

// Load FOF
// Include F0F
if(!defined('F0F_INCLUDED')) {
	require_once JPATH_LIBRARIES . '/f0f/include.php';
}

require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/router.php');

function J2StoreBuildRoute(&$query) {

	$router = new J2StoreRouter();
	return $router->build($query);
}

function J2StoreParseRoute($segments) {
	$router = new J2StoreRouter();
	return $router->parse($segments);
}

class J2StoreRouter extends JComponentRouterBase {

	public function build(&$query) {
		$segments = array ();
		// If there is only the option and Itemid, let Joomla! decide on the naming scheme
		if (isset ( $query ['option'] ) && isset ( $query ['Itemid'] ) && ! isset ( $query ['view'] ) && ! isset ( $query ['task'] ) && ! isset ( $query ['layout'] ) && ! isset ( $query ['id'] )) {
			return $segments;
		}

		$menus = JMenu::getInstance ( 'site' );

		$view = J2StoreRouterHelper::getAndPop ( $query, 'view', 'carts' );
		$task = J2StoreRouterHelper::getAndPop ( $query, 'task' );
		$layout = J2StoreRouterHelper::getAndPop ( $query, 'layout' );
		$id = J2StoreRouterHelper::getAndPop ( $query, 'id' );
		$Itemid = J2StoreRouterHelper::getAndPop ( $query, 'Itemid' );
		$catid = J2StoreRouterHelper::getAndPop ( $query, 'catid' );
		$j2storesource = J2StoreRouterHelper::getAndPop ( $query, 'j2storesource' );
		// $orderpayment_type = J2StoreRouterHelper::getAndPop($query, 'orderpayment_type');
		// $paction = J2StoreRouterHelper::getAndPop($query, 'paction');
		$qoptions = array (
				'option' => 'com_j2store',
				'view' => $view,
				'task' => $task,
				'id' => $id
		);

		switch ($view) {
			case 'carts' :
			case 'cart' :
				// Is it a mycart menu?
				if ($Itemid) {
					$menu = $menus->getItem ( $Itemid );
					$mView = isset ( $menu->query ['view'] ) ? $menu->query ['view'] : 'carts';
					$mTask = isset ( $menu->query ['task'] ) ? $menu->query ['task'] : '';
					// No, we have to find another root
					if (($mView != 'cart' && $mView != 'carts'))
						$Itemid = null;
				}

				if (empty ( $Itemid )) {
					$menu = J2StoreRouterHelper::findMenu ( $qoptions );
					$mView = isset ( $menu->query ['view'] ) ? $menu->query ['view'] : 'carts';
					$mTask = isset ( $menu->query ['task'] ) ? $menu->query ['task'] : '';
					$Itemid = empty ( $menu ) ? null : $menu->id;
				}

				if (empty ( $Itemid )) {
					// No menu found, let's add a segment manually
					$segments [] = 'carts';
					if (isset ( $task )) {
						$segments [] = $task;
					}
				} else {

				// sometimes we need task
				//	$segments [] = 'carts';
					if (isset ( $mTask ) && ! empty ( $mTask )) {
						$segments [] = $mTask;
					} elseif (isset ( $task )) {
						$segments [] = $task;
					}
					// Joomla! will let the menu item naming work its magic
					$query ['Itemid'] = $Itemid;
				}
				break;

			case 'checkouts' :
			case 'checkout' :
				// Is it a browser menu?
				if ($Itemid) {
					$menu = $menus->getItem ( $Itemid );
					$mView = isset ( $menu->query ['view'] ) ? $menu->query ['view'] : 'checkout';
					$mTask = isset ( $menu->query ['task'] ) ? $menu->query ['task'] : '';
					// $mOPType = isset($menu->query['orderpayment_type']) ? $menu->query['orderpayment_type'] : '';
					// $mPaction = isset($menu->query['paction']) ? $menu->query['paction'] : '';
					// No, we have to find another root
					if (($mView != 'checkout' && $mView != 'checkouts'))
						$Itemid = null;
				}

				if (empty ( $Itemid )) {
					$menu = J2StoreRouterHelper::findMenu ( $qoptions );
					$mView = isset ( $menu->query ['view'] ) ? $menu->query ['view'] : 'checkout';
					$mTask = isset ( $menu->query ['task'] ) ? $menu->query ['task'] : '';
					$Itemid = empty ( $menu ) ? null : $menu->id;
				}

				if (empty ( $Itemid )) {
					// No menu found, let's add a segment based on the layout
					$segments [] = 'checkout';
					if (isset ( $task )) {
						$segments [] = $task;
					}					
					// if(isset($orderpayment_type)) {
					// $segments[] = $orderpayment_type;
					// }

					// if(isset($paction)) {
					// $segments[] = $paction;
					// }
				} else {
					// sometimes we need task
					$is_task_set = false;
					if (isset ( $mTask )) {
						if (!empty($mTask))
						{
							$segments [] = $mTask;
							$is_task_set = true;
						}						
					}
					if ($is_task_set==false && isset($task))
					{
						if (!empty($task))
						{
							$segments [] = $task;
							$is_task_set = true;
						}
						
					}
					// add the order payment type
					/*
					 * if(isset($mOPType)) { $segments[] = $mOPType; } if(isset($mPaction)) { $segments[] = $mPaction; }
					 */
					// Joomla! will let the menu item naming work its magic
					$query ['Itemid'] = $Itemid;
				}
				break;

			case 'myprofile' :
				// Is it a browser menu?
				if ($Itemid) {
					$menu = $menus->getItem ( $Itemid );
					$mView = isset ( $menu->query ['view'] ) ? $menu->query ['view'] : 'myprofile';
					$mTask = isset ( $menu->query ['task'] ) ? $menu->query ['task'] : '';
					// $mOPType = isset($menu->query['orderpayment_type']) ? $menu->query['orderpayment_type'] : '';
					// $mPaction = isset($menu->query['paction']) ? $menu->query['paction'] : '';
					// No, we have to find another root
					if (($mView != 'myprofile'))
						$Itemid = null;
				}

				if (empty ( $Itemid )) {
					$menu = J2StoreRouterHelper::findMenu ( $qoptions );
					$mView = isset ( $menu->query ['view'] ) ? $menu->query ['view'] : 'myprofile';
					$mTask = isset ( $menu->query ['task'] ) ? $menu->query ['task'] : '';
					$Itemid = empty ( $menu ) ? null : $menu->id;
				}

				if (empty ( $Itemid )) {
					// No menu found, let's add a segment based on the layout
					$segments [] = 'myprofile';
					if (isset ( $task )) {
						$segments [] = $task;
					}
				} else {
					// sometimes we need task					
					if (isset ( $mTask ) && ! empty ( $mTask ) && $mView != 'checkout') {
						$segments [] = $mTask;
					} elseif (isset ( $qoptions ['task'] ) && $mView != 'checkout') {
						$segments [] = $qoptions ['task'];
					}
					// Joomla! will let the menu item naming work its magic
					$query ['Itemid'] = $Itemid;
				}
				break;

			case 'products' :
				$other_tasks = array('compare','wishlist');
				if ( isset ( $task ) && in_array($task, $other_tasks) ) {
					$Itemid = null;
				}
				// Is it a browser menu?
				if ($Itemid) {
					$menu = $menus->getItem ( $Itemid );
					$mView = isset ( $menu->query ['view'] ) ? $menu->query ['view'] : 'products';
					$mTask = isset ( $menu->query ['task'] ) ? $menu->query ['task'] : $task;
					$mId = isset ( $menu->query ['id'] ) ? $menu->query ['id'] : $id;

					// No, we have to find another root
					if (($mView != 'products'))
						$Itemid = null;
				}

				if (empty ( $Itemid )) {
					// special find. Needed because we will be using order links under checkout view
					$menu = J2StoreRouterHelper::findProductMenu ( $qoptions );
					$mView = isset ( $menu->query ['view'] ) ? $menu->query ['view'] : 'products';
					$mTask = isset ( $menu->query ['task'] ) ? $menu->query ['task'] : $task;
					$mId = isset ( $menu->query ['id'] ) ? $menu->query ['id'] : $id;
					$Itemid = isset ( $menu->id ) ? $menu->id : null;
				}

				if (empty ( $Itemid )) {
					// No menu found, let's add a segment based on the layout
					$segments [] = 'products';
					
					if (isset ( $id )) {
						if (strpos ( $id, ':' ) === false) {
							$segments [] = J2StoreRouterHelper::getItemAlias ( $id );
						}
					} elseif (isset ( $mId )) {
						if (strpos ( $mId, ':' ) === false) {
							$segments [] = J2StoreRouterHelper::getItemAlias ( $mId );
						}
					}

					$other_tasks = array('compare','wishlist');
					if ( isset ( $task ) && in_array($task, $other_tasks) ) {
						$segments [] =  $task ;
					}

				} else {
					// Joomla! will let the menu item naming work its magic
				

					if (isset ( $mId )) {
						if (strpos ( $mId, ':' ) === false) {
							$segments [] = J2StoreRouterHelper::getItemAlias ( $mId );
						}
					} elseif (isset ( $id )) {
						if (strpos ( $id, ':' ) === false) {
							$segments [] = J2StoreRouterHelper::getItemAlias ( $id );
						}
					}

					$query ['Itemid'] = $Itemid;
				}
				break;
		}

		return $segments;
	}

	/**
	 * @param array $segments
	 * @return array
	 * @throws Exception
     */
	public function parse(&$segments) {
		//var_dump($segments);
		$query = array ();
		$menus = JMenu::getInstance ( 'site' );
		$menu = $menus->getActive ();
		$vars = array ();
		$total = count ( $segments );
		/*for($i = 0; $i < $total; $i ++) {
			$segments [$i] = preg_replace ( '/-/', ':', $segments [$i], 1 );
		}*/

		if (is_null ( $menu ) && count ( $segments )) {
			if ($segments [0] == 'cart' || $segments [0] == 'carts') {
				$vars ['view'] = $segments [0];
				if (isset ( $segments [1] )) {
					$vars ['task'] = $segments [1];
				}
			}

			if ($segments [0] == 'checkout' || $segments [0] == 'checkouts') {
				$vars ['view'] = $segments [0];
				if (isset ( $segments [1] )) {
					$vars ['task'] = $segments [1];
				}
			}

			if ($segments [0] == 'myprofile') {
				$vars ['view'] = $segments [0];
				if (isset ( $segments [1] )) {
					$vars ['task'] = $segments [1];
				}
			}

			if ($segments [0] == 'products') {
				$vars ['view'] = $segments [0];
				$other_tasks = array('compare','wishlist');
				if ( isset ( $segments [1] ) && in_array($segments [1], $other_tasks) ) {
					$vars['task'] = $segments [1];
				}elseif (isset ( $segments [1] ) && $segments[1] != 'view' ) {
					$vars ['task'] = 'view';
					$vars ['id'] = $segments [1];
				}elseif(isset($segments[1]) && $segments[1] == 'view') {
					// old routing pattern detected. Send the customer to the correct page
					$vars ['task'] = 'view';
					if(isset($segments[2])) {
						$vars ['id'] = $segments [2];
					}
				}
				
				
			}
		} else {
			if (count ( $segments )) {

				$mView = $menu->query ['view'];

				if (isset ( $mView ) && ($mView == 'cart' || $mView == 'carts')) {
					$vars ['view'] = $mView;
					if (isset ( $segments [0] )) {
						$vars ['task'] = $segments [0];
					}

				} elseif ($segments [0] == 'cart' || $segments [0] == 'carts') {
					$vars ['view'] = $segments [0];
					if (isset ( $segments [1] )) {
						$vars ['task'] = $segments [1];
					}
				}

				if (isset ( $mView ) && ($mView == 'checkout' || $mView == 'checkouts')) {
					$vars ['view'] = $mView;
					if (isset ( $segments [0] )) {
						$vars ['task'] = $segments [0];
					}
				} elseif ($segments [0] == 'checkout' || $segments [0] == 'checkouts') {
					$vars ['view'] = $segments [0];
					if (isset ( $segments [1] )) {
						$vars ['task'] = $segments [1];
					}
				}

				if (isset ( $mView ) && $mView == 'myprofile') {
					$vars ['view'] = $mView;
					if (isset ( $segments [0] )) {
						$vars ['task'] = $segments [0];
					}
				} elseif ($segments [0] == 'myprofile') {
					$vars ['view'] = $segments [0];
					if (isset ( $segments [1] )) {
						$vars ['task'] = $segments [1];
					}
				}

				if (isset ( $mView ) && $mView == 'products') {
					$vars ['view'] = 'products';
					$other_tasks = array('compare','wishlist');
					if ( isset ( $segments [0] ) && in_array($segments [0], $other_tasks) ) {
						$vars['task'] = $segments [0];
					}elseif (isset ( $segments [0] ) && $segments[0] != 'view' ) {
						$vars ['task'] = 'view';
						$vars ['id'] = J2StoreRouterHelper::getArticleByAlias($segments [0]);
						
					}elseif(isset($segments[0]) && $segments[0] == 'view') {
						//old routing pattern. Re-route correct
						$vars['task'] = 'view';
						if (isset ( $segments [1] )) {
							$vars ['id'] = J2StoreRouterHelper::getArticleByAlias($segments [1]);
						}
					}
					
				} elseif ($segments [0] == 'products') {
					$vars ['view'] = $segments [0];
					$other_tasks = array('compare','wishlist');
					if ( isset ( $segments [1] ) && in_array($segments [1], $other_tasks) ) {
						$vars['task'] = $segments [1];
					}elseif (isset ( $segments [1] ) && $segments[1] != 'view') {
						// this will be the id of the product or the alias
						$vars ['task'] = 'view';
						$vars ['id'] = J2StoreRouterHelper::getArticleByAlias($segments [2]);
					}elseif (isset ( $segments [1] ) && $segments[1] == 'view') {
						$vars ['task'] = 'view';
						if (isset ( $segments [2] )) {
							$vars ['id'] = J2StoreRouterHelper::getArticleByAlias($segments[2]);
						}
					}
				}
			}
		}
		return $vars;
	}

}