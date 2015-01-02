<table cellspacing="0" cellpadding="0" border="0" class="mainTable content_element_settings_table">
	<thead>
		<tr>
			<th width="40%"><?= lang('field_settings') ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td style="padding-top: 10px;">
				<p><?= lang('settings_content_elements_title'); ?></p>

				<div class="ce_settings_toolbar_wrapper">
					<div class="content_element_elements"><?= !empty($current_configuration) ? $current_configuration : ''; ?></div>
					<div class="ce_settings_toolbar">
						<div class="ce_settings_toolbar_headline">
							<span><?= lang('settings_content_elements_description') ?></span>
						</div>			
						<?= form_input("content_element_name", lang('settings_default_content_element_name'), 'class="content_element_name"') ?>
						<?= form_dropdown("content_element_type", !empty($content_elements_type_options) ? $content_elements_type_options : array(), '', 'class="content_element_type"') ?>
						<input type="button" class="submit content_element_add" value="<?= lang('settings_add_content_element'); ?>"/>
						<div style="clear: both"></div>
					</div>
				</div>
			</td>
		</tr>
	</tbody>
</table>

<script type="text/javascript">
	//<![CDATA[

	var ContentElementsSettings;

	(function($){

		//** ---------------------------------------
		//** Bind
		//** ---------------------------------------

		ContentElementsSettings = function(p)
		{
				
			if(typeof p != 'undefined') {
				
				$('.binded').each(function(k,v) {
					$(v).removeClass('binded');
				});
			}
			
			//constructor
		
			$('.content_element_settings_table').each(function(k,v)
			{
				ContentElementsSettings.bindFuctions($(v));
			});
		}

		ContentElementsSettings.bindFuctions = function(settingsTable)
		{
			if (settingsTable.hasClass("binded"))
			{
				return;
			}
			else
			{
				settingsTable.addClass("binded")
			}
	
			//** ---------------------------------
			//** Show & Hide label (focus/blur)
			//** ---------------------------------
		
			settingsTable.find('.content_element_name').focus(function()
			{
				if ($(this).val() == '<?= lang('settings_default_content_element_name') ?>')
				{
					$(this).val('');
				}
			});
		
			settingsTable.find('.content_element_name').blur(function()
			{
				if ($(this).val() == '')
				{
					$(this).val('<?= lang('settings_default_content_element_name') ?>');
				}	
			});					
		
			//** ---------------------------------------
			//** Click on "Add content element"
			//** ---------------------------------------
		
			settingsTable.find(".content_element_add").click(function()
			{
		
				//get element name, type, label & hidden settings elm
			
				var elm_name 		= $.trim(ContentElementsSettings.stripTags(settingsTable.find(".content_element_name").val()));	//e.g. Headline
				var elm_type 		= settingsTable.find(".content_element_type").val();							//e.g. text_input
				var elm_type_label 	= $(".content_element_pattern_"+elm_type).attr("rel"); 		//e.g. Text input
				var elm_settings 	= $(".content_element_pattern_"+elm_type).html();			//html
			
				//validate
			
				if (!elm_name || elm_name == '<?= lang('settings_default_content_element_name') ?>' || elm_name=='false')
				{
					elm_name = elm_type_label;
				}
				
				if (!elm_type || elm_type=='false')
				{
					settingsTable.find(".content_element_type").focus();
					alert('<?= lang('settings_error_element_type_required') ?>');
					return false;
				}	
			
				//set key
			
				elm_hash_key = ContentElementsSettings.randomString();	
			
				if ($(this).closest('table.matrix.matrix-conf').size())
				{	
					$closest_tr = $(this).closest('tr.matrix').children('td');
					$closest_td = $(this).closest('td.matrix');			
				
					col_number 	= $closest_tr.index($closest_td);			
					col_name 	= $(this).closest('table.matrix.matrix-conf').find('tbody tr td').eq(col_number).find('select').attr("name").replace("[type]","[settings]");
				
					elm_settings = elm_settings.replace(/\[__index__\]/g,"[" + elm_hash_key + "]");			
					
					elm_settings = elm_settings.replace("content_element_item[" + elm_hash_key + "]", col_name + "[content_element_item][" + elm_hash_key + "]");
				
					var index_of_match = elm_settings.indexOf("content_element[" + elm_type + "]");				
					while (index_of_match != -1){
						elm_settings = elm_settings.replace("content_element[" + elm_type + "]", col_name + "[content_element][" + elm_type + "]");	
						index_of_match = elm_settings.indexOf("content_element[" + elm_type + "]");	
					}						
				} 
				// Grid
				else if ($(this).closest('#ft_grid').size())
				{
					var col_settings = $(this).closest('.grid_col_settings');
					
					var col_name = col_settings.find('.grid_data_type select').attr('name');
									
					if(typeof col_name == 'undefined')
						return;
							
					col_name = col_name.replace(/\[col_type\]/g, "");
					
					elm_settings = elm_settings.replace(/\[__index__\]/g,"[" + elm_hash_key + "]");
					
					elm_settings = elm_settings.replace("content_element_item[" + elm_hash_key + "]", col_name + "[col_settings][content_element_item][" + elm_hash_key + "]");
						
					var index_of_match = elm_settings.indexOf("content_element[" + elm_type + "]");				
					while (index_of_match != -1){
						elm_settings = elm_settings.replace("content_element[" + elm_type + "]", col_name + "[col_settings][content_element][" + elm_type + "]");	
						index_of_match = elm_settings.indexOf("content_element[" + elm_type + "]");	
					}
				}
				else
				{
					elm_settings = elm_settings.replace(/\[__index__\]/g,"[" + elm_hash_key + "]");			
				}
			
				//append settings body
			
				if (!elm_settings)
				{
					return false;
				}
				
				settingsTable.find(".content_element_elements").append(elm_settings);
			
				//animation
			
				settingsTable.find('.content_element_elements .ce_settings_wrapper:last').css('background-color','#ffffcf');
				settingsTable.find('.content_element_elements .ce_settings_wrapper:last').animate({
					backgroundColor: "#ffffff"
				}, 3000, function() {
					$('.ce_settings_wrapper').css('background-color', '#ffffff');
				});	
			
				//set label of element
			
				settingsTable.find(".content_element_elements .ce_settings_header:last label").html(elm_name);
			
				//set hidden value, for save
			
				settingsTable.find(".content_element_elements .ce_settings_body:last .ce_title").val(elm_name); 
			
				//clean
			
				settingsTable.find(".content_element_name").val('');
				settingsTable.find(".content_element_type option").removeAttr('selected');	
			
				ContentElementsSettings.removeButton();
				ContentElementsSettings.optionsTrigger();
				ContentElementsSettings.hoverTrigger();	
			});
		
			//** ---------------------------------------
			//** Sortable
			//** ---------------------------------------		
		
			$(document).ready(function(){		
				settingsTable.find(".content_element_elements").sortable({
					handle: '.content_elements_draggable_handler',
					helper: function(e, ui) {			
						return ui;
					},
					axis: "y",
					items: '.ce_settings_wrapper',
					tolerance: 'pointer',
					opacity: 0,
					cursor: 'move',
					stop: function(e, ui) {
						//restore all ck_editors data in the tile	
						$('.ce_settings_wrapper').removeAttr('style');
					}
				}); 
			});	
		
<?php
// If validation failed?
if (!isset($_GET['field_id'])) {
	?>
																						
					$(document).ready(function(){	
							
						content_element_elements = settingsTable.find(".content_element_elements").html();
							
						if(content_element_elements.length == 0) {
							settingsTable.find(".content_element_type option").each(function(k,v){
								if ($(v).val())
								{
									var elm_type = $(v).val();
																							
									//e.g. text_input
									var elm_type_label 	= $(".content_element_pattern_"+elm_type).attr("rel"); 		//e.g. Text input					
									var elm_settings 	= $(".content_element_pattern_"+elm_type).html();			//html	
																									
									var elm_hash_key = ContentElementsSettings.randomString();	
																									
									if ($(this).closest('table.matrix.matrix-conf').size())
									{	
										$closest_tr = $(this).closest('tr.matrix').children('td');
										$closest_td = $(this).closest('td.matrix');			
																										
										col_number 	= $closest_tr.index($closest_td);			
										col_name 	= $(this).closest('table.matrix.matrix-conf').find('tbody tr td').eq(col_number).find('select').attr("name").replace("[type]","[settings]");
																										
										elm_settings = elm_settings.replace(/\[__index__\]/g,"[" + elm_hash_key + "]");			
																											
										elm_settings = elm_settings.replace("content_element_item[" + elm_hash_key + "]", col_name + "[content_element_item][" + elm_hash_key + "]");
																										
										var index_of_match = elm_settings.indexOf("content_element[" + elm_type + "]");				
										while (index_of_match != -1){
											elm_settings = elm_settings.replace("content_element[" + elm_type + "]", col_name + "[content_element][" + elm_type + "]");	
											index_of_match = elm_settings.indexOf("content_element[" + elm_type + "]");	
										}						
									} else if ($(this).closest('#ft_grid').size())
									{
										var col_settings = $(this).closest('.grid_col_settings');
										var col_name = col_settings.find('.grid_data_type select').attr('name');
														
										if(typeof col_name == 'undefined')
											return;
												
										col_name = col_name.replace(/\[col_type\]/g, "");
												
										elm_settings = elm_settings.replace(/\[__index__\]/g,"[" + elm_hash_key + "]");
												
										if(typeof col_name != 'undefined') {
											elm_settings = elm_settings.replace("content_element_item[" + elm_hash_key + "]", col_name + "[col_settings][content_element_item][" + elm_hash_key + "]");
													
											var index_of_match = elm_settings.indexOf("content_element[" + elm_type + "]");				
											while (index_of_match != -1){
												elm_settings = elm_settings.replace("content_element[" + elm_type + "]", col_name + "[col_settings][content_element][" + elm_type + "]");	
												index_of_match = elm_settings.indexOf("content_element[" + elm_type + "]");	
											}
										}
									}
									else
									{
										elm_settings = elm_settings.replace(/\[__index__\]/g,"[" + elm_hash_key + "]");			
									}					
																															
									var elm_name 		= elm_type_label;	//e.g. Headline
																									
									//append settings body
												
									content_element_elements = settingsTable.find(".content_element_elements");
									//console.log(content_element_elements.html());
											
									content_element_elements.append(elm_settings);
																									
									//set label of element
																									
									settingsTable.find(".content_element_elements .ce_settings_header:last label").html(elm_name);
																									
									//set hidden value, for save
																									
									settingsTable.find(".content_element_elements .ce_settings_body:last .ce_title").val(elm_name); 
											
								}		
							});
																							
							$(".content_element_name").val('');
							$(".content_element_type option").removeAttr('selected');
						}
					});	

<?php } ?>
		
			//** ---------------------------------------
			//** Set triggers
			//** ---------------------------------------	
		
			$(document).ready(function(){		
				ContentElementsSettings.removeButton();
				ContentElementsSettings.optionsTrigger();
				ContentElementsSettings.hoverTrigger();			
			});
		}

		//** ---------------------------------------
		//** Settings - hash generator
		//** ---------------------------------------

		ContentElementsSettings.randomString = function()
		{
			var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
			var string_length = 16;
			var randomstring = '';
			for (var i=0; i<string_length; i++) {
				var rnum = Math.floor(Math.random() * chars.length);
				randomstring += chars.substring(rnum,rnum+1);
			}
			return randomstring;
		}
	
		//** ---------------------------------------
		//** Settings - options
		//** ---------------------------------------	
	
		ContentElementsSettings.optionsTrigger = function(){
	
			$('.ce_element_options_on').unbind('click');
			$('.ce_element_options_on').click(function(){
		
				$(this).hide();
				$(this).parent().find('.ce_element_options_off').show();
				$(this).parent().find('.ce_element_options').slideDown();
				return false;
			});
		
			$('.ce_element_options_off').unbind('click');	
			$('.ce_element_options_off').click(function(){
		
				$(this).hide();
				$(this).parent().find('.ce_element_options_on').show();
				$(this).parent().find('.ce_element_options').slideUp();
				return false;
			});
		}	
	
		//** ------------------------
		//** Strip tags
		//** ------------------------
	
		ContentElementsSettings.stripTags = function(input)
		{
			if (input) {
				var tags = '/(]+)>)/ig';
				input = input.replace(tags,'');
				return input;
			}
			return false;
		}		

		//** ------------------------
		//** Hover trigger
		//** ------------------------
	
		ContentElementsSettings.hoverTrigger = function()
		{
			$('.ce_settings_wrapper').unbind('mouseover');
			$('.ce_settings_wrapper').unbind('mouseout');	
			$('.ce_settings_wrapper').mouseover(function(){$(this).addClass('hover')});
			$('.ce_settings_wrapper').mouseout(function(){$(this).removeClass('hover')});
		}
		
		//** ---------------------------------------
		//** Bind "Remove button"
		//** ---------------------------------------
	
		ContentElementsSettings.removeButton = function()
		{
			$('.ce_settings_wrapper .ce_settings_header .button_remove').unbind('click');
			$('.ce_settings_wrapper .ce_settings_header .button_remove').click(function(){
		
				var buttons = new Object;
				var this_backup = this;
			
				buttons['<?= lang('content_elements_remove_setting_dialog_btn_yes') ?>'] = function() {
					$(this).dialog("close");
					$(this_backup).parent('.ce_settings_header').parent('.ce_settings_wrapper').remove();			
				};
				buttons['<?= lang('content_elements_remove_setting_dialog_btn_no') ?>'] = function() {
					$(this).dialog("close");				
				};
			
				$('<div title="<?= lang('content_elements_remove_setting_dialog_head') ?>"><div class="ui-dialog-content ui-widget-content" style="color: red"><?= lang('content_elements_remove_setting_dialog_body') ?></div></div></div>').dialog({
					resizable: false,
					height:130,
					modal: true,
					buttons: buttons
				});	
		
				return false;	
			});
		}	

	})(jQuery);

	$(document).ready(function(){
		ContentElementsSettings();
	});
	
	//]]>
</script>
