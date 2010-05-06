<?php
/*
  $Id: contact.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_Info_Contact extends osC_Template {

/* Private variables */

    var $_module = 'contact',
        $_group = 'info',
        $_page_title,
        $_page_contents = 'info_contact.php',
        $_page_image = 'table_background_contact_us.gif';

/* Class constructor */

    function osC_Info_Contact() {
      global $osC_Services, $osC_Language, $breadcrumb;

      $this->_page_title = $osC_Language->get('info_contact_heading');

      if ($osC_Services->isStarted('breadcrumb')) {
        $breadcrumb->add($osC_Language->get('breadcrumb_contact'), osc_href_link(FILENAME_INFO, $this->_module));
      }

      if ($_GET[$this->_module] == 'process') {
        $this->_process();
      }
    }

/* Private methods */

    function _process() {
      global $osC_Language, $messageStack;

      $name = osc_sanitize_string($_POST['name']);
      $email_address = osc_sanitize_string($_POST['email']);
      $enquiry = osc_sanitize_string($_POST['enquiry']);

      if (osc_validate_email_address($email_address)) {
        osc_email(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $osC_Language->get('contact_email_subject'), $enquiry, $name, $email_address);

        osc_redirect(osc_href_link(FILENAME_INFO, 'contact=success', 'AUTO'));
      } else {
        $messageStack->add('contact', $osC_Language->get('field_customer_email_address_check_error'));
      }
    }
  }
?>
