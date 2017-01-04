<?php
/**
* BreezingForms - A Joomla Forms Application
* @version 1.8
* @package BreezingForms
* @copyright (C) 2008-2013 by Markus Bopp
* @license Released under the terms of the GNU General Public License
**/
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

if(method_exists($obj = JFactory::getDocument(), 'addCustomTag')){
    
    $stylelink = '<link href="//fonts.googleapis.com/css?family=Maven+Pro:500&subset=latin,greek,latin-ext,cyrillic" rel="stylesheet" type="text/css">' ."\n";
    JFactory::getDocument()->addCustomTag($stylelink);
    
    JFactory::getDocument()->addScriptDeclaration('
  (function() {
    var wf = document.createElement("script");
    wf.src = ("https:" == document.location.protocol ? "https" : "http") +
      "://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js";
    wf.type = "text/javascript";
    wf.async = "true";
    var s = document.getElementsByTagName("script")[0];
    s.parentNode.insertBefore(wf, s);
  })();
');
}
