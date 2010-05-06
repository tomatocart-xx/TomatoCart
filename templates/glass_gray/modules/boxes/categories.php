<?php
/*
  $Id: categories.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2005 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  $toC_CategoryTree = new toC_CategoryTree();
  $toC_CategoryTree->reset();
  $toC_CategoryTree->setCategoryPath($cPath, '<b>', '</b>');
  $toC_CategoryTree->setParentGroupString('', '');
  $toC_CategoryTree->setParentString('', '');
  $toC_CategoryTree->setChildString('<li>', '</li>');
  $toC_CategoryTree->setSpacerString('&nbsp;', 5);
  $toC_CategoryTree->setLeadingString('<img src="templates/' . $osC_Template->getCode() . '/images/box_category_arrow.png" />&nbsp;&nbsp;');
  $toC_CategoryTree->setShowCategoryProductCount((BOX_CATEGORIES_SHOW_PRODUCT_COUNT == '1') ? true : false);
?>

<!-- box categories start //-->

<div id="boxCategories" class="boxNew">
  <div class="boxTitle"><?php echo $osC_Box->getTitle(); ?></div>

  <div class="boxContents">
    <ul>
      <?php echo $toC_CategoryTree->buildTree(); ?>
    </ul>
  </div>
</div>

<?php 
  unset($osC_Box);
?>
<!-- box categories end //-->
