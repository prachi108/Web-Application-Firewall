<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
$viewTemplate = $this->getRenderedForm();

?>
<div class="j2store-orderstatus">
	<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal form-validate">
		<?php echo J2Html::hidden('option','com_j2store');?>
		<?php echo J2Html::hidden('view','orderstatuses');?>
		<?php echo J2Html::hidden('id',$this->item->j2store_orderstatus_id,array('id'=>'id'));?>
		<?php echo J2Html::hidden('j2store_orderstatus_id',$this->item->j2store_orderstatus_id );?>
		<?php echo J2Html::hidden('task','',array('id'=>'task'));?>
		<?php echo JHtml::_('form.token'); ?>
		<?php
	        $fieldsets = $this->form->getFieldsets();
	        $shortcode = $this->form->getValue('text');
	        $tab_count = 0;
	       	foreach ($fieldsets as $key => $attr):?>
			<div class="row-fluid">
				<div class="span12">
					 <?php
		                    $layout = '';
		                    $style = '';
		                    $fields = $this->form->getFieldset($attr->name);
		                    foreach ($fields as $key => $field):
		                    if($key == 'enabled' && isset($this->item->orderstatus_core) && $this->item->orderstatus_core):?>
		                    	 <div class="control-group <?php echo $layout; ?>" <?php echo $style; ?>>
		                            <div class="control-label"><?php echo $field->label; ?></div>
		                            	<div class="controls">
										<?php echo J2Html::hidden('enabled', 1);?>
										<?php echo JText::_('J2STORE_YES');?>
		                            </div>
		                        </div>
		                    <?php else: ?>
		                       <div class="control-group <?php echo $layout; ?>" <?php echo $style; ?>>
		                            <div class="control-label"><?php echo $field->label; ?></div>
		                            	<div class="controls"><?php echo $field->input; ?>
		                            </div>
		                        </div>
		                    <?php endif;?>
		                    <?php endforeach;?>
				</div>
			</div>
			<?php
			          echo JHtml::_('bootstrap.endTab');
			           $tab_count++;
	       		 endforeach;?>
	</form>
</div>