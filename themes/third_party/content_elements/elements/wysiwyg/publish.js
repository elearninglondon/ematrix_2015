var ck_during_sortable_data_items;

//---------------------------
//	CE
//---------------------------

ContentElements.bind('wysiwyg', 'display', function(data)
{
	elm_hash_key = data.data('el-id');
	
	if(typeof elm_hash_key == 'undefined')
		elm_hash_key = ContentElements.randomString();
	
	data.find('textarea').attr("id", elm_hash_key);
		
	CKEDITOR.replace(elm_hash_key, {  
		skin: data.find('div[rel=skin]').html(),
		toolbar: eval( data.find('div[rel=toolbar]').html() ),		
		toolbarCanCollapse: false,
		height: data.find('div[rel=height]').html(),
		resize_enabled: false,
		removePlugins: 'elementspath',
	    on :
	    {
	        instanceReady : function( ev )
	        {
	            this.focus();
	        },
	        focus : function( ev )
	        {
	            $('#' + elm_hash_key).parent().find('iframe').css('border','1px solid #000000');
	        }, 
	        blur : function( ev )
	        {
	            $('#' + elm_hash_key).parent().find('iframe').css('border','1px solid #D1D5DE');
	        }  
	    }
	});
});

ContentElements.bind('wysiwyg', 'beforeSort', function(data)
{
	ck_editor_id = data.find('textarea').attr("id");
	ck_during_sortable_data_items = CKEDITOR.instances[ck_editor_id].getData();
});

ContentElements.bind('wysiwyg', 'afterSort', function(data)
{
	ck_editor_id = data.find('textarea').attr("id");
	CKEDITOR.instances[ck_editor_id].setData(ck_during_sortable_data_items);
});

ContentElements.bind('wysiwyg', 'remove', function(data)
{
	ck_editor_id = data.find('textarea').attr("id");
	CKEDITOR.instances[ck_editor_id].destroy();
});

//---------------------------
//	default
//---------------------------

$('.ce_wysiwyg').each(function(k,v){

	if ($('.ce_wysiwyg').closest('.content_elements_prototypes').size() == 0)
	{
		CKEDITOR.replace($(v).attr("id"), {  
			skin: $(v).siblings('div[rel=skin]').html(),
			toolbar: eval( $(v).siblings('div[rel=toolbar]').html() ),		
			toolbarCanCollapse: false,
			height: $(v).siblings('div[rel=height]').html(),
			resize_enabled: false,
			removePlugins: 'elementspath',
		    on :
		    {
		        focus : function( ev )
		        {
		            $('#' + $(v).attr("id")).parent().find('iframe').css('border','1px solid #000000');
		        }, 
		        blur : function( ev )
		        {
		            $('#' + $(v).attr("id")).parent().find('iframe').css('border','1px solid #D1D5DE');
		        } 
		    }
		});
	}
});
