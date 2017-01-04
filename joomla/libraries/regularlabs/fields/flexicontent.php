<?php
/**
 * @package         Regular Labs Library
 * @version         16.11.9943
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2016 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;
require_once dirname(__DIR__) . '/helpers/groupfield.php';

class JFormFieldRL_FlexiContent extends RLFormGroupField
{
	public $type          = 'FlexiContent';
	public $default_group = 'Tags';

	protected function getInput()
	{
		if ($error = $this->missingFilesOrTables(array('tags', 'types')))
		{
			return $error;
		}

		return $this->getSelectList();
	}

	function getTags()
	{
		$query = $this->db->getQuery(true)
			->select('t.name as id, t.name')
			->from('#__flexicontent_tags AS t')
			->where('t.published = 1')
			->order('t.name');
		$this->db->setQuery($query);
		$list = $this->db->loadObjectList();

		return $this->getOptionsByList($list);
	}

	function getTypes()
	{
		$query = $this->db->getQuery(true)
			->select('t.id, t.name')
			->from('#__flexicontent_types AS t')
			->where('t.published = 1')
			->order('t.name, t.id');
		$this->db->setQuery($query);
		$list = $this->db->loadObjectList();

		return $this->getOptionsByList($list);
	}
}
