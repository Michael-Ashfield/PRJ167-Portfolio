# API runner, sends API calls and prints the result.
# Currently set up for WooCommerce API v3

print("--------------------------------------------------")
print("working")

from woocommerce import API
import json
import datetime



# Settings

keymode = "live"

version = "wc/v3"



# Vars
now = datetime.datetime.now()
diff = datetime.timedelta(days=365)
future = now + diff

live_key = "REDACTED"
live_secret = "REDACTED"
live_url = "https://www.ellactiva.co.uk"

dev_key = "REDACTED"
dev_secret = "REDACTED"
dev_url = "https://dev.ellactiva.tsadvertising.co.uk"

local_key = "REDACTED"
local_secret = "REDACTED"
local_url = "http://localhost:8888/Ellactiva"

if keymode == "live":
    c_key = live_key
    c_secret = live_secret
    c_url = live_url
elif keymode == "dev":
    c_key = dev_key
    c_secret = dev_secret
    c_url = dev_url
elif keymode == "local":
    c_key = local_key
    c_secret = local_secret
    c_url = local_url


# API connection
wcapi = API(
    url=c_url,
    consumer_key=c_key,
    consumer_secret=c_secret,
    wp_api=True,
    version=version,
    query_string_auth = True
)

# Functions
def coupon():
    data = {
        "code": 'DEVTESTCOUPON5',
        "description": "A unique 10% signup discount",
        "date_expires": future.strftime("%Y-%m-%d %H:%M"),
        "discount_type": "percent",
        "amount": "10",
        "individual_use": False,
        "exclude_sale_items": False,
        "excluded_product_ids": [1769, 1775, 1776],
        "usage_limit_per_user": 1,
        "product_categories": [23]
    }
    print(wcapi.post("coupons", data).json())

def complete_order(ordernum):
    data = {
        "status": "completed"
    }
    order_find = "orders/" + ordernum

    print(wcapi.put(order_find, data).json())


# Control
complete_order("4528")