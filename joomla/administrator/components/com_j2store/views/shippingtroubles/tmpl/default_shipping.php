<?php
/**
 * -------------------------------------------------------------------------------
 * @package 	J2Store
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license 	GNU GPL v3 or later
 * @link        http://j2store.org
 * --------------------------------------------------------------------------------
 *
 * */
defined('_JEXEC') or die;
JHTML::_('behavior.modal');
$sidebar = JHtmlSidebar::render();
$this->params = J2Store::config();
?>

<?php if(!empty( $sidebar )): ?>
   <div id="j-sidebar-container" class="span2">
      <?php echo $sidebar ; ?>
   </div>
   <div id="j-main-container" class="span9">
 <?php else : ?>
	<div class="j2store">
  <?php endif;?>
	<h2> 
		<?php echo JText::_("J2STORE_SHIPPING_METHOD_VALIDATE");?>
	</h2>
	<?php if($this->shipping_avaliable):?>
	<div class="span12">
		<div class="tabbable tabs-left">
                <ul class="nav nav-tabs">
					<?php if(isset($this->shipping_messages) && !empty($this->shipping_messages)):?>
					<?php $count = 0;?>
					<?php foreach ($this->shipping_messages as $shipping_message):?>
					
					<?php foreach ($shipping_message as $key=>$value):?>
					<?php if(empty($value)):?>
                  <li class="<?php echo $count == 0 ? "active" :"";?>">
                  	<a href="#<?php echo str_replace(" ","_",trim($key));?>" data-toggle="tab">                 		 <?php echo JText::_($key); ?>
                  	</a>
                  </li>
                  <?php endif;?>
                  <?php $count++;?>
                  <?php endforeach;?>
                  <?php endforeach;?>
                  <?php endif;?>
                 </ul>
                 <div class="tab-content">
					 <?php if(isset($this->shipping_messages) && !empty($this->shipping_messages)):?>
					 <?php $count_tab = 0;?>
					<?php foreach ($this->shipping_messages as $shipping_message):?>
					
					
					<?php foreach ($shipping_message as $key=>$value):?>
					
					<?php if(empty($value)):?>
					<div class="tab-pane <?php echo $count_tab==0? "active":"";?>"  id="<?php echo str_replace(" ","_",trim($key));?>">
					  <table class="table table-striped table-bordered">
							<tbody>
					  <?php else:?>
					  
						
							<tr>
								<td><?php echo $key;?></td>
								<td><?php echo $value;?></td>
							</tr>
							
					  <?php endif;?> 
					  <?php $count_tab ++;?>
					   <?php endforeach;?>
					   </tbody>
							</table>
				</div>
                  <?php endforeach;?>
                  <?php endif;?>
						
                 </div>
         </div>
     </div>
     <?php else:?>
		<div class="alert alert-danger"><?php echo JText::sprintf('J2STORE_SHIPPING_TROUBLESHOOT_NOTE_MESSAGE','index.php?option=com_j2store&view=shippings', J2Store::buildHelpLink('support/user-guide/standard-shipping.html', 'shipping'));?></div>
     <?php endif;?>
     <div class="span9 center">
		<a class="fa fa-arrow-right btn btn-large btn-success " href="<?php echo JRoute::_('index.php?option=com_j2store&view=shippingtroubles&layout=default_shipping_product'); ?>">
			<?php echo 'Next';?>
		</a>
	</div>
 </div>        
 <style type="text/css">
	
#j-main-container .nav-tabs > li.active > a,
#j-main-container .nav-tabs > li.active > a:hover,
#j-main-container .nav-tabs > li.active > a:focus {
    border: 0; 
    color:#fff;
	background:url("<?php echo JURI::root();?>media/j2store/images/arrow_white.png") center right no-repeat #999;
}
</style>
