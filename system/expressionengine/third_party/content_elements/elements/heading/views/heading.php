<input type="hidden" name="<?= $field_name ?>" value="<?= $heading_id ?>">
<table cellpadding="0" cellspacing="0" width="100%">
<tr>
	<td width="180px">
		<select class="ce_heading_dropdown" name="<?=$field_name?>[heading]" >
			<?php foreach ($headings as $id=>$heading_name): ?>
			<option class="<?= $id ?>" value="<?= $id ?>" <?= ($id==$heading)?'selected="selected"':'' ?>><?= $heading_name ?></option>
			<?php endforeach; ?>
		</select>
	</td>
	<td>	
		<input type="text" class="ce_heading_content" name="<?=$field_name?>[content]" value="<?= htmlspecialchars($content) ?>" />
	</td>
</tr>
</table>