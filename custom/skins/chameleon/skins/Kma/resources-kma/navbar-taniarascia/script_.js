(function ($) {
	var edit = mw.cookie.get("kmaskin-show-nav-edit");

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

		$("#p-contentnavigation").fadeToggle(150);
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
		$("nav ul").toggle();
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

	$("nav ul li > a.page-tools-kda-search").click(function (e) {
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
})(jQuery);

