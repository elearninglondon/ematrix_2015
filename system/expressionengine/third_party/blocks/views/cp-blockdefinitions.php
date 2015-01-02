<?php

$this->table->set_template($cp_table_template);
$this->table->set_heading(
	lang('blocks_blockdefinitions_name'),
	lang('blocks_blockdefinitions_shortname'),
	lang('blocks_blockdefinitions_edit'),
	lang('blocks_blockdefinitions_delete'));

foreach ($blockDefinitions as $blockDefinition)
{
	$this->table->add_row(
		$blockDefinition->name,
		$blockDefinition->shortname,
		'<a href="'.BASE.AMP.$base.AMP.'method=blockdefinition'.AMP.'blockdefinition='.$blockDefinition->id.'">'. lang('blocks_blockdefinitions_edit') .'</a>',
		'<a href="'.BASE.AMP.$base.AMP.'method=confirmdelete'.AMP.'blockdefinition='.$blockDefinition->id.'">'. lang('blocks_blockdefinitions_delete') .'</a>');
}

echo $this->table->generate();

?>

<p>
	<a class="submit" href="<?php echo BASE.AMP.$base.AMP.'method=blockdefinition'.AMP.'blockdefinition=new' ?>"><?php echo lang('blocks_blockdefinitions_add') ?></a>
</p>
