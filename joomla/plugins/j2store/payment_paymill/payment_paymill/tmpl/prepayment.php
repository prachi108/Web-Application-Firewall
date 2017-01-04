<?php
/**
 * --------------------------------------------------------------------------------
 * Payment Plugin - Paymill
 * --------------------------------------------------------------------------------
 * @package     Joomla 2.5 -  3.x
 * @subpackage  J2 Store
 * @author      J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2014-19 J2Store . All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://j2store.org
 * --------------------------------------------------------------------------------
 * */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
$jsonarr = json_encode($this->code_arr);
?>
 <div class="note">
 
  		<?php 
         	$image = $this->params->get('display_image', '');         	 
         ?>
         <?php if(!empty($image)): ?>
         	<span class="j2store-payment-image">
				<img class="payment-plugin-image payment_cash" src="<?php echo JUri::root().JPath::clean($image); ?>" />
			</span>
		<?php endif; ?>
		
       <?php echo JText::_($vars->display_name); ?>
		<br />
		<?php echo JText::_($vars->onbeforepayment_text); ?>
    </div>


<form id="paymill-form" action="<?php echo JRoute::_("index.php?option=com_j2store&view=checkout"); ?>" method="post" name="adminForm" enctype="multipart/form-data">

	<div class="note">

        <table id="pay_form">
			<tr>
				<td class="field_name"><?php echo JText::_('J2STORE_CARDHOLDER_NAME') ?></td>
				<td><?php echo $vars->cardholder; ?></td>
			</tr>
            <?php

if ($vars->payment_mode == 'cc') : ?>
            <tr>
				<td class="field_name"><?php echo JText::_('J2STORE_CARD_NUMBER') ?></td>
				<td>************<?php echo $vars->cardnum_last4; ?></td>
			</tr>
			<tr>
				<td class="field_name"><?php echo JText::_('J2STORE_EXPIRY_DATE') ?></td>
				<td><?php echo $vars->cardmonth; ?>/<?php echo $vars->cardyear; ?></td>
			</tr>
			<tr>
				<td class="field_name"><?php echo JText::_('J2STORE_CARD_CVV') ?></td>
				<td>****</td>
			</tr>
	<?php else: ?>

            <tr>
				<td class="field_name"><?php echo JText::_('J2STORE_ACCOUNT_NUMBER') ?></td>
				<td>************<?php echo $vars->accnum_last4; ?></td>
			</tr>
			<tr>
				<td class="field_name"><?php echo JText::_('J2STORE_BANK_CODE') ?></td>
				<td><?php echo $vars->banknum; ?></td>
			</tr>
			<tr>
				<td class="field_name"><?php echo JText::_('J2STORE_COUNTRY') ?></td>
				<td><?php echo $vars->country; ?></td>
			</tr>
	<?php endif; ?>
        </table>
	</div>

	<input type="button" onclick="j2storePayMillSubmit(this)" id="paymill-submit-button" class="button btn btn-primary" value="<?php echo JText::_($vars->button_text); ?>" />

    <input type='hidden' name='cardholder' value='<?php echo @$vars->cardholder; ?>' />
    <input type='hidden' name='payment_mode' value='<?php echo @$vars->payment_mode; ?>' />
		<input type='hidden' name='cardnum' value='<?php echo @$vars->cardnum; ?>' />
		<input type='hidden' name='cardmonth' value='<?php echo @$vars->cardmonth; ?>' />
		<input type='hidden' name='cardyear' value='<?php echo @$vars->cardyear; ?>' />
		<input type='hidden' name='cardcvv' value='<?php echo @$vars->cardcvv; ?>' />
		<input type='hidden' name='accnum' value='<?php echo @$vars->accnum; ?>' />
		<input type='hidden' name='banknum' value='<?php echo @$vars->banknum; ?>' />
		<input type='hidden' name='country' value='<?php echo @$vars->country; ?>' />
		<input type='hidden' id="paymill-token" name='token' value=''>
		<input type='hidden' name='order_id' value='<?php echo @$vars->order_id; ?>' />
		<input type='hidden' name='orderpayment_id' value='<?php echo @$vars->orderpayment_id; ?>' />
		<input type='hidden' name='orderpayment_type' value='<?php echo @$vars->orderpayment_type; ?>' />
		<input type='hidden' name='option' value='com_j2store' />
		<input type='hidden' name='view' value='checkout' />
		<input type='hidden' name='task' value='confirmPayment' />
		<input type='hidden' name='paction' value='process' />
    <?php echo JHTML::_('form.token'); ?>
</form>
<br />
<div class="paymill-payment-errors"></div>
<br />
<div class="plugin_error_div">
		<span class="plugin_error"></span>
		<span class="plugin_error_instruction"></span>
	</div>

<script type="text/javascript" src="https://bridge.paymill.com/"></script>
<script type="text/javascript">
var PAYMILL_PUBLIC_KEY = '<?php echo $vars->public_key; ?>';

if(typeof(j2store) == 'undefined') {
	var j2store = {};
}
if(typeof(j2store.jQuery) == 'undefined') {
	j2store.jQuery = jQuery.noConflict();
}

function j2storePayMillSubmit(button) {

	(function($) {
		$(button).attr('disabled', 'disabled');
		$(button).val('<?php echo JText::_('J2STORE_PAYMENT_PROCESSING_PLEASE_WAIT')?>');

		//first create the token
		var result = doPaymillToken();

	//	if(result) {

	//	} else {
	//		$(button).val('<?php echo JText::_('J2STORE_PAYMENT_ERROR_PROCESSING')?>');
	//	}

        })(j2store.jQuery);
}

function doPaymillToken() {
	(function($) {
		<?php if($vars->payment_mode == 'cc'): ?>
		try {
			paymill.createToken({
				number:     '<?php echo $vars->cardnum; ?>',
				exp_month:  '<?php echo $vars->cardmonth; ?>',
				exp_year:   '<?php echo $vars->cardyear; ?>',
				cvc:        '<?php echo $vars->cardcvv; ?>',
				cardholder: '<?php echo $vars->cardholder; ?>',
				amount_int: '<?php echo $vars->amount; ?>',
				currency: '<?php echo $vars->currency_code; ?>',

			}, PaymillResponseHandler);
		} catch(e) {
			 $(".paymill-payment-errors").text(e);
			 logResponse(e.message);
		}

	<?php else: ?>
		try {
			paymill.createToken({
				number: '<?php echo $vars->accnum; ?>',
				bank:  '<?php echo $vars->banknum; ?>',
				accountholder: '<?php echo $vars->cardholder; ?>'
			}, PaymillResponseHandler);
		} catch(e) {
			$(".paymill-payment-errors").text(e);
			logResponse(e.message);
		}

	<?php endif ; ?>
	return false;

	})(j2store.jQuery);
}

        function PaymillResponseHandler(error, result) {
			//console.log(error);
			error ? logResponse(error.apierror) : logResponse(result.token);
			if (error) {
				var jason_error = '[<?php echo $jsonarr; ?>]';
				var slab = j2store.jQuery.parseJSON(jason_error);
				//console.log(slab);
				j2store.jQuery.each(slab[0], function(index, element) {
					if(index == error.apierror){
						var version = '<?php echo JVERSION;?>';
						//alert(version);
						if(version >= "3.0")
						{
							j2store.jQuery(".paymill-payment-errors").addClass('alert alert-error');
						}
						else
						{
							j2store.jQuery(".paymill-payment-errors").addClass('error');
						}
						j2store.jQuery(".paymill-payment-errors").text(element);
					}
				});

			}
			else
			{
				j2store.jQuery('#paymill-form #paymill-token').val(result.token);
				doSendRequest();

			}

        }

        function doSendRequest() {

        	(function($) {

	        	var button = j2store.jQuery('#paymill-submit-button');

	        	//token created. But check again
				var token = j2store.jQuery('#paymill-form #paymill-token').val();
				console.log(token);
				if(token.length == 0) {
					//token is empty
					$(button).val('<?php echo JText::_('J2STORE_PAYMENT_ERROR_PROCESSING')?>');
				} else {
					//get all form values
					var form = $('#paymill-form');
					var values = form.serializeArray();

					//submit the form using ajax
					var jqXHR =	$.ajax({
						url: 'index.php',
						type: 'post',
						data: values,
						dataType: 'json',
						beforeSend: function() {
							$(button).after('<span class="wait">&nbsp;<img src="/media/j2store/images/loader.gif" alt="" /></span>');
						}
					});

					jqXHR.done(function(json) {
						form.find('.j2success, .j2warning, .j2attention, .j2information, .j2error').remove();
						console.log(json);
						if (json['error']) {
							form.find('.plugin_error').after('<span class="j2error">' + json['error']+ '</span>');
							form.find('.plugin_error_instruction').after('<br /><span class="j2error"><?php echo JText::_('J2STORE_PAYMILL_ON_ERROR_INSTRUCTIONS'); ?></span>');
							$(button).val('<?php echo JText::_('J2STORE_PAYMENT_ERROR_PROCESSING')?>');
						}

						if (json['redirect']) {
							$(button).val('<?php echo JText::_('J2STORE_PAYMENT_COMPLETED_PROCESSING')?>');
							window.location.href = json['redirect'];
						}

					});

					jqXHR.fail(function() {
						$(button).val('<?php echo JText::_('J2STORE_PAYMENT_ERROR_PROCESSING')?>');
					})

					jqXHR.always(function() {
						$('.wait').remove();
					 });
				}
        	})(j2store.jQuery);
        }

        function logResponse(res)
        {
            // create console.log to avoid errors in old IE browsers
            if (!window.console) console = {log:function(){}};
            console.log(res);
            <?php if($vars->sandbox) : ?>
            j2store.jQuery('.debug').text(res).show().fadeOut(3000);
            <?php endif; ?>
        }
    </script>

