<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();
$this->prefix = 'jform[order]';

?>
<div class="j2store">
	<form class="form-horizontal form-validate" id="adminForm" name="adminForm" method="post" action="index.php">
		<input type="hidden" name="option" value="com_j2store">
		<input type="hidden" name="view" value="order">
		<input type="hidden" id="task" name="task" value="">
		<input type="hidden" id="id" name="id" value="<?php echo $this->item->j2store_order_id; ?>" />
		<input type="hidden" id="j2store_order_id" name="j2store_order_id" value="<?php echo $this->item->j2store_order_id; ?>" />
		<input type="hidden" name="order_id" value="<?php echo $this->item->order_id; ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
		
		<div class="row-fluid">
			<div class="span8">
				<h2 class="invoice-text-muted"><?php echo JText::_('J2STORE_INVOICE'); ?>&nbsp; <?php echo $this->item->getInvoiceNumber(); ?>
				<sup class="label <?php echo $this->item->orderstatus_cssclass;?> order-state-label">
					<?php echo JText::_($this->item->orderstatus_name);?>					
				</sup>
				</h2>
				
			</div>
			<div class="span4">
			<?php if($this->item->user_id == 0): ?>
				<label class="label label-warning"><?php echo JText::_('J2STORE_GUEST')?></label>
				<br />
				<small class="muted">(<?php echo JText::_('J2STORE_UNIQUE_TOKEN'); ?>: <?php echo $this->item->token;?>)</small>
				<?php endif;?>				
			</div>
		</div>
		<hr />
		
		<div class="j2store-general-order">
				<!-- General layout  -->
			<?php echo $this->loadTemplate('general');?>
		</div>
		<div class="row-fluid">
			<div class="span4">
					<div class="panel panel-default">			
				 	<div class="panel-body">
				 	<strong><?php echo JText::_("J2STORE_ORDER_CUSTOMER_NOTE"); ?></strong>
				 	<input class="btn btn-small btn-primary" type="submit" onclick="jQuery('#task').attr('value','saveOrderCnote');"
											value="<?php echo JText::_('J2STORE_ORDER_STATUS_SAVE'); ?>" />
					<br />
					 <textarea rows="3" cols="6" name="customer_note"><?php echo $this->item->customer_note; ?></textarea>			 
					 
					</div>						
				</div>
				<?php if($this->shipping->order_id == $this->item->order_id && !empty($this->shipping->ordershipping_type)): ?>
				<div class="panel panel-default">			
				 	<div class="panel-body">
				 	<strong><?php echo JText::_("J2STORE_SHIPPING_TRACKING_ID"); ?></strong>
				 	<input class="btn btn-small btn-primary" type="submit" onclick="jQuery('#task').attr('value','saveTrackingId');"
											value="<?php echo JText::_('J2STORE_ORDER_STATUS_SAVE'); ?>" />
					<br />
					 <textarea rows="3" cols="6" name="ordershipping_tracking_id"><?php echo $this->shipping->ordershipping_tracking_id; ?></textarea>			 
					 
					</div>						
				</div>
				<?php endif; ?>	
			</div>
			
			<div class="span8">
				<?php echo $this->loadAnyTemplate('site:com_j2store/myprofile/ordersummary');?>
			</div>
			
		</div>
	</form>
</div>