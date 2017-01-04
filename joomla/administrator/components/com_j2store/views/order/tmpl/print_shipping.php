<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
?>
<script type="text/javascript">
           window.print();
    </script>
<div class="row-fluid">
	<div class="span12">
				<div class="shipping-info">
				<h4><?php echo JText::_('J2STORE_SHIPPING_ADDRESS');?> </h4>
					<?php echo '<strong>'.$this->orderinfo->shipping_first_name." ".$this->orderinfo->shipping_last_name."</strong><br/>";
								echo $this->orderinfo->shipping_address_1;
						?>
						<?php echo $this->orderinfo->shipping_address_2 ? "<br/>".$this->orderinfo->shipping_address_2: "<br/>";
						echo $this->orderinfo->shipping_city.'<br/>';
						echo $this->orderinfo->shipping_zone_name ? $this->orderinfo->shipping_zone_name.'<br/>' : "";
						echo $this->orderinfo->shipping_zip."<br/>";
						echo $this->orderinfo->shipping_country_name."<br/>";
						echo JText::_('J2STORE_TELEPHONE') .': ';
						echo $this->orderinfo->shipping_phone_1;
						echo $this->orderinfo->shipping_phone_2 ? "<br/>".$this->orderinfo->shipping_phone_2 : "<br/> ";
						echo '<br/> ';
						echo $this->orderinfo->shipping_company ? JText::_('J2STORE_ADDRESS_COMPANY_NAME').':&nbsp;'.$this->orderinfo->shipping_company."</br>" : "";
						echo $this->orderinfo->shipping_tax_number ? JText::_('J2STORE_ADDRESS_TAX_NUMBER').':&nbsp;'.$this->orderinfo->shipping_tax_number."</br>" : "";
					?>
		</div>
	</div>
</div>