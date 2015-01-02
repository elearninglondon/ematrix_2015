<!-- Layout is loaded onto FOOTER, is hidden -->

<?php foreach ($content_elements_settings as $content_element_name=>$content_element_settings): ?>
<div class="js_hide content_element_pattern_<?= $content_element_name; ?>" rel="<?= $content_elements_type_options[$content_element_name] ?>">
<?= $content_elements_settings[$content_element_name]; ?>
</div>
<?php endforeach; ?>