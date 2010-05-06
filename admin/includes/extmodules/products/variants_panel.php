<?php
/*
  $Id: variants_panel.php $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

?>
Toc.products.StatusCheckColumn = function(config){
    Ext.apply(this, config);
    if(!this.id){
        this.id = Ext.id();
    }
    this.renderer = this.renderer.createDelegate(this);
};

Toc.products.StatusCheckColumn.prototype = {
    init : function(grid){
        this.grid = grid;
        this.grid.on('render', function(){
            var view = this.grid.getView();
            view.mainBody.on('mousedown', this.onMouseDown, this);
        }, this);
    },

    onMouseDown : function(e, t){
        if(t.className && t.className.indexOf('x-grid3-cc-'+this.id) != -1){
            e.stopEvent();
            var index = this.grid.getView().findRowIndex(t);
            var record = this.grid.store.getAt(index);
            record.set(this.dataIndex, !record.data[this.dataIndex]);
            this.grid.store.commitChanges();
        }
    },

    renderer : function(v, p, record){
        p.css += ' x-grid3-check-col-td'; 
        return '<div class="x-grid3-check-col'+(v?'-on':'')+' x-grid3-cc-'+this.id+'">&#160;</div>';
    }
};

Toc.products.VariantsPanel = function(config) {
  config = config || {};
  
  config.title = '<?php echo $osC_Language->get('section_variants'); ?>';
  config.layout = 'border';
  config.items = this.buildForm(config.productsId);
  
  this.groups = [];
  this.groupsName = [];
  this.values = [];
  this.valuesName = [];  

  this.productsId = config.productsId || null;

  this.addEvents({'variantschange' : true});    
  
  Toc.products.VariantsPanel.superclass.constructor.call(this, config);
};

Ext.extend(Toc.products.VariantsPanel, Ext.Panel, {
  getVariantGroupName: function(groupId) {
    root = this.pnlCheckTree.getRootNode();
    node = root.findChild('id', groupId);  
    
    return node.text;
  },
  
  getVariantValueName: function(groupId, value) {
    root = this.pnlCheckTree.getRootNode();
    node = root.findChild('id', groupId).findChild('id', value);

    return node.text;
  },
  
  hasVariants: function() {
    return !Ext.isEmpty(this.pnlCheckTree.getValue().toString().trim());
  },
  
  updateVariants: function() {
    this.groups = [];
    this.groupsName = [];
    this.values = [];
    this.valuesName = [];
    
    if (!Ext.isEmpty(this.pnlCheckTree.getValue().toString().trim())) {
      selValues = this.pnlCheckTree.getValue().toString().split(',');
      
      Ext.each(selValues, function(value, i){
        groupId = value.split('_')[0];
        valueId = value.split('_')[1];
         
        if (this.groups.indexOf(groupId) < 0) {
          this.groups.push(groupId);
          this.groupsName.push(this.getVariantGroupName(groupId));
           
          this.values.push([valueId]);
          this.valuesName.push([this.getVariantValueName(groupId, value)]);
        } else {
          index = this.groups.indexOf(groupId);
           
          if(this.values[index].indexOf(valueId) < 0) {
            this.values[index].push(valueId);
            this.valuesName[index].push(this.getVariantValueName(groupId, value));
          }
        }
      }, this);
    }
    
    this.updateVariantGrid();
    this.fireEvent('variantschange', this.hasVariants(), this.getQuantity());
  },
  
  updateVariantGrid: function() {
    store = this.grdVariants.getStore();
    store.removeAll();

    if (this.values.length > 0) {
      model = [];
      record = [];
         
      record.push({name: 'id', type: 'string'});
      for (i = 0; i < this.groupsName.length; i++) {
        model.push({header: this.groupsName[i], dataIndex: this.groups[i]});
        record.push({name: this.groupsName[i], type: 'string'});
      }
      
      model.push({header: '<?php echo $osC_Language->get('table_heading_quantity');?>', dataIndex: 'qty', align: 'center', editor: new Ext.form.TextField({allowBlank: false})});
      model.push({header: '<?php echo $osC_Language->get('table_heading_price_net');?>', dataIndex: 'price', align: 'center', editor: new Ext.form.TextField({allowBlank: false}), renderer: tocCurrenciesFormatter});
      model.push({header: '<?php echo $osC_Language->get('table_heading_sku');?>', dataIndex: 'sku', align: 'center', editor: new Ext.form.TextField({allowBlank: false})});
      model.push({header: '<?php echo $osC_Language->get('table_heading_model');?>', dataIndex: 'model', align: 'center', editor: new Ext.form.TextField({allowBlank: false})});
      model.push({header: '<?php echo $osC_Language->get('table_heading_weight');?>', dataIndex: 'weight', align: 'center', editor: new Ext.form.TextField({allowBlank: false})});
      model.push(this.checkColumn);
      
      record.push({name: 'qty', type: 'string'});
      record.push({name: 'price', type: 'string'});
      record.push({name: 'sku', type: 'string'});
      record.push({name: 'model', type: 'string'});
      record.push({name: 'weight', type: 'string'});
      record.push({name: 'status', type: 'bool'});
      
      variantRecord = Ext.data.Record.create(record);  
      this.grdVariants.getColumnModel().setConfig(model);  
           
      var radices = [];
      for ( i= 0; i < this.values.length ; i++) {
        var radix = 1;
        for( k = i + 1; k < this.values.length; k++){
          radix = radix * this.values[k].length;
        }
        radices.push(radix);
      }
      var numOfRows = radices[0] * this.values[0].length;
      
      for (var i = 0; i < numOfRows; i++) {
        var dividend = i;
        var data = {};
        var tmp = [];
        
        for( j = 0; j < this.values.length; j++ ){
          index = Math.floor(dividend / radices[j]);
          dividend = dividend - (index * radices[j]);
          
          data[this.groups[j]] = this.valuesName[j][index];
          tmp.push(this.groups[j] + '_' + this.values[j][index]);
        }
        
        data.id = tmp.join('-');
        data.qty = 0;
        data.price = this.pnlData.txtPriceNet.getValue();
        data.sku = '';
        data.model = '';
        data.weight = 0;
        data.status = true;
        
        var row = new variantRecord(data);      
        store.add(row);
      }
    }
  },
  
  initVariantGrid: function(productsId) {
    if (productsId != null) {
      Ext.Ajax.request({
        url: Toc.CONF.CONN_URL, 
        params: { 
          module: 'products',
          action: 'get_variants_products',
          products_id: productsId
        },
        callback: function(options, success, response) {
          if (success == true) {
            var result = Ext.decode(response.responseText);
            
            if (result.success == true) {
              store = this.grdVariants.getStore();
              store.removeAll();
          
              model = [];
              record = [];
              
              record.push({name: 'id', type: 'string'});
              for (i = 0; i < result.variants_groups_names.length; i++) {
                model.push({header: result.variants_groups_names[i], dataIndex: result.variants_groups_ids[i]});
                record.push({name: result.variants_groups_names[i], type: 'string'});
              }
              
              model.push({header: '<?php echo $osC_Language->get('table_heading_quantity');?>', dataIndex: 'qty', align: 'center', editor: new Ext.form.TextField({allowBlank: false})});
              model.push({header: '<?php echo $osC_Language->get('table_heading_price_net');?>', dataIndex: 'price', align: 'center', editor: new Ext.form.TextField({allowBlank: false}), renderer: tocCurrenciesFormatter});
              model.push({header: '<?php echo $osC_Language->get('table_heading_sku');?>', dataIndex: 'sku', align: 'center', editor: new Ext.form.TextField({allowBlank: false})});
              model.push({header: '<?php echo $osC_Language->get('table_heading_model');?>', dataIndex: 'model', align: 'center', editor: new Ext.form.TextField({allowBlank: false})});
              model.push({header: '<?php echo $osC_Language->get('table_heading_weight');?>', dataIndex: 'weight', align: 'center', editor: new Ext.form.TextField({allowBlank: false})});
              model.push(this.checkColumn);
              
              record.push({name: 'qty', type: 'string'});
              record.push({name: 'price', type: 'string'});
              record.push({name: 'sku', type: 'string'});
              record.push({name: 'model', type: 'string'});
              record.push({name: 'weight', type: 'string'});
              record.push({name: 'status', type: 'bool'});
              
              variantRecord = Ext.data.Record.create(record);  
              this.grdVariants.getColumnModel().setConfig(model);  
              
              for (i = 0; i < result.data.records.length; i++) {
                var v = new variantRecord(result.data.records[i]);      
                store.add(v);
              }
              
              this.grdVariants.doLayout();
              
              this.fireEvent('variantschange', this.hasVariants(), this.getQuantity());
            }
          } else {
            Ext.MessageBox.alert(TocLanguage.msgErrTitle, TocLanguage.msgErrTitle);
          }
        },
        scope: this
      });
    }
  },
    
  buildForm: function(productsId) {
    this.pnlCheckTree = new Ext.ux.tree.CheckTreePanel({
      title: '<?php echo $osC_Language->get('section_variants'); ?>',
      region: 'west',
      border: false,
      width: 180,
      minWidth: 160,
      maxWidth: 220,
      split: true,
      name: 'variants', 
      xtype: 'checktreepanel',
      deepestOnly: true,
      bubbleCheck: 'none',
      cascadeCheck: 'checked',
      autoScroll: true,
      rootVisible: false,
      anchor:'-24 -60',
      animate: false,
      root: {
        text: 'root',
        id: 'root',
        expanded: true,
        uiProvider: false
      },
      loader: {
        url: Toc.CONF.CONN_URL,
        baseParams: {
          module: 'products',
          action: 'get_variants',
          products_id: productsId
        }
      },
      listeners: {
        checkchange: this.updateVariants,
        scope: this
      }
    });
        
    this.checkColumn = new Toc.products.StatusCheckColumn({
       header: "<?php echo $osC_Language->get('table_heading_status');?>",
       dataIndex: 'status',
       align: 'center',
       width: 55
    });
    
    this.grdVariants = new Ext.grid.EditorGridPanel({
      region: 'center',
      ds: new Ext.data.Store(),
      cm: new Ext.grid.ColumnModel([
        new Ext.grid.RowNumberer(),
        {header: '<?php echo $osC_Language->get('table_heading_quantity');?>', dataIndex: 'qty', align: 'center', editor: new Ext.form.TextField({allowBlank: false})},
        {header: '<?php echo $osC_Language->get('table_heading_price_net');?>', dataIndex: 'price', align: 'center', editor: new Ext.form.TextField({allowBlank: false})},
        {header: '<?php echo $osC_Language->get('table_heading_sku');?>', dataIndex: 'sku', align: 'center', editor: new Ext.form.TextField({allowBlank: false})},
        {header: '<?php echo $osC_Language->get('table_heading_model');?>', dataIndex: 'model', align: 'center', editor: new Ext.form.TextField({allowBlank: false})},
        {header: '<?php echo $osC_Language->get('table_heading_weight');?>', dataIndex: 'weight', align: 'center', editor: new Ext.form.TextField({allowBlank: false})},
        this.checkColumn
      ]),
      sm: new Ext.grid.RowSelectionModel({ 
        singleSelect: true 
      }),
      clicksToEdit: 1,
      plugins: this.checkColumn,
      viewConfig: {
        forceFit: true
      },
      listeners: {
        afteredit: function(e) {
          e.grid.store.commitChanges();
          this.fireEvent('variantschange', this.hasVariants(), this.getQuantity());
        },
        scope: this
      }
    });
    this.initVariantGrid(productsId);
    
    return [this.pnlCheckTree, this.grdVariants];
  },
  
  getVariants: function() {
    var variants = [];
    
    this.grdVariants.getStore().each(function(record) {
      variant = record.get('id') + ':' + record.get('qty') + ':' + record.get('price') + ':' + record.get('sku') + ':' + record.get('model') + ':' + record.get('weight') + ':' + record.get('status');
      
      variants.push(variant);
    });  
    
    return variants.join(';');
  },
  
  getQuantity: function() {
    var quantity = 0;
    
    this.grdVariants.getStore().each(function(record) {
      quantity += parseInt(record.get('qty'));
    });  
    
    return quantity;
  },
  
  onRowAction: function(grid, record, action, row, col) {
    switch(action) {
      case 'icon-delete-record':
        this.getStore().removeAt(row);
        break;
    }
  },
    
  onRefresh: function() {
    this.getStore().reload();
  }
});