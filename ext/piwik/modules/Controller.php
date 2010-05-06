<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Controller.php 444 2008-04-11 13:38:22Z johmathe $
 * 
 * @package Piwik
 */

/**
 * Parent class of all plugins Controllers (located in /plugins/PluginName/Controller.php
 * It defines some helper functions controllers can use.
 * 
 * @package Piwik
 */
abstract class Piwik_Controller
{
	/**
	 * Plugin name, eg. Referers
	 * @var string
	 */
	protected $pluginName;
	
	/**
	 * Date string
	 * 
	 * @var string
	 */
	protected $strDate;
	
	/**
	 * Piwik_Date object or null if the requested date is a range
	 * 
	 * @var Piwik_Date|null 
	 */
	protected $date;
	
	/**
	 * Builds the controller object, reads the date from the request, extracts plugin name from 
	 *
	 */
	function __construct()
	{
		$aPluginName = explode('_', get_class($this));
		$this->pluginName = $aPluginName[1];
		$this->strDate = Piwik_Common::getRequestVar('date', 'yesterday','string');
		
		// the date looks like YYYY-MM-DD we can build it
		try{
			$this->date = Piwik_Date::factory($this->strDate);
			$this->strDate = $this->date->toString();
		} catch(Exception $e){
			// the date looks like YYYY-MM-DD,YYYY-MM-DD or other format
			// case the date looks like a range
			$this->date = null;
		}
	}
	
	/**
	 * Returns the name of the default method that will be called 
	 * when visiting: index.php?module=PluginName without the action parameter
	 * 
	 * @return string
	 */
	function getDefaultAction()
	{
		return 'index';
	}
	
	/**
	 * Given an Object implementing Piwik_iView interface, we either:
	 * - echo the output of the rendering if fetch = false
	 * - returns the output of the rendering if fetch = true
	 *
	 * @param Piwik_ViewDataTable $view
	 * @param bool $fetch
	 * @return string|void
	 */
	protected function renderView( Piwik_ViewDataTable $view, $fetch)
	{
		$view->main();
		$rendered = $view->getView()->render();
		if($fetch)
		{
			return $rendered;
		}
		echo $rendered;
	}
	
	/**
	 * Returns a ViewDataTable object of an Evolution graph 
	 * for the last30 days/weeks/etc. of the current period, relative to the current date.
	 *
	 * @param string $currentModuleName
	 * @param string $currentControllerAction
	 * @param string $apiMethod
	 * @return Piwik_ViewDataTable_Graph_ChartEvolution
	 */
	protected function getLastUnitGraph($currentModuleName, $currentControllerAction, $apiMethod)
	{
		require_once "ViewDataTable/Graph.php";
		$view = Piwik_ViewDataTable::factory('graphEvolution');
		$view->init( $currentModuleName, $currentControllerAction, $apiMethod );
		
		// if the date is not yet a nicely formatted date range ie. YYYY-MM-DD,YYYY-MM-DD we build it
		// otherwise the current controller action is being called with the good date format already so it's fine
		// see constructor
		if( !is_null($this->date))
		{
			$view->setParametersToModify( 
				$this->getGraphParamsModified( array('date'=>$this->strDate))
				);
		}
		
		return $view;
	}
	
	
	/**
	 * Returns the array of new processed parameters once the parameters are applied.
	 * For example: if you set range=last30 and date=2008-03-10, 
	 *  the date element of the returned array will be "2008-02-10,2008-03-10"
	 * 
	 * Parameters you can set:
	 * - range: last30, previous10, etc.
	 * - date: YYYY-MM-DD, today, yesterday
	 * - period: day, week, month, year
	 * 
	 * @param array  paramsToSet = array( 'date' => 'last50', 'viewDataTable' =>'sparkline' )
	 */
	protected function getGraphParamsModified($paramsToSet = array())
	{
		if(!isset($paramsToSet['range']))
		{
			$range = 'last30';
		}
		else
		{
			$range = $paramsToSet['range'];
		}
		
		if(!isset($paramsToSet['date']))
		{
			$endDate = $this->strDate;
		}
		else
		{
			$endDate = $paramsToSet['date'];
		}
		
		if(!isset($paramsToSet['period']))
		{
			$period = Piwik_Common::getRequestVar('period');
		}
		else
		{
			$period = $paramsToSet['period'];
		}
		
		$last30Relative = new Piwik_Period_Range($period, $range );
		
		$last30Relative->setDefaultEndDate(new Piwik_Date($endDate));
		
		$paramDate = $last30Relative->getDateStart()->toString() . "," . $last30Relative->getDateEnd()->toString();
		
		$params = array_merge($paramsToSet , array(	'date' => $paramDate ) );
		
		return $params;
	}
	
	/**
	 * Returns a numeric value from the API.
	 * Works only for API methods that originally returns numeric values (there is no cast here)
	 *
	 * @param string $methodToCall, eg. Referers.getNumberOfDistinctSearchEngines
	 * @return int|float
	 */
	protected function getNumericValue( $methodToCall )
	{
		$requestString = 'method='.$methodToCall.'&format=original';
		$request = new Piwik_API_Request($requestString);
		return $request->process();
	}

	/**
	 * Returns the current URL to use in a <img src=X> to display a sparkline.
	 * $action must be the name of a Controller method that requests data using the Piwik_ViewDataTable::factory
	 * It will automatically build a sparkline by setting the viewDataTable=sparkline parameter in the URL.
	 * It will also computes automatically the 'date' for the 'last30' days/weeks/etc. 
	 *
	 * @param string $action, eg. method name of the controller to call in the img src
	 * @return string the generated URL
	 */
	protected function getUrlSparkline( $action )
	{
		$params = $this->getGraphParamsModified( 
					array(	'viewDataTable' => 'sparkline', 
							'action' => $action,
							'module' => $this->pluginName)
				);
		$url = Piwik_Url::getCurrentQueryStringWithParametersModified($params);
		return $url;
	}
	
	
}