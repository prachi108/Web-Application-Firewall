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
   <div id="j-main-container" class="span10">
 <?php else : ?>
	<div class="j2store">
  <?php endif;?>

  <div class="hero-unit">
        <h1><?php echo JText::_('J2STORE_SHIPPING_TROUBLESHOOTER_HEADING'); ?></h1>
        <br />
      <p class="lead"><?php echo JText::_('J2STORE_SHIPPING_TROUBLESHOOT_INTRODUCTION'); ?></p>
      <p class="lead"><?php echo JText::_('J2STORE_SHIPPING_TROUBLESHOOT_INTRODUCTION_NOTE'); ?></p>

      <a class="btn btn-large btn-success" href="<?php echo JRoute::_("index.php?option=com_j2store&view=shippingtroubles&layout=default_shipping");?>">
         <strong>
          <?php echo JText::_('J2STORE_SHIPPING_START_WIZARD');?>
         </strong>
      </a>

  </div>
</div>
