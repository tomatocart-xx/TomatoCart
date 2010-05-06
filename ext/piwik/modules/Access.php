<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Access.php 441 2008-04-09 22:09:11Z matt $
 *
 * @package Piwik
 *
 */

require_once 'SitesManager/API.php';

/**
 * Class to handle User Access:
 * - loads user access from the DB
 * - make it easy to check that the current user has specific permissions (see check* methods)
 * 
 * In Piwik there are mainly 4 access levels
 * - no access
 * - VIEW access
 * - ADMIN access
 * - Super admin access
 *
 * An access level is on a per website basis.
 * A given user has a given access level for a given website.
 * For example:
 * User Noemie has
 * 	- VIEW access on the website 1,
 *  - ADMIN on the website 2 and 4, and
 *  - NO access on the website 3 and 5
 *
 * There is only one Super User ; he has ADMIN access to all the websites
 * and he only can change the main configuration settings.
 *
 * @package Piwik
 */

class Piwik_Access
{	
	/**
	 * Array of idsites available to the current user, indexed by permission level
	 * @see getSitesIdWith*()
	 *
	 * @var array
	 */
	protected $idsitesByAccess = null;
	
	/**
	 * Login of the current user
	 *
	 * @var string
	 */
	protected $identity = null;
	
	/**
	 * Defines if the current user is the super user
	 * @see isSuperUser()
	 * 
	 * @var bool
	 */
	protected $isSuperUser = false;

	
	/**
	 * List of available permissions in Piwik
	 *
	 * @var array
	 */
	static private $availableAccess = array('noaccess', 'view', 'admin', 'superuser');

	/**
	 * Returns the list of the existing Access level.
	 * Useful when a given API method requests a given acccess Level.
	 * We first check that the required access level exists.
	 */
	static public function getListAccess()
	{
		return self::$availableAccess;
	}

	/**
	 * @param Piwik_Auth The authentification object
	 */
	public function __construct( Piwik_Auth $auth )
	{
		$this->auth = $auth;
	}

	/**
	 * Loads the access levels for the current user.
	 *
	 * Calls the authentication method to try to log the user in the system.
	 * If the user credentials are not correct we don't load anything.
	 * If the login/password is correct the user is either the SuperUser or a normal user.
	 * We load the access levels for this user for all the websites.
	 * 
	 */
	public function loadAccess()
	{
		$idsitesByAccess = array( 'view' => array(), 'admin'  => array(), 'superuser'  => array());

		// access = array ( idsite => accessIdSite, idsite2 => accessIdSite2)
		$result = $this->auth->authenticate();

		if($result->isValid())
		{
			$this->identity = $result->getIdentity();
				
			// case the superUser is logged in
			if($result->getCode() == Piwik_Auth::SUCCESS_SUPERUSER_AUTH_CODE)
			{
				$this->isSuperUser = true;
				$idsitesByAccess['superuser'] = Piwik_SitesManager_API::getAllSitesId();
			}
			// valid authentification (normal user logged in)
			else
			{
				$db = Zend_Registry::get('db');

				// we join with site in case there are rows in access for an idsite that doesn't exist anymore
				// (backward compatibility ; before we deleted the site without deleting rows in _access table)
				$accessRaw = $db->fetchAll("SELECT access, t2.idsite
								  FROM ".Piwik::prefixTable('access'). " as t1 
									JOIN ".Piwik::prefixTable('site')." as t2 USING (idsite) ".
								" WHERE login=?", $this->identity);

				foreach($accessRaw as $access)
				{
					$idsitesByAccess[$access['access']][] = $access['idsite'];
				}
			}
		}

		$this->idsitesByAccess = $idsitesByAccess;
	}
	
	/**
	 * We bypass the normal auth method and give the current user Super User rights.
	 * This should be very carefully used.
	 * 
	 * @return void
	 */
	public function setSuperUser()
	{
		$this->isSuperUser = true;
		$this->idsitesByAccess['superuser'] = Piwik_SitesManager_API::getAllSitesId();
	}
	
	/**
	 * Returns true if the current user is logged in as the super user
	 *
	 * @return bool
	 */
	public function isSuperUser()
	{
		return $this->isSuperUser;
	}
	
	/**
	 * Returns the current user login
	 * @return string
	 */
	public function getIdentity()
	{
		return $this->identity;
	}

	/**
	 * Returns an array of ID sites for which the user has at least a VIEW access.
	 * Which means VIEW or ADMIN or SUPERUSER.
	 *
	 * @return array Example if the user is ADMIN for 4
	 *              and has VIEW access for 1 and 7, it returns array(1, 4, 7);
	 */
	public function getSitesIdWithAtLeastViewAccess()
	{
		return array_unique(array_merge(
		$this->idsitesByAccess['view'],
		$this->idsitesByAccess['admin'],
		$this->idsitesByAccess['superuser']));
	}


	/**
	 * Returns an array of ID sites for which the user has an ADMIN access.
	 *
	 * @return array Example if the user is ADMIN for 4 and 8
	 *              and has VIEW access for 1 and 7, it returns array(4, 8);
	 */
	public function getSitesIdWithAdminAccess()
	{
		return array_unique(array_merge(
		$this->idsitesByAccess['admin'],
		$this->idsitesByAccess['superuser']));
	}


	/**
	 * Returns an array of ID sites for which the user has a VIEW access only.
	 *
	 * @return array Example if the user is ADMIN for 4
	 *              and has VIEW access for 1 and 7, it returns array(1, 7);
	 * @see getSitesIdWithAtLeastViewAccess()
	 */
	public function getSitesIdWithViewAccess()
	{
		return 	$this->idsitesByAccess['view'];
	}

	/**
	 * Throws an exception if the user is not the SuperUser
	 * 
	 * @throws Exception
	 */
	public function checkUserIsSuperUser()
	{
		if($this->isSuperUser === false)
		{
			throw new Piwik_Access_NoAccessException("You can't access this resource as it requires a 'superuser' access.");
		}
	}

	/**
	 * If the user doesn't have an ADMIN access for at least one website, throws an exception
	 * 
	 * @throws Exception
	 */
	public function checkUserHasSomeAdminAccess()
	{
		$idSitesAccessible = $this->getSitesIdWithAdminAccess();
		if(count($idSitesAccessible) == 0)
		{
			throw new Piwik_Access_NoAccessException("You can't access this resource as it requires an 'admin' access for at least one website.");
		}
	}

	/**
	 * This method checks that the user has ADMIN access for the given list of websites.
	 * If the user doesn't have ADMIN access for at least one website of the list, we throw an exception.
	 * 
	 * @param int|arrayOfIntegers List of ID sites to check
	 * @throws Exception If for any of the websites the user doesn't have an ADMIN access
	 */
	public function checkUserHasAdminAccess( $idSites )
	{
		if(!is_array($idSites))
		{
			$idSites = array($idSites);
		}
		$idSitesAccessible = $this->getSitesIdWithAdminAccess();
		foreach($idSites as $idsite)
		{
			if(!in_array($idsite, $idSitesAccessible))
			{
				throw new Piwik_Access_NoAccessException("You can't access this resource as it requires an 'admin' access for the website id = $idsite.");
			}
		}
	}


	/**
	 * This method checks that the user has VIEW or ADMIN access for the given list of websites.
	 * If the user doesn't have VIEW or ADMIN access for at least one website of the list, we throw an exception.
	 * 
	 * @param int|arrayOfIntegers List of ID sites to check
	 * @throws Exception If for any of the websites the user doesn't have an VIEW or ADMIN access
	 */
	public function checkUserHasViewAccess( $idSites )
	{
		if(!is_array($idSites))
		{
			$idSites = array($idSites);
		}
		$idSitesAccessible = $this->getSitesIdWithAtLeastViewAccess();

		foreach($idSites as $idsite)
		{
			if(!in_array($idsite, $idSitesAccessible))
			{
				throw new Piwik_Access_NoAccessException("You can't access this resource as it requires a 'view' access for the website id = $idsite.");
			}
		}
	}
}

/**
 *
 * Exception thrown when a user doesn't  have sufficient access.
 * 
 * @package Piwik
 */
class Piwik_Access_NoAccessException extends Exception
{}