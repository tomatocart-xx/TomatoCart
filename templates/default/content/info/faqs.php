<?php
/*
  $Id: faqs.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd;  Copyright (c) 2006 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/
?>
<h1><?php echo $osC_Template->getPageTitle(); ?></h1>

<div id="faqs" class="moduleBox">
  <?php
  $Qfaqs = toC_Faqs::getListing();

  while ($Qfaqs->next()) {
  ?>
    <dl id="faq<?php echo $Qfaqs->valueInt('faqs_id'); ?>">
      <dt class="question">
        <?php echo $Qfaqs->value('faqs_question'); ?>
      </dt>
      <dd class="answer">
        <?php echo $Qfaqs->value('faqs_answer'); ?>
      </dd>
    </dl>
  <?php
  }
  ?>
  
  <script language="javascript" type="text/javascript">

  window.addEvent('domready',function(){
    $$('.question').each( function(question) {
      var faqs_answer = new Fx.Slide(question.getNext());
      faqs_answer.hide();

      question.addEvent('click', function(e){
        e = new Event(e);
        faqs_answer.toggle();
        e.stop();
      });

      <?php
        if (isset($_GET['faqs_id']) && !empty($_GET['faqs_id'])) {
      ?>
        if(question.getParent().id == 'faq<?php echo $_GET['faqs_id']; ?>')
          faqs_answer.toggle();
      <?php
        }
      ?>
    });
  });
  </script>
</div>

