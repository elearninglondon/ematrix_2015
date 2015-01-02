<div class="ce_wysiwyg_wrapper">
	<textarea rows="6" style="padding: 0; border: 1px solid #D1D5DE; " id="<?= $wysiwyg_id ?>" name="<?= $field_name ?>" class="ce_wysiwyg"><?= htmlspecialchars($value) ?></textarea>
	
	<div class="js_hide" rel="skin"><?= $ck_editor_theme ?></div>
	<div class="js_hide" rel="toolbar"><?= $toolbar ?></div>
	<div class="js_hide" rel="height"><?= $settings["height"] ?></div>
</div>


