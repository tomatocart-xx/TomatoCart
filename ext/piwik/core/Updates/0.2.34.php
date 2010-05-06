<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: 0.2.34.php 1844 2010-02-12 17:05:22Z vipsoft $
 *
 * @category Piwik
 * @package Updates
 */

/**
 * @package Updates
 */
class Piwik_Updates_0_2_34 extends Piwik_Updates
{
	static function update()
	{
		// force regeneration of cache files following #648
		Piwik::setUserIsSuperUser();
		$allSiteIds = Piwik_SitesManager_API::getInstance()->getAllSitesId();
		Piwik_Common::regenerateCacheWebsiteAttributes($allSiteIds);
	}
}
