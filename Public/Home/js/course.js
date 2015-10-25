$(function(){
	$('#course-edit').on('click',function(){
		$('#myModal').modal({
		    backdrop:true,
		    keyboard:true,
		    show:true
		});
		$('#user-name').val("");
	})

	$('#do-login').on('click',function(){
		$.post($('#do-login-url').val(),{user_name:$('#user-name').val()},function(data){
			if(data.status == 0){
				window.location.href = data.data;
			}
			else{
				alert(data.info);
			}
		})
	})

	$('select').on('change',function(){
		if(parseInt($('#choose-week-select').val()) == 0){
			var week = ""; 
		}else{
			var week = "?this_week=" + $('#choose-week-select').val(); 
		}
		if(parseInt($('#choose-team-select').val()) == 0){
			var team = ""; 
		}else{
			var team = "&team=" + $('#choose-team-select').val(); 
		}
		window.location.href = $('#this-url').val() + week +team;
	})

	$(document).on("keypress",function(e){
		e = (e) ? e : ((window.event) ? window.event : "") //兼容IE和Firefox获得keyBoardEvent对象  
		var key = e.keyCode?e.keyCode:e.which;
		//alert(key);
		//console.log(e.keyCode);
		if(key == 13){
			if($('#myModal').is(':visible')){
				$('#do-login').click();
			}
			return false
		}
	})
})