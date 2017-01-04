<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
jimport( 'joomla.html.html.jgrid' );

?>

<td><?php echo J2Store::product()->getVariantNamesByCSV($this->variant->variant_name); ?></td>
<td><?php echo $this->variant->sku; ?></td>
<td><?php echo J2store::currency()->format($this->variant->price); ?></td>
<td><?php echo (isset($this->variant->shipping) && ($this->variant->shipping)) ? JText::_('J2STORE_YES') : JText::_('J2STORE_NO'); ?></td>
<td><?php echo $this->variant->quantity;?></td>
<td>
<?php if( $this->variant->isdefault_variant):?>
	<a id="default-variant-<?php echo $this->variant->j2store_variant_id;?>" class="btn btn-micro hasTooltip" title="" onclick="return listItemTask(<?php echo $this->variant->j2store_variant_id;?>,'unsetDefault')" href="javascript:void(0);" data-original-title="UnSet default">
		<i class="icon-featured"></i>
	</a>
<?php else:?>
	<a id="default-variant-<?php echo $this->variant->j2store_variant_id;?>" class="btn btn-micro hasTooltip" title="" onclick="return listItemTask(<?php echo $this->variant->j2store_variant_id;?>,'setDefault')" href="javascript:void(0);" data-original-title="Set default">
		<i class="icon-unfeatured"></i>
	</a>
<?php endif;?>

</td>
<td>
<?php echo J2StorePopup::popup(
		"index.php?option=com_j2store&view=products&task=setvariant&variant_id=".$this->variant->j2store_variant_id."&layout=variant_form&tmpl=component",
		JText::_( "J2STORE_EDIT" ),
		array('class'=>'btn btn-success')
	);
?>
</td>

<script type="text/javascript">
function listItemTask(id,isDefault){
	//var id =  jQuery("#"+input_id).attr('value');
	var default_variant = {
		option: 'com_j2store',
		view : 'products',
		task : 'setDefaultVariant',
		v_id : id,
		status : isDefault,
		product_id : '<?php echo $this->variant->product_id;?>'
	};
	jQuery.ajax({
			url  : '<?php echo JRoute::_('index.php');?>',
			dataType:'json',
			data : default_variant,
			success:function(json){
				if(json['success']){
					 location.reload();
					/*if(json['status'] == 'unsetDefault'){
						jQuery(this).html('<i class="icon-featured"></i>');
						jQuery(this).attr("onclick", "return listItemTask(+input_id+,'unsetDefault')");
					}else{
						jQuery(this).html('<i class="icon-unfeatured"></i>');
						jQuery(this).attr("onclick" , "return listItemTask(+input_id +,'setDefault')");
					}*/
				}
			}
		});

}

function deleteVariant(variant_id)
(function($){
	var delete_variant = {
		option: 'com_j2store',
		view : 'products',
		task : 'deletevariant',
		variant_id : variant_id
	};
	$.ajax({
		url  : '<?php echo JRoute::_('index.php');?>',
		data : delete_variant
		beforeSend:function(){
			$("#deleteVariant-"+variant_id).attr('value','<?php echo JText::_('J2STORE_DELETING')?>');
		},
		success:function(json){
			if(json){
				$("#deleteVariant-"+variant_id).attr('value','<?php echo JText::_('J2STORE_DELETE')?>');
				$("#product-variant-"+variant_id).remove();
			}
	}
	});
})(j2store.jQuery);

</script>