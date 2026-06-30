$( document ).ready( function () {

/*
	var ooui_el = OO.ui.infuse( $( '#toolbox-ooui-select' ).get(0) );

console.log("ooui_el", ooui_el)
ooui_el.dropdownWidget.setLabel("abc")
*/

		var data = $('#toolbox-ooui-select').data();

	var items = [];

	for( var val of data.ooui.data ) {
		items.push(new OO.ui.MenuOptionWidget(val))
	}


	var buttonMenu = new OO.ui.ButtonMenuSelectWidget({
			label: data.ooui.label,
			icon: "menu",
		//	flags: ["progressive", "primary"],
//flags: ['invert'],
							//	"classes" : ['mt-sm-3'],
// invert progressive destructive warning yellow constructive
			menu: {
				items: items,
			},
			id: 'toolbox-ooui-select'
		});

		


		buttonMenu.getMenu().on("choose", function (menuOption) {
			window.location = menuOption.getData();
		});
		$('#toolbox-ooui-select').replaceWith(buttonMenu.$element);


/*
 // bind change event to select
      $('#toolbox_select').on('change', function () {
          var url = $(this).val(); // get selected value
          if (url) { // require a URL
              window.location = url; // redirect
          }
          return false;
      });
*/

	$( ".nav-list.right li").each(function(index, element) {
		$(this).find('a').next().clone().appendTo( $(".nav-list-mobile li").get(index) );
	});

	// $("#p-contentnavigation").toggle(edit);

	$(".nav-container .personal-tools-kda-edit").click(function () {
		var edit = mw.cookie.get("kmaskin-show-nav-edit");

		if (!edit) {
			mw.cookie.set("kmaskin-show-nav-edit", true, {
				path: "/",
				// not set: session cookie
				// expires: 365 * 86400
			});
		} else {
			mw.cookie.set("kmaskin-show-nav-edit", null);
		}

		$("#p-contentnavigation").fadeToggle(150).css('display', edit ? 'flex' : 'none');
	});

	$(function () {
		$("nav ul li > a:not(:only-child)").click(function (e) {
			$(this).siblings(".nav-dropdown").fadeToggle(150);
			$(".nav-dropdown").not($(this).siblings()).hide();
			if (!$(this).hasClass("page-tools-kda-search")) {
				hideSearchInput();
			}
			e.stopPropagation();
		});
		$("html").click(function () {
			$(".nav-dropdown").fadeOut(150);
		});
	});
	document.querySelector("#nav-toggle").addEventListener("click", function () {
		this.classList.toggle("active");
	});
	$("#nav-toggle").click(function () {
		$("nav ul.nav-list:not(.right)").toggle();
	});

	/*
$(document).click(function(event) {

  var $target = $(event.target);
  if(!$target.closest('.kma-search-wrapper').length ) {
    hideSearchInput()
  }

 
});
*/
	$(window).click(function () {
		hideSearchInput();
	});

	$(".nav-container .kma-search-wrapper").click(function (event) {
		event.stopPropagation();
	});

	$("nav a.page-tools-kda-search").click(function (e) {
		// $('.nav-dropdown').not($(this).siblings()).hide();

		// $(this).siblings('.kma-search-wrapper').css({ left: -200, position:'absolute'});
		// $(this).siblings('.kma-search-wrapper').fadeToggle(150);

		console.log($(".nav-container .kma-search-wrapper").first().css("opacity"));
		if ($(".nav-container .kma-search-wrapper").first().css("opacity") == 0) {
			showSearchInput();
		} else {
			hideSearchInput();
		}

		$(this).siblings(".kma-search-wrapper input").focus();
		e.stopPropagation();
	});

	function showSearchInput() {
		// $(this).css({'background': '#c00000', color: 'white'});
		$(".nav-container .kma-search-wrapper").css({
			"pointer-events": "auto",
			opacity: "1",
			visibility: "visible",
			transform: "translate3d(0,0,0)",
		});
	}

	function hideSearchInput() {
		$(".nav-container .kma-search-wrapper").css({
			"pointer-events": "none",
			opacity: "0",
			visibility: "hidden",
			transform: "translate3d(0,20px,0)",
		});
	}
} );

