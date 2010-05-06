<?php
/*
  $Id: google_sitemap.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
  require('includes/classes/google_sitemap.php');

  class toC_Json_Google_Sitemap {

    function createGoogleSitemap() {
    	global $toC_Json, $osC_Language;

    	$google_sitemap = new osC_Google_Sitemap( $_REQUEST['products_frequency'], 
                                            	  $_REQUEST['products_priority'], 
                                            	  $_REQUEST['categories_frequency'],
                                            	  $_REQUEST['categories_priority'],
                                            	  $_REQUEST['articles_frequency'],
                                            	  $_REQUEST['articles_priority'] );
    	
    	if ($google_sitemap->generateSitemap()) {
    		$response = array('success' => true, 'feedback' => $osC_Language->get('ms_success_action_performed'));
      } else {
        $response = array('success' => false, 'feedback' => $osC_Language->get('ms_error_action_not_performed'));
      }
     
      echo $toC_Json->encode($response);
    }
    
  }
?>
