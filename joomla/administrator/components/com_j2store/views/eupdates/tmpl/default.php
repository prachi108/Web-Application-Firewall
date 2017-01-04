<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// load tooltip behavior
JHtml::_('behavior.framework');
JHtml::_('behavior.modal');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

$updates = F0FModel::getTmpInstance('EUpdates', 'J2StoreModel')->getUpdates();
$update_link = J2Store::buildHelpLink('my-downloads.html', 'update');
$sidebar = JHtmlSidebar::render();
F0FModel::getTmpInstance('Updates', 'J2StoreModel')->refreshUpdateSite();
//now get update
$updateInfo = F0FModel::getTmpInstance('Updates', 'J2StoreModel')->getUpdates();

?>
<form action="<?php echo JRoute::_('index.php?option=com_j2store&view=eupdates'); ?>" method="post" name="adminForm"
	  id="adminForm" xmlns="http://www.w3.org/1999/html">

 <?php if(!empty( $sidebar )): ?>
   <div id="j-sidebar-container" class="span2">
      <?php echo $sidebar ; ?>
   </div>
   <div id="j-main-container" class="span10">
     <?php else : ?>
     <div id="j-main-container">
    <?php endif;?>

<div class="j2store updates">
		<?php if(isset($updateInfo['hasUpdate']) && $updateInfo['hasUpdate']) : ?>
		<table class="table table-bordered">
			<h3><?php echo JText::_('J2STORE_COMPONENT_UPDATE')?></h3>	
				
				<thead>
				<tr>
					<th>
						<?php echo '' ?>
					</th>
					<th>
						<?php echo JText::_('J2STORE_EXISTING_VERSION');?>
					</th>
					<th>
						<?php echo JText::_('J2STORE_NEW_VERSION');?>
					</th>
					<th>
						<?php echo JText::_('J2STORE_DOWNLOAD');?>
					</th>
				</tr>
			</thead>
			<tbody>
			
					<tr>
					<td><?php echo JText::_('COM_J2STORE'); ?></td>
					<td><?php echo J2STORE_VERSION; ?></td>
					<td><?php echo $updateInfo['version']; ?></td>
					<td>
						<a class="btn btn-danger"
							href="<?php echo 'index.php?option=com_installer&view=update' ?>"><?php echo JText::_('J2STORE_UPDATE_TO_VERSION').' '.$updateInfo['version']; ?></a>
					</td>		
					</tr>
				
			</tbody>	
		</table>
		<?php endif; ?>
		<table class="table table-bordered">
			<h3><?php echo JText::_('J2STORE_PLUGIN_APP_UPDATES')?></h3>
			<div class="alert alert-block alert-info">
				<?php echo JText::_('J2STORE_PLUGIN_APP_UPDATES_HELP')?>
			</div>
			<thead>
				<tr>
					<th>
						<?php echo JText::_('J2STORE_PLUGIN_APP_NAME');?>
					</th>
					<th>
						<?php echo JText::_('J2STORE_EXISTING_VERSION');?>
					</th>
					<th>
						<?php echo JText::_('J2STORE_NEW_VERSION');?>
					</th>
					<th>
						<?php echo JText::_('J2STORE_DOWNLOAD');?>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php if($updates):?>
			<?php foreach($updates as $ext): ?>
				<tr>
					<td>
						<?php echo JText::_($ext->name); ?>
					</td>
					<td><?php echo $ext->current_version;?></td>
					<td><?php echo $ext->new_version;?></td>
					<td>						
					 	<a class="btn btn-success" target="_blank" href="<?php echo $update_link;?>">
							<span class="fa fa-refresh"></span> <?php echo JText::_('J2STORE_DOWNLOAD');?>
					 	</a>
					</td>
				</tr>
			<?php endforeach; ?>
			<?php endif;?>
			</tbody>
		</table>
	</div>
	</form>
</div>
</div>
