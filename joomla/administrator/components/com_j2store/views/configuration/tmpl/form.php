<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.framework');
JHtml::_('behavior.modal');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

JHtml::_('script', 'media/j2store/js/j2store.js', false, false);
?>
<div class="j2store-configuration">
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal form-validate">
<?php echo J2Html::hidden('option','com_j2store');?>
<?php echo J2Html::hidden('view','configurations');?>
<?php echo J2Html::hidden('task','',array('id'=>'task'));?>
<?php echo JHtml::_('form.token'); ?>
<?php
        $fieldsets = $this->form->getFieldsets();
        $shortcode = $this->form->getValue('text');
        $tab_count = 0;

        foreach ($fieldsets as $key => $attr)
        {

	            if ( $tab_count == 0 )
	            {
	                echo JHtml::_('bootstrap.startTabSet', 'configuration', array('active' => $attr->name));
	            }
	            echo JHtml::_('bootstrap.addTab', 'configuration', $attr->name, JText::_($attr->label, true));
	            ?>
	         <?php  if(J2Store::isPro() != 1 && isset($attr->ispro) && $attr->ispro ==1 ) : ?>
				<?php echo J2Html::pro(); ?>
			<?php else: ?>

	            <div class="row-fluid">
	                <div class="span12">
	                    <?php
	                    $layout = '';
	                    $style = '';
	                    $fields = $this->form->getFieldset($attr->name);
	                    foreach ($fields as $key => $field)
	                    {
	                    	$pro = $field->getAttribute('pro');
	                    ?>
	                        <div class="control-group <?php echo $layout; ?>" <?php echo $style; ?>>
	                            <div class="control-label"><?php echo $field->label; ?></div>
	                            <?php if(J2Store::isPro() != 1 && $pro ==1 ): ?>
	                            	<?php echo J2Html::pro(); ?>
	                            <?php else: ?>
	                            	<div class="controls"><?php echo $field->input; ?>
	                            	<br />
	                            	<small class="muted"><?php echo JText::_($field->description); ?></small>
	                            <?php endif; ?>

	                            </div>
	                        </div>
	                    <?php
	                    }
	                    ?>
	                </div>
	            </div>
	            <?php endif; ?>
	            <?php
	            echo JHtml::_('bootstrap.endTab');
	            $tab_count++;

        }
        ?>
 </form>
</div>

<?php $zone_id = J2Store::config()->get('zone_id', 0); ?>
<script type="text/javascript">
	jQuery("#continue_shopping_page").on('change',function(){
		if(this.value == 'previous'){
			jQuery("#continue_shopping_url").closest('.control-group').hide();
			jQuery("#continue_shopping_menu").closest('.control-group').hide();
		}

		if(this.value =='menu'){
			jQuery("#continue_shopping_menu").closest('.control-group').show();
			jQuery("#continue_shopping_url").closest('.control-group').hide();
		}

		if(this.value == 'url'){
			jQuery("#continue_shopping_url").closest('.control-group').show();
			jQuery("#continue_shopping_menu").closest('.control-group').hide();
		}
	});

	jQuery("#continue_shopping_page").trigger('change');


	jQuery("#cart_empty_redirect").on('change',function(){
		if(this.value == 'cart'){
			jQuery("#continue_cart_redirect_menu").closest('.control-group').hide();
			jQuery("#cart_redirect_page_url").closest('.control-group').hide();
		}

		if(this.value =='menu'){
			jQuery("#continue_cart_redirect_menu").closest('.control-group').show();
			jQuery("#cart_redirect_page_url").closest('.control-group').hide();
		}

		if(this.value == 'url'){
			jQuery("#cart_redirect_page_url").closest('.control-group').show();
			jQuery("#continue_cart_redirect_menu").closest('.control-group').hide();
		}
	});

	jQuery("#cart_empty_redirect").trigger('change');


	(function($) {
		$('#j2store_country_id').bind('change', function() {
			if (this.value == '') return;
			$.ajax({
				url: 'index.php?option=com_j2store&view=zones&task=getCountry&country_id=' + this.value,
				dataType: 'json',
				beforeSend: function() {
					$('#j2store_country_id').after('<span class="wait">&nbsp;<img src="<?php echo JUri::root(true); ?>/media/j2store/images/loader.gif" alt="" /></span>');
				},
				complete: function() {
					$('.wait').remove();
				},
				success: function(json) {
					html = '<option value=""><?php echo JText::_('J2STORE_SELECT_OPTION'); ?></option>';
					if (json['zone'] != '') {
						for (i = 0; i < json['zone'].length; i++) {
		        			html += '<option value="' + json['zone'][i]['j2store_zone_id'] + '"';
							if (json['zone'][i]['j2store_zone_id'] == '<?php echo $this->form->getValue('zone_id'); ?>') {
			      				html += ' selected="selected"';
			    			}

			    			html += '>' + json['zone'][i]['zone_name'] + '</option>';
						}
					} else {
						html += '<option value="0" selected="selected"><?php echo JText::_('J2STORE_CHECKOUT_NONE'); ?></option>';
					}

					$('#j2store_zone_id').html(html);
					$('#j2store_zone_id').trigger('liszt:updated');
				},
				error: function(xhr, ajaxOptions, thrownError) {
					//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		});
		})(j2store.jQuery);
	(function($) {
			$('#j2store_country_id').trigger('change');
			$('#j2store_zone_id').trigger('liszt:updated');
		})(j2store.jQuery);
	(function($) {
		$('#j2store_testemail').on('click', function(e) {
			e.preventDefault();
			var email = $('#admin_email').val();
			$.ajax({
				url: 'index.php?option=com_j2store&view=configurations&task=testemail&admin_email='+email,
				dataType: 'json',
				beforeSend: function() {
					$('#email_message').remove();
					$('#j2store_testemail').after('<span class="wait">&nbsp;<img src="<?php echo JUri::root(true); ?>/media/j2store/images/loader.gif" alt="" /></span>');
				},
				complete: function() {
					$('.wait').remove();
				},
				success: function(json) {
					if(json['success']){
						$('#j2store_testemail').before("<div id='email_message'><span class='text-success'>"+json['success']+"</span><br></div>");
					}
					if(json['error']){
						$('#j2store_testemail').before("<div id='email_message'><span class='text-error'>"+json['error']+"</span><br></div>");
					}

				},
				error: function(xhr, ajaxOptions, thrownError) {
					//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		});
	})(j2store.jQuery);
</script>