<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Archive.php 411 2008-03-28 00:33:02Z matt $
 * 
 * @package Piwik
 */

 
require_once 'Period.php';
require_once 'Date.php';
require_once 'ArchiveProcessing.php';
require_once 'Archive/Single.php';

/**
 * The archive object is used to query specific data for a day or a period of statistics for a given website.
 * 
 * Example:
 * <pre>
 * 		$archive = Piwik_Archive::build($idSite = 1, $period = 'week', '2008-03-08' );
 * 		$dataTable = $archive->getDataTable('Provider_hostnameExt');
 * 		$dataTable->queueFilter('Piwik_DataTable_Filter_ReplaceColumnNames');
 * 		return $dataTable;
 * </pre>
 * 
 * Example bis:
 * <pre>
 * 		$archive = Piwik_Archive::build($idSite = 3, $period = 'day', $date = 'today' );
 * 		$nbVisits = $archive->getNumeric('nb_visits');
 * 		return $nbVisits;		
 * </pre>
 * 
 * If the requested statistics are not yet processed, Archive uses ArchiveProcessing to archive the statistics.
 * 
 * @package Piwik
 * @subpackage Piwik_Archive
 */
abstract class Piwik_Archive
{
	/**
	 * When saving DataTables in the DB, we sometimes replace the columns name by these IDs so we save up lots of bytes
	 * Eg. INDEX_NB_UNIQ_VISITORS is an integer: 4 bytes, but 'nb_uniq_visitors' is 16 bytes at least
	 * (in php it's actually even much more) 
	 *
	 */
	const INDEX_NB_UNIQ_VISITORS = 1;
	const INDEX_NB_VISITS = 2;
	const INDEX_NB_ACTIONS = 3;
	const INDEX_MAX_ACTIONS = 4;
	const INDEX_SUM_VISIT_LENGTH = 5;
	const INDEX_BOUNCE_COUNT = 6;

	/**
	 * Website Piwik_Site
	 *
	 * @var Piwik_Site
	 */
	protected $site = null;
	
	/**
	 * Stores the already built archives.
	 * Act as a big caching array
	 *
	 * @var array of Piwik_Archive
	 */
	static protected $alreadyBuilt = array();
	
	/**
	 * Builds an Archive object or returns the same archive if previously built.
	 *
	 * @param int $idSite
	 * @param string|Piwik_Date $date 'YYYY-MM-DD' or magic keywords 'today' @see Piwik_Date::factory()
	 * @param string $period 'week' 'day' etc.
	 * 
	 * @return Piwik_Archive
	 */
	static public function build($idSite, $period, $strDate )
	{
		$oSite = new Piwik_Site($idSite);
		
		// if a period date string is detected: either 'last30', 'previous10' or 'YYYY-MM-DD,YYYY-MM-DD'
		if(is_string($strDate) 
			&& (
				ereg('^(last|previous){1}([0-9]*)$', $strDate, $regs)
				|| ereg('^([0-9]{4}-[0-9]{1,2}-[0-9]{1,2}),([0-9]{4}-[0-9]{1,2}-[0-9]{1,2})$', $strDate, $regs)
				)
			)
		{
			require_once 'Archive/Array.php';
			$archive = new Piwik_Archive_Array($oSite, $period, $strDate);
		}
		// case we request a single archive
		else
		{
			if(is_string($strDate))
			{
				$oDate = Piwik_Date::factory($strDate);
			}
			else
			{
				$oDate = $strDate;
			}
			$date = $oDate->toString();
			
			if(isset(self::$alreadyBuilt[$idSite][$date][$period]))
			{
				return self::$alreadyBuilt[$idSite][$date][$period];
			}
			
			$oPeriod = Piwik_Period::factory($period, $oDate);
			
			$archive = new Piwik_Archive_Single;
			$archive->setPeriod($oPeriod);
			$archive->setSite($oSite);
			
			self::$alreadyBuilt[$idSite][$date][$period] = $archive;
		}
		
		return $archive;
	}
	
	/**
	 * Returns the value of the element $name from the current archive 
	 * The value to be returned is a numeric value and is stored in the archive_numeric_* tables
	 *
	 * @param string $name For example Referers_distinctKeywords 
	 * @return float|int|false False if no value with the given name
	 */
	abstract public function getNumeric( $name );
	
	/**
	 * Returns the value of the element $name from the current archive
	 * 
	 * The value to be returned is a blob value and is stored in the archive_numeric_* tables
	 * 
	 * It can return anything from strings, to serialized PHP arrays or PHP objects, etc.
	 *
	 * @param string $name For example Referers_distinctKeywords 
	 * @return mixed False if no value with the given name
	 */
	abstract public function getBlob( $name );
	
	/**
	 * Given a list of fields defining numeric values, it will return a Piwik_DataTable_Simple 
	 * containing one row per value.
	 * 
	 * For example $fields = array( 	'max_actions',
	 *						'nb_uniq_visitors', 
	 *						'nb_visits',
	 *						'nb_actions', 
	 *						'sum_visit_length',
	 *						'bounce_count',
	 *					); 
	 *
	 * @param array|string $fields array( fieldName1, fieldName2, ...)
	 * @return Piwik_DataTable_Simple
	 */
	abstract public function getDataTableFromNumeric( $fields );

	/**
	 * This method will build a dataTable from the blob value $name in the current archive.
	 * 
	 * For example $name = 'Referers_searchEngineByKeyword' will return a  Piwik_DataTable containing all the keywords
	 * If a idSubTable is given, the method will return the subTable of $name 
	 * 
	 * @param string $name
	 * @param int $idSubTable or null if requesting the parent table
	 * @return Piwik_DataTable
	 * @throws exception If the value cannot be found
	 */
	abstract public function getDataTable( $name, $idSubTable = null );

	/**
	 * Same as getDataTable() except that it will also load in memory
	 * all the subtables for the DataTable $name. 
	 * You can then access the subtables by using the Piwik_DataTable_Manager getTable() 
	 *
	 * @param string $name
	 * @param int $idSubTable or null if requesting the parent table
	 * @return Piwik_DataTable
	 */
	abstract public function getDataTableExpanded($name, $idSubTable = null);

	/**
	 * Sets the site
	 *
	 * @param Piwik_Site $site
	 */
	public function setSite( Piwik_Site $site )
	{
		$this->site = $site;
	}
	
	/**
	 * Gets the site
	 *
	 * @param Piwik_Site $site
	 */
	public function getSite( )
	{
		return $this->site;
	}
	
	/**
	 * Returns the Id site associated with this archive
	 *
	 * @return int
	 */
	public function getIdSite()
	{
		return $this->site->getId();
	}
	
}





