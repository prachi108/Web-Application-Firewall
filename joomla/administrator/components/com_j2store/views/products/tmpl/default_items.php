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
<table class="table table-striped table-bordered">
	<thead>
		<tr>
			<th><?php echo JText::_('J2STORE_NUM');?></th>
			<th><input type="checkbox" name="checkall-toggle" value=""
				title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
				onclick="Joomla.checkAll(this)" /></th>
			<th>
						<?php  echo JHTML::_('grid.sort',  'J2STORE_PRODUCT_ID', 'j2store_product_id',$this->state->filter_order_Dir, $this->state->filter_order ); ?>
					</th>
			<th width="30%" class="title">
						<?php  echo JText::_('J2STORE_PRODUCT_NAME'); ?>
					</th>

			<th><?php  echo JText::_('J2STORE_PRODUCT_SKU'); ?></th>
			<th><?php  echo JText::_('J2STORE_PRODUCT_PRICE'); ?></th>			
			<th><?php  echo JText::_('J2STORE_SHIPPING'); ?></th>
					<?php if($this->params->get('enable_inventory', 0)):?>
					<th><?php  echo JText::_('J2STORE_CURRENT_STOCK'); ?></th>
					<?php endif;?>
					<th><?php  echo JHTML::_('grid.sort',  'J2STORE_PRODUCT_SOURCE', 'product_source', $this->state->filter_order_Dir, $this->state->filter_order ); ?></th>
			<th><?php  echo JHTML::_('grid.sort',  'J2STORE_PRODUCT_SOURCE_ID', 'product_source_id', $this->state->filter_order_Dir, $this->state->filter_order ); ?></th>
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
					$checked = JHTML::_('grid.id', $i, $item->j2store_product_id );

					?>
					<tr>
			<td><?php echo $this->pagination->getRowOffset( $i ); ?>
						</td>
			<td><?php echo $checked; ?>
						</td>
			<td>
							<?php echo $item->j2store_product_id;?>

						</td>
			<td>

							<?php
								$thumbimage = '';
							 	if(JFile::exists(JPATH_SITE.'/'.$item->thumb_image)){
									$thumbimage = JUri::root().$item->thumb_image;
							 	}?>
							 	<?php if(!empty($thumbimage )): ?>
							 	<div class="pull-left">
					<a target="_blank" href="<?php echo $item->product_edit_url;?>"> <img
						class="j2store-product-thumb-image"
						src="<?php echo $thumbimage;?>"
						title="<?php echo $item->product_name;?>"
						alt="<?php echo $item->product_name;?>" />
					</a>
				</div>
								<?php endif; ?>

									<a target="_blank" href="<?php echo $item->product_edit_url;?>">
					<strong>
										<?php echo $item->product_name;?>
									</strong>
			</a> <br /> <span>
									<?php echo JText::_('J2STORE_PRODUCT_TYPE')?> : <label
					class="label label-info"><?php echo $item->product_type; ?></label>
			</span> <br /> <span>
									<?php echo JText::_('J2STORE_PRODUCT_VISIBILITY')?> : <label
					class="label label-<?php echo $item->visibility ? 'success':'important'; ?>"><?php echo $item->visibility ? JText::_('JYES'):JText::_('JNO'); ?></label>
			</span>
						<?php echo JText::_('J2STORE_TAXPROFILE'); ?>: 
						
						<?php if($item->taxprofile_id):?>
							<label class="label label-inverted"><?php echo $item->taxprofile_name; ?></label>
						<?php else: ?>
							<label class="label label-warning"><?php echo JText::_('J2STORE_NOT_TAXABLE'); ?> </label>
						<?php endif; ?>
			

			</td>

						<?php if($item->product_type !='variable'):?>
						<td><?php echo $item->sku; ?></td>
					<td> <?php echo J2store::currency()->format($item->price); ?></td>
					
			
						<td>
							<?php if($item->shipping):?>
							<label class="label label-success"> <?php echo JText::_('J2STORE_ENABLED'); ?> </label>
							<?php else: ?>
							<label class="label label-warning"> <?php echo JText::_('J2STORE_DISABLED'); ?> </label>
							<?php endif; ?>
						</td>

							<?php if($this->params->get('enable_inventory')):?>
							<td>
								<?php if($item->manage_stock == 1): ?>
									<?php echo $item->quantity; ?>
								<?php else : ?>
									<?php echo JText::_('J2STORE_NO_STOCK_MANAGEMENT'); ?>
								<?php endif; ?>
							</td>
							<?php endif;?>
						<?php else:?>
						<td colspan="4">
							<?php if($item->product_type=='variable'):?>
							<?php echo JText::_('J2STORE_HAS_VARIANTS'); ?>
							<button type="button" class="btn btn-small btn-warning"
					id="showvariantbtn-<?php echo $item->j2store_product_id;?>"
					href="javascript:void(0);"
					onclick="jQuery('#hide-icon-<?php echo $item->j2store_product_id;?>').toggle('click');jQuery('#show-icon-<?php echo $item->j2store_product_id;?>').toggle('click');jQuery('#variantListTable-<?php echo $item->j2store_product_id;?>').toggle('click');">
								<?php echo JText::_('J2STORE_OPEN_CLOSE'); ?>
								<i id="show-icon-<?php echo $item->j2store_product_id;?>"
						class="icon icon-plus"></i> <i
						id="hide-icon-<?php echo $item->j2store_product_id;?>"
						class="icon icon-minus" style="display: none;"></i>
				</button>
				<table id="variantListTable-<?php echo $item->j2store_product_id;?>"
					class="table table-condensed table-bordered hide">

					<thead>
						<th><?php echo JText::_('J2STORE_VARIANT_NAME'); ?></th>
						<th><?php echo JText::_('J2STORE_VARIANT_SKU'); ?></th>
						<th><?php echo JText::_('J2STORE_VARIANT_PRICE'); ?></th>
						<th><?php echo JText::_('J2STORE_PRODUCT_ENABLE_SHIPPING'); ?></th>
						<th><?php echo JText::_('J2STORE_CURRENT_STOCK'); ?></th>
					</thead>
					<tbody>
										<?php
										$variant_model = F0FModel::getTmpInstance('Variants', 'J2StoreModel');
										$variant_model->setState('product_type', $item->product_type);
										$variants = $variant_model->product_id($item->j2store_product_id)
													->is_master(0)
													->getList();
										if(isset($variants) && count($variants)):
										foreach($variants as $variant):
										?>
										<tr class="variants-list">
							<td><?php echo J2Store::product()->getVariantNamesByCSV($variant->variant_name); ?></td>
							<td><?php echo $variant->sku; ?></td>
							<td><?php echo J2store::currency()->format($variant->price); ?></td>
							<td><?php echo (isset($variant->shipping) && ($variant->shipping)) ? JText::_('J2STORE_YES') : JText::_('J2STORE_NO'); ?></td>
							<td><?php echo $variant->quantity;?></td>
						</tr>
										<?php endforeach;?>
										<?php else:?>
										<tr>
							<td colspan="5"><?php echo JText::_('J2STORE_NO_ITEMS_FOUND')?></td>
						</tr>
										<?php endif;?>
									</tbody>
				</table>
							<?php endif;?>
						</td>
						<?php endif;?>

						<td>
						<?php echo  $item->product_source;?>
						<br />						
						<?php if($item->product_source == 'com_content' && ($item->source->state == 0 || $item->source->state == -2)): ?>
							<?php
							$state_array = array (
									'-2' => array('important', 'JTRASHED'),
									'0' => array('warning', 'JUNPUBLISHED'),
									'1' => array('success', 'JPUBLISHED') 
							);
							?>							
							<label class="label label-<?php echo $state_array[$item->source->state][0]; ?>">
								<?php echo JText::_($state_array[$item->source->state][1]);?>
							</label>							
						<?php endif;?>
						
						</td>
			<td><?php echo $item->product_source_id;?></td>
		</tr>
				<?php endforeach;?>
				<?php else:?>
				<tr>
			<td colspan="10"><?php  echo JText::_('J2STORE_NO_ITEMS_FOUND');?></td>
		</tr>
				<?php endif;?>
			</tbody>
</table>