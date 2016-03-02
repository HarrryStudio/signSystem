$(function(){
	if($('.week-table-head-box').length > 0){
		$('.week-table-head-box').css({"height":$('.week-table-head-box').height()});
		var offsetTop = $('.week-table-head-box')[0].offsetTop;
		var p = $('.week-table-head-box');
		while(!p.is('body')){
			p = p.parent();
			if(p.css("position") == "relative"){
				offsetTop += p[0].offsetTop;
			}
		}
		$(window).on('scroll',function(){
			if(document.body.scrollTop > offsetTop){
				$('.week-table-head').css({"position":"absolute","top":document.body.scrollTop - offsetTop});
			}else{
				$('.week-table-head').css({"top":0});
			}
		}) 
	}
	$(window).resize(function(){
		if($('.time-fragment').length > 0){
			var iwidth = $('td.data-td')[0].offsetWidth;
			var iheight = ($('td.data-td')[0].offsetHeight) / 3600;
			$('.time-fragment').each(function(){
				$(this).css({
					"left": iwidth * (parseInt($(this).attr('data-col'))) + $('.time-td')[0].offsetWidth,
					"top" : iheight * parseInt($(this).attr('data-in-time')),
					"height" : iheight * parseInt($(this).attr('data-length')),
					"width" : iwidth + 1
				})
			})
			
			$('.time-fragment').on('click',function(){
				var in_top = parseInt($(this).css("top")) - 10;
				var height = $(this).height();
				//alert(in_top + height);
				$('#in-arrow').css({"top":in_top});
				$('#out-arrow').css({"top":in_top + height});
				detail_time($(this).attr('data-in-time'),$(this).attr('data-length'));
				$('#in-arrow span').text();
			})
			 
		}
		paint_color();
	}).resize();
	
	$(".dropdown-item").on('click',function(){
		var dropdown = $(this).parent().parent().parent()
		dropdown.find('.dropdown-title').text($(this).text());
		dropdown.find('input').val($(this).attr("value"))
	})
	$('.btn-group button').on('click',function(){
		$('.btn-group button').removeClass('action');
		$(this).addClass('action');
		$(this).siblings('input').val($(this).val());
		if(check_send() !== true){return ;}
		start_search();
	})
	$('#search-start').on('click',function(){
		var info = check_send();
		if( info !== true ){alert(info);return;}
		start_search();
	})

	$('.btn-group button[value='+$('#search-date-type-input').val()+']').addClass("action");
	$('.dropdown-item[value='+$('#search-type-input').val()+']').click();

})
function check_send(){
	if($('#search-type-input').val() == ""){
		return "请选择搜索类型";
	}else if($('#search-date-type-input').val() == ""){
		return ("请选择日期类型");
	}else if($('#title').val() == ""){
		return ("请填写人名或组名");
	}else{
		return true;
	}
}
function change_time(type){
	var info = check_send();
	if( info !== true ){alert(info);return;}
	var time = parseInt($('.show-time-bar').attr('time'));
	var step = 24 * 3600;
	var size = 0;
	var date_type =  parseInt($('#search-date-type-input').val());
	switch (date_type){
		case 0 : size = 1;break;
		case 1 : size = 7;break;
		case 2 : {
			if(type == 0){
				size = get_days_month(time,1);
			}else if(type == 1){
				size = get_days_month(time,0);
			}break;
		}
		default: size = 0;
	}
	if(type == 0){
		start_search(time - step * size);
	}else if(type == 1){
		start_search(time + step * size);
	}

}
function start_search(search_time){
	var query = $('.search-bar').find('input').serialize();
	var url = $('#search-start').attr('url');
	query = query.replace(/(&|^)(\w*?\d*?\-*?_*?)*?=?((?=&)|(?=$))/g,'');
    query = query.replace(/^&/g,'');
    url += '?' + query;
    if(search_time != "" && query != ""){
    	url += "&search_time=" + search_time;
    }
    window.location.href = url;
}
function get_days_month(time,last){
	time = (time - last) * 1000;
	var date = new Date(time);
	var month = parseInt(date.getMonth());
	var year = parseInt(date.getFullYear());
	var arr = [31,28,31,30,31,30,31,31,30,31,30,31];
	if(is_run(year)){
		arr[1] = 29;
	}
	return arr[month];
}
function is_run(year){
	if(year % 100 == 0){
		if(year % 400 == 0) return true;
		else return false;
	}
	if(year % 4 == 0)return true;
	else return false;
}
function detail_time(start,length){
	start = start / 60;
	length = length / 60;
	var hour = 7;
	var min = 0;
	var oh = parseInt(start / 60) + hour;
	var om = parseInt(start % 60) + min;
	if(oh < 10) oh = "0" + oh;
	if(om < 10) om = "0" + om;
	$('#in-arrow span').text(oh + " : " + om );
	start += length;
	var oh = parseInt(start / 60) + hour;
	var om = parseInt(start % 60) + min;
	if(oh < 10) oh = "0" + oh;
	if(om < 10) om = "0" + om;
	$('#out-arrow span').text(oh + " : " + om );
}
function paint_color(){
	color_arr = ["#7CB5EC","#F7A35C","#90ED7D","#F86C6C"];
	var i = 0;
	var team = {};
	if($('.gather-item').length > 0){
		$('.gather-item').each(function(){
			var key = $(this).attr("data-id");
			if(typeof(team[key]) == "undefined"){
				team[key] = color_arr[i ++];
			}
			// console.log(key);
			// console.log($(this));
			// console.log("notice"+ team[key]);
			$(this).css({'color': team[key]});
			//$(this).css({"color":color_arr[i ++]});
		})
	}
	
	if($('.time-fragment').length > 0){
		
		
		$('.time-fragment').each(function(){
/*			console.log($(this).attr('data-belong'));
			console.log(team[$(this).attr('data-belong')]);
			console.log(team);
*/
			// if(typeof(team[$(this).attr('data-belong')]) == "undefined"){
			// 	team[$(this).attr('data-belong')] = color_arr[i ++];
			// }
			$(this).css({'background-color': team[$(this).attr('data-belong')],'border-color': team[$(this).attr('data-belong')]});
		})
	}

	
}