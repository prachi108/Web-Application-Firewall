<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

?>
  	<?php if(J2Store::isPro()): ?>
			<?php echo $viewTemplate = $this->getRenderedForm(); ?>
		<?php else: ?>
		<?php $sidebar = JHtmlSidebar::render(); ?>		
		<?php if(!empty( $sidebar )): ?>
		   <div id="j-sidebar-container" class="span2">
		      <?php echo $sidebar ; ?>
		   </div>
		   <div id="j-main-container" class="span10">
		 <?php else : ?>
		     <div id="j-main-container">
		 <?php endif;?>
			
			<?php echo J2Html::pro(); ?>
		</div>
	<?php endif;?>
