<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Period.php 444 2008-04-11 13:38:22Z johmathe $
 * 
 * @package Piwik_Helper
 */

/**
 * Creating a new Piwik_Period subclass: 
 * 
 * Every overloaded method must start with the code
 * 		if(!$this->subperiodsProcessed)
 *		{
 *			$this->generate();
 *		}
 *	that checks whether the subperiods have already been computed.
 *	This is for performance improvements, computing the subperiods is done a per demand basis.
 *
 * 
 * @package Piwik_Helper
 */
abstract class Piwik_Period
{
	protected $subperiods = array();
	protected $subperiodsProcessed = false;
	protected $label = null;
	
	protected static $unknowPeriodException = "The period '%s' is not supported. Try 'day' or 'week' or 'month' or 'year'";
	protected function __construct( $date )
	{	
		$this->checkInputDate( $date );
		$this->date = clone $date;
	}
	
	static public function factory($strPeriod, $date)
	{
		switch ($strPeriod) {
			case 'day':
				return new Piwik_Period_Day($date); 
				break;
		
			case 'week':
				return new Piwik_Period_Week($date); 
				break;
				
			case 'month':
				return new Piwik_Period_Month($date); 
				break;
				
			case 'year':
				return new Piwik_Period_Year($date); 
				break;
				
			default:
				throw new Exception(sprintf(self::$unknowPeriodException, $strPeriod));
				break;
		}
	}

	/**
	 * Returns the first day of the period
	 *
	 * @return Piwik_Date First day of the period
	 */
	public function getDateStart()
	{
		if(!$this->subperiodsProcessed)
		{
			$this->generate();
		}
		if(count($this->subperiods) == 0)
		{
			return $this->getDate();
		}
		$periods = $this->getSubperiods();
		$currentPeriod = $periods[0];
		while( $currentPeriod->getNumberOfSubperiods() > 0 )
		{
			$periods = $currentPeriod->getSubperiods();
			$currentPeriod = $periods[0];
		}
		return $currentPeriod->getDate();
	}
	
	/**
	 * Returns the last day of the period ; can be a date in the future
	 *
	 * @return Piwik_Date Last day of the period 
	 */
	public function getDateEnd()
	{
		if(!$this->subperiodsProcessed)
		{
			$this->generate();
		}
		if(count($this->subperiods) == 0)
		{
			return $this->getDate();
		}
		$periods = $this->getSubperiods();
		$currentPeriod = $periods[count($periods)-1];
		while( $currentPeriod->getNumberOfSubperiods() > 0 )
		{
			$periods = $currentPeriod->getSubperiods();
			$currentPeriod = $periods[count($periods)-1];
		}
		return $currentPeriod->getDate();
	}
	
	public function getId()
	{
		return Piwik::$idPeriods[$this->getLabel()];
	}

	public function getLabel()
	{
		return $this->label;
	}
	
	/**
	 *
	 * @return Piwik_Date
	 */
	protected function getDate()
	{
		return $this->date;
	}	
	
	protected function checkInputDate($date)
	{
		if( !($date instanceof Piwik_Date))
		{
			throw new Exception("The date must be a Piwik_Date object. " . var_export($date,true));
		}
	}
	
	protected function generate()
	{
		$this->subperiodsProcessed = true;
	}
	
	public function getNumberOfSubperiods()
	{
		if(!$this->subperiodsProcessed)
		{
			$this->generate();
		}
		return count($this->subperiods);
	}
	
	/**
	 * Returns Period_Day for a period made of days (week, month),
	 * 			Period_Month for a period made of months (year) 
	 * 
	 */
	public function getSubperiods()
	{
		if(!$this->subperiodsProcessed)
		{
			$this->generate();
		}
		return $this->subperiods;
	}

	/**
	 * Add a date to the period.
	 * 
	 * Protected because it not yet supported to add periods after the initialization
	 * 
	 * @param Piwik_Date Valid Piwik_Date object
	 */
	protected function addSubperiod( $date )
	{
		$this->subperiods[] = $date;
	}
	
	/**
	 * A period is finished if all the subperiods are finished
	 */
	public function isFinished()
	{
		if(!$this->subperiodsProcessed)
		{
			$this->generate();
		}
		foreach($this->subperiods as $period)
		{
			if(!$period->isFinished())
			{
				return false;
			}
		}
		return true;
	}
		
	public function toString()
	{
		if(!$this->subperiodsProcessed)
		{
			$this->generate();
		}
		$dateString = array();
		foreach($this->subperiods as $period)
		{
			$dateString[] = $period->toString();
		}
		return $dateString;
	}
	
	public function get( $part= null )
	{
		if(!$this->subperiodsProcessed)
		{
			$this->generate();
		}
		return $this->date->get($part);
	}
	
	abstract public function getPrettyString();
}

/**
 * from a starting date to an ending date
 *
 */
class Piwik_Period_Range extends Piwik_Period
{
	public function __construct( $strPeriod, $strDate )
	{
		$this->strPeriod = $strPeriod;
		$this->strDate = $strDate;
		$this->defaultEndDate = null;
	}

	public function getPrettyString()
	{
		$out = "From ".$this->getDateStart()->toString() . " to " . $this->getDateEnd()->toString();
		return $out;
	}

	protected function removePeriod( $date, $n )
	{
		switch($this->strPeriod)
		{
			case 'day':	
				$startDate = $date->subDay( $n );
			break;
			
			case 'week':
				$startDate = $date->subDay( $n * 7 );					
			break;
			
			case 'month':
				$startDate = $date->subMonth( $n );					
			break;
			
			case 'year':
				$startDate = $date->subMonth( 12 * $n );					
			break;
			
			default:
				throw new Exception(sprintf(self::$unknowPeriodException, $this->strPeriod));
			break;
		}
		return $startDate;
	}

	protected function getMaxN($lastN)
	{	
		switch($this->strPeriod)
		{
			case 'day':	
				$lastN = min( $lastN, 5*365 );
			break;
			
			case 'week':
				$lastN = min( $lastN, 5*52 );				
			break;
			
			case 'month':
				$lastN = min( $lastN, 5*12 );			
			break;
			
			case 'year':
				$lastN = min( $lastN, 10 );					
			break;
		}
		return $lastN;
	}
	public function setDefaultEndDate( Piwik_Date $oDate)
	{
		$this->defaultEndDate = $oDate;
	}
	
	protected function generate()
	{
		if($this->subperiodsProcessed)
		{
			return;
		}
		parent::generate();
		
		if(ereg('(last|previous)([0-9]*)', $this->strDate, $regs))
		{
			$lastN = $regs[2];
			
			$lastOrPrevious = $regs[1];
			
			if(!is_null($this->defaultEndDate))
			{
				$defaultEndDate = $this->defaultEndDate;
			}
			else
			{
				$defaultEndDate = Piwik_Date::today();
			}		
			if($lastOrPrevious == 'last')
			{
				$endDate = $defaultEndDate;
			}
			elseif($lastOrPrevious == 'previous')
			{
				$endDate = $this->removePeriod($defaultEndDate, 1);
			}		
			
			// last1 means only one result ; last2 means 2 results so we remove only 1 to the days/weeks/etc
			$lastN--;
			$lastN = abs($lastN);
			
			$lastN = $this->getMaxN($lastN);
			
			$startDate = $this->removePeriod($endDate, $lastN);
		}
		elseif(ereg('([0-9]{4}-[0-9]{1,2}-[0-9]{1,2}),([0-9]{4}-[0-9]{1,2}-[0-9]{1,2})', $this->strDate, $regs))
		{
			$strDateStart = $regs[1];
			$strDateEnd = $regs[2];

			$startDate = new Piwik_Date($strDateStart);
			$endDate   = new Piwik_Date($strDateEnd);
		}
		else
		{
			throw new Exception("The date '$this->strDate' is not a date range. Should have the following format: 'lastN' or 'previousN' or 'YYYY-MM-DD,YYYY-MM-DD'.");
		}
		
		$endSubperiod = Piwik_Period::factory($this->strPeriod, $endDate);
		
		$arrayPeriods= array();
		$arrayPeriods[] = $endSubperiod;
		while($endDate->isLater($startDate))
		{
			$endDate = $this->removePeriod($endDate, 1);
			$subPeriod = Piwik_Period::factory($this->strPeriod, $endDate);
			$arrayPeriods[] =  $subPeriod ;
		}
		$arrayPeriods = array_reverse($arrayPeriods);
		foreach($arrayPeriods as $period)
		{
			$this->addSubperiod($period);
		}
//		var_dump($this->toString());exit;
	}
	
	function toString()
	{
		if(!$this->subperiodsProcessed)
		{
			$this->generate();
		}
		$range = array();
		foreach($this->subperiods as $element)
		{
			$range[] = $element->toString();
		}
		return $range;
	}
}
	
/**
 * 
 * @package Piwik_Period
 */
class Piwik_Period_Day extends Piwik_Period
{
	protected $label = 'day';
	protected $date = null;
	
	public function __construct( $date )
	{
		parent::__construct($date);		
	}

	public function getPrettyString()
	{
		$out = $this->getDateStart()->toString() ;
		return $out;
	}
	public function isFinished()
	{
		$todayMidnight = Piwik_Date::today();
		if($this->date->isEarlier($todayMidnight))
		{
			return true;
		}
	}
	
	public function getNumberOfSubperiods()
	{
		return 0;
	}	
	
	public function addSubperiod( $date )
	{
		throw new Exception("Adding a subperiod is not supported for Piwik_Period_Day");
	}
	public function toString()
	{
		return $this->date->toString("Y-m-d");
	}
	
	
}

/**
 * 
 * @package Piwik_Period
 */
class Piwik_Period_Week extends Piwik_Period
{
	protected $label = 'week';
	public function __construct( $date )
	{
		parent::__construct($date);
	}

	public function getPrettyString()
	{
		$out = $this->getDateStart()->toString() . " to " . $this->getDateEnd()->toString();
		return $out;
	}
	protected function generate()
	{
		if($this->subperiodsProcessed)
		{
			return;
		}
		parent::generate();
		$date = $this->date;
		
		if( $date->toString('N') > 1)
		{
			$date = $date->subDay($date->toString('N')-1);
		}
		
		$startWeek = $date;
		
		$currentDay = clone $startWeek;
		while($currentDay->compareWeek($startWeek) == 0)
		{
			$this->addSubperiod(new Piwik_Period_Day($currentDay) );
			$currentDay = $currentDay->addDay(1);
		}
	}

}

/**
 * 
 * @package Piwik_Period
 */
class Piwik_Period_Month extends Piwik_Period
{
	protected $label = 'month';
	public function __construct( $date )
	{
		parent::__construct($date);
	}

	public function getPrettyString()
	{
		$out = $this->getDateStart()->toString('Y-m');
		return $out;
	}
	protected function generate()
	{
		if($this->subperiodsProcessed)
		{
			return;
		}
		parent::generate();
		
		$date = $this->date;
		
		$startMonth = $date->setDay(1);
		$currentDay = clone $startMonth;
		while($currentDay->compareMonth($startMonth) == 0)
		{
			$this->addSubperiod(new Piwik_Period_Day($currentDay));
			$currentDay = $currentDay->addDay(1);
		}
	}
	
	public function isFinished()
	{
		if(!$this->subperiodsProcessed)
		{
			$this->generate();
		}
		// a month is finished 
		// if current month > month AND current year == year
		// OR if current year > year
		$year = $this->date->get("Y");
		return ( date("m") > $this->date->get("m") && date("Y") == $year)
				||  date("Y") > $year;
	}
}

/**
 * 
 * @package Piwik_Period
 */
class Piwik_Period_Year extends Piwik_Period
{	
	protected $label = 'year';
	public function __construct( $date )
	{
		parent::__construct($date);
	}

	public function getPrettyString()
	{
		$out = $this->getDateStart()->toString('Y');
		return $out;
	}
	protected function generate()
	{
		if($this->subperiodsProcessed)
		{
			return;
		}
		parent::generate();
		
		$year = $this->date->get("Y");
		for($i=1; $i<=12; $i++)
		{
			$this->addSubperiod( new Piwik_Period_Month( 
									new Piwik_Date("$year-$i-01")
								)
							);
		}
	}
	
	function toString()
	{
		if(!$this->subperiodsProcessed)
		{
			$this->generate();
		}
		$stringMonth = array();
		foreach($this->subperiods as $month)
		{
			$stringMonth[] = $month->get("Y")."-".$month->get("m")."-01";
		}
		return $stringMonth;
	}
}

