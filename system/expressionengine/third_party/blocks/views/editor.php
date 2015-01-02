<!-- -->
<div id="field_id_<?php echo $fieldid; ?>" class="blocksft grid_field_container">
	<div class="blocksft-blocks">
<?php foreach ($blocks as $blockdata): ?>
		<div class="blocksft-block<?php if ($blockdata['block']->deleted == 'true'): ?> deleted<?php endif; ?>" data-blocktype="<?php echo $blockdata['block']->definition->shortname; ?>">
			<input type="hidden" name="<?php echo $blockdata['fieldnames']->id ?>" value="<?php echo $blockdata['block']->id; ?>">
			<input type="hidden" name="<?php echo $blockdata['fieldnames']->definitionId ?>" value="<?php echo $blockdata['block']->definition->id; ?>">
			<input type="hidden" data-order-field name="<?php echo $blockdata['fieldnames']->order ?>" value="<?php echo $blockdata['block']->order; ?>">
			<input type="hidden" data-deleted-field name="<?php echo $blockdata['fieldnames']->deleted ?>" value="<?php echo $blockdata['block']->deleted; ?>">
			<span class="blocksft-block-handle">::</span>
			<div class="blocksft-actions">
				<button type="button" class="remove" title="Remove">Remove</button>
			</div>
			<div class="blocksft-header">
				<h3><?php echo $blockdata['block']->definition->name; ?></h3>
				<?php if (!is_null($blockdata['block']->definition->instructions) && $blockdata['block']->definition->instructions != ''): ?>
				<div class="blocksft-instructions"><?php echo $blockdata['block']->definition->instructions; ?></div>
				<?php endif; ?>
			</div>
			<div class="blocksft-atoms">
<?php foreach ($blockdata['controls'] as $control): ?>
				<div class="blocksft-atom grid_field" data-fieldtype="<?php echo $control['atom']->definition->type; ?>" data-column-id="blocks_<?php echo $control['atom']->definition->id ;?>" data-row-id="<?php echo $blockdata['block']->id ;?>">
					<label class="blocksft-atom-name"><?php echo $control['atom']->definition->name; ?></label>
					<?php if (!is_null($control['atom']->definition->instructions) && $control['atom']->definition->instructions != ''): ?>
					<label class="blocksft-atom-instructions"><?php echo $control['atom']->definition->instructions; ?></label>
					<?php endif; ?>
					<div class="blocksft-atomcontainer">
						<?php echo $control['html']; ?>
					</div>
					<?php if (isset($control['atom']->error)): ?>
					<div class="blocksft-atom-error">
						<p class="notice"><?php echo $control['atom']->error; ?></p>
					</div>
					<?php endif; ?>
				</div>
<?php endforeach; ?>
			</div>
		</div>
<?php endforeach; ?>
	</div>
	<div class="blocksft-new">
		Add:
<?php foreach ($blockdefinitions as $blockdefinition): ?>
			<button type="button" data-template="<?php echo $blockdefinition['templateid']; ?>"><?php echo $blockdefinition['name']; ?></button>
<?php endforeach; ?>
	</div>
<?php foreach ($blockdefinitions as $blockdefinition): ?>
	<div id="<?php echo $blockdefinition['templateid']; ?>" style="display:none;">
		<div class="blocksft-block" data-blocktype="<?php echo $blockdefinition['shortname']; ?>">
			<input type="hidden" name="<?php echo $blockdefinition['fieldnames']->blockdefinitionid; ?>" value="<?php echo $blockdefinition['blockdefinitionid']; ?>">
			<input type="hidden" data-order-field name="<?php echo $blockdefinition['fieldnames']->order ?>" value="0">
			<span class="blocksft-block-handle">::</span>
			<div class="blocksft-actions">
				<button type="button" class="remove" title="Remove">Remove</button>
			</div>
			<div class="blocksft-header">
				<h3><?php echo $blockdefinition['name']; ?></h3>
				<?php if (!is_null($blockdefinition['instructions']) && $blockdefinition['instructions'] != ''): ?>
				<div class="blocksft-instructions"><?php echo $blockdefinition['instructions']; ?></div>
			<?php endif; ?>
			</div>
			<div class="blocksft-atoms">
<?php foreach ($blockdefinition['controls'] as $control): ?>
				<div class="blocksft-atom grid_field" data-fieldtype="<?php echo $control['atom']->type; ?>" data-column-id="blocks_<?php echo $control['atom']->id ;?>">
					<label class="blocksft-atom-name"><?php echo $control['atom']->name; ?></label>
					<?php if (!is_null($control['atom']->instructions) && $control['atom']->instructions != ''): ?>
					<label class="blocksft-atom-instructions"><?php echo $control['atom']->instructions; ?></label>
					<?php endif; ?>
					<div class="blocksft-atomcontainer">
						<?php echo $control['html']; ?>
					</div>
				</div>
<?php endforeach; ?>
			</div>
		</div>
	</div>
<?php endforeach; ?>
</div>
