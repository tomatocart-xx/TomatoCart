<?php
/*
  $Id: piwik.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class toC_Piwik {

    function toC_Piwik() {

    }

    function renderJs() {
      $output = '<!-- Piwik -->' . "\n";
      $output .= '<script type="text/javascript">' . "\n";
      $output .= 'var pkBaseURL = (("https:" == document.location.protocol) ? "' . HTTPS_SERVER . DIR_WS_HTTP_CATALOG . 'ext/piwik/" : "' . HTTP_SERVER . DIR_WS_HTTP_CATALOG . 'ext/piwik/");' . "\n";
      $output .= 'document.write(unescape("%3Cscript src=\'" + pkBaseURL + "piwik.js\' type=\'text/javascript\'%3E%3C/script%3E"));' . "\n";
      $output .= '</script>' . "\n";
      $output .= '<script type="text/javascript">' . "\n";
      
      $output .= 'try {' . "\n";
      $output .= 'var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 1);' . "\n";
      $output .= 'piwikTracker.trackPageView();' . "\n";
      $output .= 'piwikTracker.enableLinkTracking();' . "\n";
      $output .= '} catch( err ) {}' . "\n";
      $output .= '</script>' . "\n";
      $output .= '<noscript><p><img src="http://www.tocext.com/ext/piwik/piwik.php?idsite=1" style="border:0" alt=""/></p></noscript>' . "\n";
      $output .= '<!-- End Piwik Tag -->' . "\n";

      return $output;
    }
  }
?>