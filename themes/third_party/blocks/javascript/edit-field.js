jQuery(function($) {

	var blockselectors = $('.blockselectors');

	var updateSelectors = function() {
		var count = 1;
		blockselectors.find('.blockselector').each(function() {
			var selector = $(this);
			var order = selector.find('[js-order]');
			var checkbox = selector.find('[js-checkbox]');

			if (checkbox.is(':checked')) {
				order.val(count);
				count++;
			}
			else {
				order.val(0);
			}
		});
	}

	blockselectors.blocksSortable({
		handle: '.blockselector-handle'
	});
	blockselectors.bind('sortupdate', updateSelectors);
	blockselectors.bind('change', 'js-checkbox', updateSelectors);
});