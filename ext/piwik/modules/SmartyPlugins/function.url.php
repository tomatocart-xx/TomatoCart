<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: function.url.php 444 2008-04-11 13:38:22Z johmathe $
 * 
 * @package SmartyPlugins
 */

require_once "Url.php";

/**
 * Smarty {url} function plugin.
 * Generates a piwik URL with the specified parameters modified.
 *
 * Examples:
 * <pre>
 * {url module="API"} will rewrite the URL modifying the module GET parameter
 * {url module="API" method="getKeywords"} will rewrite the URL modifying the parameters module=API method=getKeywords
 * </pre>
 * 
 * @see Piwik_Url::getCurrentQueryStringWithParametersModified()
 * @param $name=$value of the parameters to modify in the generated URL
 * @return	string Something like index.php?module=X&action=Y 
 */
function smarty_function_url($params, &$smarty)
{
	return Piwik_Url::getCurrentScriptName() . Piwik_Url::getCurrentQueryStringWithParametersModified( $params );
}
