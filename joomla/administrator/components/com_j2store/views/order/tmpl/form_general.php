<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();?>
<div class="row-fluid">
	<div class="span4">
		
		<div class="panel panel-solid-success order-general-information">
			<div class="panel-body">
			
				<dl class="dl-horizontal">
				<dt><?php echo JText::_("J2STORE_ORDER_ID"); ?> </dt>
				<dd><?php echo $this->item->order_id; ?></dd>
	
				<dt><?php echo JText::_("J2STORE_ORDER_AMOUNT"); ?></dt>
				<dd><?php echo $this->currency->format( $this->item->get_formatted_grandtotal(), $this->item->currency_code, $this->item->currency_value ); ?></dd>
	
				<dt><?php echo JText::_("J2STORE_ORDER_DATE"); ?></dt>
				<dd><?php echo JHTML::_('date', $this->item->created_on, $this->params->get('date_format', JText::_('DATE_FORMAT_LC1'))); ?></dd>
	
				<dt><?php echo JText::_("J2STORE_ORDER_STATUS"); ?></dt>
				<dd>
				<span class="label <?php echo $this->item->orderstatus_cssclass;?> order-state-label">
					<?php echo JText::_($this->item->orderstatus_name);?>
				</span>
				</dd>
				<dt><?php echo JText::_('J2STORE_CUSTOMER_CHECKOUT_LANGUAGE'); ?></dt>
				<dd> <?php echo $this->item->get_customer_language(); ?> </dd>
			</dl>
			</div>
		</div>		
		<?php echo J2Store::plugin()->eventWithHtml('AdminOrderAfterGeneralInformation', array($this)); ?>
		<div class="panel panel-solid-info">
			<?php echo $this->loadTemplate('orderstatus');?>
		</div>		 
	</div>
	<div class="span4">
		<?php echo $this->loadTemplate('customer');?>

		<?php echo $this->loadTemplate('payment');?>

		<?php echo $this->loadTemplate('shipping');?>

	</div>
	<div class="span4">
		<div class="order-list-print">
			<span class="action-buttons">
			<?php
			$url = JRoute::_( "index.php?option=com_j2store&view=orders&task=printOrder&tmpl=component&order_id=".$this->item->order_id);
			echo J2StorePopup::popup($url, JText::_( "J2STORE_PRINT_ORDER" ), array('class'=>'fa fa-print btn btn-primary btn-large'));
			?>
		</span>
		<span class="action-buttons">
			<?php  $url = JRoute::_( "index.php?option=com_j2store&view=orders&task=printShipping&tmpl=component&order_id=".$this->orderinfo->order_id);
			echo J2StorePopup::popupAdvanced($url,JText::_('J2STORE_PRINT_SHIPPING_ADDRESS'), array('class'=>'fa fa-print btn btn-inverse btn-large','width'=>800 , 'height'=>600));
			?>
			</span>
		</div>
		<?php echo $this->loadTemplate('orderhistory');?>
	</div>
</div>