
<a href="#" class="ce_element_options_on" ><?= lang('settings_options_show') ?> &raquo;</a>


<div class="ce_element_options js_hide">

	<table cellpadding="0" cellspacing="0" class="ce_settings_table ce_settings_<?= $element ?>" width="100%">
	
		<?php foreach ($settings as $setting_options): ?>
		
		<tr>
			<?php foreach ($setting_options as $k=>$option): ?>
			<td <?= ($k)?'':'width="40%"' ?> align="left" valign="top"><?= $option ?></td>
			<?php endforeach; ?> 
		</tr>		
		
		<?php endforeach; ?>		
		 	 		 
	</table>
</div>	

<a href="#" class="ce_element_options_off js_hide" >&laquo; <?= lang('settings_options_hide') ?></a>