<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();?>
<div class="payment-information">
	<table class="table table-bordered">
		<tr>
			<td><?php echo JText::_('J2STORE_ORDER_PAYMENT_TYPE'); ?>	:</td>
			<td><?php echo JText::_($this->item->orderpayment_type); ?></td>
		</tr>
		<?php if(!empty($this->item->transaction_id)): ?>
		<tr>
			<td><?php echo JText::_('J2STORE_ORDER_TRANSACTION_ID'); ?></td>
			<td><?php echo $this->item->transaction_id; ?></td>
		</tr>
		<?php endif; ?>
		<tr>
			<td><?php echo JText::_('J2STORE_ORDER_TRANSACTION_LOG'); ?></td>
			<td>  <!-- Button to trigger modal -->
   				 <a href="#myTransaction" role="button" class="btn btn-success" data-toggle="modal"><?php echo JText::_('J2STORE_VIEW');?></a>
  			</td>
		</tr>
		<tr>
			<td colspan="2">

				<?php $pay_html =  trim(J2Store::getSelectableBase()->getFormatedCustomFields($this->orderinfo, 'customfields', 'payment'));?>
				<?php if($pay_html ):?>
					<div class="center">
						<strong><?php echo JText::_('J2STORE_PAYMENT_ADDRESS');?></strong>
						<?php echo J2StorePopup::popupAdvanced("index.php?option=com_j2store&view=orders&task=setOrderinfo&order_id=".$this->item->order_id."&address_type=payment&layout=address&tmpl=component",'',array('class'=>'fa fa-pencil','update'=>true,'width'=>700,'height'=>600));?>
					</div>
					<?php echo $pay_html; ?>
				<?php endif;?>

			</td>
		</tr>
		
</table>
<?php echo J2Store::plugin()->eventWithHtml('AdminOrderAfterPaymentInformation', array($this)); ?>
</div>

<!-- Transaction log modal window -->
  <div id="myTransaction" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myTransactionLabel" aria-hidden="true">
   <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    	<h3 id="myTransactionLabel">
			<?php echo JText::_('J2STORE_TRANSACTION_LOG_HEADER'); ?>
						&nbsp;
						<?php echo $this->item->order_id; ?>
    	</h3>
    </div>
    <div class="modal-body">
		<div class="j2store">
			<div class="row-fluid">
				<div class="span12">
					<div class="alert alert-info">
						<?php echo JText::_('J2STORE_TRANSACTION_LOG_HELP_MSG');?>
					</div>
					<ul>
						<li><?php echo JText::_('J2STORE_ORDER_TRANSACTION_STATUS'); ?>
							<div class="alert alert-warning">
								<small><?php echo JText::_('J2STORE_ORDER_TRANSACTION_STATUS_HELP_MSG'); ?>
								</small>
							</div>
							<p>
								<?php echo JText::_($this->item->transaction_status); ?>
							</p>
						</li>
						<li><?php echo JText::_('J2STORE_ORDER_TRANSACTION_DETAILS'); ?> <br />
							<div class="alert alert-warning">
								<small><?php echo JText::_('J2STORE_ORDER_TRANSACTION_DETAILS_HELP_MSG'); ?>
								</small>
							</div>
							<p>
								<?php echo JText::_($this->item->transaction_details); ?>
							</p>
						</li>

					</ul>
				</div>
			</div>
		</div>
    </div>
    <div class="modal-footer">
	    <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('J2STORE_CLOSE');?></button>
    </div>
    </div>
