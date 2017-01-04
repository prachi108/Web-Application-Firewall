<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();
class J2Article {

	public static $instance = null;
	protected $state;

	public function __construct($properties=null) {

	}

	public static function getInstance(array $config = array())
	{
		if (!self::$instance)
		{
			self::$instance = new self($config);
		}

		return self::$instance;
	}
	
	

	/**
	 *
	 * @return unknown_type
	 */
	public function display( $articleid )
	{
		$html = '';
		if(empty($articleid)) {
			return;
		}
		//try loading language associations
		if(version_compare(JVERSION, '3.3', 'gt')) {
			$id = $this->getAssociatedArticle($articleid);
			if($id && is_int($id)) {
				$articleid = $id;
			}
		}
		$item = $this->getArticle($articleid);
		$mainframe = JFactory::getApplication();
		// Return html if the load fails
		if (!$item->id)
		{
			return $html;
		}
	
		$item->title = JFilterOutput::ampReplace($item->title);
	
		$item->text = '';
	
		$item->text = $item->introtext . chr(13).chr(13) . $item->fulltext;
	
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		$params		=$mainframe->getParams('com_content');
		$prepare_content = J2Store::config()->get('prepare_content', 0);
		if($prepare_content) {
			$html .= JHtml::_('content.prepare', $item->text);
		}else {
			$html .= $item->text;
		}	
	
		return $html;
	}
	
	public function getArticle($id) {
		static $sets;
	
		if ( !is_array( $sets ) )
		{
			$sets = array( );
		}
		if ( !isset( $sets[$id] ) )
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')->from('#__content')->where('id='.$id);
			$db->setQuery($query);
			$sets[$id] = $db->loadObject();
		}
		return $sets[$id];
	}
	
	public function getArticleByAlias($alias) {
		static $sets;

		if ( !is_array( $sets ) )
		{
			$sets = array( );
		}
		if ( !isset( $sets[$alias] ) )
		{

			$content_id = 0;
			if ( $this->isFalangInstalled() ){
				$config = J2Store::config();
				// get alternate content from falang tables
				if ( $config->get('enable_falang_support',0) ){
					$content_id = $this->loadFalangContentID($alias) ;
				}
			}

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')->from('#__content');
			if ( $content_id > 0 ) {
				$query->where($db->quoteName('id') . ' = ' . $db->quote($content_id));
			}else {
				$query->where($db->quoteName('alias') . ' = ' . $db->quote($alias));
				$tag = JFactory::getLanguage()->getTag();
				if($tag != '*' && !empty( $tag ) ){
					$query->where($db->quoteName('language') . ' IN (' . $db->quote($tag) .','.$db->quote ( '*' ).' )');
				}
			}
			$db->setQuery($query);
			try {
				$sets[$alias] = $db->loadObject();
			}catch(Exception $e) {
				$sets[$alias] = new stdClass();
			}
			
		}
		return $sets[$alias];
	}

	/**
	 * Check if Falang is installed
	 * @return bool true if falng is installed
	 * */
	public function isFalangInstalled() {
		if(JComponentHelper::isInstalled('com_falang')) {
			if(JComponentHelper::isEnabled('com_falang')) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check the falang table aliases and get corresponding content id
	 * @param string $alias article or product alias
	 * @return int    		content_id of the corresponding article or 0 if none
	 * */
	public function loadFalangContentID($alias='') {
		if (empty($alias)){
			return 0;
		}
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('reference_id')->from('#__falang_content')
			->where($db->quoteName('reference_table') . ' = ' . $db->quote('content'))
			->where($db->quoteName('reference_field') . ' = ' . $db->quote('alias'))
			->where($db->quoteName('published') . ' = 1')
			->where($db->quoteName('value') . ' = ' . $db->quote($alias));
		$db->setQuery($query);
		$content_id = $db->loadResult();
		if (empty($content_id)){
			$content_id = 0;
		}
		return $content_id;
	}
	
	public function getAssociatedArticle($id) {
	
		$associated_id =0;
		require_once JPATH_SITE . '/components/com_content/helpers/route.php';
	
		require_once(JPATH_SITE.'/components/com_content/helpers/association.php');
		$result = ContentHelperAssociation::getAssociations($id, 'article');
		$tag = JFactory::getLanguage()->getTag();
		if(isset($result[$tag])) {
			$parts = JString::parse_url($result[$tag]);
			parse_str($parts['query'], $vars);
			if(isset($vars['id'])) {
				$splits = explode(':', $vars['id']);
			}
			$associated_id = (int) $splits[0];
		}
	
		if(isset($associated_id) && $associated_id) {
			$id = $associated_id;
		}
		return $id;
	}
	
	public function getCategoryById($id) {
		if (! is_numeric ( $id ) || empty ( $id ))
			return new stdClass();
		
		static $csets;
		
		if (! is_array ( $csets )) {
			$csets = array ();
		}
		if (! isset ( $csets [$id] )) {
			$db = JFactory::getDbo ();
			$query = $db->getQuery ( true );
			$query->select ( '*' )->from ( '#__categories' )->where ( $db->quoteName ( 'id' ) . ' = ' . $db->quote ( $id ) );
			$db->setQuery ( $query );
			$csets [$id] = $db->loadObject ();
		}
		
		return $csets [$id];
	}
	
}	