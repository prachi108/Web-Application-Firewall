<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

JHtml::_('behavior.modal');
jimport('joomla.filesystem.file');
$this->loadHelper('select');
?>
<div class="j2store">
	<div id="j2store-system-message-container">

	</div>
<form class="form-horizontal form-validate" id="adminForm" name="adminForm" method="post" action="index.php">
	<input type="hidden" name="option" value="com_j2store">
	<input type="hidden" name="view" value="filtergroups">
	<input type="hidden" name="task" value="">
	<input type="hidden" id="j2store_filtergroup_id" name="j2store_filtergroup_id" value="<?php echo $this->item->j2store_filtergroup_id; ?>" />
	<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken();?>" value="1" />
	<fieldset>
	<legend><?php echo JText::_('J2STORE_PRODUCT_FILTER_GROUPS_DETAILS'); ?> </legend>
	<table class="admintable">
		<tr>
			<td width="100" align="right" class="key">
				<label for="group_name">
					<?php echo JText::_( 'J2STORE_PRODUCT_FILTER_NAME' ); ?>:
				</label>
			</td>
			<td>
				<?php echo J2Html::text('group_name' ,$this->item->group_name ,array('class'=>'required') );?>
			</td>
		</tr>
		<tr>
			<td valign="top" align="right" class="key">
				<?php echo JText::_( 'J2STORE_OPTION_STATE' ); ?>:
			</td>
			<td>
				<?php echo J2StoreHelperSelect::publish('enabled',$this->item->enabled); ?>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="group_name">
					<?php echo JText::_( 'JGRID_HEADING_ORDERING' ); ?>:
				</label>
			</td>
			<td>
				<?php echo J2Html::text('ordering' ,$this->item->ordering ,array('class'=>'required') );?>
			</td>
		</tr>
	</table>

</fieldset>
<fieldset id="filter-value">
	<div class="row-fluid">
		<div class="span9">
			<legend><h3><?php echo JText::_('J2STORE_ADD_NEW_PRODUCT_FILTER_VALUES');?></h3></legend>
				<table id="pFilerValue" class="list table table-bordered table-stripped">
		          <thead>
		            <tr>
		              <td><span><?php echo JText::_('J2STORE_PRODUCT_FILTER_VALUE');?></span></td>
		              <td class="right"><?php echo JText::_('JGRID_HEADING_ORDERING'); ?></td>
		              <td><?php echo JText::_('J2STORE_REMOVE'); ?></td>
		            </tr>
		          </thead>
		          <?php $product_filter_value_row = 0; ?>
		          <?php if(isset($this->item->filtervalues) && !empty($this->item->filtervalues)):?>
		          <?php foreach($this->item->filtervalues as $filter_value):?>
		          <tbody id="filter-value-row<?php echo $product_filter_value_row; ?>">
		            <tr>
		            	<td>
		            		<?php echo J2Html::hidden('filter_value['.$filter_value->j2store_filter_id.'][j2store_filter_id]',$filter_value->j2store_filter_id);?>
		            		<?php echo J2Html::text('filter_value['.$filter_value->j2store_filter_id.'][filter_name]',$filter_value->filter_name );?>
		            	</td>
		            	<td>
			            	<?php echo J2Html::text('filter_value['.$filter_value->j2store_filter_id.'][ordering]',$filter_value->ordering ,array('class'=>'input-mini') );?>
		            	</td>
		            	<td>
							<?php echo J2html::button('delete',JText::_('J2STORE_REMOVE'), array('class' => 'btn btn-danger',"id" => "filterValueDeleteBtn-$filter_value->j2store_filter_id",'onclick' => 'DeleteFilterValue('.$filter_value->j2store_filter_id .','.$product_filter_value_row.')') );?>
		            	</td>
		            </tr>
		          </tbody>
		          <?php $product_filter_value_row++; ?>
		          <?php endforeach; ?>
		          <?php endif;?>
		          <tfoot>
		            <tr>
		              <td  colspan="4">
		              	<a  href="javascript:void(0)" onclick="j2storeAddFilterToGroup();" class="btn btn-primary pull-right"><i class="icon icon-plus"></i> <?php echo JText::_('J2STORE_ADD'); ?></a>
		              	</td>
		            </tr>
		          </tfoot>
		        </table>
	        </div>
	        <div class="span3">
	        </div>
       </div>
</fieldset>
</form>

<script type="text/javascript">
var filter_value_row = <?php echo $product_filter_value_row; ?>;
function j2storeAddFilterToGroup(){
	(function($) {
	html  = '<tbody id="filter-value-row' + filter_value_row + '">';
	html += '  <tr>';
    html += '    <td class="left"><input type="hidden" name="filter_value[' + filter_value_row + '][j2store_filter_id]" value="" />';
	html += '<input type="text" class="input" name="filter_value[' + filter_value_row + '][filter_name]" value="" /> <br />';
	html += '  </td>';
	html += '    <td class="right"><input class="input-small" type="text" name="filter_value[' + filter_value_row + '][ordering]" value="" size="1" /></td>';
	html += '    <td class="left"><a class="btn btn-danger" onclick="j2store.jQuery(\'#filter-value-row' + filter_value_row + '\').remove();" class="button"><?php echo JText::_('J2STORE_REMOVE'); ?></a></td>';
	html += '  </tr>';
    html += '</tbody>';

	//$('#filter-value-row tfoot').before(html);
	$("#pFilerValue tfoot").before(html);

	filter_value_row++;
	})(j2store.jQuery);
}

function DeleteFilterValue(productfiltervalue_id , filter_value_row){
	(function($) {
				$.ajax({
	            url :'index.php?option=com_j2store&view=filtergroups&task=deleteproductfiltervalues&productfiltervalue_id='+productfiltervalue_id ,
				type: 'post',
				dataType:'json',
				beforeSend:function(){
					$("#filterValueDeleteBtn-"+productfiltervalue_id).attr('value','<?php echo JText::_('J2STORE_REMOVE_CONTINUE');?>');
				},
				success: function(json){
					var html = '';
					if(json['success']){
						$('#filter-value-row'+filter_value_row).remove();
						   html ='<div class="alert alert-success alert-block">';
						   html +='<p>';
						   html +=json['msg'];
						   html +='</p>';
						   html +='</div>';
						$("#j2store-system-message-container").html(html);
					}else{
						   html ='<div class="alert alert-warning alert-block">';
						   html +='<p>';
						   html +=json['msg'];
						   html +='</p>';
						   html +='</div>';
						 $("#filterValueDeleteBtn-"+productfiltervalue_id).attr('value','<?php echo JText::_('J2STORE_REMOVE');?>');
						 $("#j2store-system-message-container").html(html);
					}
				}
		});
	})(j2store.jQuery);
}

</script>


