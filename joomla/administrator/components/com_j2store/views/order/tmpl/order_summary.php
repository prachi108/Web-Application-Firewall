<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
defined ( '_JEXEC' ) or die ();
$order = $this->order;
$items = $this->order->getItems();
$currency = J2Store::currency();
?>
<?php if(count($items)):?>
<h3><?php echo JText::_('J2STORE_ORDER_SUMMARY')?></h3>
	<table class="j2store-cart-table table table-bordered">
		<thead>
			<tr>
				<th><?php echo JText::_('J2STORE_CART_LINE_ITEM'); ?></th>
				<th><?php echo JText::_('J2STORE_CART_LINE_ITEM_QUANTITY'); ?></th>
				<th><?php echo JText::_('J2STORE_CART_LINE_ITEM_TOTAL'); ?></th>
			</tr>
			</thead>
			<tbody>

				<?php foreach ($items as $item): ?>
				<?php
					$registry = new JRegistry;
					$registry->loadString($item->orderitem_params);
					$item->params = $registry;
					$thumb_image = $item->params->get('thumb_image', '');
				?>
				<tr>
					<td>
						<?php if($this->params->get('show_thumb_cart', 1) && !empty($thumb_image)): ?>
							<span class="cart-thumb-image">
								<?php if(JFile::exists(JPATH_SITE.'/'.$thumb_image)): ?>
									<img src="<?php echo JUri::root(true). '/'.$thumb_image; ?>" >
								<?php endif;?>
							</span>
						<?php endif; ?>
						<span class="cart-product-name">
							<?php echo $item->orderitem_name; ?>  
						</span>
						<br />
						<?php if(isset($item->orderitemattributes)): ?>
							<span class="cart-item-options">
							<?php foreach ($item->orderitemattributes as $attribute):
								if($attribute->orderitemattribute_type == 'file') {
									unset($table);
									$table = F0FTable::getInstance('Upload', 'J2StoreTable')->getClone();
									if($table->load(array('mangled_name'=>$attribute->orderitemattribute_value))) {
										$attribute_value = $table->original_name;
									}
								}else {
									$attribute_value = $attribute->orderitemattribute_value;
								}
							?>
								<small>
								- <?php echo JText::_($attribute->orderitemattribute_name); ?> : <?php echo $attribute_value; ?>
								</small>

								<!--link to download for files-->
             				   <?php if(JFactory::getApplication()->isAdmin() && $attribute->orderitemattribute_type=='file' && JFactory::getApplication()->input->getString('task')!='printOrder'):?>

             					  <a target="_blank" class="btn btn-primary"
             					  href="<?php echo JRoute::_('index.php?option=com_j2store&view=orders&task=download&ftoken='.$attribute->orderitemattribute_value);?>"
             					  >
             					  <i class="icon icon-download"></i>
             					   <?php echo JText::_('J2STORE_DOWNLOAD');?>
             					   </a>
             				   	<?php endif;?>
             				   	<br />
							<?php endforeach;?>
							</span>
						<?php endif; ?>

						<?php if($this->params->get('show_price_field', 1)): ?>

							<span class="cart-product-unit-price">
								<span class="cart-item-title"><?php echo JText::_('J2STORE_CART_LINE_ITEM_UNIT_PRICE'); ?></span>								
								<span class="cart-item-value">
									<?php echo $currency->format($this->order->get_formatted_order_lineitem_price($item, $this->params->get('checkout_price_display_options', 1)), $this->order->currency_code, $this->order->currency_value);?>
								</span>
							</span>
						<?php endif; ?>

						<?php if(!empty($item->orderitem_sku)): ?>
						<br />
							<span class="cart-product-sku">
								<span class="cart-item-title"><?php echo JText::_('J2STORE_CART_LINE_ITEM_SKU'); ?></span>
								<span class="cart-item-value"><?php echo $item->orderitem_sku; ?></span>
							</span>

						<?php endif; ?>
					</td>
					<td><?php echo $item->orderitem_quantity; ?></td>
					<td class="cart-line-subtotal">
						<?php echo $currency->format($item->orderitem_finalprice_without_tax, $this->order->currency_code, $this->order->currency_value ); //$this->order->get_formatted_lineitem_total($item, $this->params->get('checkout_price_display_options', 1))?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		 	
			<tfoot class="cart-footer">
		<?php $colmspan = 3;?>		
			<tr>
			<td colspan="<?php echo $colmspan;?>">
			<?php  echo $this->loadTemplate('voucher'); ?>
			</td>
			</tr>
			<tr>
			<td colspan="<?php echo $colmspan;?>">
			<?php  echo $this->loadTemplate('coupon'); ?>
			</td>
			</tr>
			<tr>
				<td colspan="<?php echo $colmspan;?>">
					<h3>
						<?php echo JText::_('J2STORE_CART_TOTALS'); ?>
					</h3>
				</td>
			</tr>
			<?php $colmspan = 2;?>
			<tr>
				<td colspan="<?php echo $colmspan;?>"><?php echo JText::_('J2STORE_CART_SUBTOTAL'); ?>
				</td>
				<td><?php echo $currency->format($this->order->order_subtotal_ex_tax,  $this->order->currency_code, $this->order->currency_value );//$this->order->get_formatted_subtotal($this->params->get('checkout_price_display_options', 1),$this->order->getItems()),?>
				</td>
			</tr>
			<!-- shipping -->
			<?php if(isset($this->order->order_shipping) && ($this->order->order_shipping > 0) && !empty($this->shipping->ordershipping_name)): ?>
			<tr>
				<td colspan="<?php echo $colmspan;?>"><?php echo JText::_(stripslashes($this->shipping->ordershipping_name)); ?>
				</td>
				<td><?php echo $this->currency->format($this->order->order_shipping,$this->order->currency_code, $this->order->currency_value); ?>
				</td>
			</tr>
			<?php endif; ?>
			<!-- shipping tax -->
			<?php if(isset($this->order->order_shipping_tax) && ($this->order->order_shipping_tax > 0) && $this->order->order_shipping_tax > 0): ?>
			<tr>
				<td colspan="<?php echo $colmspan;?>"><?php echo JText::_('J2STORE_ORDER_SHIPPING_TAX'); ?>
				</td>
				<td><?php echo $this->currency->format($this->order->order_shipping_tax,$this->order->currency_code, $this->order->currency_value); ?>
				</td>
			</tr>
			<?php endif; ?>
			<!-- shipping tax -->
			<?php foreach ( $this->order->get_fees() as $fee ) :?>			
			<tr>
				<td colspan="<?php echo $colmspan;?>"><?php echo JText::_($fee->name); ?>
				</td>
				<td><?php echo $this->currency->format($this->order->get_formatted_fees($fee, $this->params->get('checkout_price_display_options', 1)), $this->order->currency_code, $this->order->currency_value); ?>
				</td>
			</tr>
			<?php endforeach;?>
			<!-- surcharge -->
			<?php if($this->order->order_surcharge > 0):?> 
			<tr>
				<td colspan="<?php echo $colmspan;?>"><?php echo JText::_('J2STORE_CART_SURCHARGE'); ?>
				</td>
				<td><?php echo $this->currency->format($this->order->order_surcharge, $this->order->currency_code, $this->order->currency_value); ?>
				</td>
			</tr>		
			<?php endif;?>
			<!-- discount -->
			<?php foreach($this->order->getOrderDiscounts() as $discount):?>
					<?php if($discount->discount_amount > 0 ):?>
					<tr>
						<td colspan="<?php echo $colmspan;?>">
						<?php if($discount->discount_type == 'coupon'):?>
							<?php echo JText::sprintf('J2STORE_COUPON_TITLE', $discount->discount_title); ?>
							<a class="j2store-remove remove-icon" href="javascript:void(0)" onClick="removeCoupon()">X</a>
						<?php elseif($discount->discount_type == 'voucher'):?>
							<?php echo JText::sprintf('J2STORE_VOUCHER_TITLE', $discount->discount_title); ?>
							<a class="j2store-remove remove-icon" href="javascript:void(0)" onClick="removeVouchers()">X</a>
						<?php else:?>
							<?php echo JText::sprintf('J2STORE_DISCOUNT_TITLE', $discount->discount_title); ?>							
						<?php endif;?>
						</td>						
						<td><?php echo $this->currency->format($this->order->get_formatted_discount($discount, $this->params->get('checkout_price_display_options', 1)), $this->order->currency_code, $this->order->currency_value); ?>
						</td>
					</tr>
					<?php endif;?>
			<?php endforeach;?>
			<!-- taxes -->
			<?php if(isset($this->taxes) && count($this->taxes) ): ?>

				<?php foreach ($this->taxes as $tax):?>
					<tr>
						<td colspan="<?php echo $colmspan;?>"><?php if($this->params->get('checkout_price_display_options', 1)):?>
									<?php echo JText::sprintf('J2STORE_CART_TAX_INCLUDED_TITLE', $tax->ordertax_title, floatval($tax->ordertax_percent).'%');?>
							<?php else:?>
									<?php echo JText::sprintf('J2STORE_CART_TAX_EXCLUDED_TITLE', $tax->ordertax_title, floatval($tax->ordertax_percent).'%');?>
							<?php endif;?>
						</td>
						<td>
							<?php echo $this->currency->format($tax->ordertax_amount,$this->order->currency_code, $this->order->currency_value);?>
						</td>
					</tr>
				<?php endforeach;?>

			<?php endif; ?>
			<!-- refund -->
			<?php if($this->order->order_refund):?>
			<tr>
				<td colspan="<?php echo $colmspan;?>"><?php echo JText::_('J2STORE_CART_REFUND');?></td>
				<td><?php echo $this->currency->format($this->order->order_refund,$this->order->currency_code, $this->order->currency_value);?></td>
			</tr>
			<?php endif;?>
			<tr>
				<td colspan="<?php echo $colmspan;?>"><?php echo JText::_('J2STORE_CART_GRANDTOTAL'); ?>
				</td>
				<td><?php echo $this->currency->format($this->order->get_formatted_grandtotal(),$this->order->currency_code, $this->order->currency_value); ?>
				</td>
				
			</tr>				
			<tr><td colspan="<?php echo $colmspan+1;?>"><button id="calculate_tax" class="btn btn-warning"><?php echo JText::_('J2STORE_CALCULATE_TAX');?></button></td></tr>
		</tfoot>				
	</table>
<?php else :?>
<span class="cart-no-items">
				<?php echo JText::_('J2STORE_CART_NO_ITEMS'); ?>
</span>
<?php endif;?>	
<script type="text/javascript">							
	(function($){
		$('#calculate_tax').on('click', function(e){
			e.preventDefault();
		var data1 = {
				option: 'com_j2store',
				view: 'orders',
				task: 'calculateTax',	
				oid: '<?php echo $this->order->j2store_order_id;?>'			
			};
		$.ajax({
			type : 'post',
			url :  'index.php',
			data : data1,		
			dataType: 'json',
			success : function(json) {	
				
				if(json['error']){
					//$('.j2store-remove').after('<span>'+json['error']+'</span>');			
				}
				if(json['success']){
					 window.location = json['redirect']; 
				}
						
			},
		 error: function(xhr, ajaxOptions, thrownError) {
             //alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
         }
		});
		});
	})(j2store.jQuery);

function removeCoupon(){	
	(function($){
		/* $('#task').attr('value','displayAdminProduct');
		$('#view').attr('value','products'); */
		var post_data = $('#adminForm').serializeArray();
		var data1 = {
				option: 'com_j2store',
				view: 'carts',
				task: 'removeCoupon',				
			};
		$.each( post_data, function( key, value ) {
			
			 if (!(value['name'] in data1) ){
				 data1[value['name']] = value['value'];	
			}
			
		});
		console.log(data1);
		$.ajax({
			type : 'post',
			url :  'index.php',
			data : data1,		
			dataType: 'json',
			success : function(json) {	
				
				if(json['error']){
					//$('.j2store-remove').after('<span>'+json['error']+'</span>');			
				}
				if(json['success']){
					 window.location = json['redirect']; 
				}
						
			},
		 error: function(xhr, ajaxOptions, thrownError) {
             //alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
         }
		});
	})(j2store.jQuery);
}
function removeVouchers(){
	(function($){
		/* $('#task').attr('value','displayAdminProduct');
		$('#view').attr('value','products'); */
		var post_data = $('#adminForm').serializeArray();
		var data1 = {
				option: 'com_j2store',
				view: 'carts',
				task: 'removeVoucher',				
			};
		$.each( post_data, function( key, value ) {
			
			 if (!(value['name'] in data1) ){
				 data1[value['name']] = value['value'];	
			}
			
		});
		//console.log(data1);
		$.ajax({
			type : 'post',
			url :  'index.php',
			data : data1,		
			dataType: 'json',
			success : function(json) {	
				
				if(json['error']){
					//$('.j2store-remove').after('<span>'+json['error']+'</span>');			
				}
				if(json['success']){
					 window.location = json['redirect']; 
				}
						
			},
		 error: function(xhr, ajaxOptions, thrownError) {
             //alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
         }
		});
	})(j2store.jQuery);
}
</script>