<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
?>
<style>

.j2storeConfirmChange {
	margin-top:100px;
    -moz-border-radius: 0 0 8px 8px;
    -webkit-border-radius: 0 0 8px 8px;
    border-radius: 0 0 8px 8px;
    border-width: 0 8px 8px 8px;
    border:1px solid #000000;
}

.j2storeConfirmChange .modal-header{
 	border:1px solid #faa732;
	background-color:#faa732;
}
</style>
<div class="j2store-modal">
		<div class="modal fade j2storeConfirmChange hide" id="j2storeConfirmChange" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		  <div class="modal-dialog">
		    <div class="modal-content">
		      <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title" id="myModalLabel"><?php echo JText::_('J2STORE_WARNING');?></h4>
		      </div>
		      <div class="modal-body">
      			<span class="ui-icon ui-icon-info"></span>
					<?php echo JText::_('J2STORE_PRODUCT_TYPE_CHANGE_WARNING_MSG');?>
		     </div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo JText::_('J2STORE_CLOSE');?></button>
		        <?php J2Html::text('product_id', $this->item->j2store_product_id ,array('id'=>'product_id'));?>
		        <button type="button"id="changeTypeBtn" class="btn btn-warning"><?php echo JText::_('J2STORE_CONTINUE');?></button>
		      </div>
		    </div>
		  </div>
	</div>
</div>