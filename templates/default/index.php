<?php
/*
  $Id: index.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo $osC_Language->getTextDirection(); ?>" xml:lang="<?php echo $osC_Language->getCode(); ?>" lang="<?php echo $osC_Language->getCode(); ?>">

<head>

<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $osC_Language->getCharacterSet(); ?>" />

<link rel="shortcut icon" href="templates/<?php echo $osC_Template->getCode(); ?>/images/tomatocart.ico" type="image/x-icon" />

<title><?php echo STORE_NAME . ($osC_Template->hasPageTitle() ? ': ' . $osC_Template->getPageTitle() : ''); ?></title>

<base href="<?php echo osc_href_link(null, null, 'AUTO', false); ?>" />

<link rel="stylesheet" type="text/css" href="templates/<?php echo $osC_Template->getCode(); ?>/stylesheet.css" />

<?php
  if ($osC_Template->hasPageTags()) {
    echo $osC_Template->getPageTags();
  }

  if ($osC_Template->hasJavascript()) {
    $osC_Template->getJavascript();
  }
?>

<meta name="Generator" content="TomatoCart" />

</head>

<body>

<?php
  if ($osC_Template->hasPageHeader()) {
?>

<div id="pageHeader">

  <?php
    echo osc_link_object(osc_href_link(FILENAME_DEFAULT), osc_image($osC_Template->getLogo(), STORE_NAME), 'id="siteLogo"');
  ?>

  <div id="navigationBar">
    <div id="navigationLeft">
      <div id="navigationRight">
        <ul id="navigation" style="list-style-type: none;float: left; margin: 0;">

          <?php

            echo '<li>' . osc_link_object(osc_href_link(FILENAME_DEFAULT, 'index'), $osC_Language->get('home')) . '</li>' .
                 '<li>' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, 'specials'), $osC_Language->get('specials')) . '</li>' .
                 '<li>' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, 'new'), $osC_Language->get('new_products')) . '</li>';


            if ($osC_Customer->isLoggedOn()) {
              echo '<li>' . osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'logoff', 'SSL'), $osC_Language->get('sign_out')) . '</li>';
            }

            echo '<li>' . osc_link_object(osc_href_link(FILENAME_ACCOUNT, null, 'SSL'), $osC_Language->get('my_account')) . '</li>' .
                  '<li>' . osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'wishlist', 'SSL'), $osC_Language->get('my_wishlist')) . '</li>' .     
                  '<li>' . osc_link_object(osc_href_link(FILENAME_CHECKOUT, null, 'SSL'), $osC_Language->get('cart_contents')) . '</li>' .
                 '<li>' . osc_link_object(osc_href_link(FILENAME_CHECKOUT, 'checkout', 'SSL'), $osC_Language->get('checkout')) . '</li>' .
                 '<li class="last">' . osc_link_object(osc_href_link(FILENAME_INFO, 'contact'), $osC_Language->get('contact_us')) . '</li>';
          ?>
        </ul>

        <form name="search" action="<?php echo osc_href_link(FILENAME_SEARCH, null, 'NONSSL', false);?>" method="get">
          <?php echo osc_draw_input_field('keywords', null, 'maxlength="20"') . '&nbsp;' . osc_draw_hidden_session_id_field() . osc_draw_image_submit_button('button_quick_find.gif', $osC_Language->get('box_search_heading')); ?>
        </form>

      </div>
    </div>
  </div>

  <?php
    if ($osC_Services->isStarted('breadcrumb')) {
  ?>
      <div id="breadcrumbPath">
      <?php
        echo $breadcrumb->trail(' &raquo; ');
      ?>
      </div>
  <?php
    }
  ?>
</div>

<?php
  }
?>

<div id="pageWrapper">

  <div id="pageBlockLeft">
  <?php
    $content_left = '';

    if ($osC_Template->hasPageBoxModules()) {
      ob_start();

      foreach ($osC_Template->getBoxModules('left') as $box) {
        $osC_Box = new $box();
        $osC_Box->initialize();

        if ($osC_Box->hasContent()) {
          if ($osC_Template->getCode() == DEFAULT_TEMPLATE) {
            include('templates/' . $osC_Template->getCode() . '/modules/boxes/' . $osC_Box->getCode() . '.php');
          } else {
            if (file_exists('templates/' . $osC_Template->getCode() . '/modules/boxes/' . $osC_Box->getCode() . '.php')) {
              include('templates/' . $osC_Template->getCode() . '/modules/boxes/' . $osC_Box->getCode() . '.php');
            } else {
              include('templates/' . DEFAULT_TEMPLATE . '/modules/boxes/' . $osC_Box->getCode() . '.php');
            }
          }
        }

        unset($osC_Box);
      }

      $content_left = ob_get_contents();
      ob_end_clean();
    }

    if (!empty($content_left)) {
  ?>

    <div id="columnLeft">
      <div class="boxGroup">
      <?php
          echo $content_left;
      ?>
      </div>
    </div>

  <?php
    } else {
  ?>
    <style type="text/css"><!--
    #pageContent {
      width: 745px;
    }
    //--></style>
  <?php
    }
  ?>

    <div id="pageContent">

      <?php
        if ($messageStack->size('header') > 0) {
          echo $messageStack->output('header');
        }

        if ($osC_Template->hasPageContentModules()) {
          foreach ($osC_Services->getCallBeforePageContent() as $service) {
            $$service[0]->$service[1]();
          }

          foreach ($osC_Template->getContentModules('before') as $box) {
            $osC_Box = new $box();
            $osC_Box->initialize();

            if ($osC_Box->hasContent()) {
              if ($osC_Template->getCode() == DEFAULT_TEMPLATE) {
                include('templates/' . $osC_Template->getCode() . '/modules/content/' . $osC_Box->getCode() . '.php');
              } else {
                if (file_exists('templates/' . $osC_Template->getCode() . '/modules/content/' . $osC_Box->getCode() . '.php')) {
                  include('templates/' . $osC_Template->getCode() . '/modules/content/' . $osC_Box->getCode() . '.php');
                } else {
                  include('templates/' . DEFAULT_TEMPLATE . '/modules/content/' . $osC_Box->getCode() . '.php');
                }
              }
            }

            unset($osC_Box);
          }
        }

        if ($osC_Template->getCode() == DEFAULT_TEMPLATE) {
          include('templates/' . $osC_Template->getCode() . '/content/' . $osC_Template->getGroup() . '/' . $osC_Template->getPageContentsFilename());
        } else {
          if (file_exists('templates/' . $osC_Template->getCode() . '/content/' . $osC_Template->getGroup() . '/' . $osC_Template->getPageContentsFilename())) {
            include('templates/' . $osC_Template->getCode() . '/content/' . $osC_Template->getGroup() . '/' . $osC_Template->getPageContentsFilename());
          } else {
            include('templates/' . DEFAULT_TEMPLATE . '/content/' . $osC_Template->getGroup() . '/' . $osC_Template->getPageContentsFilename());
          }
        }
      ?>

      <div style="clear: both;"></div>

      <?php
        if ($osC_Template->hasPageContentModules()) {
          foreach ($osC_Services->getCallAfterPageContent() as $service) {
            $$service[0]->$service[1]();
          }

          foreach ($osC_Template->getContentModules('after') as $box) {
            $osC_Box = new $box();
            $osC_Box->initialize();

            if ($osC_Box->hasContent()) {
              if ($osC_Template->getCode() == DEFAULT_TEMPLATE) {
                include('templates/' . $osC_Template->getCode() . '/modules/content/' . $osC_Box->getCode() . '.php');
              } else {
                if (file_exists('templates/' . $osC_Template->getCode() . '/modules/content/' . $osC_Box->getCode() . '.php')) {
                  include('templates/' . $osC_Template->getCode() . '/modules/content/' . $osC_Box->getCode() . '.php');
                } else {
                  include('templates/' . DEFAULT_TEMPLATE . '/modules/content/' . $osC_Box->getCode() . '.php');
                }
              }
            }

            unset($osC_Box);
          }
        }
      ?>

    </div>

  </div>

  <?php
    $content_right = '';

    if ($osC_Template->hasPageBoxModules()) {
      ob_start();

      foreach ($osC_Template->getBoxModules('right') as $box) {
        $osC_Box = new $box();
        $osC_Box->initialize();

        if ($osC_Box->hasContent()) {
          if ($osC_Template->getCode() == DEFAULT_TEMPLATE) {
            include('templates/' . $osC_Template->getCode() . '/modules/boxes/' . $osC_Box->getCode() . '.php');
          } else {
            if (file_exists('templates/' . $osC_Template->getCode() . '/modules/boxes/' . $osC_Box->getCode() . '.php')) {
              include('templates/' . $osC_Template->getCode() . '/modules/boxes/' . $osC_Box->getCode() . '.php');
            } else {
              include('templates/' . DEFAULT_TEMPLATE . '/modules/boxes/' . $osC_Box->getCode() . '.php');
            }
          }
        }

        unset($osC_Box);
      }

      $content_right = ob_get_contents();
      ob_end_clean();
    }
  ?>
  <?php
    if (!empty($content_right)) {
  ?>
      <div id="pageColumnRight">
        <div class="boxGroup">
      <?php
          echo $content_right;
      ?>
        </div>
      </div>

  <?php
    } elseif ( empty($content_right) && empty($content_left) ) {
  ?>
      <style type="text/css"><!--
      #pageContent, #pageBlockLeft{
        width:960px;
      }
      --></style>
  <?php
    } elseif ( empty($content_right) ) {
  ?>
      <style type="text/css"><!--
      #pageContent {
        width: 745px;
      }

      #pageBlockLeft{
        width:960px;
      }
      //--></style>
  <?php
    }

    unset($content_left);
    unset($content_right);
  ?>
</div>

<?php
  if ($osC_Template->hasPageFooter()) {
?>

<div id="pageFooter">

<?php
    echo sprintf($osC_Language->get('footer'), date('Y'), osc_href_link(FILENAME_DEFAULT), STORE_NAME);
?>

</div>

<?php
    if ($osC_Services->isStarted('banner') && $osC_Banner->exists('468x60')) {
      echo '<p align="center">' . $osC_Banner->display() . '</p>';
    }
  }
?>

<?php
  if ($osC_Services->isStarted('piwik')) {
    echo $toC_Piwik->renderJs();
  }
?>
</body>
</html>