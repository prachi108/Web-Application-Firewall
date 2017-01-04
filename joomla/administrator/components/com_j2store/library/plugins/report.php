<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/_base.php');
if ( !class_exists( 'J2StoreReportPlugin' ) )
{
	class J2StoreReportPlugin extends J2StorePluginBase
	{
		/**
		 * @var $_element  string  Should always correspond with the plugin's filename,
		 *                         forcing it to be unique
		 */
		var $_element = '';

		function __construct( &$subject, $config )
		{
			parent::__construct( $subject, $config );
			$this->loadLanguage( '', JPATH_ADMINISTRATOR );
			$this->loadLanguage( '', JPATH_SITE );
		}

		/************************************
		 * Note to 3pd:
		 *
		 * The methods between here
		 * and the next comment block are
		 * yours to modify by overrriding them in your shipping plugin
		 *
		 ************************************/


		public function onJ2StoreGetReportView($row){
			if ( !$this->_isMe( $row ) )
			{
				return null;
			}
		}


		/************************************
		 * Note to 3pd:
		 *
		 * DO NOT MODIFY ANYTHING AFTER THIS
		 * TEXT BLOCK UNLESS YOU KNOW WHAT YOU
		 * ARE DOING!!!!!
		 *
		 ************************************/

		/**
		 * Tells extension that this is a shipping plugin
		 *
		 * @param $element  string      a valid shipping plugin element
		 * @return boolean	true if it is this particular shipping plugin
		 */
		public function onJ2StoreGetReportPlugins( $element )
		{
			$success = false;
			if ( $this->_isMe( $element ) )
			{
				$success = true;
			}
			return $success;
		}



		/**
		 * Gets the reports namespace for state variables
		 * @return string
		 */
		protected function _getNamespace( )
		{
			$app = JFactory::getApplication( );
			$ns = $app->getName( ) . '::' . 'com.j2store.report.' . $this->get( '_element' );
		}

		/**
		 * Get the task for the shipping plugin controller
		 */
		public static function getReportTask( )
		{
		 	return JFactory::getApplication()->input->getString( 'reportTask', '' );

		}

		/**
		 * Get the id of the current shipping plugin
		 */
		public static function getReportId( )
		{
			return JFactory::getApplication()->input->getInt( 'sid', '' );
		}

		/**
		 * Get a variable from the JRequest object
		 * @param unknown_type $name
		 */
		public function getReportVar( $name )
		{
			$var = JFactory::getApplication()->input->getString( $name, '' );
			return $var;
		}




		/**
		 * Prepares the 'view' tmpl layout
		 * when viewing a report
		 *
		 * @return unknown_type
		 */
		function _renderView( $view = 'view', $vars = null )
		{
			if ( $vars == null ) $vars = new JObject( );
			$html = $this->_getLayout( $view, $vars );

			return $html;
		}

		/**
		 * Prepares variables for the report form
		 *
		 * @return unknown_type
		 */
		function _renderForm($data )
		{
			$vars = new JObject( );
			$html = $this->_getLayout( 'form', $vars );

			return $html;
		}

		/**
		 * Gets the appropriate values from the request
		 *
		 * @return unknown_type
		 */
		function _getState( )
		{
			$state = new JObject( );

			foreach ( $state->getProperties( ) as $key => $value )
			{
				$new_value = JRequest::getVar( $key );
				$value_exists = array_key_exists( $key, JRequest::get( 'post' ) );
				if ( $value_exists && !empty( $key ) )
				{
					$state->$key = $new_value;
				}
			}


			return $state;
		}

	}

}
