<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class J2StorePopup {
	public static function popup( $url, $text, $options = array() )
	{

		$params = JComponentHelper::getParams('com_j2store');
		$html = "";

		if(!empty($options['onclose']))
		{
			//	JHTML::_('behavior.modal', 'a.modal', array('onClose'=> $options['onclose']) );
		}
		else
		{
			if (!empty($options['update']))
			{
				//		    JHTML::_('behavior.modal', 'a.modal', array('onClose'=>'\function(){j2storeUpdate();}') );
			}
			else
			{
				//	    JHTML::_('behavior.modal', 'a.modal');
			}
		}

		// set the $handler_string based on the user's browser
		if(!empty($options['onclose'])) {
			$handler_string = "{handler:'iframe',size:{x: window.innerWidth-80, y: window.innerHeight-80}, onClose: function(){}}";
		} else {
			$handler_string = "{handler:'iframe',size:{x: window.innerWidth-80, y: window.innerHeight-80}}";
		}


		if ( self::getBrowser() == 'ie')
		{
			// if IE, use
			if(!empty($options['onclose'])) {
				$handler_string = "{handler:'iframe',size:{x:document.documentElement.­clientWidth-80, y: document.documentElement.­clientHeight-80} onClose: function(){}}";
			} else {
				$handler_string = "{handler:'iframe',size:{x:document.documentElement.­clientWidth-80, y: document.documentElement.­clientHeight-80}}";
			}
		}

		$handler = (!empty($options['img']))
		? "{handler:'image'}"
		  : $handler_string;

		$lightbox_width = $params->get('lightbox_width');
		if(empty($options['width']) && !empty($lightbox_width))
			$options['width'] = $lightbox_width;

		if(!empty($options['width']))
		{
			if (empty($options['height']))
				$options['height'] = 480;

			$handler = "{handler: 'iframe', size: {x: ".$options['width'].", y: ".$options['height']. "}}";
		}

		$class = (!empty($options['class'])) ? $options['class'] : '';
		$title = (isset($options['title'])) ? $options['title'] : '';

		$html	= "<a style='display:inline;position:relative;' title=\"$title\" class=\"modal\" href=\"$url\" rel=\"$handler\" >\n";
		$html 	.= "<span class=\"".$class."\" >\n";
		$html   .= "$text\n";
		$html 	.= "</span>\n";
		$html	.= "</a>\n";
		return $html;
	}

	public static function getBrowser() {
		if(preg_match('/(?i)msie [2-9]/',$_SERVER['HTTP_USER_AGENT']))
		{
			return 'ie';
		}else {
			return 'good';
		}
	}


	/**
	 * Method to apply onclose update
	 * @param string $url
	 * @param string  $text
	 * @param array $options
	 * @return string html
	 */
	public static function popupAdvanced( $url, $text, $options = array() )
	{

		$html = "";
		$doc = JFactory::getDocument();
		$document = JFactory::getDocument();
		$js="function j2storeUpdate(){
		window.location.reload();
	}";

		$document->addScriptDeclaration( $js );
		if (!empty($options['update']))
		{
			$onclose = 'onClose: function(){j2storeUpdate(); },';
		}
		else
		{
			$onclose = '';
		}

		// set the $handler_string based on the user's browser
		$handler_string = "{handler:'iframe', ". $onclose ." size:{x: window.innerWidth-80, y: window.innerHeight-80}, onShow:$('sbox-window').setStyles({'padding': 0})}";

		if ( self::getBrowser() == 'ie')
		{
			// if IE, use
			$handler_string = "{handler:'iframe', ". $onclose ." size:{x:window.getSize().scrollSize.x-80, y: window.getSize().size.y-80}, onShow:$('sbox-window').setStyles({'padding': 0})}";
		}

		$handler = (!empty($options['img']))
		? "{handler:'image'}"
		: $handler_string;

		if (!empty($options['width']))
		{
			if (empty($options['height']))
			{
				$options['height'] = 480;
			}
			$handler = "{handler: 'iframe', ". $onclose ." size: {x: ".$options['width'].", y: ".$options['height']. "}}";
		}



		if(JFactory::getApplication()->isSite()){
			$id = (!empty($options['id'])) ? $options['id'] : '';
			$class = "zoom";
			$html	= "<a class=\"modal\" href=\"$url\" rel=\"$handler\" >\n";
			$html 	.= "<span class=\"".$class."\" id=\"".$id."\" >\n";
			$html   .= "$text\n";
			$html 	.= "</span>\n";
			$html	.= "</a>\n";
		}else{

			$id = (!empty($options['id'])) ? $options['id'] : '';
			$class = (!empty($options['class'])) ? $options['class'] : '';

			$html	= "<a class=\"modal\" href=\"$url\" rel=\"$handler\" >\n";
			$html 	.= "<span class=\"".$class."\" id=\"".$id."\" >\n";
			$html   .= "$text\n";
			$html 	.= "</span>\n";
			$html	.= "</a>\n";


		}

		return $html;
	}

}
