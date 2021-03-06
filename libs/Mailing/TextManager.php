<?php

/**
 * Manages the email texts in the database
 */
class Mailing_TextManager {
	
	static private $_instance ;
	
	private function __construct() { }

	/**
	 * Returns the unique object of the class
	 * (and creates it if it does not exist)
	 * @static
	 * @return Mailing_TextManager the unique instance of the class
	 */	
	static public function getInstance() {
		if ( (!isset(self::$_instance)) || (self::$_instance == NULL) ) {
			self::$_instance = new Mailing_TextManager();
		}
		return self::$_instance;
	}

	/**
	 * Returns the id of the action with given name
	 * @param string $actionName The name of action
	 * @return int the id
	 */
	public function getActionID ($actionName) {
		$query = 'SELECT id_event ID FROM php_wol_events WHERE name_event = "' .$actionName. '"';
		$result = mysql_query ($query);
		$row = mysql_fetch_array($result);
		return $row['ID'];	
	}
	
	/**
	 * Returns the data stored in the database, with tokens replaced
	 * @param string $actionID The id of action
	 * @param array $tokensArray The values of tokens
	 * @return array|bool the array of data contained in the database with the tokens replaced in the text
	 */
	public function getEmail ($actionID, $tokensArray, $lang="EN") {
		$data = $this->getData($actionID, $lang);
		$text = $data['text'];
	 	foreach ($tokensArray as $TOKNAME => $TOKVAL) {
	 		$text = str_replace("%".$TOKNAME."%", $TOKVAL, $text);
	 	}
	 	return array(
			'subject' => $data['subject'],
			'sender' => $data['sender'],
			'text' => $text,
	 	);
	}

	/**
	 * Returns the data stored in the database
	 * @param string $actionID The id of action
	 * @param string $lang The language (default : "EN" => English)
	 * @return array|bool the array of data contained in the database 
	 */
	public function getData ($actionID, $lang="EN") {
		$query = 'SELECT T.sender_mail SENDER, T.subject SUBJ, T.text TXT, T.token_list TOK, T.isActive ACTIVE FROM php_wol_textmails T ' .
				'WHERE id_event = ' .$actionID . ' ' .
				'AND lang = "' . $lang . '"';
		$result = mysql_query ($query); 
		if ($result === false) { 
			return false;
		} else { 
			$row = mysql_fetch_array($result);
	//		var_dump($row['ACTIVE']);
			return array(
				'subject' => $row['SUBJ'],
				'sender' => $row['SENDER'],
				'text' => $row['TXT'],
				'tokens' => $row['TOK'],
				'isActive' => ($row['ACTIVE'] == 1)?true:false
			);
		}
	}

	/**
	 * Returns the data stored in the database
	 * @param string $actionName The name of action
	 * @return bool true if we have to send the mail, else false
	 */
	public function haveToSend ($actionName) {
		$query = 'SELECT isActive FROM php_wol_textmails ' .
				'WHERE id_event = (SELECT id_event FROM php_wol_events WHERE name_event = "' . $actionName . '") ' .
				'AND isActive = TRUE;';
		$result = mysql_query ($query); 
		return (($row = mysql_fetch_array($result)) !== false);
	}

	/**
	 * Updates the value of email for the action and the language in the database 
	 * (and creates it if it does not exist) 
	 * @param string $actionID The id of action
	 * @param string $lang The language
	 * @param string $sender The sender's email
	 * @param string $subject The subject of email
	 * @param string $text The text of email
	 * @param string $isActive true is an email must be sent for this type of action, else false
	 * @return bool true on success, false on failure
	 */	
	public function setMailSettings ($actionID, $lang, $sender, $subject, $text, $isActive) {
		if ($this->existsText ($actionID, $lang)) {
			$this->updateMailSettings ($actionID, $lang, $sender, $subject, $text, $isActive);
		} else {
			$this->createNewLangForText ($actionID, $lang, $sender, $subject, $text, $isActive);
		}
	}
	
	/**
	 * Updates the value of email in the database
	 * @param string $actionID The id of action
	 * @param string $lang The language
	 * @param string $sender The sender's email
	 * @param string $subject The subject of email
	 * @param string $text The text of email
	 * @param string $isActive true is an email must be sent for this type of action, else false
	 * @return bool true on success, false on failure
	 */	
	protected function updateMailSettings ($actionID, $lang, $sender, $subject, $text, $isActive) {
		$result = true;
		$query = 'UPDATE php_wol_textmails SET text = "'.$text.'" WHERE id_event = ' . $actionID . ' AND lang = "'.$lang.'"';
		$result &= mysql_query ($query);
		$query = 'UPDATE php_wol_textmails SET sender_mail = "'.$sender.'" WHERE id_event = ' . $actionID . ' AND lang = "'.$lang.'"';
		$result &= mysql_query ($query);
		$query = 'UPDATE php_wol_textmails SET subject = "'.$subject.'" WHERE id_event = ' . $actionID . ' AND lang = "'.$lang.'"';
		$result &= mysql_query ($query);
		$query = 'UPDATE php_wol_textmails SET isActive = "'.$isActive.'" WHERE id_event = ' . $actionID . ' AND lang = "'.$lang.'"';
		$result &= mysql_query ($query);
		return $result; 
	}
	
	/**
	 * Creates new language for an action
	 * @param string $actionID The id of action
	 * @param string $lang The language
	 * @param string $sender The sender's email
	 * @param string $subject The subject of email
	 * @param string $text The text of email
	 * @param string $isActive true is an email must be sent for this type of action, else false
	 * @return bool true on success, false on failure
	 */	
	protected function createNewLangForText ($actionID, $lang, $sender, $subject, $text, $isActive) {
		$query='INSERT INTO php_wol_textmails (id_event, lang, sender_mail, subject, text, token_list, isActive) VALUES ' .
				'('. $actionID .','. $lang .','. $sender .','. $subject .','. $text .','.
				'(SELECT token_list FROM php_wol_events WHERE lang="EN" and id_event = '. 
				$actionID .')' .','. $isActive .');';
		$result = mysql_query ($query); 
		echo $query;
		return ($result);	
	}

	/**
	 * Creates new language for an action
	 * @param string $actionName The name of action
	 * @param string $lang The language
	 * @return bool true if found, else false
	 */		
	public function existsText ($actionName, $lang) {
		$query = 'SELECT lang, id_event FROM php_wol_textmails WHERE lang = "' . $lang . '" ' .
					'AND id_event = (SELECT id_event FROM php_wol_events where name_event = "'. $actionName .'" ';	
		$result = mysql_query ($query);
		return (($row = mysql_fetch_array($result)) !== false);
	}

	/**
	 * Creates new language for an action
	 * @param string $actionID The name of action
	 * @param string $lang The language
	 * @return bool true if found, else false
	 */		
	public function existsTextByActionID ($actionID, $lang) {
		$query = 'SELECT lang, id_event FROM php_wol_textmails WHERE lang = "'.$lang.'" AND id_event = '.$actionID ;	
		
	if(!($result = mysql_query ($query))){
	    echo mysql_error()." -------------------------- ".$query;
	}
	      
		return (($row = mysql_fetch_array($result)) !== false);
	}
	
	/**
	 * Gets the existing languages in database
	 * @return array the list of languages
	 */		
	public function getLangs() {
		$return = array();
		$query = 'SELECT lang FROM php_wol_textmails GROUP BY lang';
		$result = mysql_query ($query); 
		while ( $row = mysql_fetch_array($result) ) {
			$return[] = array('name' => $row['lang']);
		}
		return $return;
	}

	/**
	 * Gets the actions that can be emailed
	 * @return array the list of actions (with id and name for each)
	 */		
	public function getMailableActions () {
		$return = array();
		$query = 'SELECT name_event NAME, id_event ID FROM php_wol_events WHERE can_be_emailed = TRUE';
		$result = mysql_query ($query);
		while ( $row = mysql_fetch_array($result) ) {
			$return[] = array(
				'id' => $row['ID'],
				'name' => $row['NAME']
			);
		}	
		return $return;
	}
	
}

?>
