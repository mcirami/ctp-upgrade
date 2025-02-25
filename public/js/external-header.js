"use strict";
jQuery(document).ready(function ($) {
	$(window).on("scroll", function (event) {
		if ($(window).scrollTop() > 40) {
			$('header.external').addClass("scroll");
		} else {
			$('header.external').removeClass("scroll");
		}
	});
});