/*
 * HTML5 Sortable jQuery Plugin
 * https://github.com/voidberg/html5sortable
 *
 * Original code copyright 2012 Ali Farhadi.
 * This version is mantained by Alexandru Badiu <andu@ctrlz.ro>
 * Changes by Bryan Burgers: Added sortstart and sortend events; renamed to not conflict with jQuery UI sortable
 *
 * Thanks to the following contributors: andyburke, bistoco, daemianmack, drskullster, flying-sheep, OscarGodson, Parikshit N. Samant, rodolfospalenza, ssafejava
 *
 * Released under the MIT license.
 */
'use strict';

(function ($) {
  var dragging, draggingHeight, placeholders = $();
  $.fn.blocksSortable = function (options) {
    var method = String(options);

    options = $.extend({
      connectWith: false,
      placeholder: null,
      dragImage: null
    }, options);

    return this.each(function () {
      if (method === 'reload') {
        $(this).children(options.items).off('dragstart.h5s dragend.h5s selectstart.h5s dragover.h5s dragenter.h5s drop.h5s');
      }
      if (/^enable|disable|destroy$/.test(method)) {
        var citems = $(this).children($(this).data('items')).attr('draggable', method === 'enable');
        if (method === 'destroy') {
          $(this).off('sortstart sortupdate sortend');
          $(this).removeData('opts');
          citems.add(this).removeData('connectWith items')
            .off('dragstart.h5s dragend.h5s selectstart.h5s dragover.h5s dragenter.h5s drop.h5s').off('sortstart sortupdate sortend');
        }
        return;
      }

      var soptions = $(this).data('opts');

      if (typeof soptions === 'undefined') {
        $(this).data('opts', options);
      }
      else {
        options = soptions;
      }

      var isHandle, index, items = $(this).children(options.items);
      var startParent, newParent;
      var placeholder = ( options.placeholder === null ) ? $('<' + (/^ul|ol$/i.test(this.tagName) ? 'li' : 'div') + ' class="sortable-placeholder">') : $(options.placeholder).addClass('sortable-placeholder');

      items.find(options.handle).mousedown(function () {
        isHandle = true;
      }).mouseup(function () {
          isHandle = false;
        });
      $(this).data('items', options.items);
      placeholders = placeholders.add(placeholder);
      if (options.connectWith) {
        $(options.connectWith).add(this).data('connectWith', options.connectWith);
      }

      items.attr('role', 'option');
      items.attr('aria-grabbed', 'false');

      items.attr('draggable', 'true').on('dragstart.h5s',function (e) {
        e.stopImmediatePropagation();
        if (options.handle && !isHandle) {
          return false;
        }
        isHandle = false;
        var dt = e.originalEvent.dataTransfer;
        dt.effectAllowed = 'move';
        dt.setData('text', '');

        if (options.dragImage && dt.setDragImage) {
          dt.setDragImage(options.dragImage, 0, 0);
        }

        index = (dragging = $(this)).addClass('sortable-dragging').attr('aria-grabbed', 'true').index();
        draggingHeight = dragging.outerHeight();
        startParent = $(this).parent();
        startParent.triggerHandler('sortstart', {item: $(this), oldindex: index, startparent: startParent });
      }).on('dragend.h5s',function () {
          if (!dragging) {
            return;
          }
          dragging.removeClass('sortable-dragging').attr('aria-grabbed', 'false').show();
          placeholders.detach();
          newParent = $(this).parent();
          if (index !== dragging.index() || startParent.get(0) !== newParent.get(0)) {
            dragging.parent().triggerHandler('sortupdate', {item: dragging, oldindex: index, startparent: startParent, endparent: newParent});
          }
          dragging.parent().triggerHandler('sortend', {item: dragging, oldindex: index, startparent: startParent, endparent: newParent});
          dragging = null;
          draggingHeight = null;
        }).not('a[href], img').on('selectstart.h5s',function () {
          if (options.handle && !isHandle) {
            return true;
          }

          if (this.dragDrop) {
            this.dragDrop();
          }
          return false;
        }).end().add([this, placeholder]).on('dragover.h5s dragenter.h5s drop.h5s', function (e) {
          if (!items.is(dragging) && options.connectWith !== $(dragging).parent().data('connectWith')) {
            return true;
          }
          if (e.type === 'drop') {
            e.stopPropagation();
            placeholders.filter(':visible').after(dragging);
            dragging.trigger('dragend.h5s');
            return false;
          }
          e.preventDefault();
          e.originalEvent.dataTransfer.dropEffect = 'move';
          if (items.is(this)) {
            var thisHeight = $(this).outerHeight();
            if (options.forcePlaceholderSize) {
              placeholder.height(draggingHeight);
            }

            // Check if $(this) is bigger than the draggable. If it is, we have to define a dead zone to prevent flickering
            if (thisHeight > draggingHeight) {
              // Dead zone?
              var deadZone = thisHeight - draggingHeight, offsetTop = $(this).offset().top;
              if (placeholder.index() < $(this).index() && e.originalEvent.pageY < offsetTop + deadZone) {
                return false;
              }
              else if (placeholder.index() > $(this).index() && e.originalEvent.pageY > offsetTop + thisHeight - deadZone) {
                return false;
              }
            }

            dragging.hide();
            $(this)[placeholder.index() < $(this).index() ? 'after' : 'before'](placeholder);
            placeholders.not(placeholder).detach();
          } else if (!placeholders.is(this) && !$(this).children(options.items).length) {
            placeholders.detach();
            $(this).append(placeholder);
          }
          return false;
        });
    });
  };
})(jQuery);