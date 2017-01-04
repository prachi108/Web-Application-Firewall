<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

defined('_JEXEC') or die;

   // load tooltip behavior
   JHtml::_('bootstrap.tooltip');
   JHtml::_('behavior.multiselect');
   JHtml::_('formbehavior.chosen', 'select');
   $sidebar = JHtmlSidebar::render();
?>
<form
   action="<?php echo JRoute::_('index.php?option=com_j2store&view=cpanel'); ?>"
   method="post" name="adminForm" id="adminForm">
   <?php if(!empty( $sidebar )): ?>
   <div id="j-sidebar-container" class="span2">
      <?php echo $sidebar ; ?>
   </div>
   <div id="j-main-container" class="span10">
      <?php else : ?>
      <div id="j-main-container">
         <?php endif;?>
         <div  class ="box-widget-body ">
            <div id="container" class ="box-widget-body " style="clear:both;">
            	<div class="row-fluid">
            		<?php echo J2Store::help()->free_topbar(); ?>
            	</div>
				<div class="row-fluid">
				 	<div class="span12">
				 	<?php echo J2Store::help()->alert(
				 			'coupon_update',
				 			JText::_('J2STORE_ATTENTION'),
				 			JText::_('J2STORE_COUPON_TYPES_EXTENDED_NOTIFICATION')
					); ?>
					    <?php if(JPluginHelper::isEnabled('system', 'cache')): ?>
						    <?php echo J2Store::help()->alert_with_static_message(
							    'danger',
							    JText::_('J2STORE_ATTENTION'),
							    JText::_('J2STORE_SYSTEM_CACHE_ENABLED_NOTIFICATION')
						    ); ?>
					    <?php endif; ?>

					 <?php if(J2Store::isPro()): ?>
					  <?php $download_id = J2Store::config()->get('downloadid', ''); ?>
					  <?php if(empty($download_id)) : ?>
						<div class="alert alert-block alert-warning">
					         <strong><?php echo JText::_('J2STORE_DOWNLOAD_ID_NOT_SET'); ?> </strong>&nbsp;
							<a href="<?php echo J2Store::buildHelpLink('my-downloads.html', 'downloadid'); ?>" target="_blank" class="btn btn-danger"><?php echo JText::_('J2STORE_FIND_MY_DOWNLOAD_ID'); ?></a>
						</div>
					   <?php endif; ?>
					   <?php endif; ?>
					<div class="subscription_message" style="display:none;">
						<div class="alert alert-block alert-warning">
							<h4>
								<span class="subscription"></span>
							</h4>
						</div>
					</div>
				 	<div class="eupdate-notification" style="display:none;">
				 		<div class="alert alert-block alert-warning">
				 			<h4>
				 			<span class="total"></span>
				 			<?php echo JText::_('J2STORE_PLUGIN_UPDATES_NOTIFICATION'); ?>
				 			<a class="btn btn-danger" href="<?php echo JRoute::_('index.php?option=com_j2store&view=eupdates'); ?>">
				 			<?php echo JText::_('J2STORE_VIEW_AND_UPDATE')?>
				 			</a>
				 			</h4>
				 		</div>
				 	</div>
				 	<?php echo J2Store::help()->watch_video_tutorials(); ?>
				 	<div class="row-fluid">
				 			<!-- Chart-->
							<div class="span12 stats-mini">
								<?php echo J2Store::modules()->loadposition('j2store-module-position-1');?>
							</div>
						</div>
				 		<div class="row-fluid">
				 			<!-- Chart-->
							<div class="span12 chart">
								<?php echo J2Store::modules()->loadposition('j2store-module-position-3');?>
							</div>
						</div>
						<div class="row-fluid">
				 		   <!-- Statistics-->
							<div class="span6 statistics">
								<?php echo J2Store::modules()->loadposition('j2store-module-position-5');?>
							</div>
							<!-- Latest orders -->
							<div class="span6 latest_orders">
							<?php echo J2Store::modules()->loadposition('j2store-module-position-4');?>
							</div>

						</div>
				</div>
			</div>
			</div>
      </div>
   </div>
</form>
<script type="text/javascript">
/** Method to get all the Eupdates **/
var sEupdates = setTimeout(function () {
	(function($){
	$.ajax({
		  url: "index.php?option=com_j2store&view=cpanels&task=getEupdates",
		  dataType:'json'
		}).done(function(json) {
			if(json['total']){
				$('.eupdate-notification .total').html(json['total']);
				$('.eupdate-notification').show();
			}
		});

	})(j2store.jQuery);

}, 2000);
</script>
