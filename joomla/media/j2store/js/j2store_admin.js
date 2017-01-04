if(typeof(j2store) == 'undefined') {
	var j2store = {};
}
if(typeof(j2store.jQuery) == 'undefined') {
	j2store.jQuery = jQuery.noConflict();
}

if(typeof(J2Store) == 'undefined') {
	J2Store = jQuery.noConflict();
}

if(typeof(j2storeURL) == 'undefined') {
	var j2storeURL = '';
}

function removePAOption(pao_id) {
	(function($) {
	$.ajax({
			type : 'post',
			url :  j2storeURL+'index.php?option=com_j2store&view=products&task=removeProductOption',
			data : 'pao_id=' + pao_id,
			dataType : 'json',
			success : function(data) {
				if(data.success) {
					$('#pao_current_option_'+pao_id).remove();
				}
			 }
		});
	})(j2store.jQuery);	
}

(function($) {
	// Ajax add to cart
	$( document ).on( 'click', '.j2store-cart-button', function(e) {
		e.preventDefault();		
		
		var $thisbutton = $('.j2store-cart-button');
		var form = $('.j2store-addtocart-form');
		form.find('input[name=\'ajax\']').val(1);
		//var post_data1 = [];
		//j2store-addtocart-form
		//j2store-product-form
		var post_data1 = $('.j2store-addtocart-form').serializeArray();
		console.log(post_data1);
		//var answers = [];
		
		var $user_id = $('#user_id').val();
		var $oid = $('#oid').val();
		var $product_id = $('#product_id').val();
		form.find('input[type=\'submit\']').val(form.find('input[type=\'submit\']').data('cart-action-always'));
		var data1 = {
				option: 'com_j2store',
				view: 'carts',
				task: 'addOrderitems',
				ajax: '1',
				user_id: $user_id,
				oid: $oid,
				product_id: $product_id
			};		
		
		$.each( post_data1, function( key, value ) {			
			 if (!(value['name'] in data1) ){
				 if(value['value']){
					 data1[value['name']] = value['value'];	 
				 }
				 
			}			
		});
		

		//window.parent.SqueezeBox.onClose = function(){ console.log('ssss');};
		//console.log(post_data1);
		$.ajax({
			type : 'post',
			url :  j2storeURL+'administrator/index.php',
			data : data1,		
			dataType : 'json',	
			success : function(json) {
				$('.j2success, .j2warning, .j2attention, .j2information, .j2error').remove();
				form.find('input[type=\'submit\']').val(form.find('input[type=\'submit\']').data('cart-action-done'));
				if(json['success']){						
					$('.j2store-product').before('<div class="alert alert-success j2success">'+json["message"]+'</div>')				
				}
				if (json['error']) {
					
					if (json['error']['option']) {
						for (i in json['error']['option']) {
							form.find('#option-' + i).after('<span class="j2error">' + json['error']['option'][i] + '</span>');
						}
					}
				}				
			},
			
		 error: function(xhr, ajaxOptions, thrownError) {
             //alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
         }
		});
	});
	})(j2store.jQuery);

function doAjaxPrice(product_id, id) {
	(function($) {
		/* Get input values from form */
		var form = $(id).closest('form');		
		//sanity check
		if(form.data('product_id') != product_id) return;
		console.log(j2storeURL);
		var values = form.serializeArray();
		//pop these params from values-> task : add & view : mycart 			
		values.pop({
			name : "task",
			value : 'addOrderitems'
		});

		values.pop({
			name : "view",
			value : 'carts'
		});
		
		values.push({
			name : "product_id",
			value :product_id
		});	

		var arrayClean = function(thisArray) {
		    "use strict";
		    $.each(thisArray, function(index, item) {
		        if (item.name == 'task' || item.name == 'view') {
		            delete values[index];      
		        }
		    });
		}
		arrayClean(values);
		
		//variable check
		if(form.data('product_type') == 'variable' || form.data('product_type') == 'advancedvariable') {
			var csv = [];
			if(form.data('product_type') == 'advancedvariable') {
				form.find('input[type=\'radio\']:checked, select').each( function( index, el ) {	
					if(el.value){					
						if($(el).data('is-variant')){						
							 csv.push(el.value);						 
						}
					}
				});				
			}else {
				form.find('input[type=\'radio\']:checked, select').each( function( index, el ) {
					csv.push(el.value);	
				});
			}
			var processed_csv =[];
			processed_csv = csv.sort(function(a, b){return a-b});
			
			var $selected_variant = processed_csv.join();
			//get all variants
			var $variants = form.data('product_variants');			
			var $variant_id = get_matching_variant($variants, $selected_variant);
			form.find('input[name=\'variant_id\']').val($variant_id);
			
			values.push({
				name : "variant_id",
				value :$variant_id
			});	
		}
		
		$.ajax({
			url : j2storeURL+'administrator/index.php?option=com_j2store&view=products&task=update&product_id='+product_id,
			type : 'post',
			data : values,
			dataType : 'json',
			success : function(response) {
				console.log(response);
				var $product = $('.product-'+ product_id);

				if ($product.length
						&& typeof response.error == 'undefined') {
					//SKU
					if (response.sku) {
						$product.find('.sku').html(response.sku);
					}
					//base price
					if (response.pricing.base_price) {
						$product.find('.base-price').html(response.pricing.base_price);						
					}
					//price
					if (response.pricing.price) {
						$product.find('.sale-price').html(response.pricing.price);
					}
					//afterDisplayPrice
					if (response.afterDisplayPrice) {
						$product.find('.afterDisplayPrice').html(response.afterDisplayPrice);
					}
					//qty
					if (response.quantity) {
						$product.find('input[name="product_qty"]').val(response.quantity);						
					}
					//stock status
											
					if (typeof response.stock_status != 'undefined') {
						if (response.availability == 1) {
							$product.find('.product-stock-container').html('<span class="instock">' + response.stock_status + '</span>');
						}else {
							$product.find('.product-stock-container').html('<span class="outofstock">' + response.stock_status + '</span>');
						}	
					}
					
					//dimensions
					if (response.dimensions) {
						$product.find('.product-dimensions').html(response.dimensions);						
					}
					
					//weight
					if (response.weight) {
						$product.find('.product-weight').html(response.weight);						
					}

				}
			},
			error : function(xhr, ajaxOptions, thrownError) {
				console.log(thrownError + "\r\n" + xhr.statusText + "\r\n"
						+ xhr.responseText);
			}
		});
	})(j2store.jQuery);
}

function get_matching_variant(variants, selected) {
	for(var i in variants) {		
		if(variants[i] == selected) return i;
	}
}

function doAjaxFilter(pov_id, product_id, po_id, id) {
	(function($) {

		if (pov_id == '' || $('#ChildOptions' + po_id).length != 0) {
			$('#ChildOptions' + po_id).html('');
		}
		
		var form = $(id).closest('form');
		//sanity check
		if(form.data('product_id') != product_id) return;		
		
		var values = form.serializeArray();
		// pop these params from values-> task : add & view : mycart
		values.pop({
			name : "task",
			value : 'addOrderitems'
		});

		values.pop({
			name : "view",
			value : 'carts'
		});
		
		values.push({
			name : "product_id",
			value :product_id
		});	
		
		var arrayClean = function(thisArray) {
		    "use strict";
		    $.each(thisArray, function(index, item) {
		        if (item.name == 'task' || item.name == 'view') {
		            delete values[index];      
		        }
		    });
		}
		arrayClean(values);
		
		//variable check
		if(form.data('product_type') == 'advancedvariable') {
				
				var csv = [];
			form.find('input[type=\'radio\']:checked, select').each( function( index, el ) {	
				if(el.value){					
					if($(el).data('is-variant')){						
						 csv.push(el.value);						 
					}
				}
			});
						
			//need to sort the csv array to make sure correct array orde passing			
			
			var processed_csv =[];
			processed_csv = csv.sort(function(a, b){return a-b});	
			
			var $selected_variant = processed_csv.join();
			
			//get all variants
			//var $variants = form.data('product_variants');		
			
			
			var $variants = form.data('product_variants');
			
			
			var $variant_id = get_matching_variant($variants, $selected_variant);			
			
			form.find('input[name=\'variant_id\']').val($variant_id);		
		
			
				values.push({
					name : "variant_id",
					value :$variant_id
				});		
		}
		
		values = jQuery.param(values);
		$.ajax({
					url : j2storeURL+'administrator/index.php?option=com_j2store&view=products&task=update&po_id='
							+ po_id
							+ '&pov_id='
							+ pov_id
							+ '&product_id='
							+ product_id,
					type : 'get',
					cache : false,
					data : values,
					dataType : 'json',
					beforeSend: function() {
						$('#option-' + po_id).append('<span class="wait">&nbsp;<img src="'+j2storeURL+'/media/j2store/images/loader.gif" alt="" /></span>');
					},
					complete: function() {
						$('.wait').remove();
					},
					success : function(response) {
						console.log(response);
						var $product = $('.product-'+ product_id);
						
						if ($product.length
								&& typeof response.error == 'undefined') {

							//SKU
							if (response.sku) {
								$product.find('.sku').html(response.sku);
							}
							//base price
							if (response.pricing.base_price) {
								$product.find('.base-price').html(response.pricing.base_price);						
							}
							//price
							if (response.pricing.price) {
								$product.find('.sale-price').html(response.pricing.price);
							}
							
							//afterDisplayPrice
							if (response.afterDisplayPrice) {
								$product.find('.afterDisplayPrice').html(response.afterDisplayPrice);
							}
							
							//qty
							if (response.quantity) {
								$product.find('input[name="product_qty"]').val(response.quantity);						
							}
							
							//dimensions
							if (response.dimensions) {
								$product.find('.product-dimensions').html(response.dimensions);						
							}
							
							//weight
							if (response.weight) {
								$product.find('.product-weight').html(response.weight);						
							}
							
							//stock status
							
							if (typeof response.stock_status != 'undefined') {
								if (response.availability == 1) {
									$product.find('.product-stock-container').html('<span class="instock">' + response.stock_status + '</span>');
								}else {
									$product.find('.product-stock-container').html('<span class="outofstock">' + response.stock_status + '</span>');
								}	
							}
							
							// option html
							if (response.optionhtml) {
								$product.find(' #ChildOptions' + po_id).html(response.optionhtml);								
							}
						}

					},
					error : function(xhr, ajaxOptions, thrownError) {
						console.log(thrownError + "\r\n" + xhr.statusText+ "\r\n" + xhr.responseText);
					}
				});
	})(j2store.jQuery);
}