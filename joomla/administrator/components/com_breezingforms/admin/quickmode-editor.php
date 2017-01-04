<?php
/**
* BreezingForms - A Joomla Forms Application
* @version 1.8
* @package BreezingForms
* @copyright (C) 2008-2012 by Markus Bopp
* @license Released under the terms of the GNU General Public License
**/
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

$active_language_code = JRequest::getVar('active_language_code', '');
if($active_language_code != ''){
    $active_language_code = '_translation'.$active_language_code;
}

JImport( 'joomla.html.editor' );
$editor = JFactory::getEditor();
echo '<input type="submit" class="btn btn-primary" value="'.JText::_('SAVE').'" onclick="saveText();parent.SqueezeBox.close();"/><br/><br/>';
echo '<div style="width:700px;">'.$editor->display("bfEditor",'',700,300,40,20,1).'</div>';
echo '<br/><input type="submit" class="btn btn-primary" value="'.JText::_('SAVE').'" onclick="saveText();parent.SqueezeBox.close();"/>';
echo '<script>
function bfLoadText(){
        var keyPageIntro   = "pageIntro'.$active_language_code.'";
        var keyDescription = "description'.$active_language_code.'";
            
	var item = parent.app.findDataObjectItem(parent.app.selectedTreeElement.id, parent.app.dataObject);

	// workaround for quote bug with jce
	var testEditor = '.$editor->getContent('bfEditor').'

	if(testEditor == "item.properties[keyPageIntro]" || testEditor == "item.properties[keyDescription]"){
		if(item && item.properties.type == "page"){
			setTimeout("setIntro()",100);
		} else if(item && item.properties.type == "section"){
			setTimeout("setDescription()",250);
		}
	} else {
                if(item && item.properties.type == "page"){
			setTimeout("setIntro0()",100);
		} else if(item && item.properties.type == "section"){

			setTimeout("setDescription0()",250);
		}
        }
};
function saveText(){
        var keyPageIntro   = "pageIntro'.$active_language_code.'";
        var keyDescription = "description'.$active_language_code.'";
	var item = parent.app.findDataObjectItem(parent.app.selectedTreeElement.id, parent.app.dataObject);
	if(item && item.properties.type == "page"){
		item.properties[keyPageIntro] = '.$editor->getContent('bfEditor').'
	} else if(item && item.properties.type == "section"){
		item.properties[keyDescription] = '.$editor->getContent('bfEditor').'
	}
	'.$editor->save('bfEditor').'
}
function setIntro0(){
        var key = "pageIntro'.$active_language_code.'";
	var item = parent.app.findDataObjectItem(parent.app.selectedTreeElement.id, parent.app.dataObject);
        if(typeof item.properties[key] == "undefined"){
            item.properties[key] = "";
        }
	'.$editor->setContent('bfEditor','item.properties[key]').'
        var testEditor = '.$editor->getContent('bfEditor').'
        if( testEditor == "item.properties[key]" || testEditor == "<p>item.properties[key]</p>" || testEditor == "<div>item.properties[\'pageIntro'.$active_language_code.'\']</div>" ){
            setTimeout("setIntro00()",250);
        }
}
function setIntro00(){
    var key = "pageIntro'.$active_language_code.'";
    var item = parent.app.findDataObjectItem(parent.app.selectedTreeElement.id, parent.app.dataObject);
    if(typeof item.properties[key] == "undefined"){
        item.properties[key] = "";
    }
    '.$editor->setContent('bfEditor','\'+item.properties[key]+\'').'
}
function setDescription0(){
        var key = "description'.$active_language_code.'";
	var item = parent.app.findDataObjectItem(parent.app.selectedTreeElement.id, parent.app.dataObject);
        if(typeof item.properties[key] == "undefined"){
            item.properties[key] = "";
        }
	'.$editor->setContent('bfEditor','item.properties[key]').'
        var testEditor = '.$editor->getContent('bfEditor').'
            
        if( testEditor == "item.properties[key]" || testEditor == "<p>item.properties[key]</p>" || testEditor == "<div>item.properties[key]</div>"){
        
            setTimeout("setDescription00()",250);
        }
}
function setDescription00(){
    var key = "description'.$active_language_code.'";
    var item = parent.app.findDataObjectItem(parent.app.selectedTreeElement.id, parent.app.dataObject);
    if(typeof item.properties[key] == "undefined"){
        item.properties[key] = "";
    }
    '.$editor->setContent('bfEditor','\'+item.properties[key]+\'').'
}
function setIntro(){
        var key = "pageIntro'.$active_language_code.'";
	var item = parent.app.findDataObjectItem(parent.app.selectedTreeElement.id, parent.app.dataObject);
	'.$editor->setContent('bfEditor','\'+item.properties[key]+\'').'
}
function setDescription(){
        var key = "description'.$active_language_code.'";
	var item = parent.app.findDataObjectItem(parent.app.selectedTreeElement.id, parent.app.dataObject);
        if(typeof item.properties[key] == "undefined"){
            item.properties[key] = "";
        }
	'.$editor->setContent('bfEditor','\'+item.properties[key]+\'').'
}

setTimeout("bfLoadText()",500);
</script>';

