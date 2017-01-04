<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access
defined('_JEXEC') or die;
JHTML::_('behavior.modal');
$this->params = J2Store::config();
?>
<div class="row">
<div class="span12"><span class="pull-right"><?php echo $this->pagination->getLimitBox();?></span></div>
</div>

<table class="table table-striped table-bordered">
	<thead>
		<tr>
			<th><?php echo JText::_('J2STORE_NUM');?></th>
			<th>
				<?php  echo JHTML::_('grid.sort',  'J2STORE_PRODUCT_ID', 'variant_id',$this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>
			<th width="30%" class="title">
				<?php  echo JText::_('J2STORE_PRODUCT_NAME'); ?>
			</th>
			<th><?php  echo JText::_('J2STORE_PRODUCT_SOURCE'); ?></th>
			<!--  <th><?php  echo JHTML::_('grid.sort',  'J2STORE_PRODUCT_SOURCE_ID', 'product_source_id', $this->state->filter_order_Dir, $this->state->filter_order ); ?></th>
			-->
			<th><?php  echo JText::_('J2STORE_PRODUCT_MANAGE_STOCK'); ?></th>
			<th><?php  echo JText::_('J2STORE_PRODUCT_STOCK_QUANTITY'); ?></th>
			<th><?php  echo JText::_('J2STORE_STOCK_STATUS'); ?></th>								
			<th><?php echo JText::_('J2STORE_INVANTORY_SAVE');?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="10"><?php  echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
		<?php
			if($this->products && !empty($this->products)):
				foreach($this->products as $i => $item):
				?>
				<tr>
					<td><?php //echo $this->pagination->getRowOffset( $i ); ?></td>
					<td>
							<?php echo $item->j2store_product_id;?>

					</td>
					<td>
						<a target="_blank" href="<?php echo $item->product->product_edit_url;?>">
							<strong>
								<?php echo $item->product->product_name;?>
							</strong>
						</a>
					</td>
					<td><?php echo  $item->product->product_source;?></td>
					 <!--  <td><?php echo $item->product->product_source_id;?></td>-->
					<?php if($item->product->product_type=='variable'):?>
					<td colspan="4">							
							<?php echo JText::_('J2STORE_HAS_VARIANTS'); ?>
							<button type="button" class="btn btn-small btn-warning" id="showvariantbtn-<?php echo $item->j2store_product_id;?>" href="javascript:void(0);" onclick="jQuery('#hide-icon-<?php echo $item->j2store_product_id;?>').toggle('click');jQuery('#show-icon-<?php echo $item->j2store_product_id;?>').toggle('click');jQuery('#variantListTable-<?php echo $item->j2store_product_id;?>').toggle('click');" >
								<?php echo JText::_('J2STORE_OPEN_CLOSE'); ?>
								<i id="show-icon-<?php echo $item->product->j2store_product_id;?>" class="icon icon-plus"></i>
								<i id="hide-icon-<?php echo $item->product->j2store_product_id;?>" class="icon icon-minus" style="display:none;"></i>
							</button>
							<table id="variantListTable-<?php echo $item->j2store_product_id;?>" class="table table-condensed table-bordered hide">
								<thead>
									<th><?php echo JText::_('J2STORE_VARIANT_NAME'); ?></th>
									<th><?php echo JText::_('J2STORE_VARIANT_SKU'); ?></th>
									<th><?php echo JText::_('J2STORE_PRODUCT_MANAGE_STOCK'); ?></th>
									<th><?php echo JText::_('J2STORE_PRODUCT_STOCK_QUANTITY'); ?></th>
									<th><?php echo JText::_('J2STORE_STOCK_STATUS'); ?></th>
									<th><?php echo JText::_('J2STORE_INVANTORY_SAVE'); ?></th>
								</thead>
								<tbody>
										<?php
										$variant_model = F0FModel::getTmpInstance('Variants', 'J2StoreModel');
										$variant_model->setState('product_type', $item->product->product_type);
										$variants = $variant_model->product_id($item->product->j2store_product_id)
													->is_master(0)
													->getList();
										if(isset($variants) && count($variants)):
										foreach($variants as $variant):
										
										?>
										<tr class="variants-list">
											<td><?php echo J2Store::product()->getVariantNamesByCSV($variant->variant_name); ?></td>
											<td><?php echo $variant->sku; ?></td>
											<td>
												<select name="manage_stock[<?php echo $variant->j2store_variant_id;?>]" id="manage_stock_<?php echo $variant->j2store_variant_id;?>" style="width:100px;";>
													<option value="0" <?php if($variant->manage_stock==0)echo "selected";?>>No</option>
													<option value="1" <?php if($variant->manage_stock==1)echo "selected";?>>Yes</option>						
												 </select>											
											</td>
											<td>
											<input type="number" size="2" id="quantity_<?php echo $variant->j2store_variant_id;?>" name="quantity[<?php echo $variant->j2store_variant_id;?>]" value="<?php echo $variant->quantity;?>">
											<td>
												<select id="availability_<?php echo $variant->j2store_variant_id;?>" name="availability[<?php echo $variant->j2store_variant_id;?>]">
													<option value="0" <?php if($variant->availability==0)echo "selected";?>>Out of Stock</option>
													<option value="1" <?php if($variant->availability==1)echo "selected";?>>In Stock</option>						
					 							</select>											
											</td>
											<td><a class="btn btn-success" onclick="j2storesaveinventory(<?php echo $variant->j2store_variant_id;?>)">Save</a></td>
										</tr>
										<?php endforeach;?>
										<?php else:?>
										<tr>
											<td colspan="5"><?php echo JText::_('J2STORE_NO_ITEMS_FOUND')?></td>
										</tr>
										<?php endif;?>
									</tbody>
								</table>
					</td>
					<?php else:?>
					<td>
					 <select name="manage_stock[<?php echo $item->variant_id;?>]" id="manage_stock_<?php echo $item->variant_id;?>" style="width:100px;";>
						<option value="0" <?php if($item->manage_stock==0)echo "selected";?>>No</option>
						<option value="1" <?php if($item->manage_stock==1)echo "selected";?>>Yes</option>						
					 </select>
					 </td>
					 <td><input type="number" size="2" id="quantity_<?php echo $item->variant_id;?>" name="quantity[<?php echo $item->variant_id;?>]" value="<?php echo $item->quantity;?>"></td>
					 <td>
					 <select id="availability_<?php echo $item->variant_id;?>" name="availability[<?php echo $item->variant_id;?>]">
						<option value="0" <?php if($item->availability==0)echo "selected";?>>Out of Stock</option>
						<option value="1" <?php if($item->availability==1)echo "selected";?>>In Stock</option>						
					 </select>
					 </td>
					 
					 <td><a class="btn btn-success" onclick="j2storesaveinventory(<?php echo $item->variant_id;?>)">Save</a></td>
					 <?php endif;?>
				</tr>
				<?php endforeach; ?>
				<?php else:?>
				<tr>
					<td colspan="10"><?php  echo JText::_('J2STORE_NO_ITEMS_FOUND');?></td>
				</tr>
			<?php endif;?>
	</tbody>
</table>
<script type="text/javascript">
function j2storesaveinventory(variant){	
	var qty = jQuery('#quantity_'+variant).val();
	var availability = jQuery('#availability_'+variant).val();
	var manage_stock = jQuery('#manage_stock_'+variant).val();		
	jQuery.ajax({
		url: 'index.php?option=com_j2store&view=inventories&task=update_inventory&manage_stock='+manage_stock+'&availability='+availability+'&quantity='+qty+'&variant_id='+variant,
		type: 'post',
		dataType: 'json',		
		cache: false,
		contentType: false,
		processData: false,
		beforeSend: function() {
			
		},
		complete: function() {
			
		},
		success: function(json) {
			jQuery('.text-danger, .text-success').remove();

			if (json['error']) {
				
			}

			if (json['success']) {		
				window.location = json['success']; 																														
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});	
}
</script>