<?php

/**
 * Manages the data about hosts stored in the model and in the database
 */
class Wol_ManageHosts {
	
	static protected $_hosts = array();
	static protected $_instance ;

	/**
	 * Queries the database and constructs the model
	 */
	private function __construct() {
		$queryListHosts = '	SELECT H.id_host HOSTID, H.owner_id OWNID, H.status STATUS, H.mac MAC, H.ip IP, H.name HNAME, U.id_user, U.login UNAME
				FROM php_wol_hosts H, php_wol_users U
				WHERE H.owner_id = U.id_user
				ORDER BY H.id_host;';
		$listHosts = mysql_query ($queryListHosts);

		while ( $rowListHosts = mysql_fetch_array($listHosts) ) {
			$host2add = new Wol_Host();
			$host2add->setMac($rowListHosts['MAC']);
			$host2add->setInetAddr($rowListHosts['IP']);
			$host2add->setHostName($rowListHosts['HNAME']);
			$host2add->setID($rowListHosts['HOSTID']);
			$host2add->setOwnerID($rowListHosts['OWNID']);
			$host2add->setOwnerName($rowListHosts['UNAME']);
			$host2add->setStatus($rowListHosts['STATUS']);
			$this->_hosts[] = $host2add;
		}
	}

	/**
	 * Returns the unique object of the class
	 * (and creates it if it does not exist)
	 * @static
	 * @return Wol_ManageHosts the unique instance of the class
	 */	
	static public function getInstance() {
		if ( (!isset(self::$_instance)) || (self::$_instance == NULL) ) {
			self::$_instance = new Wol_ManageHosts();
		}
		return self::$_instance;
	}

	/**
	 * Searches an host in this class' list and deletes it
	 * @param int $id the id of the host we want to delete
	 */
	protected function removeHostFromList ($id) {
		foreach ($this->_hosts as $h) {
			if ($h->getID() === $id) {
				unset($h);
			}
		}
	}

	/**
	 * Adds an host to this class' list
	 * @param Wol_Host $newHost the new host
	 */	
	protected function addHostToList ($newHost) {
		$this->_hosts[] = $newHost;
	}

	/**
	 * Returns the list of hosts
	 * @return array List of Wol_Host
	 */
	public function getHosts () {
		return $this->_hosts;
	}

	/**
	 * Searches a host in this class' list
	 * @param int $id the id of the host we want to get
	 * @return Wol_Host|bool the host with the given id (or false if it does not exist)
	 */
	public function getByID ($id) {
		$nbHosts = count ($this->_hosts);
		for ($i = 0; $i < $nbHosts ;$i++) {
			if ( $this->_hosts[$i]->getID() === $id ) return $this->_hosts[$i];
		}
		return false;
	}

	/**
	 * Deletes the host from the database and from the model
	 * @param int $id the id of the host to delete
	 * @return bool true if the host has been deleted from the database, else false
	 */
	public function deleteHost ($id) {
		$query = sprintf("DELETE FROM php_wol_hosts WHERE id_host = '%s' ;", $id);
		$result = mysql_query ($query);
		if ($result) {
			$this->removeHostFromList($id);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Creates a new host in the database and in the model
	 * @param string $mac the mac address of the new host
	 * @param string $hostName the hostname
	 * @param string $inetAddr the IP address
	 * @param int $owner the host's owner id
	 */

	public function createHost ($mac, $hostName, $inetAddr, $owner) {
		$query = sprintf("INSERT INTO php_wol_hosts (ip, mac, name, owner_id) VALUES ('%s', '%s', '%s', '%s');", $inetAddr, $mac, $hostName, $owner);
		$result = mysql_query ($query);    
		if ($result !== false) {
			$newHost = new Wol_Host();
			$newHost->setMac($mac);
			$newHost->setInetAddr($inetAddr);
			$newHost->setHostName($hostName);
			$newHost->setOwnerID($owner);

		/* Search the host id */
   		$queryID = sprintf("SELECT H.name, H.id_host IDH
				FROM php_wol_hosts H
				WHERE H.name = '%s';", $hostName);
   		$resultID = mysql_query ($queryID);
   		$rowID = mysql_fetch_array($resultID);

			$newHost->setID($rowID['IDH']);
			$this->addHostToList($newHost);
			
			// Add the stats in the Database
			$statsManager = Statistics_ManageStats::getinstance();
			$statsManager->addHostStats($rowID['IDH']);
		}
	}

	/**
	 * Changes the mac address of an host in the database and in the model
	 * @param int $id the id of the host
	 * @param string $newMac the new value of mac address
	 * @return bool true on success, false on failure
	 */

	public function changeMac ($id, $newMac) {
		$query = sprintf ('UPDATE php_wol_hosts SET mac = "%s" WHERE id_host = "%s"', $newMac, $id);
		$result = mysql_query ($query);
	   if (($hst = $this->getByID($id)) !== false) $hst->setMac($newMac);
		return ($result !== false);
	}

	/**
	 * Changes the IP address of an host in the database and in the model
	 * @param int $id the id of the host
	 * @param string $newInetAddr the new value of IP address
	 * @return bool true on success, false on failure
	 */	
	public function changeInetAddr ($id, $newInetAddr) {
		$query = sprintf ('UPDATE php_wol_hosts SET ip = "%s" WHERE id_host = "%s"', $newInetAddr, $id);
		$result = mysql_query ($query);
		if (($hst = $this->getByID($id)) !== false) $hst->setInetAddr($newInetAddr);
		return ($result !== false);
	}

	/**
	 * Changes the owner of an host in the database and in the model
	 * @param int $id the id of the host
	 * @param int $newOwnerID the id of the new owner
	 * @return bool true on success, false on failure
	 */	
	public function changeOwner ($id, $newOwnerID) {
		$query = sprintf ('UPDATE php_wol_hosts SET owner_id = "%s" WHERE id_host = "%s"', $newOwnerID, $id);
		$result = mysql_query ($query);
		if (($hst = $this->getByID($id)) !== false) $hst->setOwnerID($newOwnerID);
		return ($result !== false);
	}
	
	/**
	 * Changes the owner of all hosts in the database and in the model
	 * @param int $oldOwnerID the id of the old owner
	 * @param int $newOwnerID the id of the new owner
	 * @return bool true on success, false on failure
	 */

	public function deletedOwner ($oldOwnerID, $newOwnerID, $newOwnerName) {
		$query = sprintf ('UPDATE php_wol_hosts SET owner_id = "%s" WHERE owner_id = "%s"', $newOwnerID, $oldOwnerID);
		$result = mysql_query ($query);
		foreach ($this->_hosts as $host) {
			if (($host ->getOwnerID()) === $oldOwnerID) {
				$host->setOwnerID($newOwnerID);
				$host->setOwnerName($newOwnerName);
			}
		}
	}

	/**
	 * Updates the status (online/outline) of the host
	 * This function is called by Cron script every XX minutes
	 */

	public function updateStatus ($hostID) {

		foreach ($this->_hosts as $host) {

		      if($hostID == $host->getID()){

			$pingCommand = sprintf( "$( if ( ping -c 1 -t 1 %s >> /dev/null ) ; then exit 0; else exit 1; fi; );", $host->getInetAddr());
			exec($pingCommand, $output, $return);
			$query = sprintf ('UPDATE php_wol_hosts SET status = %s WHERE id_host = "%s";', $return , $host->getID());
			$host->setStatus($return);
			$result = mysql_query ($query);

		      }

		}

	}

	/**
	 * Sends broadcast pings on the local network, gets the list of existing hosts and then returns these host
	 * @return array Contains a list of hosts data. For each host : ip, mac and name
	 */	
	public function hostsDiscover(){


		$return = array();
		$i = 0;

		exec("ping -c 3 -b 255.255.255.255");
		exec('/usr/sbin/arp -a', $output, $ret);

		foreach ($output as $line) {
			$patternIP = "/(([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})[^0-9]/";
			preg_match($patternIP, $line, $ipAddr);
			$patternMac = "/([0-9a-fA-F]{2})([:][0-9a-fA-F]{2}){5}/";
			preg_match($patternMac, $line, $macAddr);
			$patternName = "/^[^\ ]*/";
			preg_match($patternName, $line, $name);
			$return[] = array("id" => $i++, "ip" => $ipAddr[1], "mac" => $macAddr[0], "name" => $name[0]);
		}
		return $return;
	}

	/**
	 * Serializes all hosts
	 * @param bool $onlyNamesAndID if true, only the id and names of hosts will be returned (default false : all data are returned)
	 * @return array The list of hosts with the required data
	 */

	public function getSerialized ($onlyNamesAndID=false) {
		$return = array();
		foreach ($this->_hosts as $host) {
			$return[]=$host->getSerialized(NULL, $onlyNamesAndID);
		}
		return $return;
	}
	
	/**
	 * Sends broadcast pings on the local network, gets the list of hosts and compares mac address with existing mac in database.
	 * if the ip address is different, it is updated in the database.
	 */	
	public function ipUpdate() {
		$return = array();
		$i = 0;
		exec("ping -c 3 -b 255.255.255.255");
		exec('/usr/sbin/arp -a', $output, $ret);
		foreach ($output as $line) {
			$patternIP = "/(([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3})[^0-9]/";
			preg_match($patternIP, $line, $ipAddr);
			$patternMac = "/([0-9a-fA-F]{2})([:][0-9a-fA-F]{2}){5}/";
			preg_match($patternMac, $line, $macAddr);
			
			foreach ($this->_hosts as $host) {
				if ($host->getFormattedMac() === $macAddr[0]) {
					if ($host->getInetAddr() !== $ipAddr[1]) {
						$this->changeInetAddr($host->getID(), $ipAddr[1]);
					}
				}
			}
		}
	}
		
}
?>
