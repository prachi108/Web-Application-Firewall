<?php
/*------------------------------------------------------------------------
# mod_j2store_menu
# ------------------------------------------------------------------------
# author    Gokila Priya - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
$doc = JFactory::getDocument();
$doc->addStyleSheet(JUri::root().'media/j2store/css/font-awesome.min.css');
$icons = array (
		'dashboard' => 'fa fa-th-large',
		'COM_J2STORE_MAINMENU_CATALOG' => 'fa fa-tags',
		'products' => 'fa fa-tags',
		'options' => 'fa fa-list-ol',
		'vendors' => 'fa fa-male',
		'manufacturers' => 'fa fa-user',
		'filtergroups' => 'fa fa-filter',
		'COM_J2STORE_MAINMENU_SALES' => '',
		'orders' => 'fa fa-list-alt',
		'customers' => 'fa fa-users',
		'coupons' => 'fa fa-scissors',
		'promotions' => 'fa fa-trophy',
		'vouchers' => 'fa fa-gift',
		'COM_J2STORE_MAINMENU_LOCALISATION' => '',
		'countries' => 'fa fa-globe',
		'zones' => 'fa fa-flag',
		'geozones' => 'fa fa-pie-chart',
		'taxrates' => 'fa fa-calculator',
		'taxprofiles' => 'fa fa-sitemap',
		'lengths' => 'fa fa-arrows-v',
		'weights' => 'fa fa-arrows-h',
		'orderstatuses' => 'fa fa-check-square',
		'COM_J2STORE_MAINMENU_DESIGN' => '',
		'layouts' => 'fa fa-list-ol',
		'emailtemplates' => 'fa fa-envelope',
		'invoicetemplates' => 'fa fa-print',

		'COM_J2STORE_MAINMENU_SETUP' => '',
		'storeprofiles' => 'fa fa-edit',
		'currencies' => 'fa fa-dollar',
		'payments' => 'fa fa-credit-card',
		'shippings' => 'fa fa-truck',
		'reports' => 'fa fa-signal',
		'customfields' => 'fa fa-th-list',
		'configuration' => 'fa fa-cogs',
		'J2STORE_MAINMENU_APPLICATIONS'=>'',
		'apps' => 'fa fa-wrench'
);


$menus = array (
		array (
				'name' => 'Dashboard',
				'icon' => 'fa fa-th-large',
				'active' => 1
		),
		array (
				'name' => JText::_ ( 'COM_J2STORE_MAINMENU_CATALOG' ),
				'icon' => 'fa fa-tags',
				'submenu' => array (
						'products' => 'fa fa-tags',
						'inventories' => 'fa fa-database',
						'options' => 'fa fa-list-ol',
						'vendors' => 'fa fa-male',
						'manufacturers' => 'fa fa-user',
						'filtergroups' => 'fa fa-filter'
				)
		),
		array (
				'name' => JText::_ ( 'COM_J2STORE_MAINMENU_SALES' ),
				'icon' => 'fa fa-money',
				'submenu' => array (
						'orders' => 'fa fa-list-alt',
						'customers' => 'fa fa-users',
						'coupons' => 'fa fa-scissors',
						'promotions' => 'fa fa-trophy',
						'vouchers' => 'fa fa-gift'
				)
		),
		array (
				'name' => JText::_ ( 'COM_J2STORE_MAINMENU_LOCALISATION' ),
				'icon' => 'fa fa-globe fa-lg',
				'submenu' => array (
						'countries' => 'fa fa-globe',
						'zones' => 'fa fa-flag',
						'geozones' => 'fa fa-pie-chart',
						'taxrates' => 'fa fa-calculator',
						'taxprofiles' => 'fa fa-sitemap',
						'lengths' => 'fa fa-arrows-v',
						'weights' => 'fa fa-arrows-h',
						'orderstatuses' => 'fa fa-check-square'
				)
		),
		array (
				'name' => JText::_ ( 'COM_J2STORE_MAINMENU_DESIGN' ),
				'icon' => 'fa fa-paint-brush',
				'submenu' => array (
						'emailtemplates' => 'fa fa-envelope',
						'invoicetemplates' => 'fa fa-print'
				)
		),

		array (
				'name' => JText::_ ( 'COM_J2STORE_MAINMENU_SETUP' ),
				'icon' => 'fa fa-cogs',
				'submenu' => array (
						'configuration' => 'fa fa-cogs',
						'currencies' => 'fa fa-dollar',
						'payments' => 'fa fa-credit-card',
						'shippings' => 'fa fa-truck',
						'shippingtroubles' => 'fa fa-bug',
						'customfields' => 'fa fa-th-list',
				)
		),
		array (
				'name' => 'Apps',
				'icon' => 'fa fa-wrench',
				'active' => 0
		),

		array (
				'name' => 'Reporting',
				'icon' => 'fa fa-signal',
				'submenu' => array (
						'Reports' => 'fa fa-signal'
				)
		)
);


?>
<ul id="menu" class="nav">
	<li class="dropdown" >
		<a class="dropdown-toggle" data-toggle="dropdown" href="#"><?php echo JText::_('COM_J2STORE');?><span class="caret"></span></a>
			<ul aria-labelledby="dropdownMenu" role="menu" class="dropdown-menu">
			<?php foreach($menus as $key => $value):?>
                  <?php if(isset($value['submenu']) && count($value['submenu'])):?>
                  <li class="dropdown-submenu">
                    <a href="#" tabindex="-1">
                    	<i class="<?php echo isset($value['icon']) ? $value['icon'] : '';?>"></i>
                    	<span class="submenu-title"><?php echo $value['name'];?></span>
                    </a>
                    <ul class="dropdown-menu">

                    <!-- Here starts Submenu -->
                     <?php foreach($value['submenu'] as $key => $value): ?>
                      	<li>
                      		<a href="<?php echo 'index.php?option=com_j2store&view='.strtolower($key);?>"  tabindex="-1">
                      			<i class="<?php echo !empty($value) ? $value: '';?>"></i>
                      			<span>
	                           		<?php echo JText::_('COM_J2STORE_TITLE_'.JString::strtoupper($key));?>
	                           	</span>
	                         </a>
	                       </li>
                         <?php endforeach;?>
                    </ul>
                  </li>
                 <?php else:?>
                  <li>
                      <?php
	           	 		if($value['name']=='Dashboard'):?>
							<a class="dropdown-toggle" data-toggle="dropdown" href="<?php echo 'index.php?option=com_j2store&view=cpanels';?>">
						<?php elseif($value['name']=='Apps'): ?>
							<a href="<?php echo 'index.php?option=com_j2store&view=apps';?>">
						<?php else:?>
							<a href="javascript:void(0);">
						<?php endif;?>
						<i class="<?php echo isset($value['icon']) ? $value['icon'] : '';?>"></i>
                  			<span class="submenu-title"><?php echo $value['name'];?></span>
						</a>
					</li>
                <?php endif; ?>
               <?php endforeach;?>
			</ul>
	</li>
</ul>