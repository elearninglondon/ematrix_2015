<?=form_open($postUrl, '', $hiddenValues)?>
	<table class="mainTable padTable" cellspacing="0" cellpadding="0" border="0">
	<thead>
		<tr>
			<th colspan="2">
				<?=lang('blocks_blockdefinition_settings')?>
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<?=form_label(lang('blocks_blockdefinition_name'), 'blockdefinition_name')?><br /><?=lang('blocks_blockdefinition_name_info')?>
				<?=form_error('blockdefinition_name')?>
			</td>
			<td>
				<?=form_input(
					array(
						'id'	=> 'blockdefinition_name',
						'name'	=> 'blockdefinition_name',
						'class'	=> 'fullfield',
						'value'	=> set_value('blockdefinition_name', $blockDefinition->name)
					)
				)?>
			</td>
		</tr>
		<tr>
			<td>
				<?=form_label(lang('blocks_blockdefinition_shortname'), 'blockdefinition_shortname')?><br /><?=lang('blocks_blockdefinition_shortname_info')?>
				<?=form_error('blockdefinition_shortname')?>
			</td>
			<td>
				<?=form_input(
					array(
						'id'	=> 'blockdefinition_shortname',
						'name'	=> 'blockdefinition_shortname',
						'class'	=> 'fullfield',
						'value'	=> set_value('blockdefinition_shortname', $blockDefinition->shortname)
					)
				)?>
			</td>
		</tr>
		<tr>
			<td>
				<?=form_label(lang('blocks_blockdefinition_instructions'), 'blockdefinition_instructions')?><br /><?=lang('blocks_blockdefinition_instructions_info')?>
			</td>
			<td>
				<?=form_textarea(array('id'=>'blockdefinition_instructions','name'=>'blockdefinition_instructions','class'=>'fullfield','value'=>$blockDefinition->instructions))?>
			</td>
		</tr>
	</tbody>
	</table>

	<?php if ($errors): ?>
	<p class="notice"><?= $errors ?></p>
	<?php endif; ?>

	<?= $atomDefinitionsView ?>

	<p><?=form_submit('field_edit_submit', lang('blocks_blockdefinition_submit'), 'class="submit"')?></p>

<?=form_close()?>
