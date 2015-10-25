$(function(){
	$("#offset-button").on("click",function(){
		$.post($('#had-sign-in').val(),{name:$('#name-sign').val()},function(data){
			console.log(data);
			if(data.status == 0){
				if(data.info == 1) initTime(0);
				else initTime(data.data);
				$("#offset-box").animate({"left":0},200);
		    }else{
	 			alert(data.info);                                      
		    }
		})
	})
	$("#offset-cancel").on("click",function(){
		$("#offset-box").animate({"left":"100%"},200);
	})

	$(document).on("keypress",function(e){
		e = (e) ? e : ((window.event) ? window.event : "") //兼容IE和Firefox获得keyBoardEvent对象  
		var key = e.keyCode?e.keyCode:e.which;
		//alert(key);
		//console.log(e.keyCode);
		if(key == 13){
			return false;
		}
	})
	$('#query-button').on('mouseover',function(){
		$('#query-div').show();
	}).on('mouseout',function(){
		$('#query-div').hide();
	})
})
window.onload = function(){
	scrolltoend();
}
function initTime(info){
	$('#name-offset').val($("#name-sign").val());
	var date = new Date();
	$('#out-hour').val(date.getHours());
	$('#out-minute').val(date.getMinutes()-1);

	var flag = info == 0 ? false : true;
	if(flag) date.setTime(info.time * 1000);
	$('#offset-send').attr('data-have-in',flag);
	$('#year').val(date.getFullYear()).attr('disabled',flag);
	$('#month').val(date.getMonth() + 1).attr('disabled',flag);
	$('#day').val(date.getDate()).attr('disabled',flag);
	$('#in-hour').val(date.getHours()).attr('disabled',flag);
	$('#in-minute').val(date.getMinutes()).attr('disabled',flag);
}
function signSend(type){ 
	var form = $('#sign-form');
	var form_data = {};
	form_data['name'] = $('#name-sign').val();
	form_data["type"] = type;
	var tempDate = new Date();
	$.post(form.attr('action'),form_data,function(data){
		//console.log(data);
		if(data.status == 0){
			var block = $("#show-block");
		   	var p = document.createElement("p");
		   	p.innerHTML = data.data.name + "同学" + tempDate.getHours() + ":" + (tempDate.getMinutes() < 10 ? "0" : "" ) + tempDate.getMinutes()  + (type == 0 ? "来到小组" : "离开小组");
		   	block.append(p);
		   	scrolltoend();
	    }else{
 			alert(data.info);
	    }
	})
	return false;
}
function scrolltoend(){
	if($("#show-block").length <= 0){
		return ;
	}
	var block = $("#show-block")[0];
	var timer = setInterval(function(){
		if(block.scrollTop < block.scrollHeight - block.offsetHeight){
			block.scrollTop += 10;
		}else{
			clearInterval(timer);
		}
	},20);
}
function offsetSend(){
	var form = $('#offset-form');
	var data = {};
	data['name'] = $('#name-offset').val();
	data["have_in"] = $('#offset-send').attr("data-have-in");

	var tempDate = new Date();
	tempDate.setFullYear($('#year').val(),$('#month').val() - 1,$('#day').val());
	tempDate.setHours($('#in-hour').val(),$('#in-minute').val());
	var string = (tempDate.getMonth() + 1) + "月" + tempDate.getDate() + "日 " + tempDate.getHours() + ":" + (tempDate.getMinutes() < 10 ? "0" : "" ) + tempDate.getMinutes() + " 到 ";
	data["in_time"] = parseInt(tempDate.getTime() / 1000);
	tempDate.setHours($('#out-hour').val(),$('#out-minute').val());
	string += tempDate.getHours() + ":" + (tempDate.getMinutes() < 10 ? "0" : "" ) + tempDate.getMinutes();
	data["out_time"] = parseInt(tempDate.getTime() / 1000);
	$.post(form.attr('action'),data,function(data){
		if(data.status == 0){
			var block = $("#show-block");
		   	var p = document.createElement("p");
		   	p.innerHTML =  data.data.name  + "同学补签了 " + string + " 的记录";
		   	block.append(p);
		   	$("#offset-cancel").click();
	    }else{
 			alert(data.info);
	    }
	})
}