WooCommerce Gift Aid
====================

A simple addon for WooCommerce that allows the store to collect GiftAid consent from their customers.

Description
-----------
This addon for WooCommerce adds new field to the checkout screen for the customer to give their Gift Aid consent.
Each product can have the Gift Aid option enabled on it individually. For example a store may only have a few products
applicable to Gift Aid, in which case the Gift Aid option can be enabled for those specific products and not the others.

When a customer completes their order and consent for Gift Aid is given, that order is essentially marked so admin users
or shop keepers can easily see which orders have given consent for Gift Aid.

All the content shown to the customer on the checkout page is customisable in the settings.


Installation
------------
1.  Copy the `woocommerce-gift-aid` folder into your `wp-content/plugins` folder
2.  Activate the WooCommerce Gift Aid plugin via the plugins admin page
3.  That's all for installation!


User Guide
----------
Firstly, go to edit the product(s) you want to be claiming Gift Aid on and scroll down to the Product Data options.
You will see the checkbox option "Enable Gift Aid" under the Advanced product data options.
Checking that checkbox means that if a customer goes to the checkout with that product in their basket, they will be
asked if they're happy for their Gift Aid to be claimed.

The message shown to the customer at checkout is fully customisable. You can configure it in the WooCommerce settings
under the Products tab (`/wp-admin/admin.php?page=wc-settings&tab=products&section=gift_aid`).

Admin users viewing the WooCommerce Orders list can see a check mark next to the orders that have given consent for Gift Aid.
The WooCommerce Orders list can also be filtered so it only shows orders with Gift Aid consent given.


Credits
-------
This plugin is built by Rajiv Lodhia (https://rajivlodhia.com)


Disclaimer
----------
This plugin does not gather any data about the customer. It is the sole responsibility of the store using this plugin to
make sure their customers are eligible to claim Gift Aid on a purchase. It is also the sole responsibility of the store
to ensure their customers understand what giving consent for Gift Aid means.

According to the www.gov.uk (https://www.gov.uk/claim-gift-aid/gift-aid-declarations), a full Gift Aid declaration must
include the donor's full name, the donor's home address and an agreement from the donor to Gift Aid being claimed.

This plugin only handles the agreement from the donor. This plugin assumes that your store is already getting the user's
full name and home address as part of the billing information.
