<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.modal');
$countries = J2StoreHelperSelect::getCountries();
?>
<div class="j2store">
<form class="form-horizontal" id="adminForm" name="adminForm" method="post" action="index.php">
	<input type="hidden" name="option" value="com_j2store">
	<input type="hidden" name="view" value="geozone">
	<input type="hidden" name="task" value="">
	<input type="hidden" name="j2store_geozone_id" value="<?php echo $this->item->j2store_geozone_id; ?>" />
	<?php echo JHtml::_('form.token'); ?>

		<div class="row-fluid">
			<div class="span6">
				<h1><?php echo JText::_('J2STORE_GEOZONE'); ?></h1>
				<div class="control-group">
					<label class="control-label">
						<?php echo JText::_('J2STORE_GEOZONE_NAME'); ?>
					</label>
					<div class="controls">
						<?php echo J2Html::text('geozone_name',$this->item->geozone_name);?>
					</div>
				</div>
				<div class="control-group">
						<label class="control-label">
							<?php echo JText::_('J2STORE_ENABLED')?>
						</label>
						<div class="controls">
							<?php
							echo J2Html::select()->clearState()
							->type('genericlist')
							->name('enabled')
							->value($this->item->enabled)
							->setPlaceHolders(
									array(0=>JText::_('J2STORE_DISABLE'),1=>JText::_('J2STORE_ENABLED'))

							)->getHtml();

							?>
						</div>
					</div>
				</div>
			<div class="span6">
				<div class="j2storehelp alert alert-info"><?php echo JText::_('J2STORE_GEOZONE_HELP_TEXT'); ?></div>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span12">
			<h4><?php echo JText::_('J2STORE_GEOZONE_COUNTRIES_AND_ZONES');?></h4>
				<?php if($this->item->j2store_geozone_id):?>
				<div class="btn-toolbar">
					<div class="btn-wrapper">
						<?php echo J2StorePopup::popupAdvanced('index.php?option=com_j2store&view=countries&layout=modal&task=elements&tmpl=component&geozone_id='.$this->item->j2store_geozone_id,'<i class="icon icon-download"></i> '.JText::_('J2STORE_IMPORT_COUNTRIES') ,array('class'=>'btn btn-small btn-success','width'=>800 , 'height'=>600 ,'update'=>true));?>
					</div>
					<div class="btn-wrapper">
						<?php echo J2StorePopup::popupAdvanced('index.php?option=com_j2store&view=zones&layout=modal&task=elements&tmpl=component&geozone_id='.$this->item->j2store_geozone_id, '<i class="icon icon-download"></i> '.JText::_('J2STORE_IMPORT_ZONES') ,array('class'=>'btn btn-small btn-success','width'=>800 , 'height'=>600,'update'=>true));?>
					</div>
				</div>
				<?php endif;?>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span9">
				<table id="geozone_rule_table" class="table table-stripped table-bordered">
					<thead>
						<tr>
							<th><?php echo JText::_('J2STORE_COUNTRY');?></th>
							<th><?php echo JText::_('J2STORE_ZONE');?></th>
							<th></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="3">
								<a class="btn btn-primary pull-right" onclick="j2storeAddGeoZone();" class="button"><?php echo JText::_('J2STORE_GEOZONE_ADD_COUNTRY_OR_ZONE'); ?></a>
							</td>
						</tr>
					</tfoot>
					<?php $zone_to_geo_zone_row = 0;
						if(isset($this->item->geoRuleList ) && !empty($this->item->geoRuleList )):
					?>
			        <?php foreach ($this->item->geoRuleList as $geozonerule) : ?>
					<tbody id="zone-to-geo-zone-row<?php echo $zone_to_geo_zone_row;?>">
					   <tr>
				           <td class="left">
						  <?php
								$attr =array("onchange"=>"getZones($zone_to_geo_zone_row,this.value)");
							/* 	$option =array();
									foreach($this->item->countryList as $clist){
										$option [] = JHtml::_('select.option',$clist->j2store_country_id,$clist->country_name);
									}
									echo JHTML::_('select.genericlist', $option, 'zone_to_geo_zone['.$zone_to_geo_zone_row.'][country_id]',$attr, 'value', 'text',$geozonerule->country_id);
 */

									echo J2Html::select()->clearState()
									->type('genericlist')
									->name('zone_to_geo_zone['.$zone_to_geo_zone_row.'][country_id]')
									->value($geozonerule->country_id)
									->attribs($attr)
									->setPlaceHolders(
											array(''=>JText::_('J2STORE_SELECT_OPTION'))
									)
									->hasOne('Countries')
									->setRelations( array(
											'fields' => array (
													'key' => 'j2store_country_id',
													'name' => array('country_name')
											)
									)
									)->getHtml();


							?>
				           </td>
			               <td class="left">
			                <select name="zone_to_geo_zone[<?php echo $zone_to_geo_zone_row; ?>][zone_id]" id="zone<?php echo $zone_to_geo_zone_row; ?>">
			                </select>
			                <?php echo J2Html::hidden('zone_to_geo_zone['.$zone_to_geo_zone_row.'][j2store_geozonerule_id]', $geozonerule->j2store_geozonerule_id);?>
			               </td>
			               <td class="left"><a class="btn btn-small" onclick="j2storeRemoveZone(<?php echo $geozonerule->j2store_geozonerule_id; ?>, <?php echo $zone_to_geo_zone_row; ?>);" class="button"><i class="icon icon-trash"></i> <?php echo JText::_('J2STORE_REMOVE'); ?></a></td>
			            </tr>
					</tbody>
					<?php $zone_to_geo_zone_row++; ?>
					<?php endforeach; ?>
					<?php endif;?>
				</table>

			</div>
		</div>
	</form>
</div>

<script type="text/javascript"><!--
(function($) {
$('#zone-id').load('index.php?option=com_j2store&view=geozone&task=getZone&country_id=' + $('#country-id').attr('value') + '&zone_id=0');
})(j2store.jQuery);
//--></script>

	<?php $zone_to_geo_zone_row = 0; ?>
	<?php if(isset($this->item->geoRuleList ) && !empty($this->item->geoRuleList )):?>
	<?php foreach ($this->item->geoRuleList as $geozonerule) : ?>
	<script type="text/javascript"><!--
	(function($) {
		$('#zone<?php echo $zone_to_geo_zone_row; ?>').load('index.php?option=com_j2store&view=geozones&task=getZone&country_id=<?php echo $geozonerule->country_id; ?>&zone_id=<?php echo $geozonerule->zone_id; ?>');
	})(j2store.jQuery);
	//--></script>
	<?php $zone_to_geo_zone_row++; ?>
	<?php endforeach; ?>
	<?php endif;?>

<script type="text/javascript"><!--
var zone_to_geo_zone_row = <?php echo $zone_to_geo_zone_row; ?>;
function j2storeAddGeoZone() {
	(function($) {
	html  = '<tbody id="zone-to-geo-zone-row' + zone_to_geo_zone_row + '">';
	html += '<tr>';
	html += '<td class="left"><select name="zone_to_geo_zone[' + zone_to_geo_zone_row + '][country_id]" id="country' + zone_to_geo_zone_row + '" onchange="getZones('+ zone_to_geo_zone_row+',this.value )">';
	<?php   foreach ($countries as $key=>$value) { ?>
	html += '<option value="<?php echo $key; ?>"><?php  echo addslashes($value); ?></option>';
	<?php } ?>
	html += '</select></td>';
	html += '<td class="left"><select name="zone_to_geo_zone[' + zone_to_geo_zone_row + '][zone_id]" id="zone' + zone_to_geo_zone_row + '"></select>';
	html += '<input type="hidden" name="zone_to_geo_zone['+ zone_to_geo_zone_row + '][j2store_geozonerule_id]" value="" /></td>';
	html += '<td class="left"><a onclick="j2store.jQuery(\'#zone-to-geo-zone-row' + zone_to_geo_zone_row + '\').remove();" class="button"><?php echo JText::_('J2STORE_REMOVE'); ?></a></td>';
	html += '</tr>';
	html += '</tbody>';
	$('#geozone_rule_table > tfoot').before(html);
	$('#zone' + zone_to_geo_zone_row).load('index.php?option=com_j2store&view=geozone&task=getZone&country_id='+ $('#country'+zone_to_geo_zone_row).attr('value') + '&zone_id=0');

	zone_to_geo_zone_row++;
	})(j2store.jQuery);
}
	function j2storeRemoveZone(geozonerule_id, zone_to_geo_zone_row) {
		(function($) {
			$('.j2storealert').remove();
			$.ajax({
				method:'post',
				url:'index.php?option=com_j2store&view=geozones&task=removeGeozoneRule',
				data:{'rule_id':geozonerule_id},
				dataType:'json'
			}).done(function(response) {
				$('#zone-to-geo-zone-row'+zone_to_geo_zone_row).remove();
				$('#geozone_rule_table').before('<div class="j2storealert alert alert-block">'+response.msg+'</div>');
			});
		})(j2store.jQuery);
	}

function getZones(zone_id,country_id){
	j2store.jQuery('#zone' + zone_id).load('index.php?option=com_j2store&view=geozones&task=getZone&country_id='+ country_id+'&zone_id=0');
}
//--></script>
