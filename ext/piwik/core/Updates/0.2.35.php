<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: 0.2.35.php 1844 2010-02-12 17:05:22Z vipsoft $
 *
 * @category Piwik
 * @package Updates
 */

/**
 * @package Updates
 */
class Piwik_Updates_0_2_35 extends Piwik_Updates
{
	static function getSql()
	{
		return array(
			'ALTER TABLE `'. Piwik::prefixTable('user_dashboard') .'`
				CHANGE `layout` `layout` TEXT NOT NULL' => false,
		);
	}

	static function update()
	{
		Piwik_Updater::updateDatabase(__FILE__, self::getSql());
	}
}
