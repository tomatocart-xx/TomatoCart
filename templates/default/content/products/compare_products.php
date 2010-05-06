<?php
/*
  $Id: compare_products.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

<style type="text/css">
<!--
#pageWrapper {
  margin-left: 20px;
  padding: 0;
  float: left;
}

#pageContent {
  width: 100%;
  margin: 0;
  padding: 0;
}

div#pageBlockLeft {
  width: 0;
  margin: 0;
}

#compareProducts {
  border: 1px solid #CCCCCC;
}

td.label {
  font-weight: bold;
}

td {
  padding: 5px;
}

tr.odd {
  background: #F8F7F5 none repeat scroll 0 0;
}

tr.even {
  background: #EEEDED none repeat scroll 0 0 !important;
}
//-->
</style>

<div class="moduleBox">
  <h6><?php echo $osC_Language->get('compare_products_heading'); ?></h6>

  <div>
		<?php
		  echo $toC_Compare_Products->outputCompareProductsTable();
		?>

    <p align="right"><?php echo osc_link_object('javascript:window.close();', $osC_Language->get('close_window')); ?></p>
  </div>
</div>