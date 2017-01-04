<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/
// No direct access
defined ( '_JEXEC' ) or die ();
JHtml::_('script', 'media/com_j2store/js/j2store.js', false, false);
?>

<div class="j2store_manufacture_edit">
<?php

$viewTemplate = $this->getRenderedForm();
echo $viewTemplate;
?>
</div>
<script type="text/javascript">

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
				if (json['postcode_required'] == '1') {
					$('#shipping-postcode-required').show();
				} else {
					$('#shipping-postcode-required').hide();
				}

				html = '<option value=""><?php echo JText::_('J2STORE_SELECT_OPTION'); ?></option>';

				if (json['zone'] != '') {

					for (i = 0; i < json['zone'].length; i++) {
	        			html += '<option value="' + json['zone'][i]['j2store_zone_id'] + '"';

						if (json['zone'][i]['j2store_zone_id'] == '<?php echo $this->item->zone_id; ?>') {
		      				html += ' selected="selected"';
		    			}

		    			html += '>' + json['zone'][i]['zone_name'] + '</option>';
					}
				} else {
					html += '<option value="0" selected="selected"><?php echo JText::_('J2STORE_CHECKOUT_NONE'); ?></option>';
				}

				$('#j2store_zone_id').html(html);
			},
			error: function(xhr, ajaxOptions, thrownError) {
				//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	});

	$('#j2store_country_id').trigger('change');

	})(j2store.jQuery);
</script>
