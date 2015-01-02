//live

function ce_gallery_live()
{
	//add trigger to add button
	
	$('form .ce_gallery_item_wrapper .ce_gallery_btn_add').not('.triggered').each(function(k,v){
	
		$(v).mousedown(function(){	
		
			if (ce_add_file_trigger_version == 2)
			{
				$.ee_filebrowser.add_trigger($(v), 'upload_file', {content_type: 'images', directory: 'all'}, function(a){
					
					
					
					if (a.is_image)
					{
						gallery_id = $(this).parents('.ce_gallery_item_wrapper').attr("id");
						
						//fetch title
						
						if (typeof(a.name) !== 'undefined'){file_name = a.name;}
						if (typeof(a.file_name) !== 'undefined'){file_name = a.file_name;}
					
						//load placeholder
						
						placeholder_html = $("#placeholder_" + gallery_id).html();			
						placeholder = $(placeholder_html);
						
						placeholder.find('input').each(function(pk,pv){
							$(pv).attr("name",$(pv).attr("name").split('placeholder_')[1]);
						});
						
						placeholder.find('input.ce_gallery_dir').val(a.upload_location_id);
						placeholder.find('input.ce_gallery_name').val(file_name);				
						
						placeholder.find('.ce_gallery_img').css("background-image","url(" + a.thumb + ")");
						
						//insert						
						
						$("#" + gallery_id + " .ce_gallery_btn_add").parent().before(placeholder);
						
						//set focus
						
						$("#" + gallery_id + " .ce_gallery_btn_add").parent().parent().find('input[type=text]:last').focus();
						
						ce_gallery_live();
					}
					else
					{
						alert(ce_msg_1);
						return false;
					}
				});
			}
			else
			{
				$.ee_filebrowser.add_trigger($(v), 'upload_file', function(a){
				
					if (a.is_image)
					{
						
						gallery_id = $(this).parents('.ce_gallery_item_wrapper').attr("id");
						
						//fetch title
						
						if (typeof(a.name) !== 'undefined'){file_name = a.name;}
						if (typeof(a.file_name) !== 'undefined'){file_name = a.file_name;}				
					
						//load placeholder
						
						placeholder_html = $("#placeholder_" + gallery_id).html();			
						placeholder = $(placeholder_html);
						
						placeholder.find('input').each(function(pk,pv){
							$(pv).attr("name",$(pv).attr("name").split('placeholder_')[1]);
						});
						
						placeholder.find('input.ce_gallery_dir').val(a.directory);
						placeholder.find('input.ce_gallery_name').val(file_name);				
						
						placeholder.find('.ce_gallery_img').css("background-image","url(" + a.thumb + ")");
						//placeholder.find('.ce_gallery_img').css("background-repeat","no-repeat");	
						//placeholder.find('.ce_gallery_img').css("background-position","center");								
						
						$("#" + gallery_id + " .ce_gallery_btn_add").parent().before(placeholder);
						ce_gallery_live();
					}	
					else
					{
						alert(ce_msg_1);
						return false;
					}				
				});
			}	
			
	
		});

		$(v).addClass('triggered');
		$(v).click(function() { return false; });
	});	
		
	$('.ce_gallery_item_wrapper .ce_gallery_img_remove').not('.triggered').each(function(k,v){
		$(v).click(function() { 
			$(v).parent().parent().parent('.ce_gallery_item').remove();
			ce_gallery_live();
			return false;
		});
		$(v).addClass('triggered');
	});	
	
	//show - hide add button
	
	$('.ce_gallery_item_wrapper').each(function(k,v){
		var max_photos = parseInt($(v).find('.max_photos').text());		
		if ($(v).find('.ce_gallery_item').size() >= max_photos)
		{
			$(v).find('.ce_gallery_item_btn_add_wrapper').addClass('js_hide');	
		}
		else
		{
			$(v).find('.ce_gallery_item_btn_add_wrapper').removeClass('js_hide');	
		}
	});

	ca_gallery_sortable_script();	
}


function ca_gallery_sortable_script()
{
	$( ".ce_gallery_item_wrapper" ).sortable({
		handle: '.ce_gallery_img',
		cursor: 'move',
		items: '.ce_gallery_item',
		stop: function() {}
	}); 
}

ce_gallery_live();