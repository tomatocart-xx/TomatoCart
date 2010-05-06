<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: API.php 482 2008-05-18 17:22:35Z matt $
 * 
 * @package Piwik_ExamplePlugin
 */

/**
 * HOW TO VIEW THE API IN ACTION
 * =============================
 * Go to the API page in the Piwik Interface
 * And try the API of the plugin ExamplePlugin
 */

/**
 * 
 * @package Piwik_ExamplePlugin
 */
class MagicObject 
{
	function Incredible(){ return 'Incroyable'; }
	protected $wonderful = 'magnifique';
	public $great = 'formidable';
}


/**
 * 
 * @package Piwik_ExamplePlugin
 */
class Piwik_ExamplePlugin_API extends Piwik_Apiable
{
	static private $instance = null;
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}

	public function getAnswerToLife()
	{
		return 42;
	}

	public function getGoldenRatio()
	{
		//http://en.wikipedia.org/wiki/Golden_ratio
		return 1.618033988749894848204586834365;
	}
	public function getObject()
	{
		return new MagicObject();
	}
	public function getNull()
	{
		return null;
	}
	public function getDescriptionArray()
	{
		return array('piwik','open source','web analytics','free');
	}
	public function getCompetitionDatatable()
	{
		$dataTable = new Piwik_DataTable();
		
		$row1 = new Piwik_DataTable_Row;
		$row1->setColumns( array('name' => 'piwik', 'license' => 'GPL'));
		$dataTable->addRow($row1);
		
		$dataTable->addRowFromSimpleArray( array('name' => 'google analytics', 'license' => 'commercial')  );
		
		return $dataTable;
	}
	
	public function getMoreInformationAnswerToLife()
	{
		return "Check http://en.wikipedia.org/wiki/The_Answer_to_Life,_the_Universe,_and_Everything";
	}
	
}

