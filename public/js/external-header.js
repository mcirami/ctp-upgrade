"use strict";
jQuery(document).ready(function ($) {
	console.log("external-header.js");

	$(window).on("scroll", function (event) {
		console.log("scroll");
		if ($(window).scrollTop() > 40) {
			$('header.external').addClass("scroll");
		} else {
			$('header.external').removeClass("scroll");
		}
	});
});