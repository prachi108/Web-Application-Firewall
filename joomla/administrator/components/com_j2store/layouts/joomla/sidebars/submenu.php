<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

defined('_JEXEC') or die;
if (!defined('F0F_INCLUDED'))
{
	include_once JPATH_LIBRARIES . '/f0f/include.php';
}
$doc = JFactory::getDocument();
$doc->addStyleSheet(JUri::root().'media/j2store/css/font-awesome.min.css');
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
						'vouchers' => 'fa fa-gift',
						'promotions' => 'fa fa-trophy'
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
						'eupdates' => 'fa fa-refresh',
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
// Get installed version
$db = JFactory::getDbo();
$query = $db->getQuery(true);
$query->select($db->quoteName('manifest_cache'))->from($db->quoteName('#__extensions'))->where($db->quoteName('element').' = '.$db->quote('com_j2store'));
$query->where('type ='.$db->q('component'));
$db->setQuery($query);
$row = json_decode($db->loadResult());

//check updates if logged in as administrator
$user = JFactory::getUser();
$isroot = $user->authorise('core.admin');
$updateInfo = array();
if($isroot) {
//refresh the update sites first
F0FModel::getTmpInstance('Updates', 'J2StoreModel')->refreshUpdateSite();
//now get update
$updateInfo = F0FModel::getTmpInstance('Updates', 'J2StoreModel')->getUpdates();
}


$view = JFactory::getApplication()->input->getString('view','cpanels');


?>
<div id="j2store-sidebar">
	<!-- User Panel Starts-->
	<div class="user-panel">
		<!-- J2store Logo -->
		<div class="pull-left image">
			<img
				src="<?php echo JURI::root();?>media/j2store/images/dashboard-logo.png"
				class="img-circle" alt="j2store logo" />
		</div>
		<!-- J2store Logo Ends -->

		<!-- J2store Version Starts -->
		<div class="info">
			<h3>
						v <?php echo isset($row->version) ? $row->version : J2STORE_VERSION; ?>
						<?php if(J2Store::isPro() == 1): ?>
							<?php echo 'PRO'; ?>
						<?php else: ?>
							<?php echo 'CORE'; ?>
						<?php endif; ?>
					</h3>
		</div>
		<!-- J2store Version Ends -->

		<!-- Social Share Starts -->
		<div class="social-share">
			<div class="btn-group">
				<a class="btn btn-xs btn-primary"
					href="https://www.facebook.com/j2store" target="_blank"> <i
					class="fa fa-facebook"></i>
				</a> <a class="btn btn-xs btn-primary"
					href="https://twitter.com/j2store_joomla" target="_blank"> <i
					class="fa fa-twitter"></i>
				</a>
			</div>
		</div>
		<!-- Social Share Ends-->

				<?php if(isset($updateInfo['hasUpdate']) && $updateInfo['hasUpdate']) : ?>
					<div class="">
			<a class="btn btn-danger"
				href="<?php echo 'index.php?option=com_installer&view=update' ?>"><?php echo JText::_('J2STORE_UPDATE_TO_VERSION').' '.$updateInfo['version']; ?></a>
		</div>
				<?php endif; ?>

	        </div>
	<!-- User panel Ends -->
	
	

	<div class="sidebar-nav">
		<ul id="sidemenu" class="menu-content nav nav-list">
			<?php

				$view = JFactory::getApplication()->input->getString('view');
			foreach($menus as $key => $value):
			// $emptyClass = empty($value['active']) ? 'parent' : '';
			?>
				<?php if(isset($value['submenu']) && count($value['submenu'])):?>
				  <li class="collapsed"
				data-target="#dropdown-<?php echo str_replace(" ","-", $value['name']);?>"
				data-toggle="collapse"><a href="javascript:void(0)"> <i
					class="<?php echo isset($value['icon']) ? $value['icon'] : '';?>"></i>
					<span class="submenu-title"><?php echo $value['name'];?></span>
					<span class="pull-right"> <i class="fa fa-angle-down"></i>
				</span>

			</a>
					<?php $collapse = 'out';?>
                        <ul class="submenu-list collapse"
					id="dropdown-<?php echo str_replace(" ", "-", $value['name']);?>">
	                        <?php foreach($value['submenu'] as $key => $value):?>
	                        	<?php
	                        		$class =  '';
	                        		if($view == $key){
	                        			$class =  'active';
	                        			$collapse = 'in';
	                        		}

	                        	?>
	                            <li class="<?php echo $class?>"><a
						href="<?php echo 'index.php?option=com_j2store&view='.strtolower($key);?>">
							<span class="<?php echo isset($value) ? $value : '';?>"> <span><?php echo JText::_('COM_J2STORE_TITLE_'.JString::strtoupper($key));?></span>
						</span>
					</a></li>
	                          <?php endforeach;?>
	                        </ul></li>
				<?php else:?>
				<?php
						$active_class ='';
						if(isset($value['active']) && $value['active'] && $view =='cpanels'){
							$active_class ='active';
						}


				?>
            	<li class="<?php echo $active_class; ?>"><i
				class="<?php echo isset($value['icon']) ? $value['icon'] : '';?>"></i>
	           	 		<?php
	           	 		if($value['name']=='Dashboard'):?>
							<a href="<?php echo 'index.php?option=com_j2store&view=cpanels';?>">
						<?php elseif($value['name']=='Apps'): ?>
							<a href="<?php echo 'index.php?option=com_j2store&view=apps';?>">
						<?php else:?>
							<a href="javascript:void(0);">
						<?php endif;?>

    	       				<?php echo JText::_('COM_J2STORE_MAINMENU_'.JString::strtoupper($value['name']));?>
	            		</a></li>
            	<?php endif;?>
            <?php endforeach;?>
        </ul>
	</div>






</div>

<?php if ($displayData->displayMenu && $displayData->displayFilters) : ?>
<hr />
<?php endif; ?>
		<?php if ($displayData->displayFilters) : ?>
<div class="filter-select hidden-phone">
	<h4 class="page-header"><?php echo JText::_('JSEARCH_FILTER_LABEL');?></h4>
			<?php foreach ($displayData->filters as $filter) : ?>
				<label for="<?php echo $filter['name']; ?>"
		class="element-invisible"><?php echo $filter['label']; ?></label> <select
		name="<?php echo $filter['name']; ?>"
		id="<?php echo $filter['name']; ?>" class="span12 small"
		onchange="this.form.submit()">
					<?php if (!$filter['noDefault']) : ?>
						<option value=""><?php echo $filter['label']; ?></option>
					<?php endif; ?>
					<?php echo $filter['options']; ?>

		</select>
	<hr class="hr-condensed" />
			<?php endforeach; ?>
			</div>
<?php endif; ?>

<div class="user-guide">
	<div class="panel panel-solid-info">
		<div class="panel-body">
			<h3><?php echo JText::_('J2STORE_USER_GUIDE'); ?></h3>
			<?php echo JText::_('J2STORE_USER_GUIDE_INTRODUCTION')?>
			<br /> <a href="<?php echo J2Store::buildHelpLink('support/user-guide.html', 'dashboard'); ?>"
				target="_blank" class="btn btn-large btn-success">
			<?php echo JText::_('J2STORE_USER_GUIDE_READ')?>
			</a>
		</div>

	</div>
</div>



<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#j-main-container").attr("class", 'span9');
		jQuery("#j-sidebar-container").attr("class", 'span3');

	});
	jQuery(document).ready(function(){
			var li = jQuery(".submenu-list").html();
			jQuery('.submenu-list li').each(function(i ,value)
				{
				  if(jQuery(value).attr('class') =='active'){
					  var main_menu = jQuery( value ).parent().addClass('in');
				  }

				});
		});
</script>

