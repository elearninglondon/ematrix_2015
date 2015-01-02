<div style="display:none" class="content_elements_prototypes">	
	<?php foreach ($tiles as $tile): ?>	
	
		<div id="content_element_prototype_<?= $tile['eid'] ?>">		
			<div class="content_elements_tile_item">
				<div class="content_elements_tile_header">
					<label><?= $tile["title"] ?></label>
					<span class="content_elements_draggable_handler"></span>
					<a href="#" class="button_remove"></a>
				</div>
				<div class="content_elements_tile_body">
					<input type="hidden" value="<?= $tile["type"] ?>" name="__element_name__[__index__][element_type]"/>
					<input type="hidden" value="<?= $tile['settings'] ?>" name="__element_name__[__index__][element_settings]"/>
					
					<?= $tile['content'] ?>
				</div>
			</div>
		</div>
	
	<?php endforeach; ?>	
</div>