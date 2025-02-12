/* window.$ = window.jquery = require("jquery");
window.Popper = require("@popperjs/core");
require("bootstrap"); */
("use strict");

document.addEventListener("DOMContentLoaded", function (event) {
    let items = document.querySelectorAll(".carousel .carousel-item");
    let minPerSlide = 0;

    if (window.innerWidth < 768) {
        minPerSlide = 6;
    } else if (window.innerWidth < 992) {
        minPerSlide = 8;
    } else {
        minPerSlide = 12;
    }

    setCarousel(items, minPerSlide);
    function setCarousel(items, minPerSlide) {
        items.forEach((el) => {
            let next = el.nextElementSibling;
            for (var i = 1; i < minPerSlide; i++) {
                if (!next) {
                    // wrap carousel by using first child
                    next = items[0];
                }
                let cloneChild = next.cloneNode(true);
                el.appendChild(cloneChild.children[0]);
                next = next.nextElementSibling;
            }
        });
    }

    $(window).on("resize", function () {
        if (window.innerWidth < 768) {
            minPerSlide = 6;
        } else if (window.innerWidth < 992) {
            minPerSlide = 8;
        } else {
            minPerSlide = 12;
        }

        setCarousel(items, minPerSlide);
    });
});
