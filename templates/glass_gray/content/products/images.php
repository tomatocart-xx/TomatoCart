<?php
/*
  $Id: images.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $large_image = $osC_Image->show($osC_Product->getImage(), $osC_Product->getTitle(), 'id="productImageLarge"', 'large');
?>

<style type="text/css">
<!--
BODY {
  min-width: 0;
}
//-->
</style>

<script language="javascript" type="text/javascript">
<!--
function loadImage(imageUrl) {
  new Effect.Fade('productImageLarge', {duration: 0.3, afterFinish: function() {
    document.getElementById('productImageLarge').src = imageUrl;
    new Effect.Appear('productImageLarge', {duration: 0.3});
  }});
}
//-->
</script>

<div class="moduleBox">

<?php
  if ($osC_Product->numberOfImages() > 1) {
?>

  <div id="productImageThumbnails" class="content" style="position: absolute; top: 10px; overflow: auto; width: <?php echo ($osC_Image->getWidth('thumbnails') * 2) + 15; ?>px;">

<?php
    foreach ($osC_Product->getImages() as $images) {
      if ( isset($_GET['image']) && ($_GET['image'] == $images['id']) ) {
        $large_image = $osC_Image->show($images['image'], $osC_Product->getTitle(), 'id="productImageLarge"', 'large');
      }

      echo '<span style="width: ' . $osC_Image->getWidth($osC_Image->getCode(DEFAULT_IMAGE_GROUP_ID)) . 'px; padding: 2px; float: left; text-align: center;">' . osc_link_object(osc_href_link(FILENAME_PRODUCTS, 'images&' . $osC_Product->getID() . '&image=' . $images['id']),  $osC_Image->show($images['image'], $osC_Product->getTitle(), 'height="' . $osC_Image->getHeight($osC_Image->getCode(DEFAULT_IMAGE_GROUP_ID)) . '" style="max-width: ' . $osC_Image->getWidth($osC_Image->getCode(DEFAULT_IMAGE_GROUP_ID)) . 'px;"'), 'onclick="loadImage(\'' . $osC_Image->getAddress($images['image'], 'large') . '\'); return false;"') . '</span>';
    }
?>

  </div>

<?php
  }
?>

  <div id="productImageLargeBlock" style="position: absolute; left: <?php echo ($osC_Product->numberOfImages() > 1) ? ($osC_Image->getWidth($osC_Image->getCode(DEFAULT_IMAGE_GROUP_ID)) * 2) + 60 : 10; ?>px; top: 10px; text-align: center; width: <?php echo $osC_Image->getWidth('large'); ?>px;">

<?php
  echo $large_image;
?>

  </div>
</div>
