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

    const userOfferCapCheck = document.querySelectorAll('.enable_offer_cap')
    if(userOfferCapCheck) {
        userOfferCapCheck.forEach((check) => {
            check.addEventListener('change', (e) => {
                const offerID = e.target.dataset.offer;
                const user = e.target.dataset.rep;

                const packets = {
                    offer_id: offerID,
                    rep: user,
                    status: e.target.checked
                }

                axios.post('/user/enable-user-offer-cap', packets).then((response) => {
                    if (response.data.success) {
                        console.log("SUCCESS!");
                    } else {
                        document.querySelector('#error_message p').innerHTML = response.data.message;
                        document.querySelector('#error_message').classList.add('active');
                        setTimeout(() => {
                            document.querySelector('#error_message').classList.remove('active');
                        },5000)
                    }
                });
            });
        })
    }

    const userOffCap = document.querySelectorAll('.user_offer_cap')
    if(userOffCap) {
        ["keydown", "focusout"].forEach(evt => {
            userOffCap.forEach((cap) => {
                cap.addEventListener(evt, (e) => {
                    if( (evt === "keydown" && e.keyCode === 13) || evt === "focusout") {

                        const packets = {
                            offer_id: e.target.dataset.offer,
                            rep: e.target.dataset.rep,
                            cap: e.target.value
                        }

                        axios.post('/user/set-user-offer-cap', packets).then((response) => {
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
                        });
                    }
                });
            })
        })
    }

    const payoutOptionsRadio = document.querySelectorAll('input[type="radio"][name="payout_type"]');
    if(payoutOptionsRadio.length > 0) {
        const form = document.querySelector('#submit_payment_details');
        payoutOptionsRadio.forEach((radio) => {
            radio.addEventListener('change', (e) => {
                const payoutType = e.target.value;
                form.classList.remove('wise');
                form.classList.remove('paypal');
                if (payoutType === 'wise') {
                    setLabel("wise");
                    setPayoutText("wise");
                    form.classList.add('wise');
                }
                if (payoutType === 'paypal') {
                    setLabel("paypal");
                    setPayoutText("paypal");
                    form.classList.add('paypal');
                }
            })
        })

        const radioValue = document.querySelector('input[name="payout_type"]:checked').value;

        if (radioValue === "wise") {
            setLabel("wise");
            setPayoutText("wise");
            form.classList.add('wise');
        }

        if(radioValue === "paypal") {
            setLabel("paypal");
            setPayoutText("wise");
            form.classList.add('paypal');
        }
    }

    const payoutCountry = document.querySelector('#payout_country');
    if(payoutCountry) {
        const country = payoutCountry.dataset.value;
        if(country) {
            payoutCountry.value = country;
        }
    }

    const cancelPayoutUpdate = document.querySelector('#cancel_payout_update');
    if(cancelPayoutUpdate) {
        cancelPayoutUpdate.addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelector('.current_payout_details').classList.remove('hidden');
            document.querySelector('#update_payout_form').classList.add('hidden');
        })
    }

    const changeDetailsLink = document.querySelector('#update_details_link');
    if(changeDetailsLink) {
        changeDetailsLink.addEventListener('click', (e) => {
            e.preventDefault();
            document.querySelector('.current_payout_details').classList.add('hidden');
            document.querySelector('#update_payout_form').classList.remove('hidden');
        })
    }

    const alertMessage = document.querySelector('.alert');
    if (alertMessage) {
        setTimeout(() => {
            alertMessage.classList.add('hidden');
        }, 5000);
    }

    const payoutStatusButtons = document.querySelectorAll('.payout_status_button');
    if (payoutStatusButtons.length > 0) {
        payoutStatusButtons.forEach((button) => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const logID = e.target.dataset.log;
                markStatusPaid(logID, button);
            })
        })
    }
    const editPayoutType = document.querySelectorAll('.edit_payout_detail');
    if (editPayoutType.length > 0) {
        editPayoutType.forEach((link) => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                e.target.parentElement.classList.add('hidden');
                e.target.parentElement.parentElement.querySelector('.input_field').classList.add('active');
            })
        })
    }

    const cancelPayoutType = document.querySelectorAll('.cancel_payout_detail');
    if (cancelPayoutType.length > 0) {
        cancelPayoutType.forEach((link) => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                e.target.parentElement.parentElement.querySelector('.current_details').classList.remove('hidden');
                e.target.parentElement.classList.remove('active');
            })
        })
    }


    function removeFromString(text, valueToRemove) {
        const index = text.indexOf(valueToRemove);

        if (index === -1) {
            return text; // Value not found, return original string
        }

        return text.substring(0, index);
    }

    function setLabel(payoutType) {
        let value = "";
        let placeholder = "";
        if(payoutType === 'wise') {
            value = "<h5>Enter the @Wisetag, Email, Or Phone Number associated with your Wise account and select the country you are from:</h5>";
            placeholder = "@Wisetag, Email, Or Phone Number";
        }
        if(payoutType === 'paypal') {
            value = "<h5>Enter your PayPal email address you want us to send payments to and select the country you are from:</h5>";
            placeholder = "PayPal Email Address";
        }
        const payoutInputLabel = document.querySelector('label[for="payout_id"]');
        const inputElement = document.querySelector('#payout_id');
        payoutInputLabel.innerHTML = value;
        inputElement.placeholder = placeholder;
    }

    function setPayoutText(payoutType) {
        let html = '';
        if (payoutType === 'wise') {
            html = "<h4>Get paid directly to your bank account!</h4>" +
                "<p class='mb-2'>Don't have a Wise Account? You will need to:</p>" +
                "<ol>" +
                    "<li>" +
                        "<p>Create an account at <a class='text-decoration-underline' target='_blank' href='https://wise.com/register'>Wise.com</a></p>" +
                    "</li>" +
                    "<li>" +
                        "<p>Prove your identity.</p>" +
                    "</li>" +
                    "<li>" +
                        "<p>Link your bank.</p>" +
                    "</li>" +
                    "<li>" +
                        "<p>Add $20 from the bank account you link to wise for verification.</p>" +
                    "</li>" +
                    "<li>" +
                        "<p>Get paid directly to your bank!</p>" +
                    "</li>" +
                "</ol>";
        }
        if (payoutType === 'paypal') {
            html = "<p>Don't have a PayPal Account? <a target='_blank' href='https://www.paypal.com/us/webapps/mpp/account-selection'>Click Here To Sign Up Now!</a></p>";
        }

        document.querySelector('.payout_text').innerHTML = html;
    }

    function markStatusPaid(logID, element) {

        axios.post('/report/payout/update-status/' + logID)
        .then((response) => {
            if (response.data.success) {
                element.removeAttribute('href');
                element.setAttribute('aria-disabled', 'true');
                element.parentElement.parentElement.innerHTML = "paid";
            } else {
                document.querySelector('#error_message p').innerHTML = response.data.message;
                document.querySelector('#error_message').classList.add('active');
                setTimeout(() => {
                    document.querySelector('#error_message').classList.remove('active');
                },5000)
            }
        });
    }

});