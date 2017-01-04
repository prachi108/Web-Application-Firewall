<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

defined('_JEXEC') or die;
JHTML::_('behavior.modal');

$sidebar = JHtmlSidebar::render();
$this->params = J2Store::config();
JHTML::_('behavior.modal');
?>
<?php if(!empty( $sidebar )): ?>
   <div id="j-sidebar-container" class="span2">
      <?php echo $sidebar ; ?>
   </div>
   <div id="j-main-container" class="span10">
 <?php else : ?>
	<div class="j2store">
  <?php endif;?>
  <form action="index.php" method="post"	name="adminForm" id="adminForm">
  		<?php echo J2Html::hidden('option','com_j2store');?>
		<?php echo J2Html::hidden('view','inventories');?>
		<?php echo J2Html::hidden('task','browse',array('id'=>'task'));?>
		<?php echo J2Html::hidden('boxchecked','0');?>
		<?php echo J2Html::hidden('filter_order',$this->state->filter_order);?>
		<?php echo J2Html::hidden('filter_order_Dir',$this->state->filter_order_Dir);?>
		
		<div class="j2store-inventory-list">
					<!-- Products items -->
					<?php if(J2Store::isPro()): ?>
						<?php echo $this->loadTemplate('items');?>
					<?php else: ?>
						<?php echo J2Html::pro(); ?>
					<?php endif;?>
		</div>
		<?php echo JHTML::_( 'form.token' ); ?>
  </form>
 </div>