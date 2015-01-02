<table width="100%" cellpadding="0" cellspacing="0">
	<tr>
		<td align="left" valign="top">
		
			<input type="hidden" id="<?= $table_id ?>_eid" name="<?= $field_name ?>[eid]" value="<?= $table_id ?>" />
			<input type="hidden" id="<?= $table_id ?>_cols" name="<?= $field_name ?>[cols]" value="<?= $cols ?>" />
			<input type="hidden" id="<?= $table_id ?>_rows" name="<?= $field_name ?>[rows]" value="<?= $rows ?>" />
			
			<table border="0" class="element_table" cellpadding="0" cellspacing="0" width="100%" id="<?= $table_id ?>" rel="<?= $field_name ?>" >
			
			<?php $cell_index = 0; ?>
			<?php if ($header): ?>
			
			<thead>
			
				<td class="line_number">&nbsp;</td> 
			
				<?php for ($j=0; $j<$cols; $j++): ?>
				
				<td>
					<div class="element_table_td_wrapper">
						<input type="text" class="element_box" name="<?= $field_name ?>[cell][]" value="<?= htmlspecialchars(@$cell[$cell_index++]) ?>">
					</div>
				</td>
					
				<?php endfor; ?>
			</thead>
			<tbody>
			
			<?php endif;?>
			
						
			<?php for ($i=0; $i<$rows; $i++): ?>		
			
				<tr>
					<td class="line_number"></td> 
					
						<?php for ($j=0; $j<$cols; $j++): ?>
						
							<td>
								<div class="element_table_td_wrapper">
									<input type="text" class="element_box" name="<?= $field_name ?>[cell][]" value="<?= htmlspecialchars(@$cell[$cell_index++]) ?>">
								</div>
							</td>	
						
						<?php endfor; ?>
						
				</tr>		
					
			<?php endfor; ?>
			</tbody>
			</table>
		
		</td>
		<td align="left" valign="top" width="90px">
			<div class="element_table_buttons_col">
				<a href="#" rel="<?= $table_id ?>" class="element_table_button element_table_add_col" style="color: #E11842"><?= lang('add_col') ?></a>
				<a href="#" rel="<?= $table_id ?>" class="element_table_button element_table_remove_col" style="color: #E11842"><?= lang('remove_col') ?></a>			</div>		
		</td>
	</tr>
</table>
	 	
<div class="element_table_buttons_row">
	<a href="#" rel="<?= $table_id ?>" class="element_table_button element_table_add_row" style="color: #E11842"><?= lang('add_row') ?></a>
	<a href="#" rel="<?= $table_id ?>" class="element_table_button element_table_remove_row" style="color: #E11842"><?= lang('remove_row') ?></a>

	<script type="text/javascript">try { apply_table_sortable_script(); } catch(e) {} </script>
</div>

<div class="content_elements_clr"></div>