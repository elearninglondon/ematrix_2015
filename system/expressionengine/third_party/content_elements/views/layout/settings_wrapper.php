<div class="ce_settings_wrapper">	
	<span class="content_elements_draggable_handler" href="#"></span>
	<div class="ce_settings_header">	
		<label><?= $title ?></label>
		<a class="button_remove" href="#"></a>	
	</div>
	<div class="ce_settings_body">
		<input type="hidden" name="content_element_item[<?= $eid ?>]" value="<?= $element ?>"/>
		<input type="hidden" class="ce_title" name="content_element[<?= $element ?>][<?= $eid ?>][title]" value="<?= $title ?>"/>
		<?= $data ?>	
	</div>
</div>