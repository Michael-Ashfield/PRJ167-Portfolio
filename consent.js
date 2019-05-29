/* Disables google analytic cookies if the EU cookie consent is not set */


//Cookie settings
var cookieLength = "30"; //Value is days before expiry
var cookieDomain = ".tsadvertising.co.uk";
var cookieList = []; //List is added to via pushing cookie objects

//Cookie class
function Cookie(name, title, state) {
    //data
    this.name = name;
    this.title = title;
    this.state = state; //default false when object is created
    this.setCookie = function (onOff) { //onOff must be a bool true/false
        setCookie(this.name, onOff, cookieLength); //Adds a cookie
    }
}

//Cookie Objects - ADD NEW COOKIES HERE

//Google Analytics
var google = new Cookie("googleCookie", "Google Analytics", true);
google.enable = function () {
    var gCode = ` 
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="REDACTED"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'REDACTED');
            gtag('config', 'REDACTED');
        </script> `;
    jQuery('head').append(gCode);
}
google.trackingOn = function () {
    window['REDACTED'] = false; //Enables tracking cookies
    console.log('Google Analytics cookie enabled');
    this.state = true;
}
google.trackingOff = function () {
    window['REDACTED'] = true; //Disables tracking cookies
    console.log("Google Analytics cookie disabled, please refresh your browser to implement these changes.");
    this.state = false;
}
cookieList.push(google);

//Facebook Pixel (Can't toggle to off once it is on)
/*var facebook = new Cookie("facebookCookie", "Facebook Pixel");
[DELETED EXTRA COOKIE USED TO EXIST HERE]
*/


//Cookie controlling functions
function setCookie(name, value, days) { //Sets value as a STRING
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function eraseCookie(name) {
    document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;, path=/;';
}


//Checks if cookie is set, if so it will update the checkbox
//Runs on page load
//tracking cookies ARE NOT SET until this runs, so after they have moved page or refreshed.
jQuery(function () {
    var i;
    for (i = 0; i < cookieList.length; i++) {
        var myCookie = getCookie(cookieList[i].name);
        if (myCookie == 'true') { // String because that's how it's returned
            jQuery("#" + cookieList[i].name + "").prop('checked', true);
            cookieList[i].enable();
            cookieList[i].trackingOn();
        } else {
            if (myCookie == null) { //Cookie does not exist
                cookieList[i].setCookie("true");
                showCookieBar(); //Shows the cookie bar if any preference cookie is missing
            } else {
                cookieList[i].trackingOn();
            }
        }
    }
});


// This function will show the cookie bar and modal if no cookies are detected
var isSet = false;

function showCookieBar() {
    if (isSet === false) {
        isSet = true; //Runs once, never twice, on a single render.
        var cookieBar =
            `<div class="cookie-bar full-width centered align-center white orange-background">
                <p>This website uses cookies to give you the best experience possible, click here to adjust your cookie settings</p>
                <p>You can view more about our Cookies on our <a href="cookies">Cookies Page</a> and our <a href="privacy-policy">Privacy Policy</a>.</p>
                <div class="button" id="accept_all_button">Accept all <i class="fas fa-check"></i></div>
                <div class="button" id="open_cookie_modal">Customize cookies <i class="fas fa-plus"></i></div>
                <div class="button close_cookie_bar">Close <i class="fas fa-times"></i></div>
            </div>`;

        var outputSwitchers = function () {
            var collection = []; //Stores loop output to be Concatenated back 
            var i; //Loops through cookieList outputting buttons for each cookie
            /**
             * 
             * @param {object} cookieState 
             * Takes the cookie state and returns 'checked' if the cookie is on
             */
            function isChecked(cookieState) {
                if (cookieState == true) {
                    return "checked";
                }
            }
            for (i = 0; i < cookieList.length; i++) {
                var cookieSwitcher = `
                    <div class='cookie-controls'>
                        <p>` + cookieList[i].title + `</p>
                        <div class="switch large">
                            <input class="switch-input ` + cookieList[i].name + `"  id="` + cookieList[i].name + `" ` + isChecked(cookieList[i].state) + ` type="checkbox" name="exampleSwitch">
                            <label class="switch-paddle" for="` + cookieList[i].name + `">
                                <span class="show-for-sr">Enable cookie?</span>
                                <span class="switch-active" aria-hidden="true">On</span>
                                <span class="switch-inactive" aria-hidden="true">Off</span>
                            </label>
                        </div>
                    </div>
                    `;
                collection.push(cookieSwitcher);
            }
            return collection.join(' '); //Concatenated by space
        }

        var modalFull =
            `
            <div id="cookieModal" class="all-width centered">
                <div class="close_modal modal_close_x clickable">
                    <span><i class="fas fa-times"></i></span>
                </div>
                <h3>Cookie settings</h3>
                <p>Please adjust your cookie settings.</p>
    
                <div class="button close_modal">
                    <span aria-hidden="true">Save & Close</span>
                </div>
            </div>
            ` +
            cookieBar;

        jQuery(".modal_container").slideUp();
        jQuery(".modal_container").append(modalFull);
        jQuery("#cookieModal p").after(outputSwitchers);
        jQuery("#cookieModal").slideUp();
        jQuery(".modal_container").slideDown();
        jQuery(".cookie-bar").slideDown();
        jQuery('html, body').animate({
            scrollTop: 0
        }, 'fast');

        jQuery("#open_cookie_modal").click(function () {
            jQuery("#cookieModal").slideToggle();
            jQuery("#open_cookie_modal .fa-plus").toggleClass("fa-minus");
            jQuery('html, body').animate({
                scrollTop: 0
            }, 'fast');
        });

        jQuery(".close_cookie_bar").click(function () {
            jQuery(".cookie-bar").slideUp();
            jQuery("#cookieModal").slideUp();
        });

        jQuery(".close_modal").click(function () {
            jQuery("#cookieModal").slideUp();
            jQuery("#open_cookie_modal .fa-plus").toggleClass("fa-minus");
        });

        jQuery("#accept_all_button").click(function () {
            jQuery(".cookie-bar").slideUp();
            jQuery("#cookieModal").slideUp();
            handleAcceptAll();
        });

        addClickFunction();
    }
}


//Updates and controls the switch button, adds event listener then runs handleCookieToggle to run
//Checks for cookies page
var pathSet = false;
jQuery(".switch-input").click(function () {
    if (pathSet === false) {
        pathSet = true; //Only run once
        var pathname = window.location.pathname;
        if (pathname == "/cookies/") {
            addClickFunction();
            handleCookieToggle(jQuery(this).attr('id'));
        }
    }
});

//Adds click functionality AFTER switchers render
var clickSet = false;

function addClickFunction() {
    if (clickSet === false) {
        clickSet = true; //can only be added once
        jQuery(".switch-input").click(function () {
            handleCookieToggle(jQuery(this).attr('id'));
        });
    }
}

function handleCookieToggle(name) {
    for (var i = 0; i < cookieList.length; i++) {
        if (name == cookieList[i].name) {
            name = cookieList[i]; //Sets name to name object
            if (cookieList[i].state == false) {
                cookieList[i].setCookie("true");
                cookieList[i].trackingOn();
            } else {
                cookieList[i].setCookie("false");
                cookieList[i].trackingOff();
            }
        }
    }
}


//Accepts all cookies
function handleAcceptAll() {
    for (var i = 0; i < cookieList.length; i++) {
        cookieList[i].setCookie("true");
        cookieList[i].trackingOn();
    }
}