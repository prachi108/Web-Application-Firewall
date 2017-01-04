<?php

/**
 * Project:     Securimage: A PHP class for creating and managing form CAPTCHA images<br />
 * File:        securimage_show_example.php<br />
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or any later version.<br /><br />
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.<br /><br />
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA<br /><br />
 *
 * Any modifications to the library should be indicated clearly in the source code
 * to inform users that the changes are not a part of the original software.<br /><br />
 *
 * If you found this script useful, please take a quick moment to rate it.<br />
 * http://www.hotscripts.com/rate/49400.html  Thanks.
 *
 * @link http://www.phpcaptcha.org Securimage PHP CAPTCHA
 * @link http://www.phpcaptcha.org/latest.zip Download Latest Version
 * @link http://www.phpcaptcha.org/Securimage_Docs/ Online Documentation
 * @copyright 2009 Drew Phillips
 * @author Drew Phillips <drew@drew-phillips.com>
 * @version 2.0.1 BETA (December 6th, 2009)
 * @package Securimage
 *
 */
ob_start();

define('_JEXEC', 1);

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

define('DS', DIRECTORY_SEPARATOR);

if (file_exists(dirname(__FILE__) . '/../../../../defines.php')) {
	include_once dirname(__FILE__) . '/../../../../defines.php';
}

if (!defined('_JDEFINES')) {
	define('JPATH_BASE', dirname(__FILE__) . '/../../../../');
	require_once JPATH_BASE.'/includes/defines.php';
}

require_once JPATH_BASE.'/includes/framework.php';
/* To use Joomla's Database Class */
require_once ( JPATH_BASE .DS.'libraries'.DS.'joomla'.DS.'factory.php' );

// Instantiate the application.
$app = JFactory::getApplication('site');

ob_end_clean();

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

include JPATH_SITE . DS . 'components' . DS . 'com_breezingforms' . DS . 'images' . DS . 'captcha' . DS . 'securimage.php';

$img = new securimage();

//Change some settings
$img->image_width = 230;
$img->image_height = 80;
$img->perturbation = 0.9;

$img->image_bg_color = new Securimage_Color("#6495ED");
$img->text_color = new Securimage_Color("#B0E0E6");
$img->line_color = new Securimage_Color("#B0E0E6");
$img->noise_color = new Securimage_Color("#B0E0E6");

$img->use_transparent_text = false;
$img->text_transparency_percentage = 60; // 100 = completely transparent
$img->num_lines = 15;
$img->image_signature = '';
$img->use_wordlist = true;

$img->show(JPATH_SITE . DS . 'components' . DS . 'com_breezingforms' . DS . 'images' . DS . 'captcha' . DS . 'backgrounds' . DS . 'bg6.jpg');
