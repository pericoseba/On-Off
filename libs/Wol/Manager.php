<?php

/**
 * Class for functions who need to query directly the database, and not the model.
 */
class Wol_Manager {

	static private $_instance ;

	private function __construct() {	}

	/**
	 * Returns the unique object of the class
	 * (and creates it if it does not exist)
	 * @static
	 * @return Wol_Manager the unique instance of the class
	 */	
	static public function getInstance() {
		if ( (!isset(self::$_instance)) || (self::$_instance == NULL) ) {
			self::$_instance = new Wol_Manager();
		}
		return self::$_instance;
	}

	/**
	 * Queries the database to get all hosts that have a relationship with an user.
	 * @param string $userId the id of user
	 * @return array the list of serialized hosts associated to the user (with custom names if they exist)
	 */    
	
	public function getSerializedHostsFromUser($userId) {
		$hostsReturn = array();
		$query = sprintf("	SELECT UH.id_host HOSTID, UH.id_user
				FROM php_wol_users_hosts UH
				WHERE UH.id_user='%s'
				ORDER BY UH.id_host ASC;", $userId);
		$result = mysql_query ($query);

		$hostsManager = Wol_ManageHosts::getInstance();
	   while ($rowHosts = mysql_fetch_array($result)) {
	   	$hostsReturn[] = $hostsManager->getByID($rowHosts['HOSTID'])->getSerialized($userId);
	   }
	   return $hostsReturn;
	}

	/**
	 * Queries the database to get all farms the user is part of 
	 * @param string $userId the id of user
	 * @return array the list of serialized farms the user is part of (with custom names for hosts if they exist)
	 */

	public function getSerializedFarmsFromUser($userId) { 
		$farmsReturn = array();
		$queryFarms = sprintf("SELECT UHF.id_user, UHF.id_hostsfarm F_ID
			FROM php_wol_users_hostsfarms UHF
			WHERE UHF.id_user = %s
			ORDER BY UHF.id_hostsfarm ASC;", $userId);
		$result = mysql_query ($queryFarms);
		
		$farmsManager = Wol_ManageFarms::getInstance();
		while ($rowFarms = mysql_fetch_array($result)) {
			$farmsReturn[] = $farmsManager->getByID($rowFarms['F_ID'])->getSerialized(false, false, true, $userId);
		}
		return $farmsReturn;
	}

	/**
	 * Queries the database to get all users an host is associated with
	 * @param string $idHost the id of host
	 * @return array the list of users
	 */   	
	public function getUsersAssociated ($idHost) {
		$return = array();
		$query = sprintf("SELECT UH.id_host, UH.id_user USERID FROM php_wol_users_hosts UH WHERE UH.id_host = '%s' ORDER BY UH.id_user ASC;", $idHost);
		$result = mysql_query ($query);
		$usersManager = Wol_ManageUsers::getInstance();
	   while ($rowUsers = mysql_fetch_array($result)) {
	   	$return[] = $usersManager->getByID($rowUsers['USERID']);
	   }		
		return $return;
	}

}
?>
