<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;


class J2storeControllerReports extends F0FController
{
	public function __construct($config) {
		parent::__construct($config);
		$this->registerTask('apply', 'save');
		$this->registerTask('saveNew', 'save');
 		/* $task = JFactory::getApplication()->input->getString('task');
		if($task=='view') $this->view(); */
	}

	public function execute($task)
	{
		$app = JFactory::getApplication();
		$reportTask = $app->input->getCmd('reportTask', '');
		$values = $app->input->getArray($_POST);
		// Check if we are in a report method view. If it is so,
		// Try lo load the report plugin controller (if any)
		if ( $task  == "view" && $reportTask != '' )
		{
			$model = $this->getModel('Reports');

			 $id = $app->input->getInt('id', '0');

			if(!$id)
				parent::execute($task);

			$model->setId($id);

			// get the data
			// not using getItem here to enable ->checkout (which requires JTable object)
			$row = $model->getTable();
			$row->load( (int) $model->getId() );
			$element = $row->element;

			// The name of the Report Controller should be the same of the $_element name,
			// without the report_ prefix and with the first letter Uppercase, and should
			// be placed into a controller.php file inside the root of the plugin
			// Ex: report_standard => J2StoreControllerReportStandard in report_standard/controller.php
			$controllerName = str_ireplace('report_', '', $element);
			$controllerName = ucfirst($controllerName);

			$path = JPATH_SITE.'/plugins/j2store/';


			$controllerPath = $path.$element.'/'.$element.'/controller.php';

			if (file_exists($controllerPath)) {
				require_once $controllerPath;
			} else {
				$controllerName = '';
			}

			$className    = 'J2StoreControllerReport'.$controllerName;

			if ($controllerName != '' && class_exists($className)){

				// Create the controller
				$controller   = new $className();

				// Add the view Path
				$controller->addViewPath($path);

				// Perform the requested task
				$controller->execute( $reportTask );

				// Redirect if set by the controller
				$controller->redirect();

			} else{
				parent::execute($task);
			}
		} else{

			parent::execute($task);
		}
	}

	protected function onBeforeBrowse()
	{
		if(!$this->checkACL('j2store.reports'))
		{
			return false;
		}
		return parent::onBeforeBrowse();
	}

	 function view(){

		    if(!$this->checkACL('j2store.reports'))
		    {
			 	return false;
		    }

	    	$model = $this->getThisModel();
	    	$id = $this->input->getInt('id');
	    	$row = $model->getItem($id);
	    	$view   = $this->getThisView('Report');
	    	$view->setModel( $model, true );
	    	$view->set('row', $row );
	    	$view->setLayout( 'view' );
	    	$view->display();
	    }

}