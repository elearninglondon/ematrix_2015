ContentElements.bind('table', 'display', function(data)
{
	elm_hash_key = data.data('el-id');
	
	if(typeof elm_hash_key == 'undefined')
		elm_hash_key = ContentElements.randomString();
	
	data.html(data.html().replace(/\__element_table_index__/g, elm_hash_key));
	data.find('input[type="text"]:first').focus();
	
	ceTable.apply_table_refresh_row_numbers();
	ceTable.apply_table_sortable_script();
	ceTable.apply_buttons(data);	
});


