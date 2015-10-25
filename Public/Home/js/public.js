$(function(){
	$(window).resize(function(){
		var minHight = document.body.scrollHeight - $('#center')[0].offsetTop - $('#footer').height() - 20;
		$('#center').css({"min-height":minHight});
	}).resize();
})