<?php $rnd='rte_'.rand(0,99999).uniqid(); ?>

<div class="ce_rich_editor">
	<textarea class="ce_rich_text" rows="<?= (int)$settings["rows"] ?>" name="<?= $name; ?>"><?= htmlspecialchars($value); ?></textarea>
</div>