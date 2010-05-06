/*
  $Id: auto_completer.js $
  TomatoCart Open Source Shopping Cart Solutions
  http://www.tomatocart.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

Autocompleter.implement({
  showChoices: function() {
    var match = this.options.choicesMatch, first = this.choices.getFirst(match);
    this.selected = this.selectedValue = null;
    if (this.fix) {
      var pos = this.element.getCoordinates(this.relative), width = this.options.width || 'auto';
      this.choices.setStyles({
        'left': pos.left - 101,
        'top': pos.bottom,
        'width': (width === true || width == 'inherit') ? pos.width : width
      });
    }
    if (!first) return;
    if (!this.visible) {
      this.visible = true;
      this.choices.setStyle('display', '');
      if (this.fx) this.fx.start(1);
      this.fireEvent('onShow', [this.element, this.choices]);
    }
    if (this.options.selectFirst || this.typeAhead || first.inputValue == this.queryValue) this.choiceOver(first, this.typeAhead);
    var items = this.choices.getChildren(match), max = this.options.maxChoices;
    var styles = {'overflowY': 'hidden', 'height': ''};
    this.overflown = false;
    if (items.length > max) {
      var item = items[max - 1];
      styles.overflowY = 'scroll';
      styles.height = item.getCoordinates(this.choices).bottom;
      this.overflown = true;
    };
    this.choices.setStyles(styles);
    this.fix.show();
    
    if (this.options.visibleChoices) {
      var scroll = document.getScroll(),
      size = document.getSize(),
      coords = this.choices.getCoordinates();
      if (coords.right > scroll.x + size.x) scroll.x = coords.right - size.x;
      if (coords.bottom > scroll.y + size.y) scroll.y = coords.bottom - size.y;
      window.scrollTo(Math.min(scroll.x, coords.left), Math.min(scroll.y, coords.top));
    }
  }
});

var TocAutoCompleter = new Class({
  Extends: Autocompleter.Request.JSON,
  
  options: {
    remoteUrl: 'json.php',
    sessionName: 'sid',
    sessionId: null,
    postData: {module: 'auto_completer', action: 'get_products'},
    minLength: 3,
    filterSubset: true,
    cache: true,
    delay: 250,
    width: 235,
    selectionLength: 23
  },
  
  initialize: function(el, options) {
    this.options.postVar = el;
    this.parent(el, this.options.remoteUrl, options);
    this.options.postData[this.options.sessionName] = this.options.sessionId;
    
    if (options.template) {
      this.options.postData['template'] = options.template;  
    }
    
    this.setSelectionValueLength(this.options.selectionLength);
  },
  
  setSelectionValueLength: function(length) {
    this.observer.setValue = function(value) {
      value = value.substr(0, length);
      
      this.value = value;
      this.element.set('value', value);
      
      return this.clear();
    };
  }  
});