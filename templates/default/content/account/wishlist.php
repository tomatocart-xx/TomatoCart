<?php
  /*$Id: wishlist.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>

<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<?php
  if ($messageStack->size('wishlist') > 0) {
    echo $messageStack->output('wishlist');
  }
?>

<div class="moduleBox">
  <?php 
  if ($toC_Wishlist->hasContents()) {
  ?>
    <form name="update_wishlist" method="post" action="<?php echo osc_href_link(FILENAME_ACCOUNT, 'wishlist=update', 'SSL'); ?>">
    
      <table border="0" width="100%" cellspacing="0" cellpadding="2" class="productListing">
        <tr>
          <td class="productListing-heading" align="center"><?php echo $osC_Language->get('listing_products_heading'); ?></td>
          <td class="productListing-heading"><?php echo $osC_Language->get('listing_comments_heading'); ?></td>
          <td class="productListing-heading" align="center" width="70"><?php echo $osC_Language->get('listing_date_added_heading'); ?></td>
          <td class="productListing-heading"></td>
        </tr>
  <?php
      $rows = 0;
      foreach($toC_Wishlist->getProducts() as $product) {    
        $rows++;

  ?>
  
         <tr class="<?php echo ((($rows/2) == floor($rows/2)) ? 'productListing-even' : 'productListing-odd'); ?>">        
           <td align="center"><?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, $product['products_id']), $osC_Image->show($product['image'], $product['name'], 'hspace="5" vspace="5"')) . '<br />' . $product['name'] . '<br />' . $osC_Currencies->format($product['price']); ?></td>         
           <td valign="top"><?php echo osc_draw_textarea_field('comments[' . $product['products_id'] . ']', $product['comments'], 24, 5); ?></td>
           <td align="center" valign="top"><?php echo $product['date_added']; ?></td>
           <td align="center" valign="top">
             <p>
               <?php echo osc_link_object(osc_href_link(FILENAME_ACCOUNT, 'wishlist=delete&products_id=' . $product['products_id']), osc_draw_image_button('button_delete.gif', $osC_Language->get('button_delete'))); ?>
             </p>
             
             <?php echo osc_link_object(osc_href_link(FILENAME_PRODUCTS, $product['products_id'] . '&action=cart_add'), osc_draw_image_button('button_in_cart.gif', $osC_Language->get('button_add_to_cart'))); ?>
           </td>
         </tr>    
              
  <?php    
      }
  ?>
      </table>
      
      <div class="submitFormButtons" style="text-align: right;">
        <?php echo osc_draw_image_submit_button('button_update.gif') . '&nbsp;' . osc_link_object(osc_href_link('javascript:window.history.go(-1);'), osc_draw_image_button('button_back.gif', $osC_Language->get('button_back'))); ?>
      </div>
            
     </form>
  <?php        
    }else { 
  ?>          
  
    <div class="content">
     <span><?php echo $osC_Language->get('wishlist_empty'); ?></span>
    </div>
      
    <div class="submitFormButtons" style="text-align: right;">
      <?php echo osc_link_object(osc_href_link('javascript:window.history.go(-1);'), osc_draw_image_button('button_back.gif', $osC_Language->get('button_back'))); ?>
    </div>
    
  <?php
    } 
  ?>
  
  <?php 
  if ($toC_Wishlist->hasContents()) {
  ?>
    <div class="moduleBox">
      <em style="float: right; margin-top: 10px;"><?php echo $osC_Language->get('form_required_information'); ?></em>
        
      <h6><?php echo $osC_Language->get('share_your_wishlist_title'); ?></h6>
  
      <form name="share_wishlist" method="post" action="<?php echo osc_href_link(FILENAME_ACCOUNT, 'wishlist=share_wishlist', 'SSL'); ?>">      
        <div class="content">   
  
          <p><?php echo osc_draw_label($osC_Language->get('field_share_wishlist_customer_name'), 'wishlist_customer', null, true) . ' ' . osc_draw_input_field('wishlist_customer', ($osC_Customer->isLoggedOn() ? $osC_Customer->getName() : null)); ?></p>
          
          <p><?php echo osc_draw_label($osC_Language->get('field_share_wishlist_customer_email'), 'wishlist_from_email', null, true) . ' ' . osc_draw_input_field('wishlist_from_email', ($osC_Customer->isLoggedOn() ? $osC_Customer->getEmailAddress() : null)); ?></p>
          
          <p><?php echo osc_draw_label($osC_Language->get('field_share_wishlist_emails'), 'wishlist_emails', null, true) . ' ' . osc_draw_textarea_field('wishlist_emails', null, 40, 5); ?></p>
           
          <p><?php echo osc_draw_label($osC_Language->get('field_share_wishlist_message'), 'wishlist_message', null, true) . ' ' . osc_draw_textarea_field('wishlist_message', null, 40, 5); ?></p>                  
        </div>   
        
        <div class="submitFormButtons" style="text-align: right;">
          <?php echo osc_draw_image_submit_button('button_continue.gif'); ?>
        </div>
      
      </form>
    </div>
<? 
  }
?>    
</div>