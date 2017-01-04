<?php
/*
 * --------------------------------------------------------------------------------
   Weblogicx India  - J2 Store v 3.0 - Payment Plugin - SagePay
 * --------------------------------------------------------------------------------
 * @package		Joomla! 2.5x
 * @subpackage	J2 Store
 * @author    	Weblogicx India http://www.weblogicxindia.com
 * @copyright	Copyright (c) 2010 - 2015 Weblogicx India Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link		http://weblogicxindia.com
 * --------------------------------------------------------------------------------
*/

//no direct access
defined('_JEXEC') or die('Restricted access');



?>

<style type="text/css">
    #sagepay_form { width: 100%; }
    #sagepay_form td { padding: 5px; }
    #sagepay_form .field_name { font-weight: bold; }
</style>
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

<form id="j2store_sagepay_form" action="<?php echo JRoute::_( "index.php?option=com_j2store&view=checkout" ); ?>" method="post" name="adminForm" enctype="multipart/form-data">

        <table id="sagepay_form">
            <tr>
                <td class="field_name"><?php echo JText::_( 'J2STORE_CARDHOLDER_NAME' ) ?></td>
                <td><?php echo $vars->cardholder; ?></td>
            </tr>
            <tr>
                <td class="field_name"><?php echo JText::_( 'J2STORE_CREDITCARD_TYPE' ) ?></td>
                <td><?php echo $vars->cardtype; ?></td>
            </tr>
            <tr>
                <td class="field_name"><?php echo JText::_( 'J2STORE_CARD_NUMBER' ) ?></td>
                <td>************<?php echo $vars->cardnum_last4; ?></td>
            </tr>
            <tr>
                <td class="field_name"><?php echo JText::_( 'J2STORE_EXPIRY_DATE' ) ?></td>
                <td><?php echo $vars->cardexp; ?></td>
            </tr>
            <tr>
                <td class="field_name"><?php echo JText::_( 'J2STORE_CARD_CVV' ) ?></td>
                <td>****</td>
            </tr>
        </table>
    <input type='hidden' name='cardholder' value='<?php echo @$vars->cardholder; ?>'>
    <input type='hidden' name='cardtype' value='<?php echo @$vars->cardtype; ?>'>
    <input type='hidden' name='cardnum' value='<?php echo @$vars->cardnum; ?>'>
    <input type='hidden' name='cardexp' value='<?php echo @$vars->cardexp; ?>'>
    <input type='hidden' name='cardcvv' value='<?php echo @$vars->cardcvv; ?>'>
    <input type="button" onclick="j2storeSagepayDirectSubmit(this)" class="button btn btn-primary" value="<?php echo JText::_($vars->button_text); ?>" />

    <input type='hidden' name='order_id' value='<?php echo @$vars->order_id; ?>'>
    <input type='hidden' name='orderpayment_id' value='<?php echo @$vars->orderpayment_id; ?>'>
    <input type='hidden' name='orderpayment_type' value='<?php echo @$vars->orderpayment_type; ?>'>
    <input type='hidden' name='option' value='com_j2store' />
    <input type='hidden' name='view' value='checkout' />
    <input type='hidden' name='task' value='confirmPayment' />
    <input type='hidden' name='paction' value='process' />

    <?php echo JHTML::_( 'form.token' ); ?>

    <div class="plugin_error_div">
		<span class="plugin_error"></span>
		<span class="plugin_error_instruction"></span>
	</div>
	<br />
</form>


<script type="text/javascript">
if(typeof(j2store) == 'undefined') {
	var j2store = {};
}
if(typeof(j2store.jQuery) == 'undefined') {
	j2store.jQuery = jQuery.noConflict();
}

if(typeof(j2storeURL) == 'undefined') {
	var j2storeURL = '';
}

function j2storeSagepayDirectSubmit(button) {

	(function($) {
		$(button).attr('disabled', 'disabled');
		$(button).val('<?php echo JText::_('J2STORE_PAYMENT_PROCESSING_PLEASE_WAIT')?>');
		var form = $('#j2store_sagepay_form');
	    var values = form.serializeArray();

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
			if(json == null) {
				$(button).val('<?php echo JText::_('J2STORE_PAYMENT_ERROR_PROCESSING')?>');
				form.find('.plugin_error_instruction').after('<br /><span class="j2error"><?php echo JText::_('J2STORE_PAYMENT_ON_ERROR_INSTRUCTIONS'); ?></span>');
			}

			if (json['error']) {
				form.find('.plugin_error').after('<span class="j2error">' + json['error']+ '</span>');
				form.find('.plugin_error_instruction').after('<br /><span class="j2error"><?php echo JText::_('J2STORE_PAYMENT_ON_ERROR_INSTRUCTIONS'); ?></span>');
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

	})(j2store.jQuery);
}

</script>