<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
JHtml::_('behavior.modal');

?>
<?php $row = $this->item;?>
	<!-- shipping plg name -->
	
	<?php 
		JFactory::getLanguage()->load('plg_j2store_'.$row->element, JPATH_ADMINISTRATOR, null, true);
	?>
	
	<h3><?php echo JText::_($row->element); ?></h3>
	<?php
		$app = JFactory::getApplication();
		JPluginHelper::importPlugin('j2store');

		$results =array();
		$results = JFactory::getApplication()->triggerEvent( 'onJ2StoreGetReportView', array( $row ) );
		$html ='';
		foreach($results as $result){
			$html .=$result;
		}
		echo $html;
	?>