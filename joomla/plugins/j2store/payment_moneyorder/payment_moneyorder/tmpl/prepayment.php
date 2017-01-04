<?php
/*------------------------------------------------------------------------
# com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/


defined('_JEXEC') or die('Restricted access'); ?>

<form action="<?php echo JRoute::_( "index.php?option=com_j2store&view=checkout" ); ?>" method="post" name="adminForm" enctype="multipart/form-data">

	<p><?php echo JText::_($vars->moneyorder_information); ?></p>
	<br />
    <div class="note">
         <?php echo JText::_($vars->onbeforepayment_text); ?>
         <?php 
         	$image = $this->params->get('display_image', '');         	 
         ?>
         <?php if(!empty($image)): ?>
         	<span class="j2store-payment-image">
				<img class="payment-plugin-image payment_cash" src="<?php echo JUri::root().JPath::clean($image); ?>" />
			</span>
		<?php endif; ?>
        <p>
             <strong><?php echo JText::_($vars->display_name);?></strong>
        </p>
    </div>

    <input type="submit" class="j2store_cart_button btn btn-primary" value="<?php echo JText::_($vars->button_text); ?>" />    
    <input type='hidden' name='order_id' value='<?php echo @$vars->order_id; ?>'>    
    <input type='hidden' name='orderpayment_type' value='<?php echo @$vars->orderpayment_type; ?>'>
    <input type='hidden' name='task' value='confirmPayment'>
</form>