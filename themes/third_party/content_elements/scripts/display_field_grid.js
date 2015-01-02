$(document).ready(function(){
	
	Grid.bind('content_elements', 'display', function(cell){
		
		var wrapper = cell.find('.content_elements_wrapper');
		
		ContentElements.displayField(wrapper);
	});
	
	Grid.bind('content_elements', 'displaySettings', function(cell){
		ContentElementsSettings.bindFuctions(cell);
	});
	
});