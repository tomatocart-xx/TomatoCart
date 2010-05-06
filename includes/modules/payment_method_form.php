<?php
/*
  $Id: payment_method_form.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<form name="checkout_payment" id="checkout_payment" action="<?php echo osc_href_link(FILENAME_CHECKOUT, 'confirmation', 'SSL'); ?>" method="post">

<?php
  if (DISPLAY_CONDITIONS_ON_CHECKOUT == '1') {
?>

<div class="moduleBox">
  <h6><?php echo $osC_Language->get('order_conditions_title'); ?></h6>

  <div class="content">
    <?php echo sprintf($osC_Language->get('order_conditions_description'), osc_href_link(FILENAME_INFO, 'articles&articles_id=' . 4)) . '<br /><br />' . osc_draw_checkbox_field('conditions', array(array('id' => 1, 'text' => $osC_Language->get('order_conditions_acknowledge'))), false); ?>
  </div>
</div>

<div class="clear"></div>
<?php
  }
?>

<div class="moduleBox">

  <div class="content">

<?php
  $selection = $osC_Payment->selection();

  if (sizeof($selection) > 0) {
?>

    <div style="float: right; padding: 0px 0px 10px 20px; text-align: center;">
      <?php echo '<b>' . $osC_Language->get('please_select') . '</b><br />' . osc_image(DIR_WS_IMAGES . 'arrow_east_south.gif'); ?>
    </div>

    <p style="margin-top: 0px;"><?php echo $osC_Language->get('choose_payment_method'); ?></p>

<?php
  } else {
?>

    <p style="margin-top: 0px;"><?php echo $osC_Language->get('only_one_payment_method_available'); ?></p>

<?php
  }
?>
<?php
  if ($osC_Customer->isLoggedOn() && $osC_Customer->hasStoreCredit()) {
    echo 
      '<table border="0" width="100%" cellspacing="0" cellpadding="2">
         <tr class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">
           <td>' . 
             osc_draw_checkbox_field('payment_method_store_credit', '1', $osC_ShoppingCart->isUseStoreCredit() ? true : false) . '&nbsp;<b>' . sprintf($osC_Language->get('pay_with_store_credit_title'), $osC_Currencies->format($osC_Customer->getStoreCredit())) . '</b>' . 
          '</td>
         </tr>
       </table>';
  }
 ?>
    <table id="payment_methods" border="0" width="100%" cellspacing="0" cellpadding="2" style="display: <?php echo $osC_ShoppingCart->isTotalZero() ? 'none' : ''; ?>">
<?php
  $radio_buttons = 0;
  for ($i=0, $n=sizeof($selection); ($i<$n); $i++) {
?>

      <tr id="payment_method_<?php echo $selection[$i]['id']; ?>">
        <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">

<?php
    if ( ($n == 1) || ($osC_ShoppingCart->hasBillingMethod() && ($selection[$i]['id'] == $osC_ShoppingCart->getBillingMethod('id'))) ) {
      echo '          <tr id="defaultSelected" class="moduleRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(\'checkout_payment\', this)">' . "\n";
    } else {
      echo '          <tr class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(\'checkout_payment\', this)">' . "\n";
    }
?>

            <td width="10">&nbsp;</td>

<?php
    if ($n > 1) {
?>

            <td colspan="3"><?php echo '<b>' . $selection[$i]['module'] . '</b>'; ?></td>
            <td align="right"><?php echo osc_draw_radio_field('payment_method', $selection[$i]['id'], ($osC_ShoppingCart->hasBillingMethod() ? $osC_ShoppingCart->getBillingMethod('id') : null)); ?></td>

<?php
    } else {
?>

            <td colspan="4"><?php echo '<b>' . $selection[$i]['module'] . '</b>' . osc_draw_hidden_field('payment_method', $selection[$i]['id']); ?></td>

<?php
  }
?>

            <td width="10">&nbsp;</td>
          </tr>

<?php
    if (isset($selection[$i]['error'])) {
?>

          <tr>
            <td width="10">&nbsp;</td>
            <td colspan="4"><?php echo $selection[$i]['error']; ?></td>
            <td width="10">&nbsp;</td>
          </tr>

<?php
    } elseif (isset($selection[$i]['fields']) && is_array($selection[$i]['fields'])) {
?>

          <tr>
            <td width="10">&nbsp;</td>
            <td colspan="4"><table border="0" cellspacing="0" cellpadding="2">

<?php
      for ($j=0, $n2=sizeof($selection[$i]['fields']); $j<$n2; $j++) {
?>

              <tr>
                <td width="10">&nbsp;</td>
                <td><?php echo $selection[$i]['fields'][$j]['title']; ?></td>
                <td width="10">&nbsp;</td>
                <td><?php echo $selection[$i]['fields'][$j]['field']; ?></td>
                <td width="10">&nbsp;</td>
              </tr>

<?php
      }
?>

            </table></td>
            <td width="10">&nbsp;</td>
          </tr>

<?php
    }
?>

        </table></td>
      </tr>

<?php
    $radio_buttons++;
  }
?>

    </table>
  </div>
</div>

<div class="moduleBox">
  <h6><?php echo $osC_Language->get('add_comment_to_order_title'); ?></h6>

  <div class="content">
    <?php echo osc_draw_textarea_field('payment_comments', (isset($_SESSION['comments']) ? $_SESSION['comments'] : null), null, null, 'style="width: 98%;"'); ?>
  </div>
</div>

<br />

<div class="submitFormButtons" style="text-align: right;">
  <?php echo osc_draw_image_button('button_continue.gif', $osC_Language->get('button_continue'), 'id="btnSavePaymentMethod" style="cursor: pointer"'); ?>
  </div>

</form>