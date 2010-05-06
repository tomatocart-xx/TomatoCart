<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Limit.php 444 2008-04-11 13:38:22Z johmathe $
 * 
 * @package Piwik_DataTable
 */

/**
 * Delete all rows from the table that are not in the offset,offset+limit range
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Filter 
 */

class Piwik_DataTable_Filter_Limit extends Piwik_DataTable_Filter
{	
	/**
	 * Filter constructor.
	 * 
	 * @param Piwik_DataTable $table
	 * @param int $offset Starting row 
	 * @param int $limit Number of rows to keep (specify -1 to keep all rows)
	 */
	public function __construct( $table, $offset, $limit = null )
	{
		parent::__construct($table);
		$this->offset = $offset;
		
		if(is_null($limit))
		{
			$limit = -1;
		}
		$this->limit = $limit;
		
		$this->filter();
	}	
	
	protected function filter()
	{
		$table = $this->table;
		
		$rowsCount = $table->getRowsCount();
		
		// we have to delete
		// - from 0 to offset
		
		// at this point the array has offset less elements
		// - from limit to the end
		$table->deleteRowsOffset( 0, $this->offset );
		if( $this->limit > 0 )
		{
			$table->deleteRowsOffset( $this->limit );
		}
	}
}


