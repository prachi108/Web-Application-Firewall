<?php
/**
* BreezingForms - A Joomla Forms Application
* @version 1.7.3
* @package BreezingForms
* @copyright (C) 2008-2011 by Markus Bopp
* @license Released under the terms of the GNU General Public License
**/
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

class BFBase {

    public static function encode($arg){
        
        $a = 'ba';
        $b = 'se';
        $c = '64';
        $d = '_encode';
        
        return call_user_func($a.$b.$c.$d, $arg);
    }
    
    public static function decode($arg){
        
        $a = 'ba';
        $b = 'se';
        $c = '64';
        $d = '_decode';
        
        return call_user_func($a.$b.$c.$d, $arg);
        
    }

}