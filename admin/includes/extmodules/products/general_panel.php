<?php
/*
  $Id: general_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>

Toc.products.GeneralPanel = function(config) {
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('section_general'); ?>';
  config.activeTab = 0;
  config.deferredRender = false;
  config.items = this.buildForm();
  
  Toc.products.GeneralPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.products.GeneralPanel, Ext.TabPanel, {
  buildForm: function() {
    var panels = [];
    
    <?php
      foreach ($osC_Language->getAll() as $l) {
      
        echo 'var lang' . $l['code'] . ' = new Ext.Panel({
          title:\'' . $l['name'] . '\',
          iconCls: \'icon-' . $l['country_iso'] . '-win\',
          layout: \'form\',
          labelSeparator: \' \',
          style: \'padding: 8px\',
          defaults: {
            anchor: \'98%\'
          },
          items: [
            {xtype: \'textfield\', fieldLabel: \'' . $osC_Language->get('field_name') . '\', name: \'products_name[' . $l['id'] . ']\', allowBlank: false},
            {xtype: \'textfield\', fieldLabel: \'' . $osC_Language->get('field_tags') . '\', name: \'products_tags[' . $l['id'] . ']\'},
            {xtype: \'textarea\', fieldLabel: \'' . $osC_Language->get('field_short_description') . '\', name: \'products_short_description[' . $l['id'] . ']\', height: \'80\'},
            {xtype: \'htmleditor\', fieldLabel: \'' . $osC_Language->get('field_description') . '\', name: \'products_description[' . $l['id'] . ']\', height: \'auto\'},
            {xtype: \'textfield\', fieldLabel: \'' . $osC_Language->get('field_url') . '\', name: \'products_url[' . $l['id'] . ']\'}
            ]
        });
        
        panels.push(lang' . $l['code'] . ');
        ';
      }
    ?>
    
    return panels;
  }
});