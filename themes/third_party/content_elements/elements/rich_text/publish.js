function ce_rich_text_init()
{
	//loop not initialized areas
	
	$('textarea.ce_rich_text').not('.WysiHat-field').each(function(k,v)
	{
		// To prevent initialization Wysihat on hidden prototypes
		if ($(v).closest('.content_elements_prototypes').size() == 0)
		{
			$(v).addClass('WysiHat-field');

			if (typeof $(v).wysihat != "undefined")
			{
				$(v).wysihat({
					buttons: ["headings","bold","italic","blockquote","unordered_list","ordered_list","link","image","view_source"]
				});
			}
			else
			{
				if (typeof ce_rich_text_init_270 != "undefined")
				{
					ce_rich_text_init_270();
				}
			}
		}
	});
}

ContentElements.bind('rich_text', 'display', function(data)
{
	if (data.find(".WysiHat-container"))
	{
	
		textarea = data.find('textarea').removeClass('WysiHat-field').show();
		data.find(".ce_rich_editor").append(textarea);		
		data.find(".WysiHat-container").remove();
	}
	ce_rich_text_init();
});

$(window).load(function()
{
	ce_rich_text_init();
});

