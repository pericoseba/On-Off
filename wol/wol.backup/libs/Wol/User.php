<?php

include_once( dirname(__FILE__) . '/Tools/hashpass.php');

/**
 * Class to authenticate the users with the On/Off database or with LDAP
 */
class Wol_User {
	static protected $_obj = null;
	static protected $_login = null;
	static protected $_id = null;
	static protected $_is_ldap_user = false;	

	private function __construct() { }

	/**
	 * Returns the unique object of the class
	 * (and creates it if it does not exist)
	 * @static
	 * @return Wol_User the unique instance of the class
	 */	
	static public function getInstance() {
		if ( (!isset(self::$_obj)) || (self::$_obj == NULL) ) {
			self::$_obj = new Wol_User();
		}
		return self::$_obj;
	}
	
	/**
	 * Tries to authenticate the user with the given login and password
	 * @param string $login the login entered by the user
	 * @param string $password the password entered by the user
	 * @return bool true if the user is authenticated, else false
	 */	
	public function authenticate($login, $password) {
		
		if ($this->ldapAuthenticate ($login, $password)) {
			$userData = $this->getLdapData($login);
			$usersManager = Wol_ManageUsers::getInstance();
			$user = $usersManager->getByLdapID($userData['ldapID']);
			self::$_is_ldap_user = true;

			if ($user === false) {
				$usersManager->createLdapUser($login, $userData['email'], $userData['ldapID'], 'user');
			}		
					
		} else {

			/* If the user is not in the ldap */
			$query = sprintf("SELECT login, pass, is_ldap_user FROM php_wol_users WHERE login='%s' AND pass = '%s' AND is_ldap_user = FALSE;", $login, hashPass($password));
			$result = mysql_query ($query);
			$row = mysql_fetch_array($result);

			if ($row === false) return false;
			
			self::$_is_ldap_user = false;
			self::$_obj;
		}

		$query = sprintf("SELECT U.login, U.id_user USERID, R.id_role, R.name ROLE, UR.id_user, UR.id_role
				FROM php_wol_users U, php_wol_roles R, php_wol_users_roles UR
				WHERE U.id_user = UR.id_user 
				AND R.id_role = UR.id_role
				AND U.login = '%s';", $login);

   	$result = mysql_query ($query);
   	$row = mysql_fetch_array($result);

		self::$_id = $row['USERID'];

		if (strcmp($row['ROLE'], 'admin') === 0 ) {
			$_SESSION['user'] = new Wol_Admin();
		} else {
			$_SESSION['user'] = $this;
		}
		self::$_login = $login;
		return true;
	}

      public function getPassword(){
		$id = self::$_id;
      }
	/**
	 * Tries to authenticate the user with LDAP
	 * @param string $login the login entered by the user
	 * @param string $password the password entered by the user
	 * @return bool true if the user has been authenticated by LDAP, else false
	 */		
	private function ldapAuthenticate ($login, $password) {
		$ldapManager = Ldap_Authentication::getInstance();
		$isAuthenticated = $ldapManager->authenticate($login, $password);
		return $isAuthenticated === true;
	}

	/**
	 * Returns the LDAP data of the user with given login
	 * @param string $login the login
	 * @return array the data found on the LDAP database
	 */	
	private function getLdapData ($login) {
		$ldapManager = Ldap_Authentication::getInstance();
		return $ldapManager->getLdapData($login);
	}	

	/**
	 * Return the status of the current user (connected or not)
	 * @return bool true if the user is authenticated, else false
	 */	
	public function isAuthenticated() {
		return isset($_SESSION['user']) && $_SESSION['user'] instanceof Wol_User;
	}

	/**
	 * Return the status of the current user (connected+admin or not)
	 * @return bool true if the user is authenticated and admin, false if he is not connected or not admin
	 */
	public function isAuthenticatedAndAdmin() {
		return isset($_SESSION['user']) && $_SESSION['user'] instanceof Wol_Admin;
	}

	/**
	 * Return the login of the current user
	 * @return string the login
	 */
	static public function getLogin() {
		return (self::$_login);
	}

	/**
	 * Return the user id of the current user
	 * @return string the id
	 */
	static public function getId() {
		return (self::$_id);
	}

	/**
	 * Indicates if the user is authenticated with LDAP or not
	 * @return bool true if yes, false if no
	 */	
	static public function IsLdapUser() {
		return (self::$_is_ldap_user);
	}
}

?>
