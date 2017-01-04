<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
?>
<div class="j2store">
<?php if(!empty($this->items)):?>
<form class="form-horizontal form-validate" id="adminForm" name="adminForm" method="post" action="index.php">
	<?php echo J2Html::hidden('option','com_j2store');?>
	<?php echo J2Html::hidden('view','customer');?>
	<?php echo J2Html::hidden('task','',array('id'=>'task'));?>
	<?php echo J2Html::hidden('email',$this->item->email,array('id'=>'email'));?>
	<!-- <input type="hidden" name="j2store_address_id" value="<?php // echo $this->item->email;?>" />-->
	<?php echo JHTML::_( 'form.token' ); ?>
	<div class="row-fluid">
		<div class="span6">
		<?php
		if($this->items && !empty($this->items)):
			foreach($this->items as $item):
			$this->item = $item;
		?>
		<?php echo $this->loadTemplate('addresses');?>

		<?php endforeach;?>
		<?php endif;?>
		</div>
		<div class="span6">
			<?php echo $this->loadTemplate('orderhistory');?>
		</div>
	</div>
</form>
<?php endif;?>
</div>