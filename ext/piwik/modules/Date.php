<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Date.php 496 2008-05-29 02:30:52Z matt $
 * 
 * @package Piwik_Helper
 */

/**
 * Date object widely used in Piwik.
 * 
 * //TODO remove factory OR constructor! cant have both
 * @package Piwik_Helper
 */
class Piwik_Date
{
	/**
	 * Returns a Piwik_Date objects. 
	 * Accepts strings 'today' 'yesterday' or any YYYY-MM-DD or timestamp
	 *
	 * @param string $strDate
	 * @return Piwik_Date
	 */
	static public function factory($strDate)
	{
		switch($strDate)
		{
			case 'today': return self::today(); break;
			case 'yesterday': return self::yesterday(); break;
			default: return new Piwik_Date($strDate); break;
		}
	}
	
	/**
	 * Builds a Piwik_Date object
	 * 
	 * @param Timestamp date OR string format 2007-01-31
	 */
	public function __construct(  $date  )
	{
		if(is_int( $date ))
		{
			$this->timestamp =  $date ;
		}
		else
		{
			if (($timestamp = strtotime($date)) === false) 
			{
				throw new Exception("The date '$date' is not correct. The date format is YYYY-MM-DD or you can also use magic keywords such as 'today' or 'yesterday' or any keyword supported by the strtotime function (see http://php.net/strtotime for more information)");
			}
			$this->timestamp = $timestamp;
		}
	}
	
	/**
	 * Sets the time part of the date
	 * Doesn't modify $this
     * 
	 * @param string $time HH:MM:SS
	 * @return Piwik_Date The new date with the time part set
	 */
	//TODO test this method
	public function setTime($time)
	{
		return new Piwik_Date( strtotime( $this->get("j F Y") . " $time"));
	}
	
	/**
	 * Returns the unix timestamp of the date
	 *
	 * @return int
	 */
	public function getTimestamp()
	{
		return $this->timestamp;
	}
	
	/**
	 * Returns true if the current date is older than the given $date
	 *
	 * @param Piwik_Date $date
	 * @return bool
	 */
	public function isLater( Piwik_Date $date)
	{
		return $this->getTimestamp() > $date->getTimestamp();
	}
	
	/**
	 * Returns true if the current date is earlier than the given $date
	 *
	 * @param Piwik_Date $date
	 * @return bool
	 */
	public function isEarlier(Piwik_Date $date)
	{
		return $this->getTimestamp() < $date->getTimestamp();
	}
	
	/**
	 * Returns the Y-m-d representation of the string.
	 * You can specify the output, see the list on php.net/date
	 *
	 * @param string $part
	 * @return string
	 */
	public function toString($part = 'Y-m-d')
	{
		return date($part, $this->getTimestamp());
	}
	
	/**
	 * @see toString()
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->toString();
	}

    /**
     * Sets a new day
     * Returned is the new date object
     * Doesn't modify $this
     * 
     * @param int Day eg. 31
     * @return Piwik_Date  new date
     */
	public function setDay( $day )
	{
		$ts = $this->getTimestamp();
		$result = mktime( 
						date('H', $ts),
						date('i', $ts),
						date('s', $ts),
						date('n', $ts),
						1,
						date('Y', $ts)
					);
		return new Piwik_Date( $result );
	}
	
    /**
     * Sets a new year
     * Returned is the new date object
     * Doesn't modify $this
     * 
     * @param int 2010
     * @return Piwik_Date  new date
     */
	public function setYear( $year )
	{
		$ts = $this->getTimestamp();
		$result = mktime( 
						date('H', $ts),
						date('i', $ts),
						date('s', $ts),
						date('n', $ts),
						date('j', $ts),
						$year
					);
		return new Piwik_Date( $result );
	}
	


    /**
     * Subtracts days from the existing date object and returns a new Piwik_Date object
     * Doesn't modify $this
     * 
     * Returned is the new date object
     * @return Piwik_Date  new date
     */
    public function subDay( $n )
    {
    	$ts = strtotime("-$n day", $this->getTimestamp());
		return new Piwik_Date( $ts );
    }
    
    /**
     * Subtracts a month from the existing date object.
     * Returned is the new date object
     * Doesn't modify $this
     * 
     * @return Piwik_Date  new date
     */
    public function subMonth( $n )
    {
		$ts = $this->getTimestamp();
		$result = mktime( 
						date('H', $ts),
						date('i', $ts),
						date('s', $ts),
						date('n', $ts) - $n,
						1, // we set the day to 1
						date('Y', $ts)
					);
		return new Piwik_Date( $result );
    }
    
    /**
     * Returns a representation of a date or datepart
     *
     * @param  string  OPTIONAL Part of the date to return, if null the timestamp is returned
     * @return integer|string  date or datepart
     */
	public function get($part = null)
	{
		if(is_null($part))
		{
			return $this->getTimestamp();
		}
		return date($part, $this->getTimestamp());
	}
	
	/**
	 * Returns a localized representation of a date or datepart
	 *
	 * @param string OPTIONAL Part of the date to return (in strftime format), if null timestamp is returned
	 * @return integer|string date or datepart
	 */
	public function getLocalized($part = null)
	{
		if(is_null($part))
		{
			return $this->getTimestamp();
		}
		return strftime($part, $this->getTimestamp());
	}
	
    /**
     * Adds days to the existing date object.
     * Returned is the new date object
     * Doesn't modify $this
     * 
     * @param int Number of days to add
     * @return  Piwik_Date new date
     */
	public function addDay( $n )
	{
		$ts = strtotime("+$n day", $this->getTimestamp());
		return new Piwik_Date( $ts );
	}

    /**
     * Compares the week of the current date against the given $date
     * Returns 0 if equal, -1 if current week is earlier or 1 if current week is later
     * Example: 09.Jan.2007 13:07:25 -> compareWeek(2); -> 0
     *
     * @param Piwik_Date $date
     * @return integer  0 = equal, 1 = later, -1 = earlier
     */
    public function compareWeek(Piwik_Date $date)
    {
		$currentWeek = date('W', $this->getTimestamp());
		$toCompareWeek = date('W', $date->getTimestamp());
		if( $currentWeek == $toCompareWeek)
		{
			return 0;
		}
		if( $currentWeek < $toCompareWeek)
		{
			return -1;
		}
		return 1;
    }
    /**
     * Compares the month of the current date against the given $date month
     * Returns 0 if equal, -1 if current month is earlier or 1 if current month is later
     * For example: 10.03.2000 -> 15.03.1950 -> 0
     *
     * @param  Piwik_Date  $month   Month to compare
     * @return integer  0 = equal, 1 = later, -1 = earlier
     */
	function compareMonth( Piwik_Date $date )
	{
		$currentMonth = date('n', $this->getTimestamp());
		$toCompareMonth = date('n', $date->getTimestamp());
		if( $currentMonth == $toCompareMonth)
		{
			return 0;
		}
		if( $currentMonth < $toCompareMonth)
		{
			return -1;
		}
		return 1;
	}
	
	/**
	 * Returns true if current date is today
	 * 
	 * @return bool
	 */
	public function isToday()
	{
		return $this->get('Y-m-d') === date('Y-m-d', time());
	}
	
	/**
	 * Returns a date object set to today midnight
	 * 
	 * @return Piwik_Date
	 */
	static public function today()
	{
		$date = new Piwik_Date(date("Y-m-d 00:00:00"));
		return $date;
	}
	
	/**
	 * Returns a date object set to yesterday midnight
	 * @return Piwik_Date
	 */
	static public function yesterday()
	{
		$date = new Piwik_Date(date("Y-m-d 00:00:00", strtotime("yesterday")));
		return $date;
	}
	
}

