<div class="content_elements_wrapper" data-field="<?= $field_name ?>">

	<!-- TOOLBAR -->
	
	<div class="content_elements_tiles" id="content_elements_tiles_<?= $field_id ?>">
		
		<?php foreach ($tiles as $tile): ?>	
			
		<div class="content_elements_tile_item">
			<div class="content_elements_tile_header">
				<label><?= $tile["title"] ?></label>
				<span class="content_elements_draggable_handler"></span>
				<a href="#" class="button_remove"></a>
			</div>
			<div class="content_elements_tile_body">
				<input type="hidden" value="<?= $tile["type"] ?>" name="<?= $tile["element_field_name"] ?>[element_type]"/>
				<input type="hidden" value="<?= $tile['settings'] ?>" name="<?= $tile["element_field_name"] ?>[element_settings]"/>
				
				<?= $tile['content'] ?>
			</div>
		</div>
		
		<?php endforeach; ?>	
		
	</div>
	
	<div class="content_elements_toolbar" id="content_elements_toolbar_<?= $field_id ?>">
		<div class="content_elements_toolbar_headline"><?= lang('content_elements_publish_description') ?></div>
		<div class="content_elements_toolbar_wrapper">
	
				<?php foreach ($buttons as $button): ?>
				
				<div class="content_elements_toolbar_item" id="content_elements_toolbar_item_<?= $button["type"] ?>">
					<a href="#" id="<?= $button["eid"] ?>" rel="<?= $button["title"] ?>" class="button_add">
						<span class="content_elements_icon content_elements_icon_<?= $button["type"] ?>"></span>
						<?= $button["title"] ?>
					</a>
				</div>
				
				<?php endforeach; ?>	
			

			
			<div class="content_elements_clr"></div>
		</div>
	</div>
	
	<!-- TILES -->
	
	<div class="content_elements_clr"></div>
	
</div>	
		
