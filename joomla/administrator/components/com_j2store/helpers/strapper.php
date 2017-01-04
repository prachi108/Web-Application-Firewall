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
// no direct access
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
class J2StoreStrapper {

	public static function addJS() {
		$app = JFactory::getApplication();
		$params = J2Store::config();

		$document =JFactory::getDocument();

		JHtml::_('jquery.framework');
		JHtml::_('bootstrap.framework');
		//load name spaced jqueryui
		//load name spacer
		$document->addScript(JURI::root(true).'/media/j2store/js/j2store.namespace.js');
		$ui_location = $params->get ( 'load_jquery_ui', 3 );
		switch ($ui_location) {

			case '0' :
				// load nothing
				break;
			case '1':
				if ($app->isSite ()) {
					$document->addScript ( JURI::root ( true ) . '/media/j2store/js/jquery-ui.min.js' );
				}
				break;

			case '2' :
				if ($app->isAdmin ()) {
					$document->addScript ( JURI::root ( true ) . '/media/j2store/js/jquery-ui.min.js' );
				}
				break;

			case '3' :
			default :
				$document->addScript ( JURI::root ( true ) . '/media/j2store/js/jquery-ui.min.js' );
				break;
		}


		if($app->isAdmin()) {
			$document->addScript(JURI::root(true).'/media/j2store/js/jquery.validate.min.js');
			$document->addScript(JURI::root(true).'/media/j2store/js/j2store_admin.js');
			$document->addScript(JUri::root(true).'/media/j2store/js/jquery-ui-timepicker-addon.js');
		}
		else {
			$document->addScript(JUri::root(true).'/media/j2store/js/jquery-ui-timepicker-addon.js');
			$document->addScript(JUri::root(true).'/media/j2store/js/jquery.zoom.js');
			self::loadTimepickerScript($document);
			$document->addScript(JURI::root(true).'/media/j2store/js/j2store.js');
		}

	}

	public static function addCSS() {
		$app = JFactory::getApplication ();
		$j2storeparams = J2Store::config ();
		$document = JFactory::getDocument ();

		if ($app->isAdmin ()) {
			// always load namespaced bootstrap
			// $document->addStyleSheet(JURI::root(true).'/media/j2store/css/bootstrap.min.css');
		}

		// load full bootstrap css bundled with J2Store.
		if ($app->isSite () && $j2storeparams->get ( 'load_bootstrap', 0 )) {
			$document->addStyleSheet ( JURI::root ( true ) . '/media/j2store/css/bootstrap.min.css' );
		}

		// for site side, check if the param is enabled.
		if ($app->isSite () && $j2storeparams->get ( 'load_minimal_bootstrap', 0 )) {
			$document->addStyleSheet ( JURI::root ( true ) . '/media/j2store/css/minimal-bs.css' );
		}

		// jquery UI css
		$ui_location = $j2storeparams->get ( 'load_jquery_ui', 3 );
		switch ($ui_location) {

			case '0' :
				// load nothing
				break;
			case '1' :
				if ($app->isSite ()) {
					$document->addStyleSheet ( JURI::root ( true ) . '/media/j2store/css/jquery-ui-custom.css' );
				}
				break;

			case '2' :
				if ($app->isAdmin ()) {
					$document->addStyleSheet ( JURI::root ( true ) . '/media/j2store/css/jquery-ui-custom.css' );
				}
				break;

			case '3' :
			default :
				$document->addStyleSheet ( JURI::root ( true ) . '/media/j2store/css/jquery-ui-custom.css' );
				break;
		}

		if ($app->isAdmin ()) {
			$document->addStyleSheet ( JURI::root ( true ) . '/media/j2store/css/j2store_admin.css' );
		} else {
			$document->addStyleSheet ( JUri::root () . 'media/j2store/css/font-awesome.min.css' );
			// Add related CSS to the <head>
			if ($document->getType () == 'html' && $j2storeparams->get ( 'j2store_enable_css' )) {

				$template = self::getDefaultTemplate ();

				jimport ( 'joomla.filesystem.file' );
				// j2store.css
				if (JFile::exists ( JPATH_SITE . '/templates/' . $template . '/css/j2store.css' ))
					$document->addStyleSheet ( JURI::root ( true ) . '/templates/' . $template . '/css/j2store.css' );
				else
					$document->addStyleSheet ( JURI::root ( true ) . '/media/j2store/css/j2store.css' );
			} else {
				$document->addStyleSheet ( JURI::root ( true ) . '/media/j2store/css/j2store.css' );
			}
		}
	}

	public static function getDefaultTemplate() {

		static $tsets;

		if ( !is_array( $tsets ) )
		{
			$tsets = array( );
		}
		$id = 1;
		if(!isset($tsets[$id])) {
			$db = JFactory::getDBO();
			$query = "SELECT template FROM #__template_styles WHERE client_id = 0 AND home=1";
			$db->setQuery( $query );
			$tsets[$id] = $db->loadResult();
		}
		return $tsets[$id];
	}

	public static function loadTimepickerScript($document) {
		static $sets;

		if ( !is_array( $sets ) )
		{
			$sets = array( );
		}
		$id = 1;
		if(!isset($sets[$id])) {
			$document->addScriptDeclaration(self::getTimePickerScript());
			$sets[$id] = true;
		}
	}

	public static function getTimePickerScript($date_format='', $time_format='', $prefix='j2store', $isAdmin=false) {

		//initialise the date/time picker
		if($isAdmin) {
			$document =JFactory::getDocument();
			$document->addScript(JUri::root(true).'/media/j2store/js/jquery-ui-timepicker-addon.js');
			$document->addStyleSheet(JURI::root(true).'/media/j2store/css/jquery-ui-custom.css');
		}

		if(empty($date_format)) {
			$date_format = 'yy-mm-dd';
		}

		if(empty($time_format)) {
			$time_format = 'HH:mm';
		}
		$localisation = self::getDateLocalisation();

		$element_date = $prefix.'_date';
		$element_time = $prefix.'_time';
		$element_datetime = $prefix.'_datetime';

		$timepicker_script ="
			if(typeof(j2store) == 'undefined') {
				var j2store = {};
			}

	if(typeof(jQuery) != 'undefined') {
		jQuery.noConflict();
	}

	if(typeof(j2store.jQuery) == 'undefined') {
		j2store.jQuery = jQuery.noConflict();
	}

	if(typeof(j2store.jQuery) != 'undefined') {

		(function($) {
			$(document).ready(function(){
				//date, time, datetime

				if( $('.$element_date').length ){
					$('.$element_date').datepicker({dateFormat: '$date_format'});
				}

				if($('.$element_datetime').length){
					$('.$element_datetime').datetimepicker({
							dateFormat: '$date_format',
							timeFormat: '$time_format',
							$localisation
					});
				}

				if($('.$element_time').length){
					$('.$element_time').timepicker({timeFormat: '$time_format', $localisation});
				}

			});
		})(j2store.jQuery);
	}
	";

	return $timepicker_script;

	}

	public static function getDateLocalisation($as_array=false) {

		//add localisation
		$params = J2Store::config();
		$language = JFactory::getLanguage()->getTag();
		if($params->get('jquery_ui_localisation', 0) && strpos($language, 'en') === false) {
			$doc = JFactory::getDocument();
			$doc->addScript('//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/i18n/jquery-ui-i18n.min.js');

			//set the language default
			$tag = explode('-', $language);
			if(isset($tag[0]) && JString::strlen($tag[0]) == 2) {
				$script = "";
				$script .= "(function($) { $.datepicker.setDefaults($.datepicker.regional['{$tag[0]}']); })(j2store.jQuery);";
				$doc->addScriptDeclaration($script);
			}

		}

		//localisation
		$currentText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_CURRENT_TEXT'));
		$closeText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_CLOSE_TEXT'));
		$timeOnlyText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_CHOOSE_TIME'));
		$timeText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_TIME'));
		$hourText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_HOUR'));
		$minuteText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_MINUTE'));
		$secondText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_SECOND'));
		$millisecondText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_MILLISECOND'));
		$timezoneText = addslashes(JText::_('J2STORE_TIMEPICKER_JS_TIMEZONE'));

		if($as_array) {

			$localisation = array (
					'currentText' => $currentText,
					'closeText' => $closeText,
					'timeOnlyTitle' => $timeOnlyText,
					'timeText' => $timeText,
					'hourText' => $hourText,
					'minuteText' => $minuteText,
					'secondText' => $secondText,
					'millisecText' => $millisecondText,
					'timezoneText' => $timezoneText
			);

		} else {

			$localisation ="
			currentText: '$currentText',
			closeText: '$closeText',
			timeOnlyTitle: '$timeOnlyText',
			timeText: '$timeText',
			hourText: '$hourText',
			minuteText: '$minuteText',
			secondText: '$secondText',
			millisecText: '$millisecondText',
			timezoneText: '$timezoneText'
			";
		}

		return $localisation;

	}

	public static function addDateTimePicker($element, $json_options) {

		$timepicker_script = self::getDateTimePickerScript($element, $json_options) ;
		JFactory::getDocument ()->addScriptDeclaration ( $timepicker_script );
	}

	public static function getDateTimePickerScript($element, $json_options) {
		if (! empty ($json_options)) {
			$option_params = new JRegistry ($json_options);
		} else {
			$option_params = new JRegistry ( '{}' );
		}

		$variables = self::getDateLocalisation (true);
		$variables['dateFormat'] = $option_params->get ( 'date_format', 'yy-mm-dd' );
		$variables['timeFormat'] = $option_params->get ( 'time_format', 'HH:mm' );
		if ($option_params->get ( 'hide_pastdates', 1 )) {
			$variables ['minDate'] = 0;
		}

		$variables = json_encode ( $variables );
		$timepicker_script = "
		(function($) {
			$(document).ready(function(){
				$('.$element').datetimepicker({$variables});
			});
		})(j2store.jQuery);";

		return $timepicker_script;
	}

	public static function addDatePicker($element, $json_options) {

		$datepicker_script = self::getDatePickerScript($element, $json_options) ;
		JFactory::getDocument ()->addScriptDeclaration ( $datepicker_script);
	}

	public static function getDatePickerScript($element, $json_options) {
		if (! empty ($json_options)) {
			$option_params = new JRegistry ($json_options);
		} else {
			$option_params = new JRegistry ( '{}' );
		}

		$variables = array();
		$variables['dateFormat'] = $option_params->get ( 'date_format', 'yy-mm-dd' );
		if ($option_params->get ( 'hide_pastdates', 1 )) {
			$variables ['minDate'] = 0;
		}

		$variables = json_encode ( $variables );
		$datepicker_script = "
		(function($) {
			$(document).ready(function(){
				$('.$element').datepicker({$variables});
			});
		})(j2store.jQuery);";

		return $datepicker_script;
	}

	static public function sizeFormat($filesize)
	{
		if($filesize > 1073741824) {
			return number_format($filesize / 1073741824, 2)." Gb";
		} elseif($filesize >= 1048576) {
			return number_format($filesize / 1048576, 2)." Mb";
		} elseif($filesize >= 1024) {
			return number_format($filesize / 1024, 2)." Kb";
		} else {
			return $filesize." bytes";
		}
	}
}