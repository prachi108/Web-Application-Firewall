<?php
/**
* BreezingForms - A Joomla Forms Application
* @version 1.8
* @package BreezingForms
* @copyright (C) 2008-2012 by Markus Bopp
* @license Released under the terms of the GNU General Public License
**/
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

jimport('joomla.filesystem.file');

require_once(JPATH_SITE.'/administrator/components/com_breezingforms/libraries/Zend/Json/Decoder.php');
require_once(JPATH_SITE.'/administrator/components/com_breezingforms/libraries/Zend/Json/Encoder.php');

class BFQuickModeBootstrap{ 
	
	/**
	 * @var HTML_facileFormsProcessor
	 */
	private $p = null;
	
	private $dataObject = array();
	
	private $rootMdata = array();
	
	private $fading = true;
	
	private $fadingClass = '';
	
	private $fadingCall = '';
	
	private $useErrorAlerts = false;

        private $useDefaultErrors = false;

        private $useBalloonErrors = false;
	
	private $rollover = false;
	
	private $rolloverColor = '';
	
	private $toggleFields = '';
	
	private $hasFlashUpload = false;
	
	private $flashUploadTicket = '';
	
	private $cancelImagePath = '';
	
	private $uploadImagePath = '';
        
        private $htmltextareas = array();
        
        private $language_tag = '';
        
        private $hasResponsiveDatePicker = false;
        
        function headers(){
            
                // keep IE8 compatbility
                if(preg_match('/(?i)msie [1-8]/',$_SERVER['HTTP_USER_AGENT']))
                {
                    JFactory::getDocument()->addScript('https://html5shiv.googlecode.com/svn/trunk/html5.js');
                }
            
                if($this->hasFlashUpload){
                    JFactory::getDocument()->addScript(JURI::root(true) . '/components/com_breezingforms/libraries/jquery/plupload/moxie.js');
                    JFactory::getDocument()->addScript(JURI::root(true) . '/components/com_breezingforms/libraries/jquery/plupload/plupload.js');
                }
                
                            JFactory::getDocument()->addStyleDeclaration('

.bfClearfix:after {
content: ".";
display: block;
height: 0;
clear: both;
visibility: hidden;
}

.bfFadingClass{
display:none;
}
');
            
                jimport('joomla.version');
                $version = new JVersion();
                if(version_compare($version->getShortVersion(), '3.1', '>=')){
                    JHtml::_('behavior.framework', true);
                    // force jquery to be loaded after mootools but before any other js (since J! 3.4)
                    JHtml::_('bootstrap.framework');
                    JHtml::_('jquery.framework');
                    JFactory::getDocument()->addScriptDeclaration('
                    jQuery(document).ready(function()
                    {
                            jQuery(".hasTooltip").tooltip({"html": true,"container": "body"});
                    });');
                }
                            
                $jQuery = '';
                if(isset($this->rootMdata['disableJQuery']) && $this->rootMdata['disableJQuery']){
                    $jQuery = 'var JQuery = jQuery;'."\n";
                } else {
                    JFactory::getDocument()->addScript(JURI::root(true) . '/components/com_breezingforms/libraries/jquery/jq.min.js');
                }
                
                
            
                if($this->useBalloonErrors){
                    JFactory::getDocument()->addStyleSheet( JURI::root(true) . '/components/com_breezingforms/libraries/jquery/validationEngine.jquery.css' );
                    JFactory::getDocument()->addScript(JURI::root(true) . '/components/com_breezingforms/libraries/jquery/jquery.validationEngine-en.js');
                    JFactory::getDocument()->addScript(JURI::root(true) . '/components/com_breezingforms/libraries/jquery/jquery.validationEngine.js');
                }

		$toggleCode = '';
		if($this->toggleFields != '[]'){
			$toggleCode = '
			var toggleFieldsArray = '.$this->toggleFields.';
			String.prototype.beginsWith = function(t, i) { if (i==false) { return
			(t == this.substring(0, t.length)); } else { return (t.toLowerCase()
			== this.substring(0, t.length).toLowerCase()); } } 
			function bfDeactivateSectionFields(){
				for( var i = 0; i < bfDeactivateSection.length; i++ ){
                                        bfSetFieldValue(bfDeactivateSection[i], "off");
					JQuery("#"+bfDeactivateSection[i]+" .ff_elem").each(function(i){
                                            if( JQuery(this).get(0).name && JQuery(this).get(0).name.beginsWith("ff_nm_", true) ){
                                                bfDeactivateField[JQuery(this).get(0).name] = true;
                                            }
					});
				}
                                for( var i = 0; i < toggleFieldsArray.length; i++ ){
                                    if(toggleFieldsArray[i].state == "turn"){
                                        bfSetFieldValue(toggleFieldsArray[i].tName, "off");
                                    }
                                }
			}
			function bfToggleFields(state, tCat, tName, thisBfDeactivateField){
                                if(state == "on"){
					if(tCat == "element"){
                                                JQuery("[name=\"ff_nm_"+tName+"[]\"]").closest(".bfElemWrap").css("display","");
						thisBfDeactivateField["ff_nm_"+tName+"[]"] = false;
                                                bfSetFieldValue(tName, "on");
					} else {
						JQuery("#"+tName).css("display", "");
                                                bfSetFieldValue(tName, "on");
						JQuery("#"+tName).find(".ff_elem").each(function(i){
                                                    if( JQuery(this).get(0).name && JQuery(this).get(0).name.beginsWith("ff_nm_", true) ){
                                                        thisBfDeactivateField[JQuery(this).get(0).name] = false;
                                                    }
						});
					}
				} else {
					if(tCat == "element"){
                                                JQuery("[name=\"ff_nm_"+tName+"[]\"]").closest(".bfElemWrap").css("display","none");
						thisBfDeactivateField["ff_nm_"+tName+"[]"] = true;
                                                bfSetFieldValue(tName, "off");
					} else {
						JQuery("#"+tName).css("display", "none");
                                                bfSetFieldValue(tName, "off");
						JQuery("#"+tName+" .ff_elem").each(function(i){
                                                    if( JQuery(this).get(0).name && JQuery(this).get(0).name.beginsWith("ff_nm_", true) ){
                                                        thisBfDeactivateField[JQuery(this).get(0).name] = true;
                                                    }
						});
					}
				}
                                if(typeof bfRefreshAll != "undefined"){
                                    bfRefreshAll();
                                }
			}
                        function bfSetFieldValue(name, condition){
                            for( var i = 0; i < toggleFieldsArray.length; i++ ){
                                if( toggleFieldsArray[i].action == "if" ) {
                                    if(name == toggleFieldsArray[i].tCat && condition == toggleFieldsArray[i].statement){

                                        var element = JQuery("[name=\"ff_nm_"+toggleFieldsArray[i].condition+"[]\"]");
                                        
                                        switch(element.get(0).type){
                                            case "text":
                                            case "textarea":
                                                if(toggleFieldsArray[i].value == "!empty"){
                                                    element.val("");
                                                } else {
                                                    element.val(toggleFieldsArray[i].value);
                                                }
                                            break;
                                            case "select-multiple":
                                            case "select-one":
                                                if(toggleFieldsArray[i].value == "!empty"){
                                                    for(var j = 0; j < element.get(0).options.length; j++){
                                                        element.get(0).options[j].selected = false;
                                                    }
                                                }
                                                for(var j = 0; j < element.get(0).options.length; j++){
                                                    if(element.get(0).options[j].value == toggleFieldsArray[i].value){
                                                        element.get(0).options[j].selected = true;
                                                    }
                                                }
                                            break;
                                            case "radio":
                                            case "checkbox":
                                                var radioLength = element.size();
                                                if(toggleFieldsArray[i].value == "!empty"){
                                                    for(var j = 0; j < radioLength; j++){
                                                        element.get(j).checked = false;
                                                    }
                                                }
						for(var j = 0; j < radioLength; j++){
                                                    if( element.get(j).value == toggleFieldsArray[i].value ){
                                                        element.get(j).checked = true;
                                                    }
                                                }
                                            break;
                                        }
                                    }
                                }
                            }
                        }
			function bfRegisterToggleFields(){
                        
                                var offset = 0;
                                var last_offset = 0;
                                var limit  = 10;
                                var limit_cnt = 0;
                                
                                if( arguments.length == 1 ){
                                    offset = arguments[0];
                                }

                                var thisToggleFieldsArray = toggleFieldsArray;
				var thisBfDeactivateField = bfDeactivateField;
                                var thisBfToggleFields = bfToggleFields;
                                
				for( var i = offset; limit_cnt < limit && i < toggleFieldsArray.length; i++ ){
                                //  for( var i = 0; i < toggleFieldsArray.length; i++ ){
                                              if( toggleFieldsArray[i].action == "turn" && (toggleFieldsArray[i].tCat == "element" || toggleFieldsArray[i].tCat == "section") ){
						var toggleField = toggleFieldsArray[i];
						var element = JQuery("[name=\"ff_nm_"+toggleFieldsArray[i].sName+"[]\"]");
						if(element.get(0)){
							switch(element.get(0).type){
								case "text":
								case "textarea":
                                                                        JQuery("[name=\"ff_nm_"+toggleField.sName+"[]\"]").unbind("blur");
									JQuery("[name=\"ff_nm_"+toggleField.sName+"[]\"]").blur(
										function(){
											for( var k = 0; k < thisToggleFieldsArray.length; k++ ){
												var regExp = "";
                                                                                                var testRegExp = null;
												if(thisToggleFieldsArray[k].value.beginsWith("!", true) && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"){
										 			regExp = thisToggleFieldsArray[k].value.substring(1, thisToggleFieldsArray[k].value.length);
                                                                                                        testRegExp = new RegExp(regExp);
                                                                                                }

                                                                                                if(thisToggleFieldsArray[k].condition == "isnot"){
                                                                                                    if(
                                                                                                            ( ( regExp != "" && testRegExp.test(JQuery(this).val()) == false ) || JQuery(this).val() != thisToggleFieldsArray[k].value ) && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"
                                                                                                    ){
                                                                                                            var names = thisToggleFieldsArray[k].tName.split(",");
                                                                                                            for(var n = 0; n < names.length; n++){
                                                                                                                thisBfToggleFields(thisToggleFieldsArray[k].state, thisToggleFieldsArray[k].tCat, JQuery.trim(names[n]), thisBfDeactivateField);
                                                                                                            }
                                                                                                            //break;
                                                                                                    }
                                                                                                } else if(thisToggleFieldsArray[k].condition == "is"){
                                                                                                    if(
                                                                                                            ( ( regExp != "" && testRegExp.test(JQuery(this).val()) == true ) || JQuery(this).val() == thisToggleFieldsArray[k].value ) && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"
                                                                                                    ){
                                                                                                            var names = thisToggleFieldsArray[k].tName.split(",");
                                                                                                            for(var n = 0; n < names.length; n++){
                                                                                                                thisBfToggleFields(thisToggleFieldsArray[k].state, thisToggleFieldsArray[k].tCat, JQuery.trim(names[n]), thisBfDeactivateField);
                                                                                                            }
                                                                                                            //break;
                                                                                                    }
                                                                                                }
											}
										}
									);
									break;
								case "select-multiple":
								case "select-one":
                                                                        JQuery("[name=\"ff_nm_"+toggleField.sName+"[]\"]").unbind("change");
									JQuery("[name=\"ff_nm_"+toggleField.sName+"[]\"]").change(
										function(){
											var res = JQuery.isArray( JQuery(this).val() ) == false ? [ JQuery(this).val() ] : JQuery(this).val();
											for( var k = 0; k < thisToggleFieldsArray.length; k++ ){
												
												// The or-case in lists 
												var found = false;
												var chkGrpValues = new Array();
										 		if(thisToggleFieldsArray[k].value.beginsWith("#", true) && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"){
										 			chkGrpValues = thisToggleFieldsArray[k].value.substring(1, thisToggleFieldsArray[k].value.length).split("|");
										 			for(var l = 0; l < chkGrpValues.length; l++){
										 				if( JQuery.inArray(chkGrpValues[l], res) != -1 ){
										 					found = true;
										 					break;
										 				}
										 			}
										 		}
												// the and-case in lists
												var foundCount = 0;
												chkGrpValues2 = new Array();
										 		if(thisToggleFieldsArray[k].value.beginsWith("#", true) && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"){
										 			chkGrpValues2 = thisToggleFieldsArray[k].value.substring(1, thisToggleFieldsArray[k].value.length).split(";");
										 			for(var l = 0; l < res.length; l++){
										 				if( JQuery.inArray(res[l], chkGrpValues2) != -1 ){
										 					foundCount++;
										 				}
										 			}
										 		}
                                                                                                
                                                                                                if(thisToggleFieldsArray[k].condition == "isnot"){
                                                                                                
                                                                                                    if(
                                                                                                            (
                                                                                                                    !JQuery.isArray(res) && JQuery(this).val() != thisToggleFieldsArray[k].value && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"
                                                                                                            )
                                                                                                            ||
                                                                                                            (
                                                                                                                    JQuery.isArray(res) && ( JQuery.inArray(thisToggleFieldsArray[k].value, res) == -1 || !found || ( foundCount == 0 || foundCount != chkGrpValues2.length ) ) && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"
                                                                                                            )
                                                                                                     ){
                                                                                                            var names = thisToggleFieldsArray[k].tName.split(",");
                                                                                                            for(var n = 0; n < names.length; n++){
                                                                                                                thisBfToggleFields(thisToggleFieldsArray[k].state, thisToggleFieldsArray[k].tCat, JQuery.trim(names[n]), thisBfDeactivateField);
                                                                                                            }
                                                                                                            //break;
                                                                                                    }
                                                                                                } else if(thisToggleFieldsArray[k].condition == "is"){
                                                                                                    if(
                                                                                                            (
                                                                                                                    !JQuery.isArray(res) && JQuery(this).val() == thisToggleFieldsArray[k].value && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"
                                                                                                            )
                                                                                                            ||
                                                                                                            (
                                                                                                                    JQuery.isArray(res) && ( JQuery.inArray(thisToggleFieldsArray[k].value, res) != -1 || found || ( foundCount != 0 && foundCount == chkGrpValues2.length ) ) && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"
                                                                                                            )
                                                                                                     ){
                                                                                                            var names = thisToggleFieldsArray[k].tName.split(",");
                                                                                                            for(var n = 0; n < names.length; n++){
                                                                                                                thisBfToggleFields(thisToggleFieldsArray[k].state, thisToggleFieldsArray[k].tCat, JQuery.trim(names[n]), thisBfDeactivateField);
                                                                                                            }
                                                                                                            //break;
                                                                                                    }
                                                                                                }
											}
										}
									);
									break;
								case "radio":
								case "checkbox":
									var radioLength = JQuery("[name=\"ff_nm_"+toggleField.sName+"[]\"]").size();
									for(var j = 0; j < radioLength; j++){
                                                                                 JQuery("#" + JQuery("[name=\"ff_nm_"+toggleField.sName+"[]\"]").get(j).id ).unbind("click");
										 JQuery("#" + JQuery("[name=\"ff_nm_"+toggleField.sName+"[]\"]").get(j).id ).click(										 	
										 	function(){
										 		// NOT O(n^2) since its ony executed on click event!
                                                                                                var res = [];
                                                                                                JQuery("[name=\"ff_nm_"+toggleField.sName+"[]\"]:checked").each(function() {
                                                                                                  res.push(JQuery(this).val());
                                                                                                });
										 		for( var k = 0; k < thisToggleFieldsArray.length; k++ ){
										 			
										 			// used for complex checkbox group case below
										 			var chkGrpValues = new Array();
                                                                                                        var found = false;
										 			if(JQuery(this).get(0).checked && thisToggleFieldsArray[k].value.beginsWith("#", true) && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"){
										 				chkGrpValues = thisToggleFieldsArray[k].value.substring(1, thisToggleFieldsArray[k].value.length).split("|");
                                                                                                                for(var l = 0; l < chkGrpValues.length; l++){
                                                                                                                    if( JQuery.inArray(chkGrpValues[l], res) != -1 ){
                                                                                                                            found = true;
                                                                                                                            break;
                                                                                                                    }
                                                                                                                }
										 			}
                                                                                                        
                                                                                                        // the and-case
                                                                                                        var foundCount = 0;
                                                                                                        chkGrpValues2 = new Array();
                                                                                                        if(thisToggleFieldsArray[k].value.beginsWith("#", true) && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"){
                                                                                                                chkGrpValues2 = thisToggleFieldsArray[k].value.substring(1, thisToggleFieldsArray[k].value.length).split(";");
                                                                                                                for(var l = 0; l < res.length; l++){
                                                                                                                        if( JQuery.inArray(res[l], chkGrpValues2) != -1 ){
                                                                                                                                foundCount++;
                                                                                                                        }
                                                                                                                }
                                                                                                        }

                                                                                                        if(thisToggleFieldsArray[k].condition == "isnot"){

                                                                                                            if(
                                                                                                                    // simple radio case for selected value
                                                                                                                    ( JQuery(this).get(0).type == "radio" && JQuery(this).get(0).checked && JQuery(this).val() != thisToggleFieldsArray[k].value && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]" )
                                                                                                                    ||
                                                                                                                    // single checkbox case for checked/unchecked
                                                                                                                    (
                                                                                                                            JQuery(this).get(0).type == "checkbox" &&
                                                                                                                            JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]" &&
                                                                                                                            ( JQuery(this).get(0).checked && thisToggleFieldsArray[k].value != "!checked"
                                                                                                                             ||
                                                                                                                              JQuery(this).get(0).checked && thisToggleFieldsArray[k].value == "!unchecked"
                                                                                                                            )
                                                                                                                    )
                                                                                                                    ||
                                                                                                                    // complex checkbox/radio group case by multiple values
                                                                                                                    (
                                                                                                                            JQuery.isArray(res) && ( JQuery.inArray(thisToggleFieldsArray[k].value, res) == -1 || !found || ( foundCount == 0 || foundCount != chkGrpValues2.length ) ) && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"
                                                                                                                    )
                                                                                                                    ||
                                                                                                                    // simple checkbox group case by single value
                                                                                                                    (
                                                                                                                            JQuery(this).get(0).type == "checkbox" && JQuery(this).get(0).checked && JQuery(this).val() != thisToggleFieldsArray[k].value && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"
                                                                                                                    )
                                                                                                            ){
                                                                                                                    var names = thisToggleFieldsArray[k].tName.split(",");
                                                                                                                    for(var n = 0; n < names.length; n++){
                                                                                                                        thisBfToggleFields(thisToggleFieldsArray[k].state, thisToggleFieldsArray[k].tCat, JQuery.trim(names[n]), thisBfDeactivateField);
                                                                                                                    }
                                                                                                                    //break;
                                                                                                            }
                                                                                                        }
                                                                                                        else
                                                                                                        if(thisToggleFieldsArray[k].condition == "is"){
                                                                                                            if(
                                                                                                                    // simple radio case for selected value
                                                                                                                    ( JQuery(this).get(0).type == "radio" && JQuery(this).get(0).checked && JQuery(this).val() == thisToggleFieldsArray[k].value && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]" )
                                                                                                                    ||
                                                                                                                    // single checkbox case for checked/unchecked
                                                                                                                    (
                                                                                                                            JQuery(this).get(0).type == "checkbox" &&
                                                                                                                            JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]" &&
                                                                                                                            ( JQuery(this).get(0).checked && thisToggleFieldsArray[k].value == "!checked"
                                                                                                                             ||
                                                                                                                              !JQuery(this).get(0).checked && thisToggleFieldsArray[k].value == "!unchecked"
                                                                                                                            )
                                                                                                                    )
                                                                                                                    ||
                                                                                                                    // complex checkbox/radio group case by multiple values
                                                                                                                    (
                                                                                                                            JQuery.isArray(res) && ( JQuery.inArray(thisToggleFieldsArray[k].value, res) != -1 || found || ( foundCount != 0 && foundCount == chkGrpValues2.length ) ) && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"
                                                                                                                    )
                                                                                                                    ||
                                                                                                                    // simple checkbox group case by single value
                                                                                                                    (
                                                                                                                            JQuery(this).get(0).type == "checkbox" && JQuery(this).get(0).checked && JQuery(this).val() == thisToggleFieldsArray[k].value && JQuery(this).get(0).name == "ff_nm_"+thisToggleFieldsArray[k].sName+"[]"
                                                                                                                    )
                                                                                                            ){
                                                                                                                    var names = thisToggleFieldsArray[k].tName.split(",");
                                                                                                                    for(var n = 0; n < names.length; n++){
                                                                                                                        thisBfToggleFields(thisToggleFieldsArray[k].state, thisToggleFieldsArray[k].tCat, JQuery.trim(names[n]), thisBfDeactivateField);
                                                                                                                    }
                                                                                                                    //break;
                                                                                                            }
                                                                                                        }
												}
												
											}
										 );
									}
									break;
							}
						}
					}
                                        
                                        limit_cnt++;
                                        last_offset = i;
                                }
                                
                                if( last_offset+1 < toggleFieldsArray.length ){ setTimeout("bfRegisterToggleFields( "+last_offset+" )", 350); }
                        }';
			
		}
		
		JFactory::getDocument()->addScriptDeclaration(
                        $jQuery.'
			var inlineErrorElements = new Array();
			var bfSummarizers = new Array();
			var bfDeactivateField = new Array();
			var bfDeactivateSection = new Array();
			'.$toggleCode.'
                        
                        function bf_validate_nextpage(element, action)
                        {
                            if(typeof bfUseErrorAlerts != "undefined"){
                             JQuery(".bfErrorMessage").html("");
                             JQuery(".bfErrorMessage").css("display","none");
                            }

                            error = ff_validation(ff_currentpage);
                            if (error != "") {
                               if(typeof bfUseErrorAlerts == ""){
                                   alert(error);
                                } else {
                                   bfShowErrors(error);
                                }
                                ff_validationFocus("");
                            } else {
                                ff_switchpage(ff_currentpage+1);
                                self.scrollTo(0,0);   
                            }
                        } 


			function bfCheckMaxlength(id, maxlength, showMaxlength){
				if( JQuery("#ff_elem"+id).val().length > maxlength ){
					JQuery("#ff_elem"+id).val( JQuery("#ff_elem"+id).val().substring(0, maxlength) );
				}
				if(showMaxlength){
					JQuery("#bfMaxLengthCounter"+id).text( "(" + (maxlength - JQuery("#ff_elem"+id).val().length) + " '.BFText::_('COM_BREEZINGFORMS_CHARS_LEFT').')" );
				}
			}
			function bfRegisterSummarize(id, connectWith, type, emptyMessage, hideIfEmpty){
				bfSummarizers.push( { id : id, connectWith : connectWith, type : type, emptyMessage : emptyMessage, hideIfEmpty : hideIfEmpty } );
			}
			function bfField(name){
				var value = "";
				switch(ff_getElementByName(name).type){
					case "radio":
						if(JQuery("[name=\""+ff_getElementByName(name).name+"\"]:checked").val() != "" && typeof JQuery("[name=\""+ff_getElementByName(name).name+"\"]:checked").val() != "undefined"){
							value = JQuery("[name=\""+ff_getElementByName(name).name+"\"]:checked").val();
							if(!isNaN(value)){
								value = Number(value);
							}
						}
						break;
					case "checkbox":
					case "select-one":
					case "select-multiple":
						var nodeList = document["'.$this->p->form_id.'"][""+ff_getElementByName(name).name+""];
						if(ff_getElementByName(name).type == "checkbox" && typeof nodeList.length == "undefined"){
							if(typeof JQuery("[name=\""+ff_getElementByName(name).name+"\"]:checked").val() != "undefined"){
								value = JQuery("[name=\""+ff_getElementByName(name).name+"\"]:checked").val();
								if(!isNaN(value)){
									value = Number(value);
								}
							}
						} else {
							var val = "";
							for(var j = 0; j < nodeList.length; j++){
								if(nodeList[j].checked || nodeList[j].selected){
									val += nodeList[j].value + ", ";
								}
							}
							if(val != ""){
								value = val.substr(0, val.length - 2);
								if(!isNaN(value)){
									value = Number(value);
								}
							}
						}
						break;
					default:
						if(!isNaN(ff_getElementByName(name).value)){
							value = Number(ff_getElementByName(name).value);
						} else {
							value = ff_getElementByName(name).value;
						}
				}
				return value;
			}
			function populateSummarizers(){
				// cleaning first
                                
				for(var i = 0; i < bfSummarizers.length; i++){
					JQuery("#"+bfSummarizers[i].id).parent().css("display", "");
					JQuery("#"+bfSummarizers[i].id).html("<span class=\"bfNotAvailable\">"+bfSummarizers[i].emptyMessage+"</span>");
				}
				for(var i = 0; i < bfSummarizers.length; i++){
					var summVal = "";
					switch(bfSummarizers[i].type){
						case "bfTextfield":
						case "bfTextarea":
						case "bfHidden":
						case "bfCalendar":
                                                case "bfCalendarResponsive":
						case "bfFile":
							if(JQuery("[name=\"ff_nm_"+bfSummarizers[i].connectWith+"[]\"]").val() != ""){
								JQuery("#"+bfSummarizers[i].id).text( JQuery("[name=\"ff_nm_"+bfSummarizers[i].connectWith+"[]\"]").val() ).html();
								var breakableText = JQuery("#"+bfSummarizers[i].id).html().replace(/\\r/g, "").replace(/\\n/g, "<br/>");
								
								if(breakableText != ""){
									var calc = null;
									eval( "calc = typeof bfFieldCalc"+bfSummarizers[i].id+" != \"undefined\" ? bfFieldCalc"+bfSummarizers[i].id+" : null" );
									if(calc){
										breakableText = calc(breakableText);
									}
								}
								
								JQuery("#"+bfSummarizers[i].id).html(breakableText);
								summVal = breakableText;
							}
						break;
						case "bfRadioGroup":
						case "bfCheckbox":
							if(JQuery("[name=\"ff_nm_"+bfSummarizers[i].connectWith+"[]\"]:checked").val() != "" && typeof JQuery("[name=\"ff_nm_"+bfSummarizers[i].connectWith+"[]\"]:checked").val() != "undefined"){
								var theText = JQuery("[name=\"ff_nm_"+bfSummarizers[i].connectWith+"[]\"]:checked").val();
								if(theText != ""){
									var calc = null;
									eval( "calc = typeof bfFieldCalc"+bfSummarizers[i].id+" != \"undefined\" ? bfFieldCalc"+bfSummarizers[i].id+" : null" );
									if(calc){
										theText = calc(theText);
									}
								}
								JQuery("#"+bfSummarizers[i].id).text( theText );
								summVal = theText;
							}
						break;
						case "bfCheckboxGroup":
						case "bfSelect":
							var val = "";
							var nodeList = document["'.$this->p->form_id.'"]["ff_nm_"+bfSummarizers[i].connectWith+"[]"];
							
							for(var j = 0; j < nodeList.length; j++){
								if(nodeList[j].checked || nodeList[j].selected){
									val += nodeList[j].value + ", ";
								}
							}
							if(val != ""){
								var theText = val.substr(0, val.length - 2);
								if(theText != ""){
									var calc = null;
									eval( "calc = typeof bfFieldCalc"+bfSummarizers[i].id+" != \"undefined\" ? bfFieldCalc"+bfSummarizers[i].id+" : null" );
									if(calc){
										theText = calc(theText);
									}
								}
								JQuery("#"+bfSummarizers[i].id).text( theText );
								summVal = theText;
							}
						break;
					}
					
					if( ( bfSummarizers[i].hideIfEmpty && summVal == "" ) || ( typeof bfDeactivateField != "undefined" && bfDeactivateField["ff_nm_"+bfSummarizers[i].connectWith+"[]"] ) ){
                                            JQuery("#"+bfSummarizers[i].id).parent().css("display", "none");
					}
				}
			}
');
		
		if($this->fading || !$this->useErrorAlerts || $this->rollover){
			if(!$this->useErrorAlerts){
                                $defaultErrors = '';
                                if($this->useDefaultErrors || (!$this->useDefaultErrors && !$this->useBalloonErrors)){
                                    $defaultErrors = 'JQuery(".bfErrorMessage").html("");
					JQuery(".bfErrorMessage").css("display","none");
					JQuery(".bfErrorMessage").fadeIn(1500);
					var allErrors = "";
					var errors = error.split("\n");
					for(var i = 0; i < errors.length; i++){
						allErrors += "<div class=\"bfError\">" + errors[i] + "</div>";
					}
					JQuery(".bfErrorMessage").html(allErrors);
					JQuery(".bfErrorMessage").css("display","");';
                                }
				JFactory::getDocument()->addScriptDeclaration('var bfUseErrorAlerts = false;'."\n");
				JFactory::getDocument()->addScriptDeclaration('
				function bfShowErrors(error){
                                        '.$defaultErrors.'

                                        if(JQuery.bfvalidationEngine)
                                        {
                                            JQuery("#'.$this->p->form_id.'").bfvalidationEngine({
                                              promptPosition: "bottomLeft",
                                              success :  false,
                                              failure : function() {}
                                            });

                                            for(var i = 0; i < inlineErrorElements.length; i++)
                                            {
                                                if(inlineErrorElements[i][1] != "")
                                                {
                                                    var prompt = null;
                                                    
                                                    if(inlineErrorElements[i][0] == "bfCaptchaEntry"){
                                                        prompt = JQuery.bfvalidationEngine.buildPrompt("#bfCaptchaEntry",inlineErrorElements[i][1],"error");
                                                    }
                                                    else if(inlineErrorElements[i][0] == "bfReCaptchaEntry"){
                                                        // nothing here yet for recaptcha, alert is default
                                                        alert(inlineErrorElements[i][1]);
                                                    }
                                                    else if(typeof JQuery("#bfUploader"+inlineErrorElements[i][0]).get(0) != "undefined")
                                                    {
                                                        alert(inlineErrorElements[i][1]);
                                                        //prompt = JQuery.bfvalidationEngine.buildPrompt("#"+JQuery("#bfUploader"+inlineErrorElements[i][0]).val(),inlineErrorElements[i][1],"error");
                                                    }
                                                    else
                                                    {
                                                        if(ff_getElementByName(inlineErrorElements[i][0])){
                                                            prompt = JQuery.bfvalidationEngine.buildPrompt("#"+ff_getElementByName(inlineErrorElements[i][0]).id,inlineErrorElements[i][1],"error");
                                                        }else{
                                                            alert(inlineErrorElements[i][1]);
                                                        }
                                                    }
                                                    
                                                    JQuery(prompt).mouseover(
                                                        function(){
                                                            var inlineError = JQuery(this).attr("class").split(" ");
                                                            if(inlineError && inlineError.length && inlineError.length == 2){
                                                                var result = inlineError[1].split("formError");
                                                                if(result && result.length && result.length >= 1){
                                                                    JQuery.bfvalidationEngine.closePrompt("#"+result[0]);
                                                                }
                                                            }
                                                        }
                                                    );
                                                }
                                                else
                                                {
                                                    if(typeof JQuery("#bfUploader"+inlineErrorElements[i][0]).get(0) != "undefined")
                                                    {
                                                        //JQuery.bfvalidationEngine.closePrompt("#"+JQuery("#bfUploader"+inlineErrorElements[i][0]).val());
                                                    }
                                                    else
                                                    {
                                                        if(ff_getElementByName(inlineErrorElements[i][0])){
                                                            JQuery.bfvalidationEngine.closePrompt("#"+ff_getElementByName(inlineErrorElements[i][0]).id);
                                                        }
                                                    }
                                                }
                                            }
                                            inlineErrorElements = new Array();
                                        }
				}');
			}
			if($this->fading){
				$this->fadingClass = ' bfFadingClass';
				$this->fadingCall  = 'bfFade();';
				JFactory::getDocument()->addScriptDeclaration('
					function bfFade(){
						JQuery(".bfPageIntro").fadeIn(1000);
						var size = 0;
						JQuery(".bfFadingClass").each(function(i,val){
							var t = this;
							setTimeout(function(){JQuery(t).fadeIn(1000)}, (i*100));
							size = i;
						});
						setTimeout(\'JQuery(".bfSubmitButton").fadeIn(1000)\', size * 100);
						setTimeout(\'JQuery(".bfPrevButton").fadeIn(1000)\', size * 100);
						setTimeout(\'JQuery(".bfNextButton").fadeIn(1000)\', size * 100);
						setTimeout(\'JQuery(".bfCancelButton").fadeIn(1000)\', size * 100);
					}
				');
			}
                        
			if($this->rollover && trim($this->rolloverColor) != ''){
				// removed in bootstrap
			}
		}
		JFactory::getDocument()->addScriptDeclaration('
			JQuery(document).ready(function() {
				if(typeof bfFade != "undefined")bfFade();
				if(typeof bfRollover != "undefined")bfRollover();
				if(typeof bfRollover2 != "undefined")bfRollover2();
				if(typeof bfRegisterToggleFields != "undefined")bfRegisterToggleFields();
				if(typeof bfDeactivateSectionFields != "undefined")bfDeactivateSectionFields();
                                if(JQuery.bfvalidationEngine)
                                {
                                    JQuery.bfvalidationEngineLanguage.newLang();
                                    JQuery(".ff_elem").change(
                                        function(){
                                            JQuery.bfvalidationEngine.closePrompt(this);
                                        }
                                    );
                                }
				JQuery(".hasTip").css("color","inherit"); // fixing label text color issue
				JQuery(".bfTooltip").css("color","inherit"); // fixing label text color issue
    
                                JQuery("input[type=text]").bind("keypress", function(evt) {
                                    if(evt.keyCode == 13) {
                                        evt.preventDefault();
                                    }
                                });
			});
		');
                // loading system css
                
		if(method_exists($obj = JFactory::getDocument(), 'addCustomTag')){
                
                    jimport('joomla.version');
                    $version = new JVersion();
                    
                    // loading theme
                    if(isset($this->rootMdata['themebootstrap'])){
                        
                        if(version_compare($version->getShortVersion(), '3.0', '<') && isset($this->rootMdata['themeusebootstraplegacy']) && $this->rootMdata['themeusebootstraplegacy']){
                            $stylelink = '<link rel="stylesheet" href="'.JURI::root(true) . '/components/com_breezingforms/libraries/bootstrap/css/bootstrap'.($this->rootMdata['themebootstrap'] == '' ? '-simple' : '').'.css" />' ."\n";
                            JFactory::getDocument()->addCustomTag($stylelink);
                        }
                        
                        if(version_compare($version->getShortVersion(), '3.0', '<')){
                            $stylelink = '<link rel="stylesheet" href="'.JURI::root(true) . '/components/com_breezingforms/libraries/bootstrap/css/bootstrap-icons.css" />' ."\n";
                            JFactory::getDocument()->addCustomTag($stylelink);
                        }
                        
                        $stylelink = '<link rel="stylesheet" href="'.JURI::root(true) . '/components/com_breezingforms/themes/quickmode-bootstrap/system.css" />' ."\n";
                        JFactory::getDocument()->addCustomTag($stylelink);
                        
                        $vars = '';
                        $themecss = '';
                        $scriptjs = '';
                        $scriptphp = '';
                        
                        $themecss_path = JPATH_SITE .'/media/breezingforms/themes-bootstrap/'. $this->rootMdata['themebootstrap'].'/theme.css';
                        $vars_path = JPATH_SITE .'/media/breezingforms/themes-bootstrap/'. $this->rootMdata['themebootstrap'].'/vars.txt';
                        $scriptjs_path = JPATH_SITE .'/media/breezingforms/themes-bootstrap/'. $this->rootMdata['themebootstrap'].'/script.js';
                        $scriptphp_path = JPATH_SITE .'/media/breezingforms/themes-bootstrap/'. $this->rootMdata['themebootstrap'].'/script.php';
                        
                        if($this->rootMdata['themebootstrap'] != '' && $this->rootMdata['themebootstrap'] != 'none' && JFile::exists($themecss_path)){
                            
                            if(JFile::exists($vars_path)){   
                                $vars = JFile::read($vars_path);
                            }
                            if(JFile::exists($themecss_path)){   
                                $themecss = JFile::read($themecss_path);
                            }
                            if(JFile::exists($scriptphp_path)){   
                                require_once($scriptphp_path);
                            }
                            if(JFile::exists($scriptjs_path)){   
                                $scriptjs = JFile::read($scriptjs_path);
                            }
                            
                            $vars = str_replace("\r",'',$vars);
                            $vars = explode("\n",$vars);
                            foreach($vars As $var){
                                if(trim($var)){
                                    $keyvalue = explode('=',$var);
                                    if(count($keyvalue) == 2){
                                        $themecss = str_replace('{'.trim($keyvalue[0]).'}',trim($keyvalue[1]),$themecss);
                                    }
                                }
                            }
                            
                            $style = '<style type="text/css">/** BreezingForms Bootstap Theme '.strip_tags($this->rootMdata['themebootstrap']).' **/'."\n".$themecss."\n".'</style>' ."\n";
                            JFactory::getDocument()->addCustomTag($style);
                            if($scriptjs){
                                JFactory::getDocument()->addCustomTag('<script type="text/javascript">'."\n".$scriptjs."\n".'</script>');
                            }
                        }
                    }
                }
        }
        
	function __construct( HTML_facileFormsProcessor $p ){
                
                jimport('joomla.version');
                $version = new JVersion();
                
                if(version_compare($version->getShortVersion(), '2.5', '>=')){
                    $this->language_tag = JFactory::getLanguage()->getTag() != JFactory::getLanguage()->getDefault() ? JFactory::getLanguage()->getTag() : 'zz-ZZ';
                }
                
                JFactory::getDocument()->addScriptDeclaration('<!--');
                
                $this->p = $p;
		$this->dataObject = Zend_Json::decode( bf_b64dec($this->p->formrow->template_code) );
                
		$this->rootMdata = $this->dataObject['properties'];
                
                if(JRequest::getVar('ff_applic','') != 'mod_facileforms' && JRequest::getVar('ff_applic','') != 'plg_facileforms'){
                    /* translatables */
                    if(isset($this->rootMdata['title_translation'.$this->language_tag]) && $this->rootMdata['title_translation'.$this->language_tag] != ''){
                        $this->rootMdata['title'] = $this->rootMdata['title_translation'.$this->language_tag];
                        JFactory::getDocument()->setTitle($this->rootMdata['title']);
                    }
                    /* translatables end */
                }
                
                $this->fading = $this->rootMdata['fadeIn'];
		$this->useErrorAlerts = $this->rootMdata['useErrorAlerts'];
                $this->useDefaultErrors = isset($this->rootMdata['useDefaultErrors']) ? $this->rootMdata['useDefaultErrors'] : false;
                $this->useBalloonErrors = isset($this->rootMdata['useBalloonErrors']) ? $this->rootMdata['useBalloonErrors'] : false;
		$this->rollover = $this->rootMdata['rollover'];
		$this->rolloverColor = $this->rootMdata['rolloverColor'];
		$this->toggleFields = $this->parseToggleFields( isset($this->rootMdata['toggleFields']) ? $this->rootMdata['toggleFields'] : '[]' );
		
		mt_srand();
		$this->flashUploadTicket = md5( strtotime('now') .  mt_rand( 0, mt_getrandmax() ) );
                $this->cancelImagePath = JURI::root(true) . '/media/breezingforms/themes-bootstrap/cancel.png';
                $this->uploadImagePath = JURI::root(true) . '/media/breezingforms/themes-bootstrap/upload.png';
                if(isset($this->rootMdata['themebootstrap']) && @file_exists(JPATH_SITE .'/media/breezingforms/themes-bootstrap/'. $this->rootMdata['themebootstrap'].'/images/cancel.png')){
                        $this->cancelImagePath = JURI::root(true) . '/media/breezingforms/themes-bootstrap/'. $this->rootMdata['themebootstrap'].'/images/cancel.png';
                }
                if(isset($this->rootMdata['themebootstrap']) && @file_exists(JPATH_SITE .'/media/breezingforms/themes-bootstrap/'. $this->rootMdata['themebootstrap'].'/images/upload.png')){
                        $this->uploadImagePath = JURI::root(true) . '/media/breezingforms/themes-bootstrap/'. $this->rootMdata['themebootstrap'].'/images/upload.png';
                }
	}
	
	public function process(&$dataObject, $parent = null, $parentPage = null, $index = 0, $childrenLength = 0, $parentFull = null){
		if(isset($dataObject['attributes']) && isset($dataObject['properties']) ){
			
			$options = array('type' => 'normal', 'displayType' => 'breaks');
			if($parent != null && $parent['type'] == 'section'){
				$options['type'] = $parent['bfType'];
				$options['displayType'] = $parent['displayType'];
			}
			$parentInline = false;
			$class = ' class="'.$this->fadingClass.'"';
			if($options['displayType'] == 'inline'){
				$class = ' class="'.$this->fadingClass.'"';
                                $parentInline = true;
			}
                        
                        //determine non-elements and reduce them from the children total to determine the right span sizes
			$reduce = 0;
                        $parentFullChildrenLength = 0;
                        
                        if($parentFull != null && isset($parentFull['children'])){
                            $parentFullChildrenLength = count($parentFull['children']);
                            foreach($parentFull['children'] As $child){
                                if(!isset($child['properties']) || ( isset($child['properties']) && isset($child['properties']['bfType']) && $child['properties']['bfType'] == 'bfHidden' ) || (isset($child['properties']) && $child['properties']['type'] != 'element' && $child['properties']['type'] != 'section')){
                                    $reduce++;
                                }
                                //if(isset($child['properties']) && isset($child['properties']['off']) && $child['properties']['off'] && $child['properties']['type'] == 'section'){
                                //    $reduce++;
                                //}
                            }
                        }
                        
                        $span = '';
                        if(($parentFullChildrenLength-$reduce) > 0 && $parentInline){
                            switch(12/($parentFullChildrenLength-$reduce)){
                                case 6:   $span = ' span6'; break;
                                case 4:   $span = ' span4'; break;
                                case 3:   $span = ' span3'; break;
                                case 2.4: $span = ' span2'; break;
                                case 2:   $span = ' span2'; break;
                            }
                        }
                        
			$mdata = $dataObject['properties'];
			
			if($mdata['type'] == 'page'){
				
				$parentPage = $mdata;
				if($parentPage['pageNumber'] > 1){
					echo '</div><!-- bfPage end -->'."\n"; // closing previous pages
				}
                                
                                $display = ' style="display:none;"';
                                if(JRequest::getInt('ff_form_submitted',0) == 0 && JRequest::getInt('ff_page',1) == $parentPage['pageNumber']){
                                    $display = '';
                                } else if( JRequest::getInt('ff_form_submitted',0) == 1 && $this->rootMdata['lastPageThankYou'] && $parentPage['pageNumber'] == count($this->dataObject['children']) ){
                                    $display = '';
                                } else if(JRequest::getInt('ff_form_submitted',0) == 1 && false == $this->rootMdata['lastPageThankYou'] && $parentPage['pageNumber'] == 1){
                                    $display = '';
                                }
                                
				echo '<div id="bfPage'.$parentPage['pageNumber'].'" class="bfPage"'.$display.'>'."\n"; // opening current page
				
                                /* translatables */
                                if(isset($mdata['pageIntro_translation'.$this->language_tag]) && $mdata['pageIntro_translation'.$this->language_tag] != ''){
                                    $mdata['pageIntro'] = $mdata['pageIntro_translation'.$this->language_tag];
                                }
                                /* translatables end */
                                
				if(trim($mdata['pageIntro'])!=''){
					
                                        echo '<div class="'.(isset($this->rootMdata['themebootstrapUseHeroUnit']) && $this->rootMdata['themebootstrapUseHeroUnit'] ? 'hero-unit' : '').$this->fadingClass.'">'."\n";
                                        
                                        $regex		= '/{loadposition\s+(.*?)}/i';
                                        $introtext = $mdata['pageIntro'];
                                        
                                        preg_match_all($regex, $introtext, $matches, PREG_SET_ORDER);
                                        
                                        jimport('joomla.version');
                                        $version = new JVersion();
                                        
                                        if ($matches && version_compare($version->getShortVersion(), '1.6', '>=')) {
                                            
                                            $document	= JFactory::getDocument();
                                            $renderer	= $document->loadRenderer('modules');
                                            $options	= array('style' => 'xhtml');
                                            
                                            foreach ($matches as $match) {
                                        
                                                $matcheslist =  explode(',', $match[1]);
                                                $position = trim($matcheslist[0]);
                                                $output = $renderer->render($position, $options, null);
                                                $introtext = preg_replace("|$match[0]|", addcslashes($output, '\\'), $introtext, 1);
                                            }
                                        }
                                        
                                        echo $introtext."\n";
                                        
					echo '</div>'."\n";
				}
                                
                                if(!$this->useErrorAlerts){
					echo '<div class="bfErrorMessage alert alert-error" style="display:none"></div>'."\n";
				}
				
			} else if($mdata['type'] == 'section'){

				if(isset($dataObject['properties']['name']) && isset($mdata['off']) && $mdata['off']){
					echo '<script type="text/javascript"><!--'."\n".'bfDeactivateSection.push("'.$dataObject['properties']['name'].'");'."\n".'//--></script>'."\n";
				}
				
                                /* translatables */
                                if(isset($mdata['title_translation'.$this->language_tag]) && $mdata['title_translation'.$this->language_tag] != ''){
                                    $mdata['title'] = $mdata['title_translation'.$this->language_tag];
                                }
                                /* translatables end */
                                
				if($mdata['bfType'] == 'section'){
                                        echo '<fieldset'.(isset($mdata['off']) && $mdata['off'] ? ' style="display:none" ' : '').''.(isset($mdata['off']) && $mdata['off'] ? '' : ' class="'.$span.' '.$this->fadingClass.'"').(isset($dataObject['properties']['name']) && $dataObject['properties']['name'] != "" ? ' id="'.$dataObject['properties']['name'].'"' : '').'>'."\n";
					
                                        if(trim($mdata['title']) != ''){
						echo '<legend>'.htmlentities(trim($mdata['title']), ENT_QUOTES, 'UTF-8').'</legend>'."\n";
					}
                                        
                                        echo '<div class="row-fluid"><i style="display:none;"></i>';
				} 
				else if( $mdata['bfType'] == 'normal' ) {
					if(isset($dataObject['properties']['name']) && $dataObject['properties']['name'] != ''){
						echo '<section '.(isset($mdata['off']) && $mdata['off'] ? 'style="display:none" ' : ' class="'.$span.' '.$this->fadingClass.'"').(isset($dataObject['properties']['name']) && $dataObject['properties']['name'] != "" ? ' id="'.$dataObject['properties']['name'].'"' : '').'>'."\n";
                                                echo '<div class="row-fluid"><i style="display:none;"></i>';
                                        }
				}
				
                                /* translatables */
                                if(isset($mdata['description_translation'.$this->language_tag]) && $mdata['description_translation'.$this->language_tag] != ''){
                                    $mdata['description'] = $mdata['description_translation'.$this->language_tag];
                                }
                                /* translatables end */
                                
				if(trim($mdata['description'])!=''){
					echo '<div class="bfSectionDescription'.(isset($this->rootMdata['themebootstrapUseWell']) && $this->rootMdata['themebootstrapUseWell'] ? ' well well-small' : '').'">'."\n";
                                        
                                        $regex		= '/{loadposition\s+(.*?)}/i';
                                        $introtext = $mdata['description'];
                                        
                                        preg_match_all($regex, $introtext, $matches, PREG_SET_ORDER);
                                        
                                        jimport('joomla.version');
                                        $version = new JVersion();
                                        
                                        if ($matches && version_compare($version->getShortVersion(), '1.6', '>=')) {
                                            
                                            $document	= JFactory::getDocument();
                                            $renderer	= $document->loadRenderer('modules');
                                            $options	= array('style' => 'xhtml');
                                            
                                            foreach ($matches as $match) {
                                        
                                                $matcheslist =  explode(',', $match[1]);
                                                $position = trim($matcheslist[0]);
                                                $output = $renderer->render($position, $options, null);
                                                $introtext = preg_replace("|$match[0]|", addcslashes($output, '\\'), $introtext, 1);
                                            }
                                        }
                                        
					echo $introtext."\n";
					echo '</div>'."\n";
				}
				
			} else if($mdata['type'] == 'element'){
				
				$onclick = '';
				if($mdata['actionClick'] == 1){
					$onclick = 'onclick="'.$mdata['actionFunctionName'] . '(this,\'click\');" ';	
				}
				
				$onblur = '';
				if($mdata['actionBlur'] == 1){
					$onblur = 'onblur="'.$mdata['actionFunctionName'] . '(this,\'blur\');" ';	
				}
				
				$onchange = '';
				if($mdata['actionChange'] == 1){
					$onchange = 'onchange="'.$mdata['actionFunctionName'] . '(this,\'change\');" ';	
				}
				
				$onfocus = '';
				if($mdata['actionFocus'] == 1){
					$onfocus = 'onfocus="'.$mdata['actionFunctionName'] . '(this,\'focus\');" ';	
				}
				
				$onselect = '';
				if(isset($mdata['actionSelect']) && $mdata['actionSelect'] == 1){
					$onselect = 'onselect="'.$mdata['actionFunctionName'] . '(this,\'select\');" ';	
				}
				
				if($mdata['bfType'] != 'bfHidden'){
					echo '<div '.(isset($mdata['off']) && $mdata['off'] ? 'style="display:none" ' : '').'class="bfElemWrap control-group'.$span.(isset($mdata['off']) && $mdata['off'] ? '' : $this->fadingClass).'" id="bfElemWrap'.$mdata['dbId'].'">'."\n";
					
				}
				
                                $label = '';
				if(!$mdata['hideLabel']){
					
					$maxlengthCounter = '';
					if($mdata['bfType'] == 'bfTextarea' && isset($mdata['maxlength']) && $mdata['maxlength'] > 0 && isset($mdata['showMaxlengthCounter']) && $mdata['showMaxlengthCounter']){
						$maxlengthCounter = ' <span class=***bfMaxLengthCounter*** id=***bfMaxLengthCounter'.$mdata['dbId'].'***>('.$mdata['maxlength'].' '.BFText::_('COM_BREEZINGFORMS_CHARS_LEFT').')</span>';
					}
                                        
                                        /* translatables */
                                        if(isset($mdata['label_translation'.$this->language_tag]) && $mdata['label_translation'.$this->language_tag] != ''){
                                            $mdata['label'] = $mdata['label_translation'.$this->language_tag];
                                        }
                                        if(isset($mdata['hint_translation'.$this->language_tag]) && $mdata['hint_translation'.$this->language_tag] != ''){
                                            $mdata['hint'] = $mdata['hint_translation'.$this->language_tag];
                                        }
                                        /* translatables end */
					
					$tipOpen  = '';
					$tipClose = '';
					$labelText = trim($mdata['label']) . str_replace("***","\"",$maxlengthCounter);
					if(trim($mdata['hint']) != ''){
                                            jimport('joomla.version');
                                            $version = new JVersion();
                                            if( version_compare($version->getShortVersion(), '3.0', '<') || (  version_compare($version->getShortVersion(), '3.0', '>=') && isset($this->rootMdata['joomlaHint']) && $this->rootMdata['joomlaHint'] )  ){
                                                JHTML::_('behavior.tooltip');
                                                $content = trim($mdata['hint']);
						$tipOpen   = '<span title="'.addslashes(trim($mdata['label'])).'::'.str_replace( array("\n","\r"), array("",""), htmlentities($content, ENT_QUOTES, 'UTF-8')).'" class="editlinktip hasTip">';
						$tipClose  = '</span>';
                                            } else {
						$content = trim($mdata['hint']);
                                                // compat
                                                $explodeHint = explode('<<<style',trim($mdata['hint']));
                                                if(count($explodeHint) > 1 && trim($explodeHint[0]) != ''){
                                                    $content = trim($explodeHint[1]);
                                                }
                                                $tipOpen   = '<span class="hasTooltip" title="'.JHtml::tooltipText($content).'">';
						$tipClose  = '</span>';
                                            }
                                        }
                                        
                                        if($tipOpen){
                                            $tipOpen = $tipOpen.'<i class="icon-question-sign">&nbsp;</i> ';
                                        }
                                        
                                        $for = '';
                                        if( $mdata['bfType'] == 'bfTextfield' ||
                                            $mdata['bfType'] == 'bfTextarea' ||
                                            $mdata['bfType'] == 'bfCheckbox' ||
                                            $mdata['bfType'] == 'bfCheckboxGroup' ||
                                            $mdata['bfType'] == 'bfCalendar' ||
                                            $mdata['bfType'] == 'bfCalendarResponsive' ||
                                            $mdata['bfType'] == 'bfSelect' ||
                                            $mdata['bfType'] == 'bfRadioGroup' || 
                                            ( $mdata['bfType'] == 'bfFile' && ( ( !isset( $mdata['flashUploader'] ) && !isset( $mdata['html5']) ) || ( isset( $mdata['flashUploader'] ) && !$mdata['flashUploader'] ) && ( isset( $mdata['html5']) && !$mdata['html5'] ) ) ) ){
                                            $for = 'for="ff_elem'.$mdata['dbId'].'"';
                                        }
                                        
                                        if($mdata['bfType'] == 'bfCaptcha'){
                                            $for = 'for="bfCaptchaEntry"';
                                        }
                                        else if($mdata['bfType'] == 'bfReCaptcha'){
                                            $for = 'for="recaptcha_response_field"';
                                        }
                                        $required = '';
                                        if($mdata['required']){
                                            $required = ' <i class="icon-asterisk"></i> '."\n";
                                        }
					$label = '<label class="control-label'.(isset($this->rootMdata['themebootstrapLabelTop']) && $this->rootMdata['themebootstrapLabelTop'] ? ' bfLabelBlock' : '').'" id="bfLabel'.$mdata['dbId'].'" '.$for.'>'.$tipOpen.str_replace("***","\"",$labelText).$tipClose.$required.'</label>'."\n";
				}
				
				$readonly = '';
				if($mdata['readonly']){
					$readonly = 'readonly="readonly" ';
				}
				
				$tabIndex = '';
				if($mdata['tabIndex'] != -1 && is_numeric($mdata['tabIndex'])){
					$tabIndex = 'tabindex="'.intval($mdata['tabIndex']).'" ';
				}
			
				for($i = 0; $i < $this->p->rowcount; $i++) {
					$row = $this->p->rows[$i];
					if($mdata['bfName'] == $row->name){
						if( ( isset($mdata['value']) || isset($mdata['list']) || isset($mdata['group']))
							&& 
							( 
								$mdata['bfType'] == 'bfTextfield' ||
								$mdata['bfType'] == 'bfTextarea' ||
								$mdata['bfType'] == 'bfCheckbox' ||
								$mdata['bfType'] == 'bfCheckboxGroup' ||
								$mdata['bfType'] == 'bfSubmitButton' ||
								$mdata['bfType'] == 'bfHidden' ||
								$mdata['bfType'] == 'bfCalendar' ||
                                                                $mdata['bfType'] == 'bfCalendarResponsive' ||
								$mdata['bfType'] == 'bfSelect' ||
								$mdata['bfType'] == 'bfRadioGroup'
							)
						){
							if($mdata['bfType'] == 'bfSelect')
							{
								$mdata['list'] = $this->p->replaceCode($row->data2, "data2 of " . $mdata['bfName'], 'e', $mdata['dbId'], 0);
							} 
							else if($mdata['bfType'] == 'bfCheckboxGroup' || $mdata['bfType'] == 'bfRadioGroup')
							{
								$mdata['group'] = $this->p->replaceCode($row->data2, "data2 of " . $mdata['bfName'], 'e', $mdata['dbId'], 0);
							} 
							else
							{
								$mdata['value'] = $this->p->replaceCode($row->data1, "data1 of " . $mdata['bfName'], 'e', $mdata['dbId'], 0);	
							}
						}
						if(isset($mdata['checked']) && $mdata['bfType'] == 'bfCheckbox'){
							$mdata['checked'] = $row->flag1 == 1 ? true : false;
						}
						break;
					}
				}
				
				switch($mdata['bfType']){
					
					case 'bfTextfield':
						$type = 'text';
						
						if($mdata['password']){
							$type = 'password';
						}
						$maxlength = '';
						if(is_numeric($mdata['maxLength'])){
							$maxlength = 'maxlength="'.intval($mdata['maxLength']).'" ';
						}
						$size = '';
						if($mdata['size']!=''){
							$size = 'style="width:'.htmlentities(strip_tags($mdata['size'])).' !important; min-width:'.htmlentities(strip_tags($mdata['size'])).' !important;" ';
						}
                                                
                                                /* translatables */
                                                if(isset($mdata['value_translation'.$this->language_tag]) && $mdata['value_translation'.$this->language_tag] != ''){
                                                    $mdata['value'] = $mdata['value_translation'.$this->language_tag];
                                                }
                                                
                                                if(isset($mdata['placeholder_translation'.$this->language_tag]) && $mdata['placeholder_translation'.$this->language_tag] != ''){
                                                    $mdata['placeholder'] = $mdata['placeholder_translation'.$this->language_tag];
                                                }
                                                /* translatables end */
                                                
						echo '<div class="controls form-inline">';
                                                echo $label;
						echo '<input '.(isset($mdata['placeholder']) && $mdata['placeholder'] ? 'placeholder="'.htmlentities($mdata['placeholder'], ENT_QUOTES, 'UTF-8').'" ' : '').'class="ff_elem inputbox" '.$size.$tabIndex.$maxlength.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'type="'.$type.'" name="ff_nm_'.$mdata['bfName'].'[]" value="'.htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8').'" id="ff_elem'.$mdata['dbId'].'"/>'."\n";
						echo '</div>';
                                                if($mdata['mailbackAsSender']){
							echo '<input type="hidden" name="mailbackSender['.$mdata['bfName'].']" value="true"/>'."\n";
						}
						
						break;
						
					case 'bfTextarea':
						
						$width = '';
						if($mdata['width']!=''){
							$width = 'width:'.htmlentities(strip_tags($mdata['width'])).' !important; min-width:'.htmlentities(strip_tags($mdata['width'])).' !important;';
						}
						$height = '';
						if($mdata['height']!=''){
							$height = 'height:'.htmlentities(strip_tags($mdata['height'])).';';
						}
						$size = '';
						if($height != '' || $width != ''){
							$size = 'style="'.$width.$height.'" ';
						}
						$onkeyup = '';
						if(isset($mdata['maxlength']) && $mdata['maxlength'] > 0){
							$onkeyup = 'onkeyup="bfCheckMaxlength('.intval($mdata['dbId']).', '.intval($mdata['maxlength']).', '.(isset($mdata['showMaxlengthCounter']) && $mdata['showMaxlengthCounter'] ? 'true' : 'false').')" ';	
						}
                                                
                                                /* translatables */
                                                if(isset($mdata['placeholder_translation'.$this->language_tag]) && $mdata['placeholder_translation'.$this->language_tag] != ''){
                                                    $mdata['placeholder'] = $mdata['placeholder_translation'.$this->language_tag];
                                                }
                                                if(isset($mdata['value_translation'.$this->language_tag]) && $mdata['value_translation'.$this->language_tag] != ''){
                                                    $mdata['value'] = $mdata['value_translation'.$this->language_tag];
                                                }
                                                /* translatables end */
                                                
                                                echo '<div class="controls form-inline">';
                                                echo $label;
                                                if( isset($mdata['is_html']) && $mdata['is_html'] ){
                                                    echo '<div style="display: inline-block; vertical-align: top; width: '.strip_tags($mdata['width']).';">';
                                                    JImport( 'joomla.html.editor' );
                                                    $editor = JFactory::getEditor();
                                                    $this->htmltextareas[] = 'ff_nm_'.$mdata['bfName'].'[]';
                                                    echo $editor->display('ff_nm_'.$mdata['bfName'].'[]',htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8'), strip_tags($mdata['width']), strip_tags($mdata['height']), '75', '20');
                                                    echo '</div>';
                                                    echo '<style type="text/css">.toggle-editor{display: none;}</style>';
                                                } else {
                                                    echo '<textarea '.(isset($mdata['placeholder']) && $mdata['placeholder'] ? 'placeholder="'.htmlentities($mdata['placeholder'], ENT_QUOTES, 'UTF-8').'" ' : '').'cols="20" rows="5" class="ff_elem inputbox" '.$onkeyup.$size.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'name="ff_nm_'.$mdata['bfName'].'[]" id="ff_elem'.$mdata['dbId'].'">'.htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8').'</textarea>'."\n";
						}
                                                echo '</div>';
						break;
						
					case 'bfRadioGroup':
						
                                                /* translatables */
                                                if(isset($mdata['group_translation'.$this->language_tag]) && $mdata['group_translation'.$this->language_tag] != ''){
                                                    $mdata['group'] = $mdata['group_translation'.$this->language_tag];
                                                }
                                                /* translatables end */
                                            
						if($mdata['group'] != ''){
                                                        
                                                        echo '<div class="controls form-inline">';
                                                        echo $label;
                                                        if($mdata['wrap']){
                                                            echo '<div style="display: inline-block; vertical-align: top;">';
                                                        }
							$mdata['group'] = str_replace("\r", '', $mdata['group']);
							$gEx = explode("\n", $mdata['group']);
							$lines = count($gEx);
							for($i = 0; $i < $lines; $i++){
								$idExt = $i != 0 ? '_'.$i : '';
								$iEx = explode(";", $gEx[$i]);
								$iCnt = count($iEx);
								if($iCnt == 3){
									echo '<label '.($mdata['wrap'] ? 'style="display: block;" ' : 'style="vertical-align: baseline;" ').'class="radio'.(!$mdata['wrap'] ? ' inline ' : '').'" id="bfGroupLabel'.$mdata['dbId'].$idExt.'" for="ff_elem'.$mdata['dbId'].$idExt.'">';
									echo '<input '.($iEx[0] == 1 ? 'checked="checked" ' : '').' class="ff_elem" '.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.($readonly ? ' disabled="disabled" ' : '').'type="radio" name="ff_nm_'.$mdata['bfName'].'[]" value="'.htmlentities(trim($iEx[2]), ENT_QUOTES, 'UTF-8').'" id="ff_elem'.$mdata['dbId'].$idExt.'"/>'."\n";
									echo trim($iEx[1]).'</label>'.($i + 1 < $lines && $mdata['wrap'] ? '<div style="clear:both;"></div>' : '');
								}
							}
                                                        if($mdata['wrap']){
                                                            echo '</div>';
                                                        }
                                                        echo '</div>';
						}
						
						break;
						
					case 'bfCheckboxGroup':
						/* translatables */
                                                if(isset($mdata['group_translation'.$this->language_tag]) && $mdata['group_translation'.$this->language_tag] != ''){
                                                    $mdata['group'] = $mdata['group_translation'.$this->language_tag];
                                                }
                                                /* translatables end */
						if($mdata['group'] != ''){
							echo '<div class="controls form-inline">';
                                                        echo $label;
                                                        if($mdata['wrap']){
                                                            echo '<div style="display: inline-block; vertical-align: top;">';
                                                        }
                                                        $mdata['group'] = str_replace("\r", '', $mdata['group']);
							$gEx = explode("\n", $mdata['group']);
							$lines = count($gEx);
							
							for($i = 0; $i < $lines; $i++){
								$idExt = $i != 0 ? '_'.$i : '';
								$iEx = explode(";", $gEx[$i]);
								$iCnt = count($iEx);
								if($iCnt == 3){
									echo '<label '.($mdata['wrap'] ? 'style="display: block;" ' : 'style="vertical-align: baseline;" ').'class="checkbox'.(!$mdata['wrap'] ? ' inline ' : '').'" id="bfGroupLabel'.$mdata['dbId'].$idExt.'" for="ff_elem'.$mdata['dbId'].$idExt.'">';
									echo '<input '.($iEx[0] == 1 ? 'checked="checked" ' : '').' class="ff_elem" '.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.($readonly ? ' disabled="disabled" ' : '').'type="checkbox" name="ff_nm_'.$mdata['bfName'].'[]" value="'.htmlentities(trim($iEx[2]), ENT_QUOTES, 'UTF-8').'" id="ff_elem'.$mdata['dbId'].$idExt.'"/>'."\n";
									echo trim($iEx[1]).'</label>'.($i + 1 < $lines && $mdata['wrap'] ? '<div style="clear:both;"></div>' : '');
								}
							}
                                                        if($mdata['wrap']){
                                                            echo '</div>';
                                                        }
                                                        echo '</div>';
						}
						
						break;
					
					case 'bfCheckbox':
						echo '<div class="controls form-inline">';
                                                echo $label;
						echo '<input style="vertical-align: baseline;" class="ff_elem" '.($mdata['checked'] ? 'checked="checked" ' : '').$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.($readonly ? ' disabled="disabled" ' : '').'type="checkbox" name="ff_nm_'.$mdata['bfName'].'[]" value="'.htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8').'" id="ff_elem'.$mdata['dbId'].'"/>'."\n";
						echo '</div>';
                                                if($mdata['mailbackAccept']){
							echo '<input type="hidden" class="ff_elem" name="mailbackConnectWith['.$mdata['mailbackConnectWith'].']" value="true_'.$mdata['bfName'].'"/>'."\n";
						}
						
						break;
						
					case 'bfSelect':
						/* translatables */
                                                if(isset($mdata['list_translation'.$this->language_tag]) && $mdata['list_translation'.$this->language_tag] != ''){
                                                    $mdata['list'] = $mdata['list_translation'.$this->language_tag];
                                                }
                                                /* translatables end */
						if($mdata['list'] != ''){
							
							$width = '';
							if(isset($mdata['width']) && $mdata['width']!=''){
								$width = 'width:'.htmlentities(strip_tags($mdata['width'])).' !important; min-width:'.htmlentities(strip_tags($mdata['width'])).' !important;';
							}
							$height = '';
							if(isset($mdata['height']) && $mdata['height']!=''){
								$height = 'height:'.htmlentities(strip_tags($mdata['height'])).';';
							}
							$size = '';
							if($height != '' || $width != ''){
								$size = 'style="'.$width.$height.'" ';
							}
							
							$mdata['list'] = str_replace("\r", '', $mdata['list']);
							$gEx = explode("\n", $mdata['list']);
							$lines = count($gEx);
                                                        echo '<div class="controls form-inline">';
                                                        echo $label;
							echo '<select data-chosen="no-chzn" class="ff_elem chzn-done" '.$size.($mdata['multiple'] ? 'multiple="multiple" ' : '').$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'name="ff_nm_'.$mdata['bfName'].'[]" id="ff_elem'.$mdata['dbId'].'">'."\n";
							for($i = 0; $i < $lines; $i++){
								$iEx = explode(";", $gEx[$i]);
								$iCnt = count($iEx);
								if($iCnt == 3){
									echo '<option '.($iEx[0] == 1 ? 'selected="selected" ' : '').'value="'.htmlentities(trim($iEx[2]), ENT_QUOTES, 'UTF-8').'">'.htmlentities(trim($iEx[1]), ENT_QUOTES, 'UTF-8').'</option>'."\n";
								}
							}
							echo '</select>'."\n";
                                                        echo '</div>';
						}
						
						break;
						
					case 'bfFile':
                                                echo '<div class="controls form-inline">';
                                                echo $label;
						if( ( isset( $mdata['flashUploader'] ) && $mdata['flashUploader'] ) || ( isset( $mdata['html5'] ) && $mdata['html5'] ) ){
                                                        
                                                        $base = explode('/',JURI::base());
                                                        if(isset($base[count($base)-2]) && $base[count($base)-2] == 'administrator'){
                                                            unset($base[count($base)-2]);
                                                            $base = array_merge($base);
                                                        }
                                                        $base = implode('/', $base);
                                                        
                                                        echo '<input type="hidden" id="flashUpload'.$mdata['bfName'].'" name="flashUpload'.$mdata['bfName'].'" value="bfFlashFileQueue'.$mdata['dbId'].'"/>'."\n";
							$this->hasFlashUpload = true;
							//allowedFileExtensions
							$allowedExts = explode(',',$mdata['allowedFileExtensions']);
							$allowedExtsCnt = count($allowedExts);
							for($i = 0; $i < $allowedExtsCnt;$i++){
								$allowedExts[$i] = $allowedExts[$i];
							}
							$exts = '';
							if($allowedExtsCnt != 0){
								$exts = implode(',',$allowedExts);
							}
                                                        $bytes = (isset($mdata['flashUploaderBytes']) && is_numeric($mdata['flashUploaderBytes']) && $mdata['flashUploaderBytes'] > 0 ? "max_file_size : '" . intval($mdata['flashUploaderBytes']) ."'," : '');
							echo "
                                                        <span id=\"bfUploadContainer".$mdata['dbId']."\">
                                                            <button type=\"button\" class=\"btn bfUploadButton button\" id=\"bfPickFiles".$mdata['dbId']."\"><i class=\"icon-upload\"></i></button>
							</span>
                                                        <span id=\"bfUploader".$mdata['bfName']."\"></span>
                                                        <div class=\"bfFlashFileQueueClass\" id=\"bfFlashFileQueue".$mdata['dbId']."\"></div>
                                                        <script type=\"text/javascript\">
                                                        <!--
							bfFlashUploaders.push('ff_elem".$mdata['dbId']."');
                                                        var bfFlashFileQueue".$mdata['dbId']." = {};
                                                        function bfUploadImageThumb(file) {
                                                                var img;
                                                                img = new ctplupload.Image;
                                                                img.onload = function() {
                                                                        img.embed(JQuery('#' + file.id+'thumb').get(0), { 
                                                                                width: 100, 
                                                                                height: 60, 
                                                                                crop: true,
                                                                                swf_url: mOxie.resolveUrl('".$base."components/com_breezingforms/libraries/jquery/plupload/Moxie.swf')
                                                                        });
                                                                };

                                                                img.onembedded = function() {
                                                                        img.destroy();
                                                                };

                                                                img.onerror = function() {
                                                                        
                                                                };
                                                                
                                                                img.load(file.getSource());
                                                                
                                                        }
                                                        JQuery(document).ready(
                                                            function() {
                                                                var iOS = ( navigator.userAgent.match(/(iPad|iPhone|iPod)/i) ? true : false );
                                                                var uploader = new plupload.Uploader({
                                                                        multi_selection: ".( isset($mdata['flashUploaderMulti']) && $mdata['flashUploaderMulti'] ? 'true' : 'false' ).",
                                                                        unique_names: iOS,
                                                                        chunk_size: '100kb',
                                                                        runtimes : '".( isset( $mdata['html5'] ) && $mdata['html5'] ? 'html5,' : '' ).( isset( $mdata['flashUploader'] ) && $mdata['flashUploader'] ? 'flash,' : '')."html4',
                                                                        browse_button : 'bfPickFiles".$mdata['dbId']."',
                                                                        container: 'bfUploadContainer".$mdata['dbId']."',
                                                                        file_data_name: 'Filedata',
                                                                        multipart_params: { form: ".$this->p->form.", itemName : '".$mdata['bfName']."', bfFlashUploadTicket: '".$this->flashUploadTicket."', option: 'com_breezingforms', format: 'html', flashUpload: 'true', Itemid: 0 },
                                                                        url : '".$base.(BFJoomlaConfig::get('config.sef') && !BFJoomlaConfig::get('config.sef_rewrite') ? 'index.php/' : '').(JRequest::getCmd('lang','') && BFJoomlaConfig::get('config.sef') ? JRequest::getCmd('lang','') . ( BFJoomlaConfig::get('config.sef_rewrite') ? 'index.php' : '' ) : 'index.php')."',
                                                                        flash_swf_url : '".$base."components/com_breezingforms/libraries/jquery/plupload/Moxie.swf',
                                                                        filters : [
                                                                                {title : '".addslashes(BFText::_('COM_BREEZINGFORMS_CHOOSE_FILE'))."', extensions : '".$exts."'}
                                                                        ]
                                                                });
                                                                uploader.bind('FilesAdded', function(up, files) {
                                                                
                                                                        for (var i in files) {
                                                                                if(typeof files[i].id != 'undefined' && files[i].id != null){
                                                                                    var fsize = '';
                                                                                    if(typeof files[i].size != 'undefined'){
                                                                                        fsize = '(' + plupload.formatSize(files[i].size) + ') ';
                                                                                    }
                                                                                    JQuery('#bfFileQueue').append( '<div id=\"' + files[i].id + 'queue\">' + (iOS ? '' : files[i].name) + ' '+fsize+'<b></b></div>' );
                                                                                }
                                                                        }
                                                                        for (var i in files) {
                                                                            if(typeof files[i].id != 'undefined' && files[i].id != null){
                                                                                var error = false;
                                                                                var fsize = '';
                                                                                if(typeof files[i].size != 'undefined'){
                                                                                    fsize = '(' + plupload.formatSize(files[i].size) + ') ';
                                                                                }
                                                                                JQuery('#bfFlashFileQueue".$mdata['dbId']."').append('<div class=\"bfFileQueueItem\" id=\"' + files[i].id + 'queueitem\"><div id=\"' + files[i].id + 'thumb\"></div><div id=\"' + files[i].id + '\"><img id=\"' + files[i].id + 'cancel\" src=\"".$this->cancelImagePath."\" style=\"cursor: pointer; padding-right: 10px;\" />' + (iOS ? '' : files[i].name ? files[i].name.substring(0,12) : '') + ' ' + fsize + '<b id=\"' + files[i].id + 'msg\" style=\"color:red;\"></b></div></div>');
                                                                                var file_ = files[i];
                                                                                var uploader_ = uploader;
                                                                                var bfUploaders_ = bfUploaders;
                                                                                JQuery('#' + files[i].id + 'cancel').click( 
                                                                                    function(){
                                                                                        for( var i = 0; i < bfUploaders_.length; i++ ){
                                                                                            bfUploaders_[i].stop();
                                                                                        }
                                                                                        var id_ = this.id.split('cancel');
                                                                                        id_ = id_[0];
                                                                                        uploader_.removeFileById(id_);
                                                                                        JQuery('#'+id_+'queue').remove();
                                                                                        JQuery('#'+id_+'queueitem').remove();
                                                                                        bfFlashUploadersLength--;
                                                                                        for( var i = 0; i < bfUploaders_.length; i++ ){
                                                                                            bfUploaders_[i].start();
                                                                                        }
                                                                                        // re-enable button if there is none left
                                                                                        if( ".( isset($mdata['flashUploaderMulti']) && $mdata['flashUploaderMulti'] ? 'true' : 'false' )." == false ){
                                                                                            var the_size = JQuery('#bfFlashFileQueue".$mdata['dbId']." .bfFileQueueItem').size();
                                                                                            if( the_size == 0 ){
                                                                                                JQuery('#bfPickFiles".$mdata['dbId']."').prop('disabled',false);
                                                                                            }
                                                                                        }
                                                                                    } 
                                                                                );
                                                                                var thebytes = ".(isset($mdata['flashUploaderBytes']) && is_numeric($mdata['flashUploaderBytes']) && $mdata['flashUploaderBytes'] > 0 ? intval($mdata['flashUploaderBytes']) : '0').";
                                                                                if(thebytes > 0 && typeof files[i].size != 'undefined' && files[i].size > thebytes){
                                                                                     alert(' ".addslashes(BFText::_('COM_BREEZINGFORMS_FLASH_UPLOADER_TOO_LARGE'))."');
                                                                                     error = true;
                                                                                }
                                                                                var ext = files[i].name.split('.').pop().toLowerCase();
                                                                                var exts = '".strtolower($exts)."'.split(',');
                                                                                var found = 0;
                                                                                for (var x in exts){
                                                                                    if(exts[x] == ext){
                                                                                        found++;
                                                                                    }
                                                                                }
                                                                                if(found == 0){
                                                                                    alert( ' ".addslashes(BFText::_('COM_BREEZINGFORMS_FILE_EXTENSION_NOT_ALLOWED'))."' );
                                                                                    error = true;
                                                                                }
                                                                                if(error){
                                                                                    JQuery('#'+files[i].id+'queue').remove();
                                                                                    JQuery('#'+files[i].id+'queueitem').remove();
                                                                                }else{
                                                                                    bfFlashUploadersLength++;
                                                                                }
                                                                                bfUploadImageThumb(files[i]);
                                                                            }
                                                                        }
                                                                        // disable the button if no multi upload
                                                                        if( ".( isset($mdata['flashUploaderMulti']) && $mdata['flashUploaderMulti'] ? 'true' : 'false' )." == false ){
                                                                            var the_size = JQuery('#bfFlashFileQueue".$mdata['dbId']." .bfFileQueueItem').size();
                                                                            if( the_size > 0 ){
                                                                                JQuery('#bfPickFiles".$mdata['dbId']."').prop('disabled',true);
                                                                            }
                                                                        }
                                                                });
                                                                uploader.bind('UploadProgress', function(up, file) {
                                                                    if(typeof JQuery('#'+file.id+'queue').get(0) != 'undefined'){
                                                                        JQuery('#'+file.id+'queue').get(0).getElementsByTagName('b')[0].innerHTML = file.percent + '% <div style=\"height: 5px;width: ' + (file.percent*1.5) + 'px;background-color: #9de24f;\"></div>';
                                                                    }
                                                                });
                                                                uploader.bind('FileUploaded', function(up, file, response) {
                                                                    if(response.response!=''){
                                                                        if(response.response !== null){
                                                                            alert(response.response);
                                                                        }
                                                                    }
                                                                    JQuery('#'+file.id+'queue').remove();
                                                                });
                                                                uploader.init();
                                                                bfUploaders.push(uploader);
                                                            });
							//-->
                                                        </script>
							";
                                                        echo '<input class="ff_elem" '.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'type="hidden" name="ff_nm_'.$mdata['bfName'].'[]" id="ff_elem'.$mdata['dbId'].'"/>'."\n";
						}else{
                                                    echo '<input class="ff_elem" '.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'type="file" name="ff_nm_'.$mdata['bfName'].'[]" id="ff_elem'.$mdata['dbId'].'"/>'."\n";
						}
						if($mdata['attachToAdminMail']){
							echo '<input type="hidden" name="attachToAdminMail['.$mdata['bfName'].']" value="true"/>'."\n";
						}
						if($mdata['attachToUserMail']){
							echo '<input type="hidden" name="attachToUserMail['.$mdata['bfName'].']" value="true"/>'."\n";
						}
                                                echo '</div>';
						break;
						
					case 'bfSubmitButton':
                                                /* translatables */
                                                if(isset($mdata['src_translation'.$this->language_tag]) && $mdata['src_translation'.$this->language_tag] != ''){
                                                    $mdata['src'] = $mdata['src_translation'.$this->language_tag];
                                                }
                                                if(isset($mdata['value_translation'.$this->language_tag]) && $mdata['value_translation'.$this->language_tag] != ''){
                                                    $mdata['value'] = $mdata['value_translation'.$this->language_tag];
                                                }
                                                /* translatables end */
                                                
						echo '<div class="controls form-inline">';
                                                echo $label;
						$value = '';
						$type = 'submit';
						$src = '';
                                                
						if($mdata['src'] != ''){
							$type = 'image';
							$src = 'src="'.$mdata['src'].'" ';
						}
						if($mdata['value'] != ''){
							$value = 'value="'.htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8').'" ';
						}
						if($mdata['actionClick'] == 1){
							$onclick = 'onclick="if(typeof bf_htmltextareainit != \'undefined\'){ bf_htmltextareainit() }populateSummarizers();if(document.getElementById(\'bfPaymentMethod\')){document.getElementById(\'bfPaymentMethod\').value=\'\';};'.$mdata['actionFunctionName'] . '(this,\'click\');return false;" ';
						} else {
							$onclick = 'onclick="if(typeof bf_htmltextareainit != \'undefined\'){ bf_htmltextareainit() }populateSummarizers();if(document.getElementById(\'bfPaymentMethod\')){document.getElementById(\'bfPaymentMethod\').value=\'\';};return false;" ';
						}
                                                if($src == ''){
                                                    echo '<button type="button" class="ff_elem btn button bfCustomSubmitButton" '.$value.$src.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'type="'.$type.'" name="ff_nm_'.$mdata['bfName'].'[]" id="ff_elem'.$mdata['dbId'].'">'.$mdata['value'].'</button>'."\n";
                                                }else{
                                                    echo '<input type="button" class="ff_elem bfCustomSubmitButton" '.$value.$src.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'type="'.$type.'" alt="" name="ff_nm_'.$mdata['bfName'].'[]" id="ff_elem'.$mdata['dbId'].'" value="'.$mdata['value'].'"/>'."\n";
                                                }
                                                echo '</div>';
						break;
						
					case 'bfHidden':
						
						echo '<input class="ff_elem" type="hidden" name="ff_nm_'.$mdata['bfName'].'[]" value="'.htmlentities(trim($mdata['value']), ENT_QUOTES, 'UTF-8').'" id="ff_elem'.$mdata['dbId'].'"/>'."\n";
						break;
						
					case 'bfSummarize':
                                                /* translatables */
                                                if(isset($mdata['emptyMessage_translation'.$this->language_tag]) && $mdata['emptyMessage_translation'.$this->language_tag] != ''){
                                                    $mdata['emptyMessage'] = $mdata['emptyMessage_translation'.$this->language_tag];
                                                }
                                                /* translatables end */
						echo '<div class="controls form-inline">';
                                                echo $label;
						echo '<div style="display: inline-block; vertical-align: top;" class="ff_elem bfSummarize" id="ff_elem'.$mdata['dbId'].'"></div>'."\n";
						echo '<script type="text/javascript"><!--'."\n".'bfRegisterSummarize("ff_elem'.$mdata['dbId'].'", "'.$mdata['connectWith'].'", "'.$mdata['connectType'].'", "'.addslashes($mdata['emptyMessage']).'", '.($mdata['hideIfEmpty'] ? 'true' : 'false').')'."\n".'//--></script>';
						if(trim($mdata['fieldCalc']) != ''){
							echo '<script type="text/javascript">
                                                        <!--
							function bfFieldCalcff_elem'.$mdata['dbId'].'(value){
								if(!isNaN(value)){
									value = Number(value);
								}
								'.$mdata['fieldCalc'].'
								return value;
							}
                                                        //-->
							</script>';
						}
                                                echo '</div>';
						break;

                                        case 'bfReCaptcha':
                                            
                                            echo '<div class="controls form-inline'.(isset($mdata['pubkey']) && $mdata['pubkey'] ? '' : ' well well-small').'">';
                                            echo $label;
                                            if(isset($mdata['pubkey']) && $mdata['pubkey'] != ''){

                                                if(isset($mdata['newCaptcha']) && $mdata['newCaptcha']){
                                                    
                                                    $http = 'http';
                                                    if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) {
                                                        $http .= 's';
                                                    }
                                                    $lang = JRequest::getVar('lang','');
                                                    if($lang != ''){
                                                        $lang = ',lang: '.json_encode($lang).'';
                                                    }
                                                    
                                                    //JFactory::getDocument()->addScript($http.'://www.google.com/recaptcha/api.js?onload=onloadBFNewRecaptchaCallback&render=explicit', $type = "text/javascript", true, true);
                                                    
                                                    echo '
                                                    <div style="display: inline-block !important; vertical-align: middle;">
                                                        <div class="control-group">
                                                            <div class="controls">
                                                                    <div id="newrecaptcha"></div>
                                                                </div>
                                                        </div>
                                                        <div class="g-recaptcha" data-sitekey="'.$mdata['pubkey'].'"></div>
                                                    </div>
                                                    <script type="text/javascript">
                                                    <!--
                                                    var onloadBFNewRecaptchaCallback = function() {
                                                      grecaptcha.render("newrecaptcha", {
                                                        "sitekey" : "'.$mdata['pubkey'].'",
                                                        "theme" : "'.(trim($mdata['theme']) == '' ? 'light' : trim($mdata['theme'])).'",
                                                      });
                                                    };
                                                    JQuery(document).ready(function(){
                                                        JQuery.getScript("'.$http.'://www.google.com/recaptcha/api.js?onload=onloadBFNewRecaptchaCallback&render=explicit");
                                                    });
                                                    -->
                                                  </script>';
                                                    
                                                } else {
                                                
                                                    $http = 'http';
                                                    if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) {
                                                        $http .= 's';
                                                    }
                                                    $lang = JRequest::getVar('lang','');
                                                    if($lang != ''){
                                                        $lang = ',lang: '.json_encode($lang).'';
                                                    }
                                                    JFactory::getDocument()->addScript($http.'://www.google.com/recaptcha/api/js/recaptcha.js');
                                                    echo '<div class="bfReCaptchaDiv" id="recaptcha_widget" style="display: inline-block !important;">

                                                            <div class="control-group">

                                                                <div class="controls">
                                                                    <span id="recaptcha_image" class="thumbnail"></span>
                                                                </div>
                                                            </div>

                                                           <div class="control-group">

                                                              <div class="controls">
                                                                      <input type="text" id="recaptcha_response_field" name="recaptcha_response_field" class="input-recaptcha" />
                                                                      <button type="button" class="btn button" onclick="Recaptcha.reload()"><i class="icon-refresh"></i></button>
                                                                      <button type="button" class="btn recaptcha_only_if_image button" onclick="Recaptcha.switch_type(\'audio\')"><i title="Get an audio CAPTCHA" class="icon-play"></i></button>
                                                                      <button type="button" class="btn recaptcha_only_if_audio button" onclick="Recaptcha.switch_type(\'image\')"><i title="Get an image CAPTCHA" class="icon-picture"></i></button>
                                                                      <button type="button" class="btn button" onclick="Recaptcha.showhelp()"><i class="icon-question-sign"></i></button>
                                                              </div>
                                                        </div>

                                                    </div>
                                                    <script type="text/javascript">
                                                    <!--
                                                    Recaptcha.create("'.$mdata['pubkey'].'",
                                                        "bfReCaptchaDiv", {
                                                        theme: "custom",
                                                        custom_theme_widget: "recaptcha_widget"
                                                        '.$lang.'
                                                        }
                                                    );
                                                    -->
                                                    </script>'."\n";
                                                
                                                }
                                            }
                                            else
                                            {
                                                echo '<span class="bfCaptcha">'."\n";
                                                echo 'WARNING: No public key given for ReCaptcha element!';
                                                echo '</span>'."\n";
                                            }
                                            
                                            echo '</div>';
                                            
                                            break;

					case 'bfCaptcha':
                                                
                                                echo '<div class="controls form-inline">';
                                                echo $label;
                                                
                                                if(JFactory::getApplication()->isSite())
                                                 {
                                                    $captcha_url = JURI::root(true).'/components/com_breezingforms/images/captcha/securimage_show.php';
                                                 }
                                                 else
                                                 {
                                                    $captcha_url = JURI::root(true).'/administrator/components/com_breezingforms/images/captcha/securimage_show.php';
                                                 }
                                            
                                                echo '<div style="display: inline-block;">';
                                                
                                                echo '<img alt="" '.(isset($mdata['width']) && intval($mdata['width']) > 0 ? ' style="width: '.intval($mdata['width']).'px !important;min-width: '.intval($mdata['width']).'px !important;max-width: '.intval($mdata['width']).'px !important;"' : 'style="width: 230px !important;min-width: 230px !important;max-width: 230px !important;"' ).' id="ff_capimgValue" class="ff_capimg img-polaroid" src="'.$captcha_url.'"/>'."\n";
                                                echo '<br/>';
                                                echo '<br/>';
                                                echo '<div class="input-append">';
                                                echo '<input '.(isset($mdata['width']) && intval($mdata['width']) > 0 ? ' style="width:'.(intval($mdata['width'])-45).'px !important;min-width:'.(intval($mdata['width'])-45).'px !important;max-width:'.(intval($mdata['width'])-45).'px !important;"' : ' style="width: 185px !important;min-width: 185px !important;max-width: 185px !important;"' ).' autocomplete="off" class="ff_elem bfCaptchaField" type="text" name="bfCaptchaEntry" id="bfCaptchaEntry" />'."\n";
						echo '<button type="button" class="ff_elem btn button" onclick="document.getElementById(\'bfCaptchaEntry\').value=\'\';document.getElementById(\'bfCaptchaEntry\').focus();document.getElementById(\'ff_capimgValue\').src = \''.$captcha_url.'?bfMathRandom=\' + Math.random(); return false"><i class="icon-refresh"></i></button>'."\n";
						echo '</div>';
                                                echo '</div>';
                                                
                                                echo '</div>';
                                                
						break;
					
                                        case 'bfCalendar':
                                                /* translatables */
                                                if(isset($mdata['value_translation'.$this->language_tag]) && $mdata['value_translation'.$this->language_tag] != ''){
                                                    $mdata['value'] = $mdata['value_translation'.$this->language_tag];
                                                }
                                                if(isset($mdata['format_translation'.$this->language_tag]) && $mdata['format_translation'.$this->language_tag] != ''){
                                                    $mdata['format'] = $mdata['format_translation'.$this->language_tag];
                                                }
                                                /* translatables end */
                                                echo '<div class="controls form-inline">';
                                                echo $label; 
                                                JHTML::_( 'behavior.calendar' ); 
                                            
						$size = '';
						if($mdata['size']!=''){
							$size = 'style="width:'.htmlentities(strip_tags($mdata['size'])).'" ';
						}
                                                
                                                $exploded = explode('::',trim($mdata['value']));
                                                
                                                $left = '';
                                                $right = '';
                                                if(count($exploded) == 2){
                                                    $left = trim($exploded[0]);
                                                    $right = trim($exploded[1]); 
                                                }else{
                                                    $right = trim($exploded[0]);
                                                }
                                                
						echo '<div class="input-append">'."\n";
						echo '<input autocomplete="off" class="ff_elem" '.$size.'type="text" name="ff_nm_'.$mdata['bfName'].'[]"  id="ff_elem'.$mdata['dbId'].'" value="'.htmlentities($left, ENT_QUOTES, 'UTF-8').'"/>'."\n";
						echo '<button type="button" id="ff_elem'.$mdata['dbId'].'_calendarButton" type="submit" class="bfCalendar btn button" value="'.htmlentities($right, ENT_QUOTES, 'UTF-8').'"><span><i class="icon-calendar"></i>'.htmlentities($right == '...' ? '' : $right, ENT_QUOTES, 'UTF-8').'</span></button>'."\n";
						echo '</div>'."\n";
						
                                                echo '<script type="text/javascript">
                                                <!--
                                                Calendar.setup({
                                                        inputField     :    "ff_elem'.$mdata['dbId'].'",
                                                        ifFormat       :    "'.$mdata['format'].'",
                                                        button         :    "ff_elem'.$mdata['dbId'].'_calendarButton",
                                                        align          :    "Bl",
                                                        singleClick    :    true
                                                    });
                                                //-->
                                                </script>'."\n";
                                                echo '</div>';
						break;
                                                
					case 'bfCalendarResponsive':
                                                /* translatables */
                                                if(isset($mdata['value_translation'.$this->language_tag]) && $mdata['value_translation'.$this->language_tag] != ''){
                                                    $mdata['value'] = $mdata['value_translation'.$this->language_tag];
                                                }
                                                if(isset($mdata['format_translation'.$this->language_tag]) && $mdata['format_translation'.$this->language_tag] != ''){
                                                    $mdata['format'] = $mdata['format_translation'.$this->language_tag];
                                                }
                                                /* translatables end */
                                                echo '<div class="controls form-inline">';
                                                echo $label; 
                                                
						$size = '';
						if($mdata['size']!=''){
							$size = 'style="width:'.htmlentities(strip_tags($mdata['size'])).'" ';
						}
                                                
                                                $exploded = explode('::',trim($mdata['value']));
                                                
                                                $left = '';
                                                $right = '';
                                                if(count($exploded) == 2){
                                                    $left = trim($exploded[0]);
                                                    $right = trim($exploded[1]); 
                                                }else{
                                                    $right = trim($exploded[0]);
                                                }
                                                
						echo '<div class="input-append">'."\n";
						echo '<input autocomplete="off" class="ff_elem" '.$size.'type="text" name="ff_nm_'.$mdata['bfName'].'[]"  id="ff_elem'.$mdata['dbId'].'" value="'.htmlentities($left, ENT_QUOTES, 'UTF-8').'"/>'."\n";
						echo '<button style="cursor:pointer !important;" type="button" id="ff_elem'.$mdata['dbId'].'_calendarButton" type="submit" class="bfCalendar btn button" value="'.htmlentities($right, ENT_QUOTES, 'UTF-8').'"><span><i class="icon-calendar"></i>'.htmlentities($right == '...' ? '' : $right, ENT_QUOTES, 'UTF-8').'</span></button>'."\n";
						echo '</div>'."\n";
						
                                                $container = 'JQuery("body").append("<div class=\"bfCalendarResponsiveContainer'.$mdata['dbId'].'\" style=\"display:block;position:absolute;left:-9999px;\"></div>");';
                                                
                                                $c = '';
                                                
                                                if(!$this->hasResponsiveDatePicker){
                                                    ob_start();
                                                    ?>
                                                    <script type="text/javascript">
                                                    <!--
                                                    function bf_add_yearscroller( fieldname ){
                                                        if(!JQuery("#bfCalExt"+fieldname).length){
                                                            // prev
                                                            if( JQuery(".bfCalendarResponsiveContainer"+fieldname+" .picker__select--year").get(0).selectedIndex > 0 ){
                                                                JQuery(".bfCalendarResponsiveContainer"+fieldname+" .picker__select--year").before('<img id="bfCalExt'+fieldname+'" onclick="JQuery(\'.bfCalendarResponsiveContainer'+fieldname+' .picker__select--year\').get(0).selectedIndex=JQuery(\'.bfCalendarResponsiveContainer'+fieldname+' .picker__select--year\').get(0).selectedIndex-1;JQuery(\'.bfCalendarResponsiveContainer'+fieldname+' .picker__select--year\').trigger(\'change\')" border="0" src="<?php echo Juri::root(true) . '/components/com_breezingforms/libraries/jquery/pickadate/minusyear.png' ?>" style="width: 30px; vertical-align: top; cursor:pointer;" />');
                                                            }
                                                            // next
                                                            if( JQuery(".bfCalendarResponsiveContainer"+fieldname+" .picker__select--year").get(0).selectedIndex + 1 < JQuery(".bfCalendarResponsiveContainer"+fieldname+" .picker__select--year").get(0).options.length ){
                                                                JQuery(".bfCalendarResponsiveContainer"+fieldname+" .picker__select--year").after('<img id="bfCalExt'+fieldname+'" onclick="JQuery(\'.bfCalendarResponsiveContainer'+fieldname+' .picker__select--year\').get(0).selectedIndex=JQuery(\'.bfCalendarResponsiveContainer'+fieldname+' .picker__select--year\').get(0).selectedIndex+1;JQuery(\'.bfCalendarResponsiveContainer'+fieldname+' .picker__select--year\').trigger(\'change\')" border="0" src="<?php echo Juri::root(true) . '/components/com_breezingforms/libraries/jquery/pickadate/plusyear.png' ?>" style="width: 30px; vertical-align: top; cursor:pointer;" />');
                                                            }
                                                            
                                                            JQuery('.bfCalendarResponsiveContainer'+fieldname+' .picker__select--year').on('change', function(){
                                                                bf_add_yearscroller( fieldname );
                                                            }); 
                                                            JQuery('.bfCalendarResponsiveContainer'+fieldname+' .picker__select--month').on('change', function(){
                                                                bf_add_yearscroller( fieldname );
                                                            });
                                                            
                                                            var myVal = JQuery('.bfCalendarResponsiveContainer'+fieldname+' .picker__select--year').val();
                                                            var myInterval = setInterval(function(){
                                                                if( myVal != JQuery('.bfCalendarResponsiveContainer'+fieldname+' .picker__select--year').val() ){
                                                                    clearInterval(myInterval);
                                                                    bf_add_yearscroller( fieldname );
                                                                }
                                                            }, 200);
                                                            
                                                            var myVal = JQuery('.bfCalendarResponsiveContainer'+fieldname+' .picker__select--month').val();
                                                            var myInterval = setInterval(function(){
                                                                if( myVal != JQuery('.bfCalendarResponsiveContainer'+fieldname+' .picker__select--month').val() ){
                                                                    clearInterval(myInterval);
                                                                    bf_add_yearscroller( fieldname );
                                                                }
                                                            }, 200);
                                                        }
                                                    }
                                                    //-->
                                                    </script>
                                                    <?php 
                                                    $c = ob_get_contents();
                                                    ob_end_clean();
                                                }
                                                
                                                echo $c;
                                                
                                                echo '<script type="text/javascript">
                                                <!--
                                                JQuery(document).ready(function () {
                                                    '.$container.'
                                                    JQuery("#ff_elem'.$mdata['dbId'].'_calendarButton").pickadate({
                                                        format: "'.$mdata['format'].'", 
                                                        selectYears: 60, 
                                                        selectMonths: true,
                                                        editable: true,
                                                        firstDay: 1,
                                                        container: ".bfCalendarResponsiveContainer'.$mdata['dbId'].'",
                                                        onOpen: function() {
                                                            bf_add_yearscroller( '.json_encode($mdata['dbId']).' );
                                                        },
                                                        onSet: function() {
                                                            JQuery("#ff_elem'.$mdata['dbId'].'").val(this.get("value"));
                                                        }
                                                    });
                                                });
                                                //-->
                                                </script>'."\n";
                                                
                                                $this->hasResponsiveDatePicker = true;
                                                
                                                echo '</div>';
						break;	
						
					case 'bfPayPal':
                                                /* translatables */
                                                if(isset($mdata['image_translation'.$this->language_tag]) && $mdata['image_translation'.$this->language_tag] != ''){
                                                    $mdata['image'] = $mdata['image_translation'.$this->language_tag];
                                                }
                                                /* translatables end */
						echo '<div class="controls form-inline">';
                                                echo $label;
						$value = '';
						$type = 'submit';
						$src = '';
						if($mdata['image'] != ''){
							$type = 'image';
							$src = 'src="'.$mdata['image'].'" alt="PayPal" ';
						}else{
							$value = 'value="PayPal" ';
						}
						if($mdata['actionClick'] == 1){
							$onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'PayPal\';'.$mdata['actionFunctionName'] . '(this,\'click\');" ';	
						} else {
							$onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'PayPal\';" ';
						}
						echo '<input class="ff_elem" '.$value.$src.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'type="'.$type.'" name="ff_nm_'.$mdata['bfName'].'[]" id="ff_elem'.$mdata['dbId'].'"/>'."\n";
						echo '</div>';
                                                break;
						
					case 'bfSofortueberweisung':
                                                /* translatables */
                                                if(isset($mdata['image_translation'.$this->language_tag]) && $mdata['image_translation'.$this->language_tag] != ''){
                                                    $mdata['image'] = $mdata['image_translation'.$this->language_tag];
                                                }
                                                /* translatables end */
						echo '<div class="controls form-inline">';
                                                echo $label;
						$value = '';
						$type = 'submit';
						$src = '';
						if($mdata['image'] != ''){
							$type = 'image';
							$src = 'src="'.$mdata['image'].'" alt="Sofort.com" ';
						}else{
							$value = 'value="Sofortueberweisung" ';
						}
						if($mdata['actionClick'] == 1){
							$onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'Sofortueberweisung\';'.$mdata['actionFunctionName'] . '(this,\'click\');" ';	
						} else {
							$onclick = 'onclick="document.getElementById(\'bfPaymentMethod\').value=\'Sofortueberweisung\';" ';
						}
						echo '<input class="ff_elem" '.$value.$src.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'type="'.$type.'" name="ff_nm_'.$mdata['bfName'].'[]" id="ff_elem'.$mdata['dbId'].'"/>'."\n";
						echo '</div>';
                                                break;
				}
				
				if(isset($mdata['bfName']) && isset($mdata['off']) && $mdata['off']){
					echo '<script type="text/javascript"><!--'."\n".'bfDeactivateField["ff_nm_'.$mdata['bfName'].'[]"]=true;'."\n".'//--></script>'."\n";
				}
				
				if($mdata['bfType'] == 'bfFile'){
                                    echo '<span id="ff_elem'.$mdata['dbId'].'_files"></span>';
                                }
				
				if($mdata['bfType'] != 'bfHidden'){
                                    echo '</div>'."\n";
				}
			}
		}

		/**
		 * Paging and wrapping of inline element containers
		 */
		
		if(isset($dataObject['properties']) && $dataObject['properties']['type'] == 'section' && $dataObject['properties']['displayType'] == 'inline'){
			echo '<div class="bfClearfix">'."\n";
		}
		
		if(isset($dataObject['children']) && count($dataObject['children']) != 0){
			$childrenAmount = count($dataObject['children']);
			for($i = 0; $i < $childrenAmount; $i++){
				$this->process( $dataObject['children'][$i], $mdata, $parentPage, $i, $childrenAmount, $dataObject );
			}
		}	
		
		if(isset($dataObject['properties']) && $dataObject['properties']['type'] == 'section' && $dataObject['properties']['displayType'] == 'inline'){
			echo '</div>'."\n";
		}
		
		if(isset($dataObject['properties']) && $dataObject['properties']['type'] == 'section' && $dataObject['properties']['bfType'] == 'section'){
			echo '</div>'; // row-fluid
                        echo '</fieldset>'."\n";
			
		} else if( isset($dataObject['properties']) && $dataObject['properties']['type'] == 'section' && $dataObject['properties']['bfType'] == 'normal' ) {
			if(isset($dataObject['properties']['name']) && $dataObject['properties']['name'] != ''){
                            echo '</div>'; // row-fluid
                            echo '</section>'."\n";
			}
		}
		else if(isset($dataObject['properties']) && $dataObject['properties']['type'] == 'page'){

			$isLastPage = false;
			if($this->rootMdata['lastPageThankYou'] && $dataObject['properties']['pageNumber'] == count($this->dataObject['children']) && count($this->dataObject['children']) > 1){
				$isLastPage = true;
			}
			
			if(!$isLastPage){
			
				$last = 0;
				if($this->rootMdata['lastPageThankYou']){
					$last = 1;
				}
				
                                echo '<div class="bfClearfix"></div>';
                                echo '<div class="form-actions">';
                                
				if($this->rootMdata['pagingInclude'] && $dataObject['properties']['pageNumber'] > 1){
                                        /* translatables */
                                        if(isset($this->rootMdata['pagingPrevLabel_translation'.$this->language_tag]) && $this->rootMdata['pagingPrevLabel_translation'.$this->language_tag] != ''){
                                            $this->rootMdata['pagingPrevLabel'] = $this->rootMdata['pagingPrevLabel_translation'.$this->language_tag];
                                        }
                                        /* translatables end */
					echo '<button type="button" class="bfPrevButton btn btn-primary pull-left button'.$this->fadingClass.'" type="submit" onclick="ff_validate_prevpage(this, \'click\');populateSummarizers();if(typeof bfRefreshAll != \'undefined\'){bfRefreshAll();}" value="'.htmlentities(trim($this->rootMdata['pagingPrevLabel']), ENT_QUOTES, 'UTF-8').'"><span>'.htmlentities(trim($this->rootMdata['pagingPrevLabel']), ENT_QUOTES, 'UTF-8').'</span></button>'."\n";
				}
	
				if($this->rootMdata['pagingInclude'] && $dataObject['properties']['pageNumber'] < count($this->dataObject['children']) - $last){
                                        /* translatables */
                                        if(isset($this->rootMdata['pagingNextLabel_translation'.$this->language_tag]) && $this->rootMdata['pagingNextLabel_translation'.$this->language_tag] != ''){
                                            $this->rootMdata['pagingNextLabel'] = $this->rootMdata['pagingNextLabel_translation'.$this->language_tag];
                                        }
                                        /* translatables end */
					echo '<button type="button" class="bfNextButton btn btn-primary pull-right button'.$this->fadingClass.'" type="submit" onclick="ff_validate_nextpage(this, \'click\');populateSummarizers();if(typeof bfRefreshAll != \'undefined\'){bfRefreshAll();}" value="'.htmlentities(trim($this->rootMdata['pagingNextLabel']), ENT_QUOTES, 'UTF-8').'"><span>'.htmlentities(trim($this->rootMdata['pagingNextLabel']), ENT_QUOTES, 'UTF-8').'</span></button>'."\n";
				}
	
				if($this->rootMdata['cancelInclude'] && $dataObject['properties']['pageNumber'] + 1 > count($this->dataObject['children']) - $last){
                                        /* translatables */
                                        if(isset($this->rootMdata['cancelLabel_translation'.$this->language_tag]) && $this->rootMdata['cancelLabel_translation'.$this->language_tag] != ''){
                                            $this->rootMdata['cancelLabel'] = $this->rootMdata['cancelLabel_translation'.$this->language_tag];
                                        }
                                        /* translatables end */
					echo '<button class="bfCancelButton btn btn-secondary pull-right button'.$this->fadingClass.'" type="submit" onclick="ff_resetForm(this, \'click\');"  value="'.htmlentities(trim($this->rootMdata['cancelLabel']), ENT_QUOTES, 'UTF-8').'"><span>'.htmlentities(trim($this->rootMdata['cancelLabel']), ENT_QUOTES, 'UTF-8').'</span></button>'."\n";
				}
				
				$callSubmit = 'ff_validate_submit(this, \'click\')';
				if( $this->hasFlashUpload ){
					$callSubmit = 'if(typeof bfAjaxObject101 == \'undefined\' && typeof bfReCaptchaLoaded == \'undefined\'){bfDoFlashUpload()}else{ff_validate_submit(this, \'click\')}';
				}
				if($this->rootMdata['submitInclude'] && $dataObject['properties']['pageNumber'] + 1 > count($this->dataObject['children']) - $last){
                                        /* translatables */
                                        if(isset($this->rootMdata['submitLabel_translation'.$this->language_tag]) && $this->rootMdata['submitLabel_translation'.$this->language_tag] != ''){
                                            $this->rootMdata['submitLabel'] = $this->rootMdata['submitLabel_translation'.$this->language_tag];
                                        }
                                        /* translatables end */
					echo '<button type="button" id="bfSubmitButton" class="bfSubmitButton btn btn-primary pull-right button'.$this->fadingClass.'" onclick="this.disabled=true;if(typeof bf_htmltextareainit != \'undefined\'){ bf_htmltextareainit() }if(document.getElementById(\'bfPaymentMethod\')){document.getElementById(\'bfPaymentMethod\').value=\'\';};'.$callSubmit.';" value="'.htmlentities(trim($this->rootMdata['submitLabel']), ENT_QUOTES, 'UTF-8').'"><span>'.htmlentities(trim($this->rootMdata['submitLabel']), ENT_QUOTES, 'UTF-8').'</span></button>'."\n";
				}
                                
                                echo '</div>';
			
			}
		}
	}
	
	public function render(){

                if( isset($this->rootMdata['themebootstrapUseProgress']) && $this->rootMdata['themebootstrapUseProgress'] ){
                    echo '<div class="progress"><div id="bfProgressBar" class="bar"></div></div>
                        <script type="text/javascript">
                        <!--
                        function bfUpdateProgress(){
                            if(ff_currentpage > 1){
                                var pages = JQuery(".bfPage").size()'.($this->rootMdata['lastPageThankYou'] ? '-1' : '').';
                                var result = Math.round(((ff_currentpage-1) / pages)*100);
                                JQuery("#bfProgressBar").css("width",result+"%");
                            }else{
                                JQuery("#bfProgressBar").css("width","0%");
                            }
                        }
                        JQuery(document).ready(function(){
                            setInterval("bfUpdateProgress()", 500);
                        });
                        -->
                        </script>';
                }
            
                $this->process($this->dataObject);
		echo '</div>'."\n"; // closing last page
                
                $this->headers();
                
                if($this->hasResponsiveDatePicker){
                    JFactory::getDocument()->addScript(JURI::root(true).'/components/com_breezingforms/libraries/jquery/pickadate/picker.js');
                    JFactory::getDocument()->addScript(JURI::root(true).'/components/com_breezingforms/libraries/jquery/pickadate/picker.date.js');

                    $lang = JFactory::getLanguage()->getTag();
                    $lang = explode('-', $lang);
                    $lang = strtolower($lang[0]);
                    if(JFile::exists(JPATH_SITE.'/components/com_breezingforms/libraries/jquery/pickadate/translations/'.$lang.'.js')){
                        JFactory::getDocument()->addScript(JURI::root(true).'/components/com_breezingforms/libraries/jquery/pickadate/translations/'.$lang.'.js');
                    }
                    
                    JFactory::getDocument()->addStyleSheet(JURI::root(true).'/components/com_breezingforms/libraries/jquery/pickadate/themes/default.css');
                    JFactory::getDocument()->addStyleSheet(JURI::root(true).'/components/com_breezingforms/libraries/jquery/pickadate/themes/default.date.css');
                }
                
                // we must make sure that everything mootools related is included after moxie and plupload
                if(isset(JFactory::getDocument()->_scripts)){
                    foreach(JFactory::getDocument()->_scripts As $script_name => $script_value){
                        if(basename($script_name) != 'moxie.js' && basename($script_name) != 'plupload.js' 
                                && basename($script_name) != 'calendar.js' && basename($script_name) != 'calendar-setup.js'){
                            unset(JFactory::getDocument()->_scripts[$script_name]);
                            JFactory::getDocument()->_scripts[$script_name] = $script_value;
                        }
                    }
                }
                // we gonna add a blank to each textarea, since the value is transferred upon submit
                // requires a different mandatory validation than ff_valuenotempty
                if(count($this->htmltextareas)){
                    JImport( 'joomla.html.editor' );
                    $editor = JFactory::getEditor();
                    $htmltextarea_out = '';
                    foreach($this->htmltextareas As $htmltextarea){
                        $htmltextarea_out .= 'JQuery("[name=\"'.$htmltextarea.'\"]").val(JQuery.trim(JQuery("[name=\"'.$htmltextarea.'\"]").val())+" ");'."\n";
                        $htmltextarea_out .= 'bf_htmltextareas.push("'.rtrim(trim($editor->getContent($htmltextarea)),';').'")'."\n";
                        $htmltextarea_out .= 'bf_htmltextareanames.push("'.$htmltextarea.'")'."\n";
                    }
                    echo '<script type="text/javascript">
                          <!--
                          var bf_htmltextareas     = [];
                          var bf_htmltextareanames = [];
                          function bf_htmltextareainit(){
                            '.$htmltextarea_out.'
                          }
                          //-->
                          </script>';
                }
                
                if( $this->hasFlashUpload ){
			$tickets = JFactory::getSession()->get('bfFlashUploadTickets', array());
			$tickets[$this->flashUploadTicket] = array(); // stores file info for later processing
			JFactory::getSession()->set('bfFlashUploadTickets', $tickets);
			echo '<input type="hidden" name="bfFlashUploadTicket" value="'.$this->flashUploadTicket.'"/>'."\n";
			JFactory::getDocument()->addScript(JURI::root(true) . '/components/com_breezingforms/libraries/jquery/center.js');
			JFactory::getDocument()->addScriptDeclaration('
                        var bfUploaders = [];
                        var bfUploaderErrorElements = [];
			var bfFlashUploadInterval = null;
			var bfFlashUploaders = new Array();
                        var bfFlashUploadersLength = 0;
                        function bfRefreshAll(){
                            for( var i = 0; i < bfUploaders.length; i++ ){
                                bfUploaders[i].refresh();
                            }
                        }
                        function bfInitAll(){
                            for( var i = 0; i < bfUploaders.length; i++ ){
                                bfUploaders[i].init();
                            }
                        }
			function bfDoFlashUpload(){
                                JQuery("#bfSubmitMessage").css("visibility","hidden");
                                JQuery("#bfSubmitMessage").css("display","none");
                                JQuery("#bfSubmitMessage").css("z-index","999999");
				JQuery(".bfErrorMessage").html("");
                                JQuery(".bfErrorMessage").css("display","none");
                                for(var i = 0; i < bfUploaderErrorElements.length; i++){
                                    JQuery("#"+bfUploaderErrorElements[i]).html("");
                                }
                                bfUploaderErrorElements = [];
                                if(ff_validation(0) == ""){
					try{
                                            bfFlashUploadInterval = window.setInterval( bfCheckFlashUploadProgress, 1000 );
                                            if(bfFlashUploadersLength > 0){
                                                JQuery("#bfFileQueue").bfcenter(true);
                                                JQuery("#bfFileQueue").css("visibility","visible");
                                                for( var i = 0; i < bfUploaders.length; i++ ){
                                                    bfUploaders[i].start();
                                                }
                                            }
					} catch(e){alert(e)}
				} else {
					if(typeof bfUseErrorAlerts == "undefined"){
                                            alert(error);
                                        } else {
                                            bfShowErrors(error);
                                        }
                                        ff_validationFocus("");
                                        document.getElementById("bfSubmitButton").disabled = false;
				}
			}
			function bfCheckFlashUploadProgress(){
                                if( JQuery("#bfFileQueue").html() == "" ){ // empty indicates that all queues are uploaded or in any way cancelled
					JQuery("#bfFileQueue").css("visibility","hidden");
					window.clearInterval( bfFlashUploadInterval );
                                        if(typeof bfAjaxObject101 != \'undefined\' || typeof bfReCaptchaLoaded != \'undefined\'){
                                            ff_submitForm2();
                                        }else{
                                            ff_validate_submit(document.getElementById("bfSubmitButton"), "click");
                                        }
					JQuery(".bfFlashFileQueueClass").html("");
                                        if(bfFlashUploadersLength > 0){
                                            JQuery("#bfSubmitMessage").bfcenter(true);
                                            JQuery("#bfSubmitMessage").css("visibility","visible");
                                            JQuery("#bfSubmitMessage").css("display","block");
                                            JQuery("#bfSubmitMessage").css("z-index","999999");
                                        }
                                        
				}
			}
			');
			echo "<div style=\"visibility:hidden;\" id=\"bfFileQueue\"></div>";
			echo "<div style=\"visibility:hidden;display:none;\" id=\"bfSubmitMessage\">".BFText::_('COM_BREEZINGFORMS_SUBMIT_MESSAGE')."</div>";
		}
		echo '<noscript>Please turn on javascript to submit your data. Thank you!</noscript>'."\n";
                JFactory::getDocument()->addScriptDeclaration('//-->');
	}
	
	public function parseToggleFields( $code ){
		/*
		 	example codes:

			turn on element bla if blub is on
			turn off section bla if blub is on
			turn on section bla if blub is off
			turn off element bla if blub is off

                        if element opener is off set opener huhuu

			syntax:
			ACTION STATE TARGETCATEGORY TARGETNAME if SRCNAME is VALUE 
		 */
		
		$parsed = '';
		$code = str_replace("\r", '', $code);
		$lines = explode( "\n", $code );
		$linesCnt = count( $lines );
		
		for($i = 0; $i < $linesCnt;$i++){
			$tokens = explode( ' ', trim($lines[$i]) );
			$tokensCnt = count($tokens);
			if($tokensCnt >= 8){
				$state = '';
				// rebuilding the state as it could be a value containing blanks
				for($j = 7; $j < $tokensCnt; $j++){
					if($j+1 < $tokensCnt)
						$state .= $tokens[$j] . ' ';
					else
						$state .= $tokens[$j];
				}
				$parsed .= '{ action: "'.$tokens[0].'", state: "'.$tokens[1].'", tCat: "'.$tokens[2].'", tName: "'.$tokens[3].'", statement: "'.$tokens[4].'", sName: "'.$tokens[5].'", condition: "'.$tokens[6].'", value: "'.addslashes($state).'" },';
			}
		}
		
		return "[".rtrim($parsed, ",")."]";
	}
}