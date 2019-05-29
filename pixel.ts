/**
 * * Manages FB Pixel
 * TODO: Email
 */
declare var fbq: any;
declare var gtag: any;

document.addEventListener('DOMContentLoaded', function () {

    // Facebook obj
    var pixel_controller = {

        blog_view: function () {
            fbq('track', 'ViewContent');
        },

        add_to_cart: function () {
            fbq('track', 'Purchase', {
                value: 29.99,
                currency: 'GBP',
            });
        },

        order_received: function () {
            fbq('track', 'Purchase', {
                value: 29.99,
                currency: 'GBP',
            });
        },

        email_lead: function () {
            fbq('track', 'Lead');
        }
    }
    
    // Google obj
    var google_controller = {
        /**
         * Sends the event to Google Analytics
         * @param {string} action This is the action the user is doing, e.g. "play" for a video
         * @param {string} cat This is the major category for the event
         * @param {string} label This is the specific label for this event
         */
        send: function (action: string, cat: string,  label: string) {
            try {
                gtag('event', action,{
                    'event_category': cat,
                    'event_label': label
                });
            } catch (error) {
                console.error(error);
            }

        },

        /**
         * @param {string} name This should be the name of the cookie
         */
        checkCookie: function (name: string) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }
    }

    // Second Google account
    /**
     * @param {string} order_no The order number of the transaction
     */
    var google_two_controller = {
        conversion: function (order_no: string) {
            gtag('event', 'conversion', { 'send_to': 'REDACTED', 'transaction_id': order_no });
        }
    }

    // * Single post / blog
    if (document.body.classList.contains('single-post')) {
        pixel_controller.blog_view();
        if (google_controller.checkCookie("googleCookie")) {
            google_controller.send("Read blog", "Blog", "Blogs");
        }

    }

    // * Shop 'buy now' buttons
    if (document.body.classList.contains('post-type-archive-product')) {
        let button_arr = document.querySelectorAll('.product .add_to_cart_button');
        for (let this_button = 0; this_button < button_arr.length; this_button++) {
            try {
                if (button_arr[this_button] == null) {
                    break;
                } else {
                    document.querySelectorAll('.product .add_to_cart_button')[this_button].addEventListener("click", pixel_controller.add_to_cart);
                    document.querySelectorAll('.product .add_to_cart_button')[this_button].addEventListener("click", function(){
                        if (google_controller.checkCookie("googleCookie")) {
                            google_controller.send("Add to basket", "Cart", "Shop page add to cart");
                        }
                    });
                }
            } catch (error) {
                console.error(error);
                break;
            }
        }
    }

    // * Product 'buy now' buttons
    if (document.body.classList.contains("single-product")) {
        document.getElementsByClassName('single_add_to_cart_button')[0].addEventListener("click", pixel_controller.add_to_cart);
        if (google_controller.checkCookie("googleCookie")) {
            google_controller.send("Add to basket", "Cart", "Product page add to cart");
        }
    }

    // * Completed purchase
    if (document.body.classList.contains('woocommerce-order-received')) {
        pixel_controller.order_received();
        if (google_controller.checkCookie("googleCookie")) {
            let order_no = document.querySelectorAll(".woocommerce-order-overview__order strong")[0].innerHTML;
            google_two_controller.conversion(order_no);
        }
    }
}, false);