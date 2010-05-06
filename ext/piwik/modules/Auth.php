<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Auth.php 444 2008-04-11 13:38:22Z johmathe $
 *
 * @package Piwik
 */

/**
 * Authentication object.
 * Should be reviewed and refactor to allow simple plugin overwrite
 * See OpenId authentication plugin, using Zend_Auth_OpenId on http://dev.piwik.org/trac/ticket/160
 * See Review the Login plugin to make it really modular  http://dev.piwik.org/trac/ticket/144
 * 
 * @package Piwik
 */
class Piwik_Auth extends Zend_Auth_Adapter_DbTable
{
	const SUCCESS_SUPERUSER_AUTH_CODE = 42;

	public function __construct()
	{
		$db = Zend_Registry::get('db');
		parent::__construct($db);
	}

	public function authenticate()
	{
		// we first try if the user is the super user
		$rootLogin = Zend_Registry::get('config')->superuser->login;
		$rootPassword = Zend_Registry::get('config')->superuser->password;
		$rootToken = Piwik_UsersManager_API::getTokenAuth($rootLogin,$rootPassword);

		//		echo $rootToken;
		//		echo "<br>". $this->_credential;exit;
		if($this->_identity == $rootLogin
		&& $this->_credential == $rootToken)
		{
			return new Piwik_Auth_Result(Piwik_Auth::SUCCESS_SUPERUSER_AUTH_CODE,
			$this->_identity,
			array() // message empty
			);
		}

		// we then look if the user is API authenticated
		// API authentication works without login name, but only with the token
		// TODO the logic (sql select) should be in the Login plugin, not here
		// this class should stay simple. Another Login plugin should only have to create an auth entry
		// of this class in the zend_registry and it should work
		if(is_null($this->_identity))
		{
			$authenticated = false;
				
			if($this->_credential === $rootToken)
			{
				return new Piwik_Auth_Result(Piwik_Auth::SUCCESS_SUPERUSER_AUTH_CODE,
											$rootLogin,
											array() // message empty
				);
			}
				
			$login = Zend_Registry::get('db')->fetchOne(
						'SELECT login FROM '.Piwik::prefixTable('user').' WHERE token_auth = ?',
						array($this->_credential)
			);
			if($login !== false)
			{
				return new Piwik_Auth_Result(Zend_Auth_Result::SUCCESS,
											$login,
											array() // message empty
											);
			}
			else
			{
				return new Piwik_Auth_Result( Zend_Auth_Result::FAILURE,
											$this->_identity,
											array()
											);
			}
		}

		// if not then we return the result of the database authentification provided by zend
		return parent::authenticate();
	}

	public function getTokenAuth()
	{
		return $this->_credential;
	}
}



/**
 *
 * @package Piwik
 */
class Piwik_Auth_Result extends Zend_Auth_Result
{
	public function __construct($code, $identity, array $messages = array())
	{
		$this->_code		= (int)$code;
		$this->_identity	= $identity;
		$this->_messages	= $messages;
	}
}
