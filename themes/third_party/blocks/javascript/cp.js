;jQuery(function($) {

$('.blocksft').each(function() {

	var blocksft = $(this);
	var blocks = blocksft.find('.blocksft-blocks');

	var newBlockCount = 1;
	blocksft.find('.blocksft-new').on('click', 'button', function(e) {
		e.preventDefault();
		var newbutton = $(this);
		var templateid = newbutton.attr('data-template');
		createBlock(templateid);
	});

	fireEvent("display", blocks.find('[data-fieldtype]'));

	// If this is a new entry, then auto-create the first block type.
	if ($('#publishForm input[name="entry_id"]').val() === '0') {
		var firstButton = blocksft.find('.blocksft-new [data-template]').first();
		if (firstButton.length) {
			var templateid = firstButton.attr('data-template');
			createBlock(templateid);
		}
	}

	function createBlock(templateid) {
		var template = blocksft.find('#' + templateid).find('.blocksft-block');
		var clone = template.clone();

		// TODO: Disable/enable all inputs?
		clone.html(clone.html().replace(/\[blocks_new_row_\d+\]/g, '[blocks_new_row_' + newBlockCount + ']'));

		blocks.append(clone);
		fireEvent("display", clone.find('[data-fieldtype]'));
		reorderFields();
		newBlockCount++;
		blocks.blocksSortable('reload');
	}

	// Set the order value for all of the fields.
	function reorderFields() {
		var order = 1;
		blocksft.find('[data-order-field]').each(function() {
			$(this).val(order);
			order++;
		});
	}

	blocks.blocksSortable({
		handle: '.blocksft-block-handle',
		forcePlaceholderSize: true
	});
	blocks.on('sortstart', function(e, ui) {
		var block = $(ui.item);
		block.find('[data-fieldtype]').each(function() {
			fireEvent('beforeSort', $(this));
		});
	});
	blocks.on('sortupdate', reorderFields);
	blocks.on('sortend', function(e, ui) {
		var block = $(ui.item);
		block.find('[data-fieldtype]').each(function() {
			fireEvent('afterSort', $(this));
		});
	});

	// Punt on a fancy re-orderer. We can figure that out later.
	blocks.on('click', 'button.move.up, button.move.down', function(e) {
		e.preventDefault();
		var button = $(this);
		var up = button.is('.up');
		var block = button.closest('.blocksft-block');

		if (up) {
			var prev = block.prev('.blocksft-block');
			if (prev.length) {
				block.find('[data-fieldtype]').each(function() {
					fireEvent('beforeSort', $(this));
				});
				prev.before(block);
				block.find('[data-fieldtype]').each(function() {
					fireEvent('afterSort', $(this));
				});
				reorderFields();
			}
		}
		else {
			var next = block.next('.blocksft-block');
			if (next.length) {
				block.find('[data-fieldtype]').each(function() {
					fireEvent('beforeSort', $(this));
				});
				next.after(block);
				block.find('[data-fieldtype]').each(function() {
					fireEvent('afterSort', $(this));
				});
				reorderFields();
			}
		}
	});

	blocks.on('click', 'button.remove', function(e) {
		e.preventDefault();
		var button = $(this);
		var block = button.closest('.blocksft-block');

		var deletedInput = block.find('[data-deleted-field]');
		if (deletedInput.length) {
			deletedInput.val('true');
			block.addClass('deleted');
			block.find('[data-order-field]').remove();
		}
		else {
			block.remove();
		}
		reorderFields();
	});
});

function fireEvent(eventName, fields) {
	fields.each(function() {
		// Some field types require this.
		window.Grid.Settings.prototype._fireEvent(eventName, $(this));
	});
}

// On occassion, Blocks will load before another field type within a
// block, and so Grid.bind will not have been called yet. So, we need to
// intercept those and initialize them as well. I'm not convinced this is
// the best way to do this, so it may need to be refined in the future.
var g = Grid;
var b = g.bind;
g.bind = function(fieldType, eventName, callback) {
	b.apply(g, [fieldType, eventName, callback]);
	if (eventName === "display") {
		fireEvent("display", $('.blocksft .blocksft-blocks [data-fieldtype="' + fieldType + '"]'));
	}
};

});
