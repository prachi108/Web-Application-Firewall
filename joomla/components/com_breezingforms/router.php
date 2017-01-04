<?php
defined('_JEXEC') or die('Direct Access to this location is not allowed.');
/**
 * @package     BreezingForms
 * @author      Markus Bopp
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
 */
jimport('joomla.version');

function BreezingformsBuildRoute(&$query) {

    $segments = array();
    
    if(isset($query['ff_applic']) && $query['ff_applic'] == 'com_tags'){
        
        if($query['found_menu'] == 'false'){
            $segments[0] = 'form';
            $segments[1] = isset($query['menuitemid']) ? $query['menuitemid'] : $query['Itemid'];
        }else{
            $segments[0] = '';
            $segments[1] = '';
        }
        $segments[2] = $query['ff_form'] . '-' . $query['title'];
        
        if(isset($query['found_menu'])){
            unset($query['found_menu']);
        }
        if(isset($query['ff_applic'])){
            unset($query['ff_applic']);
        }
        if(isset($query['ff_form'])){
            unset($query['ff_form']);
        }
        if(isset($query['title'])){
            unset($query['title']);
        }
        if(isset($query['menuitemid'])){
            if(isset($query['Itemid'])){
                unset($query['Itemid']);
            }
            if(isset($query['itemid'])){
                unset($query['itemid']);
            }
        }
        if(isset($query['menuitemid'])){
            unset($query['menuitemid']);
        }
    } else {
        
        foreach($query As $key => $value){
            if( !in_array($key, array('option', 'Itemid', 'lang')) ){
                $segments[] = $key;
                $segments[] = $value;
            }
        }
    }
    
    if(isset($query['view'])){
        unset($query['view']);
    }
    
    return $segments;
}

function BreezingformsParseRoute($segments) {
    
    $vars = array();
    
    if(isset($segments[0]) && $segments[0] == 'form'){
        $exploded = explode(',', $segments[2]);
        $vars['ff_applic'] = 'com_tags';
        if($segments[1] != ''){
            $vars['Itemid'] = $segments[1];
        }
        $_exploded = explode(':', $exploded[0]);
        $vars['ff_form'] = $_exploded[0];
        unset($exploded[0]);
        $vars['title'] = implode('-', $exploded);
        
    }else{
    
        $key = '';
        $last_key = '';
        $value = '';
        $seglength = count($segments);
        for($i = 0; $i < $seglength; $i++){
            if($i % 2 == 0){
                $vars[$segments[$i]] = '';
                $last_key = $segments[$i];
            }else{
                $vars[$last_key] = $segments[$i];
            }
        }
    }
    return $vars;
}
