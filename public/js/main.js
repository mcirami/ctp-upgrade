function checkBoxesInDiv(divID) {

    $("#" + divID).find('input[type=checkbox]').each(function () {
        this.checked = true;
    });

}


function unCheckBoxesInDiv(divID) {

    $("#" + divID).find('input[type=checkbox]').each(function () {
        this.checked = false;
    });

}

function adminLogin(id) {
    window.open('/login/' + id);
}

jQuery(document).ready(function ($) {
 /*   var $trigger = $(".dropdown");


    // Show hide popover
    $(".dropdown > a").click(function (e) {
        e.preventDefault();

        if (!$(this).parent().find(".dropdown-menu").hasClass('open')) {
            $(this).parent().find(".dropdown-menu").slideDown(400).addClass('open');
        } else {
            $(this).parent().find(".dropdown-menu").slideUp(400).removeClass('open');
        }


    });
*/
    /* $(".dropdown").on("click", function(event){
          if($trigger !== event.target && !$trigger.has(event.target).length){
              $(".dropdown-menu").slideUp("fast");
          }
      });
   */
/*
    var activeDropdown = $(".dropdown-menu li").find('.active');

    $(activeDropdown).parent().parent().css('display', 'block').addClass('open');
*/

    axios.defaults.headers.common = {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': window.csrf_token
    };

    $('#default').click(function (e) {
        e.preventDefault();

        $('#valueSpan1').val("484848");
        $('#valueSpan2').val("FFFFFF");
        $('#valueSpan3').val("2A58AD");
        $('#valueSpan4').val("1D4C9E");
        $('#valueSpan5').val("82A7EB");
        $('#valueSpan6').val("FCED16");
        $('#valueSpan7').val("EAEEF1");
        $('#valueSpan8').val("FFFFFF");
        $('#valueSpan9').val("404452");
        $('#valueSpan10').val("999999");
    });

    $('.open_popup').click(function (e) {
        e.preventDefault();
        var popupLink = $(this).attr("href");
        var popup = $('#popup');

        $.ajax({
            url: popupLink,
            success: function (data) {
                popup.addClass("show").html(data);
                $('body').addClass('popup_open');
                $('.popup_wrapper').addClass('magictime spaceInUp');
            }
        });
    });

    $(document).on('click', '.popup_close', function (e) {
        e.preventDefault();
        $('.popup_wrapper').addClass('magictime spaceOutDown');
        window.setTimeout(function () {
                $('#popup').removeClass('show').empty()
                $('body').removeClass('popup_open');
            }
            , 800);


    });

    var notifShowing = false;

    $('#notif_icon').click(function (e) {
        $('#notification_box').toggleClass('open');
    });

    const blockButtons = document.querySelectorAll('.block_sub_id');
    if (blockButtons) {
        blockButtons.forEach((button) => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const button = e.target;
                const userID = button.dataset.rep;
                const subID = button.dataset.subid;

                const packets = {
                    user_id: userID,
                    sub_id: subID
                }

                axios.post('user/block-sub-id', packets).then((response) => {
                    if (response.data.success) {
                        button.innerHTML = "Blocked"
                        button.disabled = true;
                        button.classList.remove("value_span6-2", "value_span2", "value_span1-2");
                        const unblockButton = button.nextElementSibling;
                        unblockButton.disabled = false;
                        unblockButton.style.display = "block";
                    } else {
                        console.log(response);
                    }
                })

            })
        });
    }
    const unblock_buttons = document.querySelectorAll('.unblock_button');
    if(unblock_buttons) {
        unblock_buttons.forEach((button) => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const button = e.target;
                const userID = button.dataset.rep;
                const subID = button.dataset.subid;

                const packets = {
                    user_id: userID,
                    sub_id: subID
                }

                axios.post('user/unblock-sub-id', packets).then((response) => {
                    if (response.data.success) {
                        button.disabled = true;
                        button.style.display = "none";
                        const blockButton = button.previousElementSibling;
                        blockButton.innerHTML = "Block ID";
                        blockButton.disabled = false;
                        blockButton.classList.add("value_span6-2", "value_span2", "value_span1-2");
                    } else {
                        console.log(response);
                    }
                })

            })
        });
    }

    let tabsContainer = document.querySelector("#tabs");
    if(tabsContainer) {
        let tabTogglers = tabsContainer.querySelectorAll("#tabs a");
        tabTogglers.forEach(function(toggler) {
            toggler.addEventListener("click", function(e) {
                e.preventDefault();

                let tabName = this.getAttribute("href");

                let tabContents = document.querySelector("#user_info");

                for (let i = 0; i < tabContents.children.length; i++) {

                    tabTogglers[i].parentElement.classList.remove("border-t",
                        "border-r", "border-l", "-mb-px", "value_span6-1");
                    tabTogglers[i].classList.remove("value_span2");
                    tabContents.children[i].classList.remove("hidden");
                    if ("#" + tabContents.children[i].id === tabName) {
                        continue;
                    }
                    tabContents.children[i].classList.add("hidden");

                }

                e.target.parentElement.classList.add("border-t", "border-r",
                    "border-l", "-mb-px", "value_span6-1");
                e.target.classList.add("value_span2");
            });
        });
    }

    const offerPayoutInputs = document.querySelectorAll('.update_aff_payout');
    if (offerPayoutInputs) {
        ["keydown", "focusout"].forEach(evt => {
            offerPayoutInputs.forEach((offer) => {
                offer.addEventListener(evt, (e) => {
                    if( (evt === "keydown" && e.keyCode === 13) || evt === "focusout") {
                        const payout = e.target.value;
                        const offer = e.target.dataset.offer;
                        const rep = e.target.dataset.rep;

                        const packets = {
                            payout: payout,
                            offer_id: offer,
                            rep: rep
                        }

                        axios.post('/user/change-aff-payout', packets).then((response) => {
                            if (response.data.success) {
                                e.target.classList.add('updated_animation');

                                setTimeout(() => {
                                    e.target.classList.remove('updated_animation');
                                },3000)
                            } else {
                                document.querySelector('#error_message p').innerHTML = response.data.message;
                                document.querySelector('#error_message').classList.add('active');
                                setTimeout(() => {
                                    document.querySelector('#error_message').classList.remove('active');
                                },5000)
                            }
                        })
                    }

                })
            });
        })
    }

    const offerAccessCheck = document.querySelectorAll('.offer_access_check');
    if (offerAccessCheck) {
        offerAccessCheck.forEach((check) => {
            check.addEventListener('change', (e) => {

                const offerID = e.target.dataset.offer;
                const access = e.target.checked
                const packets = {
                    access: access,
                    rep: e.target.dataset.rep,
                    offer_id: offerID,
                }

                if(access) {
                    packets["payout"] = document.querySelector('#offer_' + offerID).value
                }

                axios.post('/user/update-offer-access', packets).then((response) => {
                    if (response.data.success) {
                        console.log("SUCCESS!");
                    } else {
                        document.querySelector('#error_message p').innerHTML = response.data.message;
                        document.querySelector('#error_message').classList.add('active');
                        setTimeout(() => {
                            document.querySelector('#error_message').classList.remove('active');
                        },5000)
                    }
                })
            })
        })
    }

});