<?php
/*
  $Id: tell_a_friend.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  require_once(realpath(dirname(__FILE__) . '/../../../'). '/includes/classes/email_template.php');

  class osC_Email_Template_tell_a_friend extends osC_Email_Template {

/* Private variables */

    var $_template_name = 'tell_a_friend',
        $_keywords = array( '%%from_name%%',
                            '%%from_email_address%%',
                            '%%to_name%%',
                            '%%to_email_address%%',
                            '%%message%%',
                            '%%contents%%',
                            '%%store_name%%',
                            '%%store_address%%',
                            '%%store_owner_email_address%%');

/* Class constructor */

    function osC_Email_Template_tell_a_friend() {
      parent::osC_Email_Template($this->_template_name);
    }


/* Private methods */

  function setData($from_name, $from_email_address, $to_name, $to_email_address, $message, $contents){
      $this->_from_name = $from_name;
      $this->_from_email_address = $from_email_address;
      $this->_to_name = $to_name;
      $this->_to_email_address = $to_email_address;
      $this->_message = $message;
      $this->_contents = $contents;
      

      $this->addRecipient($this->_to_name, $this->_to_email_address);
    }

    function buildMessage() {
      $replaces = array($this->_from_name, $this->_from_email_address, $this->_to_name, $this->_to_email_address, $this->_message, $this->_contents, STORE_NAME, HTTP_SERVER . DIR_WS_CATALOG, STORE_OWNER_EMAIL_ADDRESS);

      $this->_title = str_replace($this->_keywords, $replaces, $this->_title);
      $this->_email_text = str_replace($this->_keywords, $replaces, $this->_content);
    }
  }
?>
