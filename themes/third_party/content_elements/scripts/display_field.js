

if (typeof ContentElementsCallbacks == "undefined")
{
	ContentElementsCallbacks = {
		display: {},
		beforeSort: {},
		afterSort: {},
		remove: {}
	}
}

(function($) {

	//** -------------------------
	//**	CONTENT ELEMENTS
	//** -------------------------	

	ContentElements = function() {
		
		$('.content_elements_wrapper').each(function(k,v)
		{
			ContentElements.displayField($(v));
		});
	}
	
	ContentElements.displayField = function(_wrapper){
	
		//** -------------------------
		//**	INIT
		//** -------------------------	

		if (_wrapper.data('initialized'))
		{
			return false;
		}
		else
		{
			_wrapper.data('initialized', true);
		}	
		
		//** -------------------------
		//**	ADD BUTTON
		//** -------------------------			
					
		_wrapper.find('.content_elements_toolbar .button_add').click(function()
		{		
			_button_add = $(this);
		
			var label 			= _button_add.attr("rel");
			var elm_type 		= _button_add.parents('.content_elements_toolbar_item').attr("id").split('content_elements_toolbar_item_')[1];
			var elm_name		= _wrapper.attr("data-field");					
			var field_id		= _button_add.parents('.content_elements_toolbar').attr("id").split('content_elements_toolbar_')[1];
			var eid				= _button_add.attr("id");
			var elm_field 		= $('#content_element_prototype_' + eid).html();
			var elm_hash_key 	= ContentElements.randomString();
		
			if($(this).parents().hasClass('grid_field')) {
				
				elm_name =  'content_elements';
			}

			var elm_field 		= elm_field.replace(/__element_name__/g, elm_name);
			var elm_field 		= elm_field.replace(/\[__index__\]/g, "[" + elm_hash_key + "]");
		
			_wrapper.find('.content_elements_tiles').append(elm_field);	
			
			$last = _wrapper.find('.content_elements_tiles .content_elements_tile_body:last');
			
			if($(this).parents().hasClass('grid_field')) {
				
				var row_id = _wrapper.closest('td').data('row-id');
				
				if(typeof row_id != 'undefined') {
					var grid_row = 'row_id_' + row_id;
				} else {
					var grid_row = 'new_row_' + $(this).closest('.grid_row_container').find("tr.grid_row").size();
				}
				
				$last.append('<input type="hidden" class="gallery_id" name="' + $(this).closest('.grid_field_container').attr('id') + '[rows][' + grid_row + '][' + _wrapper.attr("data-field") + '][' + elm_hash_key + ']" value="' + elm_hash_key + '" />');
			}
			
			element_item = $last.parent('.content_elements_tile_item');
			
			element_item.attr('data-el-id', elm_hash_key);
			
			//callback
			
			ContentElements.callback('display', elm_type, $last.parent('.content_elements_tile_item'));
			
			//other buttons
			
			ContentElements.attachRemoveButton();
			ContentElements.attachSortableScript();
		
			//set yellow		
	
			$last.parent('.content_elements_tile_item').css('background-color', '#ffffcf');
		
			$last.parent('.content_elements_tile_item').animate({
				backgroundColor: "#ffffff"
			}, 3000, function(){
				$('.content_elements_tile_item').css('background-color', '#ffffff');
			});						
						
			return false;

		});		
	}
	
	//** -------------------------
	//**	GET RANDOM STRING
	//** -------------------------
	ContentElements.randomString = function()
	{
		var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
		var string_length = 16;
		var randomstring = '';
		for (var i=0; i<string_length; i++) {
			var rnum = Math.floor(Math.random() * chars.length);
			randomstring += chars.substring(rnum,rnum+1);
		}
		return randomstring;
	}
	
	//** -------------------------
	//**	REMOVE BUTTON
	//** -------------------------
	ContentElements.attachRemoveButton = function()
	{
		//bind click function
	
		//$('.content_elements_tile_header .button_remove').unbind('click');
		$('.content_elements_tile_header .button_remove').click(function(){
		
			if ($(this).data('initialized'))
			{
				return false;
			}
			else
			{
				$(this).data('initialized', $(this));
			}			
		
			var buttons = new Object;
			var this_backup = this;
			
			buttons[content_elements_remove_dialog_btn_yes] = function() {
				$(this).dialog("close");
				
				//get element name
				
				$removed_markup = $(this_backup).parent('.content_elements_tile_header').parent('.content_elements_tile_item');				
				element_name = $removed_markup.find('.content_elements_tile_body input:first').val();
				
				//call callback				
				
				ContentElements.callback('remove', element_name, $removed_markup);		
				
				//remove markup
					
				$removed_markup.remove();								
			};
			
			buttons[content_elements_remove_dialog_btn_no] = function() {
				$(this).dialog("close");				
			};
		
			$( '#ce_delete_alert_body' ).dialog({
				resizable: false,
				height:130,
				modal: true,
				buttons: buttons
			});	
	
			return false;
		});		
		
		//hide or show if tile hovered
		
		$('.content_elements_tile_item').unbind('mouseover');
		$(".content_elements_tile_item").mouseover(function(){
			$(this).addClass("hover");
		});
		$('.content_elements_tile_item').unbind('mouseout');	
		$(".content_elements_tile_item").mouseout(function(){
			$(this).removeClass("hover");
		});	
	}	
	
	//** -------------------------
	//**	SORTABLE
	//** -------------------------	 	
	
	ContentElements.attachSortableScript = function()
	{
		$( ".content_elements_tiles" ).sortable({
			start: function(e, ui) {				
		
				$moved_markup = ui.item;				
				element_name = $moved_markup.find('.content_elements_tile_body input:first').val();			
		
				ContentElements.callback('beforeSort', element_name, $moved_markup);
			},
			handle: '.content_elements_draggable_handler',
			axis: "y",
			items: '.content_elements_tile_item',
			tolerance: 'pointer',
			opacity: 0,
			cursor: 'move',
			stop: function(e, ui) {

				$moved_markup = ui.item;				
				element_name = $moved_markup.find('.content_elements_tile_body input:first').val();			
		
				ContentElements.callback('afterSort', element_name, $moved_markup);
			}
		}); 
	}
		 
	//** -------------------------
	//**	BIND
	//** -------------------------	 
		 
	ContentElements.bind = function(element_type, event, callback)
	{
		ContentElementsCallbacks[event][element_type] = callback;		
	};
	
	//** -------------------------
	//**	UnBIND
	//** -------------------------	
	
	ContentElements.unbind = function(element_type, event)
	{
		if (typeof ContentElementsCallbacks[event][element_type] == 'undefined')
		{
			return;
		}
		else
		{
			delete ContentElementsCallbacks[event][element_type];
		}
	};
	
	//** -------------------------
	//**	CALLBACK
	//** -------------------------		
	
	ContentElements.callback = function(event, element_type, param)
	{
		if (typeof ContentElementsCallbacks[event][element_type] == 'undefined')
		{
			return;
		}
		else
		{
			ContentElementsCallbacks[event][element_type].call({}, param);
		}	
	};
	
	ContentElements.attachRemoveButton();
	ContentElements.attachSortableScript();	
	
})(jQuery);

//** --------------------------------
//**	RUN CONTENT ELEMENTS ENGINE
//** --------------------------------

$(document).ready(function()
{
	ContentElements();
});