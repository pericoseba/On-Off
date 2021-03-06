<?php

/**
 * Stores all the statistics related to an host, for a given week.
**/
class Statistics_HostUptimeWeek {
	
	protected $_numberOfWeek = false;
	protected $_monday = false;
	protected $_tuesday = false;
	protected $_wednesday = false;
	protected $_thursday = false;
	protected $_friday = false;
	protected $_saturday = false;
	protected $_sunday = false;
	
	/**
	 * Creates a HostUptimeWeek object
	 * @param int $nbOfWeek the number of the week (0 = current week, 1 = last week...)
	 * @param int $monday this week's Monday's uptime
	 * @param int $tuesday this week's Tuesday's uptime
	 * @param int $wednesday this week's Wednesday's uptime
	 * @param int $thursday this week's Thursday's uptime
	 * @param int $friday this week's Friday's uptime
	 * @param int $saturday this week's Saturday's uptime
	 * @param int $sunday this week's Sunday's uptime
	**/
	public function __construct($nbOfWeek, $monday, $tuesday, $wednesday, $thursday, $friday, $saturday, $sunday) {
		$this->_numberOfWeek = $nbOfWeek;
		$this->_monday = $monday;
		$this->_tuesday = $tuesday;
		$this->_wednesday = $wednesday;
		$this->_thursday = $thursday;
		$this->_friday = $friday;
		$this->_saturday = $saturday;
		$this->_sunday = $sunday;
	}
   
   /**
	 * Returns the number of the week
	 * @return int the number of this week
	**/
	
   public function getNumberOfWeek () {
		return $this->_numberOfWeek;
	}

	/**
	 * Returns the uptime of the given day of the week
	 * @param int $day the position of the day in the week (1 => Monday, 2 => Tuesday, ..., 7 => Sunday)
	 * @return int this day's uptime on this week
	**/	
	public function getDayFromNumber($day) {
		switch($day) {
			case 1: return $this->_monday;
			case 2: return $this->_tuesday;
			case 3: return $this->_wednesday;
			case 4: return $this->_thursday;
			case 5: return $this->_friday;
			case 6: return $this->_saturday;
			case 7: return $this->_sunday;
			default: return false;
		}
	}
	
	/**
	 * Adds the value of "time" (in minutes) to today's uptime
	 * @param int $time the time to add to today's uptime 
	**/
	public function addToday($time) {
		switch(date("D")) {
			case 'Mon': 
				$this->_monday += $time; 
			break;
			case 'Tue': 
				$this->_tuesday += $time; 
			break;
			case 'Wed': 
				$this->_wednesday += $time; 
			break;
			case 'Thu': 
				$this->_thursday += $time; 
			break;
			case 'Fri': 
				$this->_friday += $time; 
			break;
			case 'Sat': 
				$this->_saturday += $time; 
			break;
			case 'Sun': 
				$this->_sunday += $time;
			break;
			default: break;
			
		}
	}
	
	/**
	 * Returns an array with the data contained in the object
	 * @return array the array contains for each day : uptime (hours and minutes) and date at format "Thursday 1 January 1970")
	**/
	public function getSerialized() {
		return array (
			'numberOfWeek' => $this->_numberOfWeek,
			'MonH' => self::toHours($this->_monday),
			'TueH' => self::toHours($this->_tuesday),
			'WedH' => self::toHours($this->_wednesday),
			'ThuH' => self::toHours($this->_thursday),
			'FriH' => self::toHours($this->_friday),
			'SatH' => self::toHours($this->_saturday),
			'SunH' => self::toHours($this->_sunday),
			'MonM' => self::toMinutes($this->_monday),
			'TueM' => self::toMinutes($this->_tuesday),
			'WedM' => self::toMinutes($this->_wednesday),
			'ThuM' => self::toMinutes($this->_thursday),
			'FriM' => self::toMinutes($this->_friday),
			'SatM' => self::toMinutes($this->_saturday),
			'SunM' => self::toMinutes($this->_sunday),
			'MonDate' => $this->getDate(1, $this->_numberOfWeek),
			'TueDate' => $this->getDate(2, $this->_numberOfWeek),
			'WedDate' => $this->getDate(3, $this->_numberOfWeek),
			'ThuDate' => $this->getDate(4, $this->_numberOfWeek),
			'FriDate' => $this->getDate(5, $this->_numberOfWeek),
			'SatDate' => $this->getDate(6, $this->_numberOfWeek),
			'SunDate' => $this->getDate(7, $this->_numberOfWeek),
		);
	}
	
	/**
	 * Returns the litteral date from the given week number and number of day
	 * @static
	 * @param int $dayOfWeek the position of the day in the week (Monday has number 1, Tuesday number 2, ..., Sunday number 7)
	 * @param int $NumberOfWeek the number of the week (week number 0 in current week, week number 1 is last week...)
	 * @return string the litteral date at format "Thursday 1 January 1970" (or an empty string if the date is in the future)
	**/
	static public function getDate($dayOfWeek, $NumberOfWeek) {
		$day = new DateTime(); // value : the current date
		$nbOfDays = (7 * $NumberOfWeek) + intval(date("N")) - $dayOfWeek;
		if ($nbOfDays >= 0) {
			$day->sub(new DateInterval('P' . $nbOfDays . 'D')); // value : current date - NB Days
			return $day->format('l j F Y');
		} else { 
			return "";
		}
	}

	/**
	 * Returns the number of hours (integer) contained in the given number of minutes
	 * @static
	 * @param int $nbMinutes the number on minutes to convert
	 * @return int the number of hours
	**/	
	static public function toHours ($nbMinutes) {
		return intval($nbMinutes/60);
	}
	
	/**
	 * Returns the number of minutes (without hours) contained in the given number of minutes
	 * @static
	 * @param int $nbMinutes the number on minutes to convert
	 * @return int the number of minutes	 
	**/
	static public function toMinutes ($nbMinutes) {
		return (($nbMinutes % 60) === 0) ? "" : $nbMinutes % 60;
	}
}