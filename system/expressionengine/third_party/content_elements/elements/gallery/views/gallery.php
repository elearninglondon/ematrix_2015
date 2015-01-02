<input type="hidden" name="<?= $field_name ?>" value="<?= $gallery_id ?>" />
<div class="ce_gallery">

	<div id="placeholder_<?= $gallery_id ?>" style="display:none">
		<div class="ce_gallery_item">
			<div class="ce_gallery_img_wrapper">
				<a class="ce_gallery_img" href="#">
					<div class="ce_gallery_img_remove"></div>
				</a>
			</div>
			<div class="ce_gallery_caption">
				<input type="hidden" 	class="ce_gallery_dir" 		name="<?=$field_name?>[dir][]" />
				<input type="hidden" 	class="ce_gallery_name" 	name="<?=$field_name?>[name][]" />
				<input type="text" 		class="ce_gallery_caption" 	name="<?=$field_name?>[caption][]" placeholder="<?= lang('gallery_caption') ?>" /><br />
				<input type="text" 		class="ce_gallery_url" 		name="<?=$field_name?>[url][]" placeholder="<?= lang('gallery_url') ?>" />
			</div>
		</div>
	</div>
	
	<div class="ce_gallery_item_wrapper" id="<?= $gallery_id ?>">
	
		<?php foreach ($images as $img): ?>
		
		<div class="ce_gallery_item">
			<div class="ce_gallery_img_wrapper">
				<a class="ce_gallery_img" style="background-image: url(<?= htmlspecialchars($img["thumb"]) ?>)" href="#">
					<div class="ce_gallery_img_remove"></div>
				</a>
			</div>
			<div class="ce_gallery_caption">
				<input type="hidden" 	class="ce_gallery_dir" 		name="<?=$field_name?>[dir][]" value="<?= htmlspecialchars($img["dir"]) ?>" />
				<input type="hidden" 	class="ce_gallery_name" 	name="<?=$field_name?>[name][]" value="<?= htmlspecialchars($img["name"]) ?>" />
				<input type="text" 		class="ce_gallery_caption" 	name="<?=$field_name?>[caption][]" placeholder="<?= lang('gallery_caption') ?>" value="<?= htmlspecialchars($img["caption"]) ?>"/><br />
				<input type="text" 		class="ce_gallery_url" 	name="<?=$field_name?>[url][]" placeholder="<?= lang('gallery_url') ?>" value="<?= !empty($img["url"]) ? htmlspecialchars($img["url"]) : '' ?>"/>
			</div>
		</div>
		
		<?php endforeach; ?>
		
		<div class="ce_gallery_item_btn_add_wrapper">
			<span class="max_photos js_hide"><?= $max_photos ?></span>
			<a href="#" class="ce_gallery_btn_add">
				<span><?= lang('gallery_add'); ?></span>
			</a>
		</div>			
		
		<div class="ce_gallery_clr"></div>
	</div>

</div>
