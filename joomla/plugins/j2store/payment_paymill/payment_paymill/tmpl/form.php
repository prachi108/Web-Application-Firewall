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
defined('_JEXEC') or die('Restricted access');
?>

<div class="note">
	<p><?php echo JText::_($vars->onselection_text); ?></p>
</div>
            <div class="j2store">
						<div id="field">
							<div class="control-group">
									<label class="control-label"><?php echo JText::_('J2STORE_CARDHOLDER_NAME');?></label>
									<div class="controls"><input class="paymill-card-holdername" name="cardholder" type="text" size="20"
									value="" />
									</div>
	                        </div>
	                        <div class="control-group">
								<label class="control-label"><?php echo JText::_('J2STORE_PAYMENT_TYPE');?></label>
									<div class="controls">
										<select id="payment_type" name="paymill_payment_mode" onchange="ChangeDropdowns(this.value);">
											<option value="cc" selected="true"><?php echo JText::_('J2STORE_CREDITCARD');?></option>
											<option value="dc"><?php echo JText::_('J2STORE_DEBIT_CARD');?></option>
									</select>
							</div>
						</div>

                        <div id="cc">
							<div class="control-group">
									<label class="control-label"><?php echo JText::_('J2STORE_CARD_NUMBER');?></label>
									<div class="controls"><input class="paymill-card-number" name="cardnum" type="text" maxlength="16" value="" />
									</div>
							</div>


							<div class="control-group">
									<label class="control-label"><?php echo JText::_('J2STORE_EXPIRY_DATE');?></label>
								   <div class="controls">
								     <select name="month" class="required number" title="<?php echo JText::_('J2STORE_EXPIRY_VALIDATION_ERROR_MONTH'); ?>">
        							<option value=""><?php echo JText::_('J2STORE_EXPIRY_MONTH'); ?></option>
							        	<option value="01">01</option>
							        	<option value="02">02</option>
							        	<option value="03">03</option>
							        	<option value="04">04</option>
							        	<option value="05">05</option>
							        	<option value="06">06</option>
							        	<option value="07">07</option>
							        	<option value="08">08</option>
							        	<option value="09">09</option>
							        	<option value="10">10</option>
							        	<option value="11">11</option>
							        	<option value="12">12</option>
        							</select>
								   /

								<select name="year" class="required number"
						        title="<?php echo JText::_('J2STORE_EXPIRY_VALIDATION_ERROR_YEAR'); ?>"
						        >
						        	<option value=""><?php echo JText::_('J2STORE_EXPIRY_YEAR'); ?></option>
						        	<?php
						        	$two_digit_year = date('y');
						        	$four_digit_year = date('Y');
						        	$max = $four_digit_year+50;
						        	?>
						        	<?php for($i=$four_digit_year;$i<$max;$i++) {?>
						        		<option value="<?php echo $i;?>"><?php echo $four_digit_year;?></option>
						        	<?php
						        	$four_digit_year++;
						        	} ?>
						        </select>
							</div>
							</div>

							<div class="control-group">
									<label class="control-label"><?php echo JText::_('J2STORE_CARD_CVV');?></label>
									<div class="controls"><input class="paymill-card-cvv" name="cardcvv" type="text" maxlength="5" value="" />
									</div>
							</div>

                        </div>

                        <div id="bank" style="display:none;">

									 <div class="control-group">
											<label class="control-label"><?php echo JText::_('J2STORE_ACCOUNT_NUMBER');?></label>
											<div class="controls"> <input class="paymill-debit-number" name="accnum" maxlength="10" type="text" size="20" value="" /></div>
									</div>
									 <div class="control-group">
											<label class="control-label"><?php echo JText::_('J2STORE_BANK_CODE');?></label>
											<div class="controls">  <input class="paymill-debit-bank" name="banknum" maxlength="8" type="text" size="20" value="" /></div>
									</div>

									<!--
									<div class="control-group">
												<label class="control-label"><?php echo JText::_('J2STORE_COUNTRY');?></label>
												<div class="controls"><input class="paymill-debit-country" name="country" type="text" size="20" value="" /></div>
									</div>
									 -->
                        </div>
                     </div>

                </div>
<div class="paymill-payment-errors"></div>

         <script type="text/javascript" src="https://bridge.paymill.com/"></script>
        <script type="text/javascript">
        var PAYMILL_PUBLIC_KEY = '<?php echo $vars->public_key; ?>';
        if(typeof(j2store) == 'undefined') {
        	var j2store = {};
        }
        if(typeof(j2store.jQuery) == 'undefined') {
        	j2store.jQuery = jQuery.noConflict();
        }

        j2store.jQuery(".paymill-debit-bank").bind("paste cut keydown",function(e) {
			var that = this;
			setTimeout(function() {
					paymill.getBankName(j2store.jQuery(that).val(), function(error, result) {
					error ? logResponse(error.apierror) : j2store.jQuery(".paymill-debit-bankname").val(result);
						});
					}, 200);
			});

		function ChangeDropdowns(value)
		{
		   if(value=="cc")
		   {
			   j2store.jQuery("#cc").css("display", "block");
			   j2store.jQuery("#bank").css("display", "none");
		   }else if(value=="dc")
		   {
			   j2store.jQuery("#cc").css("display", "none");
			   j2store.jQuery("#bank").css("display", "block");
		   }
		}
        </script>
