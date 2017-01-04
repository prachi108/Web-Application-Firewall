<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
$global_config = JFactory::getConfig();
//get the config
$limit = $global_config->get('list_limit',20);
?>

<div class="j2store-product-variants">
	<div class="control-group">
		<?php if(isset($this->item->product_options) && !empty($this->item->product_options)):?>
			<?php if(isset($this->item->variants) && count($this->item->variants)):?>
				<div class="well">
					<input type="button" id="j2store-regenerate-variants" data-confirm_type="regenerate" class="btn btn-large btn-success launchConfirm" value="<?php echo JText::_('J2STORE_REGENERATE_VARIANTS');	?>"/>
					<input type="button" id="j2store-delete-variants" data-confirm_type="delete"  class="btn btn-large btn-danger launchConfirm" value="<?php echo JText::_('J2STORE_DELETE_VARIANTS'); ?>" />

				</div>
				<div class="pull-right">
					<a id="openAll-panel" href="javascript:void(0);"  class="btn btn-success"  onclick="setExpandAll();">
						<?php echo JText::_('J2STORE_OPEN_ALL');?>
					</a>
					<a id="closeAll-panel"  href="javascript:void(0);" class="btn btn-inverse"  onclick="setCloseAll();"><?php echo JText::_('J2STORE_CLOSE_ALL');?></a>
				</div>

				<div id="regenerateConfirm" class="modal hide fade j2storeConfirmChange" >
					<div class="modal-header">
						<button class="close" data-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
						<h4 id="myModalLabel" class="modal-title"><?php echo JText::_('J2STORE_WARNING');?></h4>
					</div>
					<div class="modal-body">
						<p><?php echo JText::_('J2STORE_PRODUCT_VARIANT_REGENERATE_HELP');?></p>
		  			</div>
				  <div class="modal-footer">
				    <button type="button" data-dismiss="modal" class="btn btn-warning" data-value="1"><?php echo JText::_('J2STORE_YES');?></button>
				    <button type="button" data-dismiss="modal" class="btn btn-default" data-value="0"><?php echo JText::_('J2STORE_NO');?></button>
				  </div>
				</div>
			<?php else:?>
				<input type="button" id="j2store-generate-variants" onclick="generateVariants(this)" class="btn btn-large btn-success" 	value="<?php echo JText::_('J2STORE_GENERATE_VARIANTS'); ?>" />
			<?php endif;?>
		<?php endif;?>
	</div>

	<!-- Advanced variable starts here  -->
	<div class="bs-example j2store-advancedvariants-settings">
		<div class="panel-group" id="accordion">
			<?php
					/* to get ajax advanced variable list need to
					 *  assign these variables
					 */
					$this->variant_list = $this->item->variants;
					$this->variant_pagination =$this->item->variant_pagination;
					$this->weights = $this->item->weights;
					$this->lengths = $this->item->lengths;

				?>
				<?php  echo $this->loadTemplate('ajax_avoptions');?>
		</div>
	</div>
</div>
<script type="text/javascript">
var currentPage = <?php echo $this->item->variant_pagination->pagesCurrent;?>;
var total_variants =<?php echo $this->item->variant_pagination->total;?>;
var limit  = <?php echo $limit;?>;
var product_id = <?php echo $this->item->j2store_product_id;?>;

(function($) {
   /**  on load will create footer list **/
	$(document).ready(function(){
     	$('#accordion').after('<div id="nav" class="pagination pagination-toolbar"><ul class="pagination-list"></ul></div>');
		var numPages = total_variants / limit;
		// now convert the numPages to int
		numPages = Math.ceil(numPages);
		if(numPages > 1 ){
	       	createFooterList(numPages);
    	    	 $('#nav .pagination-list a').bind('click', function(){
       			$('#nav .pagination-list li').removeClass('active');
           			$(this).parent('li').addClass('active');
         		});
		}
  });

})(j2store.jQuery);


/***
 *  This method will append pagination li to parent Ul
 */
function createFooterList(numPages){
	(function($) {
		 var limitstart = 0;
			 for(i = 0;i < numPages;i++) {
         		var pageNum = i + 1;
         		limitstart = i * limit;
         		$('#nav .pagination-list').append('<li><a data-get_limitstart="'+limitstart +'" data-get_page="'+i+'"  onclick="getVariantList(this);" href="javascript:void(0);" rel="'+i+'">'+pageNum+'</a></li> ');
     		}
	  		$('#nav .pagination-list li:first').addClass('active');
	})(j2store.jQuery);
 }

/**
 * Method to run ajax request to get the list of variants based on the page requested
 */
function getVariantList(element){
	(function($) {
		//var limit = $('#variant-limit').val();
		var getPage = $(element).data('get_page');
		var limitstart = $(element).data('get_limitstart');
		var ajOptions = {
				type : 'post',
				url :  'index.php',
				cache: false,
				dataType : 'json',
				data:{
						'option':'com_j2store',
						'view' :'products',
						'task' :'getVariantListAjax',
						'limitstart':limitstart,
						'product_id' : product_id,
						'limit' : limit
				}
			}
		$.ajax(ajOptions)
			.done( function(json) {
				if(json['html']){
					$('#accordion').html(json['html']);
				}
			})
	})(j2store.jQuery);

}


(function($) {

	/* to open confirm pop  used for both regenerate variants and deletevariants
	 * based on the confirm_type will call the regenerate , deletevariants respectively
	 */
	$('.launchConfirm').on('click', function (e) {
		var  confirm_type = jQuery(this).data('confirm_type');
		$('#regenerateConfirm')
	        .modal({ backdrop: 'static', keyboard: false })
	        .one('click', '[data-value]', function (e) {
	            if($(this).data('value')) {
	            	if(confirm_type == 'regenerate'){
	            		regenerateVariants();
	            	}else if(confirm_type == 'delete'){
	            		deleteVariants();
	                }
	            } else {
	                return false;
	            }
	        });
	});
})(j2store.jQuery);


function getOptionValue(element,variant_id){
	(function($) {
		 $(element).closest('.opvalues').remove();
		var productoption_id = $(element).attr('value');
		var ajOptions = {
				type : 'post',
				url :  'index.php',
				cache: false,
				dataType : 'json',
				data:{'option':'com_j2store','view' :'products','task' :'getProductOptionValue','productoption_id' : productoption_id}
			}
		$.ajax(ajOptions)
			.done( function(json) {
					var html ='';
					html += '<option value=""><?php echo JText::_('J2STORE_SELECT_OPTION'); ?></option>';
				 for(var i=0;  i < json['value'].length; i++ ){
				 	html +='<option value="'+json['value'][i]['id'] +'">'+ json['value'][i]['name'] +'</option>';
				 }
				  $(element).closest('.variant-option-values').find('.opvalues').html(html);
			})
	})(j2store.jQuery);
}

var counter ;
function addVariant(){
	(function($) {
		var ajOptions = {
				type : 'post',
				url :  'index.php',
				cache: false,
				dataType : 'json',
				data:{
						'option':'com_j2store',
						'view' :'products',
						'task' :'createVariant',
						'product_id' : <?php echo $this->item->j2store_product_id; ?>}
			}

		$.ajax(ajOptions)
			.done( function(json) {
				if(json['html']){
					var last_element_counter = $('.j2store-panel-default').data('variant_id');
					counter = last_element_counter ++ ;
					//console.log(json['html']);
					var html = $(json['html']).find('.j2store-panel-default').last();
				//	$(html).find('a').attr("href",'collapse-'+counter);
					$('#accordion').append(json['html']);
				}

		});

		//var clone = image_div.clone();
	})(j2store.jQuery);
}
/**
 * Method to Expand All Accordian Panel
 */
function setExpandAll(){
	(function($) {
		$('.panel-collapse:not(".in")').collapse('show');
	})(j2store.jQuery);
}

/**
 * Method to Close All Accordian Panel
 */
function setCloseAll(){
	(function($) {
		$('.panel-collapse.in') .collapse('hide');
	})(j2store.jQuery);
}

/**
 * Method to remove
 */
function removePAOption(pao_id) {
	(function($) {
		var variable_option_remove = {
			option: 'com_j2store',
			view: 'products',
			task: 'removeProductOption',
			product_type: 'variable',
			pao_id: pao_id
		};
		$.ajax({
			type : 'post',
			url  : '<?php echo JRoute::_('index.php');?>',
			data : variable_option_remove,
			dataType : 'json',
			success : function(data) {
				console.log(data);
				if(data.success) {
					$('#pao_current_option_'+pao_id).remove();
				}
				if(data.error){
					$("<p class='alert alert-warning'>"+data.error+"</p>").insertBefore("#attribute_options_table");
				}
			}
		});
	})(j2store.jQuery);
}


function generateVariants(button) {
	(function($) {
		$(button).attr('disabled', true);
		$(button).val('<?php echo JText::_('J2STORE_GENERATING_VARIANTS')?>');
		var generate_variant = {
			option: 'com_j2store',
			view: 'products',
			task: 'generateVariants',
			product_id: '<?php echo $this->item->j2store_product_id; ?>'
		};
		var ajOptions = {
				type : 'post',
				url  : '<?php echo JRoute::_('index.php');?>',
				cache: false,
				dataType : 'json',
				data:generate_variant
			}

		$.ajax(ajOptions)
			.done( function(response) {
				//if(response['redirect'])
					location.reload(true);
					location.reload();
				//}//

		});
	})(j2store.jQuery);
}



function regenerateVariants(){
	(function($) {
		$('#j2store-regenerate-variants').attr('disabled', true);
		$('#j2store-regenerate-variants').attr('value','<?php echo JText::_('J2STORE_REGENERATING_VARIANTS')?>');
		var regenerate_variant = {
			option: 'com_j2store',
			view: 'products',
			task: 'regenerateVariants',
			product_id: '<?php echo $this->item->j2store_product_id; ?>'
		};
		var ajOptions = {
				type : 'post',
				url  : '<?php echo JRoute::_('index.php');?>',
				cache: false,
				dataType : 'json',
				data:regenerate_variant
			}
		$.ajax(ajOptions)
			.success( function(json) {
					location.reload(true);
		});
	})(j2store.jQuery);
}



function deleteVariants(button){
	(function($) {
		$('#j2store-delete-variants').attr('disabled', true);
		$('#j2store-delete-variants').attr('value','<?php echo JText::_('J2STORE_DELETE_VARIANTS_CONTINUE')?>');

		var delete_variant = {
			option: 'com_j2store',
			view: 'products',
			task: 'deleteAllVariants',
			product_id: '<?php echo $this->item->j2store_product_id; ?>'
		};

		var ajOptions = {
				type : 'post',
				url  : '<?php echo JRoute::_('index.php');?>',
				cache: false,
				dataType : 'json',
				data: delete_variant
			}
		$.ajax(ajOptions)
			.done( function(response) {
					location.reload(true);
		});
	})(j2store.jQuery);
}
</script>