$(function(){
	//$('.week-double[type=all]')[0].checked = 'true'; 
	$('.week-double').on('click',function(){
		$('.week-td').removeClass('chosed-week');
		var type = $(this).attr('value');
		if(type == 'none') return;
		if(type == 'all') type = '';
		else if(type != '')type = ':' + type;
		$('.week-td'+ type).addClass('chosed-week');
	})

	$('.week-td').on('click',function(){
		if($(this).is('.chosed-week')){
			$(this).removeClass('chosed-week');
		}else{
			$(this).addClass('chosed-week');
		}
	})

	$(".course-td").click(function() {
		var index = $(this).attr('index');
		$('#chosed-course-index').val(index);
		console.log(course_data);
		console.log(index);
		console.log(course_data[index]);
		var this_course = course_data[index];
		if(typeof this_course == 'undefined' || this_course == ''){
			$('.week-td').addClass('chosed-week');
		}else{
			$('.week-td').removeClass('chosed-week');
			weeks = this_course['weeks'];
			for(var val in weeks){
				$($('.week-td')[weeks[val]]).addClass('chosed-week');
			}
		}
		
		$('#myModal').modal({
		    backdrop:true,
		    keyboard:true,
		    show:true
		});
		
		$('.week-double[value=all]')[0].checked = 'true'; 
		// var data = $(this).siblings('input').val();
		// $("#confirm_modal .btn-primary").attr('data',data);
	})

	$('#save-change').on('click',function(){
		var arr = [];
		$('.chosed-week').each(function(){
			arr.push($(this).attr('index'));
			//arr.push(this.index);
		})
		var index = $('#chosed-course-index').val();
		var temp = course_data[index];
		if(arr.length <= 0){
			course_data[index] = "";
		}else{
			course_data[index] = {
				index:index ,
				name:$('#course-name').val(),
				weeks:arr
			};
		}
		console.log(course_data);
		return;
		$.post($('#save-change-url').val(),{data:course_data},function(data){
			if(data.status == 0){
				alert(data.info);
				add_color(course_data[index],index);
			}else{
				alert(data.info);
				course_data[index] = temp;
			}
			$('#myModal').modal('hide');
		})
	})

	$('select').on('change',function(){
		if(parseInt($(this).val()) == 0){
			var week = ""; 
		}else{
			var week = "?this_week=" + $(this).val(); 
		}
		window.location.href = $('#this-url').val() + week;
	})

	$('#do-copy').on('click',function(){
		$.post($('#do-copy-url').val(),{user_name:$('#user-name').val()},function(data){
			if(data.status == 0){
				alert(data.info);
				$('#copyModal').modal('hide');
			}
			else{
				alert(data.info);
			}
		})
	})
})
function add_color(data,index){
	if(data == ""){
		$($('.course-td')[index]).removeClass('no-this-week').removeClass('have-course');
		return;
	}
	 index = data['index'];
	 if($.inArray((this_week-1) + "", data['weeks']) >= 0){
	   $($('.course-td')[index]).removeClass('no-this-week');
	   $($('.course-td')[index]).addClass('have-course');
	 }else{
	   $($('.course-td')[index]).removeClass('have-course');
	   $($('.course-td')[index]).addClass('no-this-week');
	 }
}