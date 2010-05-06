<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Filter.php 482 2008-05-18 17:22:35Z matt $
 * 
 * @package Piwik_DataTable
 */

/**
 * A filter is applied instantly to a given DataTable and can 
 * - remove rows 
 * - change columns values (lowercase the strings, truncate, etc.)
 * - add/remove columns or details (compute percentage values, add an 'icon' detail based on the label, etc.)
 * - add/remove/edit sub DataTable associated to some rows
 * - whatever you can imagine
 * 
 * The concept is very simple: the filter is given the DataTable 
 * and can do whatever is necessary on the data (in the filter() method).
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Filter
 */
abstract class Piwik_DataTable_Filter
{
	/*
	 * @var Piwik_DataTable
	 */
	protected $table;
	
	public function __construct($table)
	{
		if(!($table instanceof Piwik_DataTable))
		{
			throw new Exception("The filter accepts only a Piwik_DataTable object.");
		}
		$this->table = $table;
	}
	
	abstract protected function filter();
}

require_once "DataTable/Filter/ColumnCallbackDeleteRow.php";
require_once "DataTable/Filter/ColumnCallbackAddDetail.php";
require_once "DataTable/Filter/ColumnCallbackReplace.php";
require_once "DataTable/Filter/DetailCallbackAddDetail.php";
require_once "DataTable/Filter/AddConstantDetail.php";
require_once "DataTable/Filter/Null.php";
require_once "DataTable/Filter/ExcludeLowPopulation.php";
require_once "DataTable/Filter/Limit.php";
require_once "DataTable/Filter/Pattern.php";
require_once "DataTable/Filter/PatternRecursive.php";
require_once "DataTable/Filter/ReplaceColumnNames.php";
require_once "DataTable/Filter/Sort.php";
require_once "DataTable/Filter/AddSummaryRow.php";
