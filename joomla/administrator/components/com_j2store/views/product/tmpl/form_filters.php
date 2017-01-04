<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
?>
<div class="j2store-product-filters">
	<div class="row-fluid">
		<div class="span7">
			<div class="alert alert-info alert-block">
				<strong><?php echo JText::_('J2STORE_NOTE'); ?></strong> <?php echo JText::_('J2STORE_FEATURE_AVAILABLE_IN_J2STORE_PRODUCT_LAYOUTS'); ?>
			</div>
			<div class="control-group">
				<h3><?php echo JText::_('COM_J2STORE_TITLE_FILTERGROUPS'); ?></h3>
				<table id="product_filters_table"
					class="adminlist table table-striped table-bordered j2store">
					<thead>
						<th><?php echo JText::_('J2STORE_PRODUCT_FILTER_VALUE');?></th>
						<th><?php echo JText::_('J2STORE_REMOVE');?></th>
					</thead>
					<tbody>
					<?php if(isset($this->product_filters) && count($this->product_filters)): ?>
					<?php foreach($this->product_filters as $group_id=>$filters):?>
						<tr>
							<td colspan="2"><h4><?php echo $filters['group_name']; ?></h4></td>
						</tr>
						<?php foreach($filters['filters'] as $filter):
						?>
						<tr
							id="product_filter_current_option_<?php echo $filter->filter_id;?>">
							<td class="addedFilter">
									<?php echo $filter->filter_name ;?>
								</td>
							<td><span class="filterRemove"
								onclick="removeFilter(<?php echo $filter->filter_id; ?>, <?php echo $this->item->j2store_product_id; ?>);">x</span>
								<input type="hidden" value="<?php echo $filter->filter_id;?>"
								name="<?php echo $this->form_prefix.'[productfilter_ids]' ;?>[]" />
							</td>
							</td>
						</tr>
					<?php endforeach;?>
					<?php endforeach;?>
					<?php endif;?>
					<tr class="j2store_a_filter">
							<td colspan="2">
								<?php echo JText::_('J2STORE_SEARCH_AND_PRODUCT_FILTERS');?>
								<?php echo J2Html::text('productfilter' ,'' ,array('id' =>'J2StoreproductFilter'));?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="span5">&nbsp;</div>
	</div>

</div>

<script type="text/javascript">

						function removeFilter(filter_id, product_id) {
							var rem_filter = {
								option: 'com_j2store',
								view: 'products',
								task: 'deleteproductfilter',
								filter_id: filter_id,
								product_id: product_id
							};
							(function($) {
								$('.j2notify').remove();
								$.ajax({
									type : 'post',
									url  : '<?php echo JRoute::_('index.php');?>',
									data : rem_filter,
									dataType : 'json',
									success : function(data) {
										if(data.success) {
											$('#product_filter_current_option_'+filter_id).remove();
										}
										$('#product_filters_table').before('<div class="j2notify alert alert-block">'+data.msg+'</div>');
									}
								});
							})(j2store.jQuery);

						}


(function($) {
		$(document).ready(function() {
			$('#J2StoreproductFilter').autocomplete({
				source : function(request, response) {
					var search_filter = {
						option: 'com_j2store',
						view: 'products',
						task: 'searchproductfilters',
						q: request.term
					};
					$.ajax({
						type : 'post',
						url  : '<?php echo JRoute::_('index.php');?>',
						data : search_filter,
						dataType : 'json',
						success : function(data) {
							$('#J2StoreproductFilter').removeClass('optionsLoading');
							response($.map(data, function(item) {
								return {
									label:item.group_name +' > '+item.filter_name,
									value: item.j2store_filter_id
								}
							}));
						}
					});
				},
				minLength : 2,
				select : function(event, ui) {
					$('<tr><td class=\"addedFilter\">' + ui.item.label+ '</td><td><span class=\"filterRemove\" onclick=\"j2store.jQuery(this).parent().parent().remove();\">x</span><input type=\"hidden\"  value=\"' + ui.item.value+ '\" name=\"<?php echo $this->form_prefix.'[productfilter_ids]' ;?>[]\" /> </td></tr>').insertBefore('.j2store_a_filter');
					this.value = '';
					return false;
				},
				search : function(event, ui) {
					$('#J2StoreproductFilter').addClass('optionsLoading');
				}
			});

		});
		})(j2store.jQuery);
</script>
