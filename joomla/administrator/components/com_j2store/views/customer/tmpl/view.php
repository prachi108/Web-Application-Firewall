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
<?php if($this->email):?>
<div class="j2store">
<form class="form-horizontal form-validate" id="adminForm" name="adminForm" method="post" action="index.php">
		<?php echo J2Html::hidden('option','com_j2store');?>
		<?php echo J2Html::hidden('view','customer');?>
		<?php echo J2Html::hidden('task','',array('id'=>'task'));?>
		<?php echo J2Html::hidden('email',$this->email ,array('id' =>'customer_email_id'));?>
		<?php echo JHtml::_('form.token'); ?>
	<div class="row-fluid">
		<h4><?php JText::_('J2STORE_CUSTOMER_DETAILS');?></h4>
		<div class="span8">
			<div class="control-group well">
						<label class="control-label">
							<?php echo J2Html::label(JText::_('J2STORE_EMAIL'));?>
						</label>
						<div class="controls" id="customer-email-edit-info" style="display:none;">
							<?php echo J2Html::text('new_email',$this->email ,array('id'=>'new-email-input'));?>
							<input id="customer-save-btn"  class="btn btn-success" type="button" onclick="getUpdatedEmail(this,'changeEmail');"	value="<?php echo JText::_('JAPPLY'); ?>" />
							<input id="customer-confirm-btn"  class="btn btn-warning" type="button" onclick="getUpdatedEmail(this,'confirmchangeEmail');"	value="<?php echo JText::_('J2STORE_CONFIRM_UPDATE'); ?>" style="display:none;" />
							<input class="btn btn-default" type="button" onclick="canUpdate();"
								value="<?php echo JText::_('JCANCEL'); ?>" />
						</div>

						<div id="customer-email-info" class="controls">
								<?php echo $this->email;?>
								<a class="btn btn-primary" onclick="jQuery('#customer-email-edit-info').toggle();jQuery('#customer-email-info').toggle();">
									<?php echo JText::_('J2STORE_EDIT');?>
								</a>
						</div>
				</div>
		</div>
		<div class="span4"></div>
	</div>
	<div class="row-fluid">
		<div class="span6">
		<h4><?php echo JText::_('J2STORE_ADDRESS_LIST');?></h4>
		<?php
		if($this->addresses && !empty($this->addresses)):
			foreach($this->addresses as $item):
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
</div>
<?php endif;?>

<script type="text/javascript">

/** Method to cancel the update option **/
function canUpdate(){
	(function($){
		//empty the task
		$('#task').attr('value','');
		location.reload();
	})(j2store.jQuery);
}
function getUpdatedEmail(element , task){
	(function($){
		$('#task').attr('value',task);
		var form  =$('#adminForm');
		var values = form.serializeArray();
		$.ajax({
				method: 'POST',
				url :'index.php',
				dataType:'json',
				data:values,
				success:function(json){
					if(json['redirect']){
						  window.location.replace(json['redirect']);
					}else{
						if(json['msgType'] !='' ){
							$(element).prop( "disabled", true );
							$('#new-email-input').prop( "readonly", 'readonly' );
							$('#system-message-container').append('<div class="alert"><p>'+  json['msg'] +'</p></div>');
							$('#customer-confirm-btn').show();
						}
					}
				}
			})
	})(j2store.jQuery);
}
</script>