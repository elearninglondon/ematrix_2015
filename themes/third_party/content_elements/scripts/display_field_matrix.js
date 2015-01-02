$(document).ready(function() {
	
	Matrix.bind('content_elements', 'display', function(cell)
	{
		ContentElements.displayField(cell.dom.$td.find('.content_elements_wrapper'));
	});
	
});	