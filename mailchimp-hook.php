<?php
/**
 * This function controls the mailchimp webhook for users who subscribe.
 *
 * This creates a wordpress REST endpoint and runs a set of functions which make a coupon
 * using Woocommerce Classes, then emails the coupon using wp_mail to the user
 *
 */

// Adds custom POST endpoint
add_action('rest_api_init', function () {
    register_rest_route('REDACTED', 'REDACTED', array(
        'methods' => 'POST',
        'callback' => 'mailchimpDecode',
    ));
});
// Adds custom GET endpoint
add_action('rest_api_init', function () {
    register_rest_route('REDACTED', 'REDACTED', array(
        'methods' => 'GET',
        'callback' => 'mailchimpGet',
    ));
});

// Returns 200 on get request
function mailchimpGet($data)
{
    $id = $data['id'];
    if ($id == 0) {
        echo "204"; // Returns no content
    } else {
        echo "403"; // Returns unauthorised
    }
}

// Main function
function mailchimpDecode(WP_REST_Request $request)
{
    $parameters = $request->get_body_params();
    $param = $request['data'];
    $id = $request['id'];
    if ($id == 0) { // Check URL ID
        echo "200"; // Returns a OK header
        $email = (string)$param['email']; // Gets the email from request
        if(!empty($email)){
            $userDiscountCode = getDiscountCode(); // Creates a discount code
            createWooCoupon($userDiscountCode, $email); // Creates a coupon
            mailCoupon($userDiscountCode, $email); // Emails the discount code to the user
        }
        else {
            echo "500 - Email not found";
        }
    } else {
        echo "403"; // Returns unauthorised
    }
}

// Creates a discount code (vowels removed to avoid accidental rude words being generated)
function getDiscountCode()
{
    $length = 8;
    return substr(str_shuffle(str_repeat($x = '0123456789bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ', ceil($length / strlen($x)))), 1, $length);
}

// Creates the coupon on Woocommerce
function createWooCoupon($userDiscountCode, $email)
{
    // Coupon settings
    $coupon_code = $userDiscountCode; // Code
    $amount = 10; // Amount
    $discount_type = 'percent'; // Type: fixed_cart, percent, fixed_product, percent_product
    $description = 'Unique signup discount';
    $individual_use = true; // Can be used in conjunction with 3 for 2
    $exclude_sale_items = false; // Applies to items already on sale
    $usage_limit_per_user = 1; // Customer can only use this particular coupon once
    $usage_count = 1; // Coupon can only be used once
    $excluded_product_ids = [2053, 1775, 1776]; // Doesn't work on giftcards
    $product_categories = [23]; // Only applies to collagen& category
    $email_restrictions = [$email]; // Array - Applies the coupon only to the signed up user

    // Creates a new coupon object
    $wc_coupon = new WC_Coupon($coupon_code);

    // Set the coupon data
    $wc_coupon->set_code($coupon_code);
    $wc_coupon->set_amount(floatval($amount));
    $wc_coupon->set_discount_type($discount_type);
    $wc_coupon->set_description($description);
    $wc_coupon->set_individual_use($individual_use);
    $wc_coupon->set_exclude_sale_items($exclude_sale_items);
    $wc_coupon->set_usage_limit_per_user($usage_limit_per_user);
    $wc_coupon->set_usage_limit($usage_count);
    $wc_coupon->set_excluded_product_ids($excluded_product_ids);
    $wc_coupon->set_product_categories($product_categories);
    $wc_coupon->set_email_restrictions($email_restrictions);

    // SAVE the coupon
    $wc_coupon->save();
}

// Sends an email to the user
function mailCoupon($userDiscountCode, $email)
{
    $to = $email;
    $subject = 'Thank you for subscribing!';
    $body = emailTemplate($userDiscountCode);
    $headers[] = 'Content-Type: text/html';
    $headers[] = 'charset=UTF-8';
    $headers[] = 'From: Ellactiva <info@ellactiva.co.uk>';
    $headers[] = 'Bcc: dev@tandsadvertising.co.uk';

    wp_mail($to, $subject, $body, $headers);
}

// Is the template for the email
function emailTemplate($userDiscountCode)
{
    $email_template = <<<EOT
    <head>
    <title>Share your thoughts</title>
</head>

<body style="margin: 0 auto;">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width">
    <!--[if (gte mso 9)|(IE)]>
        <style type="text/css">
            a { 
                font-weight: normal !important;
                text-decoration: none !important;
            }
            h1 {font-family: Arial, sans-serif !important; 
                font-weight: 100;
            }
            p {
                font-weight: 400;
            }
        </style>
    <![endif]-->
    <style>
        h1 {
            color: #c8b180;
            text-shadow: none;
        }

        h2 {
            color: #c8b180;
        }

        h3 {
            color: #c8b180;
        }

        img {
            margin: 0 auto;
            max-width: 600px;
            display: block;
        }

        @media only screen and (max-width: 600px) {

            .container,
            table,
            .container .column {
                width: 100% !important;
            }

            .container {
                padding-left: 2px;
                padding-right: 2px;
            }

            .column {
                display: block;
            }

            .product-table {
                width: 95% !important;
                margin: 0 auto;
            }

            .main-img,
            .pack-shot {
                width: 100% !important;
            }

            .for-life {
                width: 70% !important;
                max-width: 300px;
            }

            .power-up {
                width: 85% !important;
            }
        }
    </style>
    <div
        style="display:none; font-size:1px; color:#c8b180; line-height:1px; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;max-height:0px; max-width:0px; opacity:0; overflow:hidden; mso-hide:all;">
        Thank you for subscribing to Ellactiva, here is our gift to you
    </div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0"
        style="border-top: solid 15px #c8b180; color: #c1c3c1; margin: 0 auto;">
        <tr>
            <td style="padding-left: 0; padding-top: 0; padding-right: 0; padding-bottom: 0;" width="600">
                <table class="container" width="600" border="0" cellspacing="0" cellpadding="0"
                    style="margin: 0 auto; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif; max-width: 600px;">
                    <tr>
                        <td>
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center">
                                        <table width="98%" border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td>
                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <td align="center">
                                                                <!--[if gte MSO 9]>
                                                                    <table width="300"><tr><td>
                                                                <![endif]-->
                                                                <table width="100%" border="0" cellspacing="0"
                                                                    cellpadding="0" style="max-width: 300px;">
                                                                    <tr>
                                                                        <td align="center"
                                                                            style="padding-top: 50px; padding-bottom: 50px;">
                                                                            <img src="https://www.ellactiva.co.uk/wp-content/uploads/2018/10/Ellactiva_forLife_one_line-1.png"
                                                                                alt="Ellactiva - for life"
                                                                                class="for-life" width="300" />
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                                <!--[if gte MSO 9]>
                                                                    </td></tr></table>
                                                                <![endif]-->
                                                            </td>
                                                        </tr>
                                                    </table>

                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <td align="center" style="padding-bottom: 20px;">
                                                                <h1
                                                                    style="text-align: center; font-size: 80px; font-weight: 100; line-height: 100px; letter-spacing: 7px; margin-bottom: 0; color: #c8b180; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
                                                                    THANK YOU</h1>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td align="center" style="padding-bottom: 10px;">
                                                                <h2
                                                                    style="text-align: center; font-size: 32px; margin: 0; font-weight: 500; line-height: 40px; color: #c8b180; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
                                                                    FOR SUBSCRIBING</h2>
                                                            </td>
                                                        </tr>
                                                    </table>

                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <td align="center" style="padding-bottom: 40px;">
                                                                <p
                                                                    style="color: #c8b180; font-size: 24px; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
                                                                    Here is your code for 10% off your next purchase:
                                                                </p>
                                                                <p bgcolor="#f8f8f8"
                                                                    style="color: #c8b180; font-size: 24px; font-family:'Lucida Console', Monaco, monospace">
                                                                    $userDiscountCode
                                                                </p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td align="center" style="padding-bottom: 40px;">
                                                                <p style="width: 65% !important; color: #636363; font-weight: 300; line-height: 26px; font-size: 17px; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;"
                                                                    width="390">
                                                                    You will now be the first to hear about the latest news in beauty and wellness, our exclusive offers, expert advise and more!</p>
                                                            </td>
                                                        </tr>
                                                    </table>

                                                    <hr style="margin-top: 30px; margin-bottom: 30px; width: 80%;">

                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <td align="center" width="70%">
                                                                <p
                                                                    style="color: #636363; font-weight: 300; line-height: 26px; font-size: 17px; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
                                                                    Learn more about the nutritional ways you can
                                                                    power-up your beauty and wellness regime at</p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td align="center">
                                                                <a href="www.ellactiva.co.uk" title="Ellactiva Homepage"
                                                                    style="text-decoration: none;">
                                                                    <p
                                                                        style="font-size: 28px; color: #c8b180; margin-top: 10px; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
                                                                        www.ellactiva.co.uk</p>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </table>

                                                    <hr style="margin-top: 50px; margin-bottom: 80px; width: 80%;">

                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <td align="center">
                                                                <p
                                                                    style="width: 90%; color: #636363; font-weight: 300; line-height: 11px; font-size: 12px; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
                                                                    This coupon can not be used in conjunction with our 3 for 2 offer.</p>
                                                                <p
                                                                    style="width: 90%; color: #636363; font-weight: 300; line-height: 11px; font-size: 12px; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
                                                                    This coupon is limited to this email address only and cannot be traded or exchanged.</p>
                                                                <p
                                                                    style="width: 90%; color: #636363; font-weight: 300; line-height: 11px; font-size: 12px; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
                                                                    For full details read our <a href=“https://www.ellactiva.co.uk/terms-of-sale/” title=“Terms of sale”>Terms of Sale</a></p>
                                                                <p
                                                                    style="width: 70%; color: #636363; font-weight: 300; line-height: 26px; font-size: 12px; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
                                                                    Oxford Pharmascience Limited, London BioScience Innovation Centre, 2 Royal College Street, London, NW1 0NH</p>
                                                                <p
                                                                    style="width: 70%; color: #636363; font-weight: 300; line-height: 26px; font-size: 12px; font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;">
                                                                    Ellactiva - <a href="https://ellactiva.us14.list-manage.com/unsubscribe?u=8114cdf53848cadf665e75124&id=ac6875981c"
                                                                        title="Unsubscribe">Unsubscribe</a></p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
EOT;
    return $email_template;
};