<script type='text/javascript'>
	function ipol_popup_virt(code, info) { // Вспл. подсказки 
		var offset = $(info).position().top;
		var LEFT = $(info).offset().left;		
		
		var obj;
		if (code == 'next') 	
			obj = $(info).next();
		else
			obj = $('#'+code);

		LEFT -= parseInt(parseInt(obj.css('width')) / 2);
		
		obj.css({
			top: (offset + 15)+'px',
			left: LEFT,
			display: 'block'
		});	
		return false;
	}
	
	$(document).ready(function() {//инструкции 
		$('.hinta').click(function() {
			$(this).next('.hintdiv').slideToggle();
		});
		
		if (versionBx<versionBxNewFunc) {
			$("#HIDELOCATION").prop("checked", false).attr("disabled", true);
		}
		
		$("[name='ERRWRONGANSWER']").attr("disabled", true);
		$("[name='ERRWRONGANSWERDATE']").attr("disabled", true);
	});
</script>	