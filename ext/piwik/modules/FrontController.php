<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: FrontController.php 499 2008-05-29 03:30:45Z matt $
 * 
 * @package Piwik
 */


/**
 * Zend classes
 */
require_once "Zend/Exception.php";
require_once "Zend/Loader.php"; 
require_once "Zend/Auth.php";
require_once "Zend/Auth/Adapter/DbTable.php";

/**
 * Piwik classes
 */
require_once "Timer.php";
require_once "modules/Piwik.php";
require_once "API/APIable.php";
require_once "Access.php";
require_once "Auth.php";
require_once "API/Proxy.php";
require_once "Site.php";
require_once "Translate.php";
require_once "Mail.php";
require_once "Url.php";
require_once "Controller.php";

require_once "PluginsFunctions/Menu.php";
require_once "PluginsFunctions/AdminMenu.php";
require_once "PluginsFunctions/Widget.php";
require_once "PluginsFunctions/Sql.php";

/**
 * Front controller.
 * This is the class hit in the first place.
 * It dispatches the request to the right controller.
 * 
 * For a detailed explanation, see the documentation on http://dev.piwik.org/trac/wiki/MainSequenceDiagram
 * 
 * @package Piwik
 */
class Piwik_FrontController
{
	/**
	 * Set to false and the Front Controller will not dispatch the request
	 *
	 * @var bool
	 */
	static public $enableDispatch = true;
	
	static private $instance = null;
	
	/**
	 * returns singleton
	 * 
	 * @return Piwik_FrontController
	 */
	static public function getInstance()
	{
		if (self::$instance == null)
		{			
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	/**
	 * Dispatches the request to the right plugin and executes the requested action on the plugin controller.
	 * 
	 * @throws Exception in case the plugin doesn't exist, the action doesn't exist, there is not enough permission, etc.
	 *
	 * @param string $module
	 * @param string $action
	 * @param array $parameters
	 * @return mixed The returned value of the calls, often nothing as the module print but don't return data
	 * @see fetchDispatch() 
	 */
	function dispatch( $module = null, $action = null, $parameters = null)
	{
		if( self::$enableDispatch === false)
		{
			return;
		}
		
		if(is_null($module))
		{
			$defaultModule = 'Home';
			// load the module requested
			$module = Piwik_Common::getRequestVar('module', $defaultModule, 'string');
		}
		
		if(is_null($action))
		{
			$action = Piwik_Common::getRequestVar('action', false);
		}
		
		if(is_null($parameters))
		{
			$parameters = array();
		}
		
		if(!ctype_alnum($module))
		{
			throw new Exception("Invalid module name '$module'");
		}
		
		
		// check that the plugin is enabled
		if( ! Piwik_PluginsManager::getInstance()->isPluginEnabled( $module )) 
		{
			throw new Exception_PluginDeactivated($module);
		}
				
		
		$controllerClassName = "Piwik_".$module."_Controller";
		
		if(!class_exists($controllerClassName))
		{
			$moduleController = "plugins/" . $module . "/Controller.php";
			
			if( !Zend_Loader::isReadable($moduleController))
			{
				throw new Exception("Module controller $moduleController not found!");
			}
			require_once $moduleController;
		}
		
		$controller = new $controllerClassName;
		
		if($action === false)
		{
			$action = $controller->getDefaultAction();
		}
		
		if( !is_callable(array($controller, $action)))
		{
			throw new Exception("Action $action not found in the controller $controllerClassName.");				
		}
		
		try {
			return call_user_func_array( array($controller, $action ), $parameters);
		} catch(Piwik_Access_NoAccessException $e) {
			Piwik_PostEvent('FrontController.NoAccessException', $e);					
		}
	}
	
	/**
	 * Often plugins controller display stuff using echo/print.
	 * Using this function instead of dispath() returns the output form the actions calls.
	 *
	 * @param string $controllerName
	 * @param string $actionName
	 * @param array $parameters
	 * @return string
	 */
	function fetchDispatch( $controllerName = null, $actionName = null, $parameters = null)
	{
		ob_start();
		$output = $this->dispatch( $controllerName, $actionName, $parameters);
		// if nothing returned we try to load something that was printed on the screen
		if(empty($output))
		{
			$output = ob_get_contents();
		}
	    ob_end_clean();
	    return $output;
	}
	
	/**
	 * Called at the end of the page generation
	 *
	 */
	function __destruct()
	{
		try {
			Piwik::printSqlProfilingReportZend();
			Piwik::printQueryCount();
		} catch(Exception $e) {}
		
		if(Piwik::getModule() !== 'API')
		{
//			Piwik::printMemoryUsage();
//			Piwik::printTimer();
		}
	}
	
	/**
	 * Checks that the directories Piwik needs write access are actually writable
	 * Displays a nice error page if permissions are missing on some directories
	 * 
	 * @return void
	 */
	protected function checkDirectoriesWritableOrDie()
	{
		$resultCheck = Piwik::checkDirectoriesWritable( );
		if( array_search(false, $resultCheck) !== false )
		{ 
			$directoryList = '';
			foreach($resultCheck as $dir => $bool)
			{
				$dir = realpath($dir);
				if(!empty($dir) && $bool === false)
				{
					$directoryList .= "<code>chmod 777 $dir</code><br>";
				}
			}
			$directoryList .= '';
			
			$directoryMessage = "<p><b>Piwik couldn't write to some directories</b>.</p> <p>Try to Execute the following commands on your Linux server:</P>";
			$directoryMessage .= $directoryList;
			$directoryMessage .= "<p>If this doesn't work, you can try to create the directories with your FTP software, and set the CHMOD to 777 (with your FTP software, right click on the directories, permissions).";
			$directoryMessage .= "<p>After applying the modifications, you can <a href='index.php'>refresh the page</a>.";
			$directoryMessage .= "<p>If you need more help, try <a href='http://piwik.org'>Piwik.org</a>.";
			
			
			$html = '
				<html>
				<head>
					<title>Piwik &rsaquo; Error</title>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<style>
				
				html { background: #eee; }
				
				body {
					background: #fff;
					color: #000;
					font-family: Georgia, "Times New Roman", Times, serif;
					margin-left: 20%;
					margin-top: 25px;
					margin-right: 20%;
					padding: .2em 2em;
				}
				
				#h1 {
					color: #006;
					font-size: 45px;
					font-weight: lighter;
				}
				
				#subh1 {
					color: #879DBD;
					font-size: 25px;
					font-weight: lighter;
				}
				
				
				p, li, dt {
					line-height: 140%;
					padding-bottom: 2px;
				}
				
				ul, ol { padding: 5px 5px 5px 20px; }
				
				#logo { margin-bottom: 2em; }
				
				code { margin-left: 40px; }
				</style>
				</head>
				<body>
					<span id="h1">Piwik </span><span id="subh1"> # open source web analytics</span>
					<p>'.$directoryMessage.'</p>
				
				</body>
				</html>
				';
		
			print($html);
			exit;	
		}
	}
	
	/**
	 * Must be called before dispatch()
	 * - checks that directories are writable,
	 * - loads the configuration file,
	 * - loads the plugin, 
	 * - inits the DB connection,
	 * - etc.
	 * 
	 * @return void 
	 */
	function init()
	{
		Zend_Registry::set('timer', new Piwik_Timer);
		
		$this->checkDirectoriesWritableOrDie();
		
		$this->assignCliParametersToRequest();
		
		$exceptionToThrow = false;
		
		//move into a init() method
		try {
			Piwik::createConfigObject();
		} catch(Exception $e) {
			Piwik_PostEvent('FrontController.NoConfigurationFile', $e);
			$exceptionToThrow = $e;
		}
		
		Piwik::loadPlugins();
		
		if($exceptionToThrow)
		{
			throw $exceptionToThrow;
		}
		// database object
		Piwik::createDatabaseObject();
		
		// Create the log objects
		Piwik::createLogObject();
		
		
		Piwik::terminateLoadPlugins();
		
		Piwik::install();
		
//		Piwik::printMemoryUsage('Start program');

		// can be used for debug purpose
		$doNotDrop = array(
				Piwik::prefixTable('access'),
				Piwik::prefixTable('user'),
				Piwik::prefixTable('site'),
				Piwik::prefixTable('archive'),
				
				Piwik::prefixTable('logger_api_call'),
				Piwik::prefixTable('logger_error'),
				Piwik::prefixTable('logger_exception'),
				Piwik::prefixTable('logger_message'),
				
				Piwik::prefixTable('log_visit'),
				Piwik::prefixTable('log_link_visit_action'),
				Piwik::prefixTable('log_action'),
				Piwik::prefixTable('log_profiling'),
		);
		
		// Setup the auth object
		Piwik_PostEvent('FrontController.authSetCredentials');

		try {
			$authAdapter = Zend_Registry::get('auth');
		}
		catch(Exception $e){
			throw new Exception("Object 'auth' cannot be found in the Registry. Maybe the Login plugin is not enabled?
								<br>You can enable the plugin by adding:<br>
								<code>Plugins[] = Login</code><br>
								under the <code>[Plugins]</code> section in your config/config.inc.php");
		}
		
		// Perform the authentication query, saving the result
		$access = new Piwik_Access($authAdapter);
		Zend_Registry::set('access', $access);		
		Zend_Registry::get('access')->loadAccess();

		Piwik::raiseMemoryLimitIfNecessary();
	}
	
	/**
	 * Assign CLI parameters as if they were REQUEST or GET parameters.
	 * You can trigger Piwik from the command line by
	 * # /usr/bin/php5 /path/to/piwik/index.php -- "module=API&method=Actions.getActions&idSite=1&period=day&date=previous8&format=php"
	 *
	 * @return void
	 */
	protected function assignCliParametersToRequest()
	{
		if(isset($_SERVER['argc'])
			&& $_SERVER['argc'] > 0)
		{
			for ($i=1; $i < $_SERVER['argc']; $i++)
			{
				parse_str($_SERVER['argv'][$i],$tmp);
				$_REQUEST = array_merge($_REQUEST, $tmp);
				$_GET = array_merge($_GET, $tmp);
			}
		}				
	}
}
/**
 * Exception thrown when the requested plugin is not activated in the config file
 *
 * @package Piwik
 */
// TODO organize exceptions
class Exception_PluginDeactivated extends Exception
{
	function __construct($module)
	{
		parent::__construct("The plugin '$module' is not enabled. You can activate the plugin on the <a href='?module=AdminHome&action=showInContext&moduleToLoad=PluginsAdmin'>Plugins admin page</a>.");
	}
}
