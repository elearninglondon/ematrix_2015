<textarea class="ce_code linedarea" name="<?= $field_name ?>" rows="<?= (int)$settings["rows"] ?>"><?= htmlspecialchars($value); ?></textarea>

<script type="text/javascript">

$(document).ready(function(){

	$(".content_elements_wrapper .linedarea").linedtextarea();
	
	$(".content_elements_wrapper .linedarea").focus(function() {
		$(this).parent('.linedtextarea').parent('.linedwrap').addClass('ce_linedwrap_hover');
	});
	$(".content_elements_wrapper .linedarea").blur(function() {
		$(this).parent('.linedtextarea').parent('.linedwrap').removeClass('ce_linedwrap_hover');
	});	
	
	//$(".linedarea").focus();
	
	$(".content_elements_wrapper .linedarea").removeClass('linedarea');	

});

</script>

