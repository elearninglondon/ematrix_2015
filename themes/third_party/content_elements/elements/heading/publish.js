ContentElements.bind('heading', 'display', function(data)
{
	elm_hash_key = data.data('el-id');
	
	if(typeof elm_hash_key == 'undefined')
		elm_hash_key = ContentElements.randomString();
	
	data.html(data.html().replace(/\__element_heading_index__/g, elm_hash_key));
	data.find('input[type="text"]:first').focus();
});





