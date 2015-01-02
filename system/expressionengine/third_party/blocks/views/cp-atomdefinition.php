<div class="grid_col_settings" data-field-name="<?=$field_name?>">
	<div class="grid_col_settings_section grid_data_type alt">
		<?=form_dropdown(
			'grid[cols]['.$field_name.'][col_type]',
			$fieldtypes,
			isset($atomDefinition->type) ? $atomDefinition->type : 'text',
			'class="grid_col_select"')?>
		<a href="#" class="grid_button_delete" title="<?=lang('grid_delete_column')?>"><?=lang('grid_delete_column')?></a>
	</div>
	<div class="grid_col_settings_section text">
		<?=form_input('grid[cols]['.$field_name.'][col_label]', isset($atomDefinition->name) ? $atomDefinition->name : '', ' class="grid_col_field_label"')?>
	</div>
	<div class="grid_col_settings_section text alt">
		<?=form_input('grid[cols]['.$field_name.'][col_name]', isset($atomDefinition->shortname) ? $atomDefinition->shortname : '', ' class="grid_col_field_name"')?>
	</div>
	<div class="grid_col_settings_section text">
		<?=form_input('grid[cols]['.$field_name.'][col_instructions]', isset($atomDefinition->instructions) ? $atomDefinition->instructions : '')?>
	</div>
	<div class="grid_col_settings_section grid_data_search alt">
		<label><?=form_checkbox('grid[cols]['.$field_name.'][col_required]', 'y', isset($atomDefinition->settings['col_required']) && $atomDefinition->settings['col_required'] == 'y')?><?=lang('blocks_blockdefinition_atomdefinition_extra_required')?></label>
	</div>
	<div class="grid_col_settings_custom" data-field-name="<?=$field_name?>">
		<?php if (isset($settingsForm)): ?>
			<?=$settingsForm?>
		<?php endif ?>
	</div>
	<div class="grid_col_settings_section grid_col_copy">
		<a href="#" class="grid_col_copy"><?=lang('grid_copy_column')?></a>
	</div>
</div>
