ContentElements.bind('gallery', 'display', function(data)
{
	elm_hash_key = data.data('el-id');
	
	if(typeof elm_hash_key == 'undefined')
		elm_hash_key = ContentElements.randomString();
	
	data.html(data.html().replace(/\__element_gallery_index__/g, elm_hash_key));
	ce_gallery_live(); 
});