<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

require_once(JPATH_ADMINISTRATOR .'/components/com_j2store/helpers/j2html.php');

class JFormFieldGeozones extends JFormFieldList
{
	protected $type = 'geozones';
	function getInput(){
		return  J2Html::select()->clearState()
				->type('genericlist')
				->name($this->name)
				->value($this->value)
				->attribs(array('multiple'=>true))
				->setPlaceHolders(
						array('*'=>JText::_('J2STORE_SELECT_ALL'))
				)
				->hasOne('Geozones')
				->setRelations( array(
						'fields' => array (
								'key' => 'j2store_geozone_id',
								'name' => array('geozone_name')
						)
				)
			)->getHtml();
		}
}

