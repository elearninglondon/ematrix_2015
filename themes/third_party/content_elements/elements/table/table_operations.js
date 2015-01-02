var ceTable = [];

ceTable.apply_table_draggable_handlers = function()
{
	$('table.element_table tbody').each(function(k1,v1)
	{	
		$(v1).find('tr').find('.ce_table_draggable').remove();	
		$(v1).find('tr').find('td:last').each(function(k2,v2)
		{
			$(v2).find('.element_table_td_wrapper').append('<span class="ce_table_draggable"></span>');	
		});		
	});		
}

ceTable.apply_table_sortable_script = function()
{
	ceTable.apply_table_draggable_handlers();
	ceTable.apply_table_active_buttons(); 
	
	$( "table.element_table tbody" ).sortable({
		helper: function(e, ui) {
			ui.children().each(function() {
				$(this).width($(this).width());
			});
			return ui;
		},
		handle: '.ce_table_draggable',
		axis: "y",
		items: 'tr',
		opacity: 0,
		cursor: 'move',
		stop: function() { 
			$( "table.element_table tr td" ).not('.line_number').css("width","");
			ceTable.apply_table_refresh_row_numbers();
		}
	}); 		
}

ceTable.apply_table_refresh_row_numbers = function()
{
	//numbers ordering 1. 2. 3.
	
	$('table.element_table tbody').each(function(k1,v1)
	{
		$(v1).find('td.line_number').each(function(k2,v2)
		{
			$(v2).html((parseInt(k2)+1)+'.');	
		});	
	});
}	

ceTable.apply_table_active_buttons = function()
{
	$('table.element_table').each(function(k,v)
	{	
		var table_id 	= $(v).attr("id");
		var field_id	= $(v).attr("rel");	
		
		var rows = $('#'+table_id + ' tbody').find("tr").length;
		var cols = $('#'+table_id).find("tr:first td").length - 1;
		
		if (rows <= 1)
		{
			$(this).parent().parent().parent().parent().parent().find('.element_table_remove_row').addClass('btn_disabled');
			$(this).parent().parent().parent().parent().parent().find('.element_table_remove_row').css('opacity', 0.5);
		}
		else
		{
			$(this).parent().parent().parent().parent().parent().find('.element_table_remove_row').removeClass('btn_disabled');		
			$(this).parent().parent().parent().parent().parent().find('.element_table_remove_row').css('opacity', 1);
		}
		if (cols <= 1)
		{
			$(this).parent().parent().find('.element_table_remove_col').addClass('btn_disabled');		
			$(this).parent().parent().find('.element_table_remove_col').css('opacity', 0.5);
		}
		else
		{
			$(this).parent().parent().find('.element_table_remove_col').removeClass('btn_disabled');
			$(this).parent().parent().find('.element_table_remove_col').css('opacity', 1);
		}		
	});	
}

ceTable.apply_buttons = function(data)
{
	data.find('.element_table_add_row').click(function(){
		var rel		= $(this).attr("rel");
		var table_id 	= $('#' + rel).attr("id");
		var field_name = $('#' + table_id + '_eid').attr('name').replace('[eid]', '[cell][]');
	
		var new_cell	= '<td><div class="element_table_td_wrapper"><input class="element_box" type="text" value="" name="'+field_name+'" /></div></td>';
		var rows = $('#'+table_id + ' tbody').find("tr").length;
		var cols = $('#'+table_id).find("tr:first td").length - 1;
			
		var row = '<tr><td class="line_number"></td>';
		for (i=1; i<=cols; i++)
		{
			row = row + new_cell;
		}
		row = row + '</tr>';	
		
		$('#'+table_id).append(row);
		$('#'+table_id+'_rows').val(parseInt(rows)+1);
		
		ceTable.apply_table_refresh_row_numbers();
		ceTable.apply_table_draggable_handlers();	
		ceTable.apply_table_active_buttons();
		
		return false;
	});
	
	data.find('.element_table_add_col').click(function(){	
		var rel			= $(this).attr("rel");
		var table_id 	= $('#' + rel).attr("id");
		var field_name = $('#' + table_id + '_eid').attr('name').replace('[eid]', '[cell][]');
	
		var new_cell	= '<td><div class="element_table_td_wrapper"><input class="element_box" type="text" value="" name="'+field_name+'" /></div></td>';
		
		var rows = $('#'+table_id + ' tbody').find("tr").length;
		var cols = $('#'+table_id).find("tr:first td").length - 1;
		
		$('#'+table_id+' tr').each(function(k,v){
			$(v).append(new_cell);
		});
		
		$('#'+table_id+'_cols').val(parseInt(cols)+1);
		
		ceTable.apply_table_refresh_row_numbers();	
		ceTable.apply_table_draggable_handlers();
		ceTable.apply_table_active_buttons();		
		return false;
	});
	
	data.find('.element_table_remove_row').click(function(){		

		if ($(this).hasClass('btn_disabled')) return false;
	
		var rel			= $(this).attr("rel");
		var table_id 	= $('#' + rel).attr("id");	
	
		var rows = $('#'+table_id + ' tbody').find("tr").length;
		var cols = $('#'+table_id).find("tr:first td").length - 1;
	
		$(this).parent().parent().find('table.element_table tbody tr:last').remove();
		
		$('#'+table_id+'_rows').val(parseInt(rows)-1);
		
		ceTable.apply_table_draggable_handlers();		
		ceTable.apply_table_active_buttons();		
		return false;
	});
	
	data.find('.element_table_remove_col').click(function(){	

		if ($(this).hasClass('btn_disabled')) return false;	
	
		var rel			= $(this).attr("rel");
		var table_id 	= $('#' + rel).attr("id");
		
		var rows = $('#'+table_id + ' tbody').find("tr").length;
		var cols = $('#'+table_id).find("tr:first td").length - 1;
		
		$('#'+table_id+' tr').each(function(k,v){
			$(v).find('td:last').remove();
		});
		
		$('#'+table_id+'_cols').val(parseInt(cols)-1);
		
		ceTable.apply_table_draggable_handlers();		
		ceTable.apply_table_active_buttons();		
		return false;
	});	
}

$(document).ready(function()
{
	ceTable.apply_table_refresh_row_numbers();
	ceTable.apply_table_sortable_script();	
	ceTable.apply_buttons($('body'));
});