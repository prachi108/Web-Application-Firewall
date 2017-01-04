<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access
defined('_JEXEC') or die;
$key = 0;
?>

<div class="j2store-product-options">
	<div class="row-fluid">
		<div class="span7">
			<div class="control-group">
				<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_OPTIONS'), 'option_name'); ?>
				<?php // $this->item->has_options = count($this->item->product_options) ? true : false;?>

				<table id="attribute_options_table" class="adminlist table table-striped table-bordered j2store">
					<thead>
						<tr>
						<th><?php echo JText::_('J2STORE_OPTION_NAME');?></th>
						<th><?php echo JText::_('J2STORE_OPTION_REQUIRED');?></th>						
						<th><?php echo JText::_('J2STORE_OPTION_ORDERING');?></th>
						
						<th><?php echo JText::_('J2STORE_OPTION_REMOVE');?></th>
						</tr>
				</thead>
				<tbody>
					<?php if(isset($this->item->product_options ) && !empty($this->item->product_options)):

					?>
					<?php foreach($this->item->product_options as  $poption ):?>
					<tr id="pao_current_option_<?php echo $poption->j2store_productoption_id;?>">
						<td>
							<?php echo $poption->option_name;?>
							<?php echo J2Html::hidden($this->form_prefix.'[item_options]['.$poption->j2store_productoption_id .'][j2store_productoption_id]', $poption->j2store_productoption_id);?>
							<?php echo J2Html::hidden($this->form_prefix.'[item_options]['.$poption->j2store_productoption_id .'][option_id]', $poption->option_id);?>
							<small>(<?php  echo $poption->option_unique_name;?>)</small>
							<small><?php JText::_('J2STORE_OPTION_TYPE');?><?php echo JText::_('J2STORE_'.JString::strtoupper($poption->type))?></small>
							<?php if(isset($poption->type) && ($poption->type =='select' || $poption->type =='radio' || $poption->type =='checkbox')):?>
							<?php echo J2StorePopup::popup("index.php?option=com_j2store&view=products&task=setproductoptionvalues&product_id=".$this->item->j2store_product_id."&productoption_id=".$poption->j2store_productoption_id."&layout=productoptionvalues&tmpl=component", JText::_( "J2STORE_OPTION_SET_VALUES" ), array());?>
							<?php endif;?>
						</td>
						<td>
						<?php echo J2Html::select()->clearState()
												   ->type('genericlist')
													->name($this->form_prefix.'[item_options]['.$poption->j2store_productoption_id .'][required]')
													->value($poption->required)
													->setPlaceHolders(array('0' => JText::_('J2STORE_NO') ,'1' => JText::_('J2STORE_YES')))
													->attribs(array('class'=>'input-small'))
													->getHtml();
							?>
						</td>
						<td><?php echo J2Html::text($this->form_prefix.'[item_options]['.$poption->j2store_productoption_id .'][ordering]',$poption->ordering,array('id'=>'ordering' ,'class'=>'input-small'));?></td>
						<td>
							<span class="optionRemove" onClick="removePAOption(<?php echo $poption->j2store_productoption_id;?>)">X</span>
						</td>
					</tr>
					<?php $key++;?>
					<?php endforeach;?>
					<?php endif;?>
					<tr class="j2store_a_options">					
						<td colspan="4">
							<label class="attribute_option_label">
							<?php echo JText::_('J2STORE_SEARCH_AND_ADD_VARIANT_OPTION');?>
							</label>
							<?php echo J2Html::text('Selectoption' ,'',array('id'=>'optionselector'));?>
						</td>
					</tr>
				</tbody>
					<tfoot>
						<tr>
							<td colspan="4">
								<?php echo J2StorePopup::popup("index.php?option=com_j2store&view=products&task=setpaimport&product_type=".$this->item->product_type."&product_id=".$this->item->j2store_product_id."&layout=paimport&tmpl=component", JText::_('J2STORE_IMPORT_PRODUCT_OPTIONS'), array('class'=>'btn btn-success','width'=>800 , 'height'=>500));?>
							</td>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
		<div class="span5">
			<div class="alert alert-info">
				<h4><?php echo JText::_('J2STORE_QUICK_HELP'); ?></h4>
				<?php echo JText::_('J2STORE_PRODUCT_OPTIONS_HELP_TEXT'); ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
var key =<?php echo $key;?>;
(function($) {
		$(document).ready(function() {
			$('#optionselector').autocomplete({
				source : function(request, response) {
					var data = {
						option: 'com_j2store',
						view: 'options',
						task: 'getOptions',
						product_type: '<?php echo $this->item->product_type;?>',
						q:request.term
					};
					$.ajax({
						type : 'post',
						url :  '<?php echo JRoute::_('index.php');?>',
						data : data,
						dataType : 'json',
						success : function(data) {
							$('#optionselector').removeClass('optionsLoading');
							response($.map(data, function(item) {
								return {
									label: item.option_name+' ('+item.option_unique_name+')',
									value: item.j2store_option_id
								}
							}));
						}
					});
				},
				minLength : 2,
				select : function(event, ui) {
					$('<tr id=\"j2store-op-tr-'+key+'\"><td class=\"addedOption\">' + ui.item.label+ '</td><td><select name=\"<?php echo $this->form_prefix.'[item_options]' ;?>['+ key+'][required]\" ><option value=\"0\"><?php echo Jtext::_('J2STORE_NO');?></option><option value=\"1\"><?php echo JText::_('J2STORE_YES'); ?></option></select></td><td><input class=\"input-small\" name=\"<?php echo $this->form_prefix.'[item_options]' ;?>['+ key+'][ordering]\" value=\"0\"></td><td><span class=\"optionRemove\" onclick=\"j2store.jQuery(this).parent().parent().remove();\">x</span><input type=\"hidden\" value=\"' + ui.item.value+ '\" name=\"<?php echo $this->form_prefix.'[item_options]' ;?>['+ key+'][option_id]\" /><input type=\"hidden\" value="" name=\"<?php echo $this->form_prefix.'[item_options]' ;?>['+ key +'][j2store_productoption_id]\" /> </td></tr>').insertBefore('.j2store_a_options');
					this.value = '';
					return false;
				},
				search : function(event, ui) {
					$('#optionselector').addClass('optionsLoading');
					key++;
				}
			});

		});
		})(j2store.jQuery);



function removePAOption(pao_id) {
	(function($) {
		var remove_option = {
			option: 'com_j2store',
			view: 'products',
			task: 'removeProductOption',
			pao_id: pao_id
		};
	$.ajax({
			type : 'post',
			url :  '<?php echo JRoute::_('index.php');?>',
			data : remove_option,
			dataType : 'json',
			success : function(data) {
				if(data.success) {
					$('#pao_current_option_'+pao_id).remove();
				}
			 }
		});
	})(j2store.jQuery);
}

</script>