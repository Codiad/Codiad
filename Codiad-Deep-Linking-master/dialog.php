<form>
<input type="text" readonly value="<?php echo($_GET['path']); ?>" onmouseup="this.select();">
<button class="btn-left" onclick="window.open($(this).parent().children('input').val(), '_blank'); return false;">Open</button>
<button class="btn-right" onclick="codiad.modal.unload(); return false;">Close</button>
<script type="text/javascript">
	$('#modal-content input').focus();
</script>
</form>
