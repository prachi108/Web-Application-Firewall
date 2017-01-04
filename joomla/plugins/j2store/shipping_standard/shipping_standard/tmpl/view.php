<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
?>

<?php $form = $this->form2; ?>
<?php $row = $this->item;
JFilterOutput::objectHTMLSafe( $row );
?>
<div class="j2store">
<form action="<?php echo JRoute::_( $form['action'] ); ?>" method="post" class="adminForm" id="adminForm"  name="adminForm" enctype="multipart/form-data">
<div class="row-fluid">
	<div class="span8">
	<legend><?php echo JText::_('J2STORE_ADD_STANDARD_SHIPPING_METHOD'); ?></legend>
		<table class="admintable table table-striped">
			<tr>
				<td width="100" align="right" class="key">
					<label for="shipping_method_name">
					<?php echo JText::_('J2STORE_STANDARD_SHIPPING_NAME'); ?>:
					</label>
				</td>
				<td>
					<input type="text" name="shipping_method_name" id="shipping_method_name" value="<?php echo $row->shipping_method_name; ?>" size="48" maxlength="250" />
				</td>
			</tr>
	        <tr>
	            <td width="100" align="right" class="key">
	                <label for="tax_class_id">
	                <?php echo JText::_('J2STORE_TAX_CLASS'); ?>:
	                </label>
	            </td>
	            <td>
	                <?php echo $this->data['taxclass']; ?>
	            </td>
	        </tr>
			<tr>
				<td width="100" align="right" class="key">
					<label for="shipping_method_enabled">
					<?php echo JText::_('J2STORE_ENABLED'); ?>:
					</label>
				</td>
				<td>
					 <?php echo $this->data['published']; ?>
				</td>
			</tr>
	        <tr>
	            <td width="100" align="right" class="key">
	                <label for="shipping_method_type">
	                <?php echo JText::_('J2STORE_STANDARD_SHIPPING_TYPE'); ?>:
	                </label>
	            </td>
	            <td>
	                <?php echo $this->data['shippingtype']; ?>
	            </td>
	        </tr>

	          <tr>
	            <td width="100" align="right" class="key">
	                <label for="address_override">
	                <?php echo JText::_('J2STORE_STANDARD_SHIPPING_ADDRESS_OVERRIDE'); ?>:
	                </label>
	            </td>
	            <td>
	                <?php echo $this->data['address_override']; ?>
	                <br />
	                <p class="text-info"><?php echo JText::_('J2STORE_STANDARD_SHIPPING_ADDRESS_OVERRIDE_HELP_TEXT'); ?></p>
	            </td>
	        </tr>
	        <tr>
	            <td width="100" align="right" class="key">
	                <label for="subtotal_minimum">
	                	<?php echo JText::_('J2STORE_SHIPPING_METHODS_MINIMUM_SUBTOTAL_REQUIRED'); ?>:
	                </label>
	            </td>
	            <td>
	                <input type="text" name="subtotal_minimum" id="subtotal_minimum" value="<?php echo $row->subtotal_minimum; ?>" size="10" />
	            </td>
	        </tr>
	        <tr>
	            <td width="100" align="right" class="key">
	                <label for="subtotal_maximum">
	             	   <?php echo JText::_('J2STORE_SHIPPING_METHODS_SUBTOTAL_MAX'); ?>:
	                </label>
	            </td>
	            <td>
	                <input type="text" name="subtotal_maximum" id="subtotal_maximum" value="<?php echo $row->subtotal_maximum; ?>" size="10" />
	            </td>
	        </tr>
		</table>
	</div>
		<div class="span4">
		    <div class="alert alert-block alert-info">
		        <stong>
		        <?php echo JText::_('J2STORE_SHIPPING_TYPE_HELP_TEXT'); ?>:
		        </stong>
		        <ul>
		            <li><?php echo JText::_('J2STORE_FLAT_RATE_PER_ITEM_HELP_TEXT'); ?></li>
		            <li><?php echo JText::_('J2STORE_WEIGHT_BASED_PER_ITEM_HELP_TEXT'); ?></li>
		            <li><?php echo JText::_('J2STORE_WEIGHT_BASED_PER_ORDER_HELP_TEXT'); ?></li>
		            <li><?php echo JText::_('J2STORE_PRICE_BASED_PER_ITEM_HELP_TEXT'); ?></li>
		            <li><?php echo JText::_('J2STORE_QUANTITY_BASED_PER_ORDER_HELP_TEXT'); ?></li>
		            <li><?php echo JText::_('J2STORE_PRICE_BASED_PER_ORDER_HELP_TEXT'); ?></li>
		        </ul>
		    </div>
		</div>
		 	<input type="hidden" name="j2store_shippingmethod_id" value="<?php echo $row->j2store_shippingmethod_id; ?>" />
	  	    <input type="hidden" id="shippingTask" name="shippingTask" value="" />
			<input type="hidden" name="option" value="com_j2store" />
			<input type="hidden" name="view" value="shippings" />
			<input type="hidden" id="task" name="task" value="view" />
		</div>
	</form>
</div>
<script type="text/javascript">
Joomla.submitbutton =function (task){
	(function($){
		$("#shippingTask").attr('value',task);
		submitform('view');
	})(j2store.jQuery);
}
</script>