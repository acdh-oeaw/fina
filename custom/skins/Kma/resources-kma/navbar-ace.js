//https://github.com/samsono/Ace-Responsive-Menu/blob/master/horizontal-menu.html
$(document).ready(function(){
	$("#respMenu").aceResponsiveMenu({
		resizeWidth: '768',	//SetthesameinMediaquery
		animationSpeed: 250,	//slow,medium,fast

/*
		effectOptionsUp: { easing: "swing", direction: 'down'  },	//easeInOutQuart
		effectOptionsDown: { easing: "swing", direction: 'up'  },	//easeInOutQuart
		// https://jqueryui.com/effect/
		effect: 'slide',	// blind, bounce, clip, drop, explode, fade, fold, highlight, puff, pulsate, scale, shake, size, slide, transfer
*/


		accoridonExpAll: false	//Expands all the accordion menu onclick
	});
});

