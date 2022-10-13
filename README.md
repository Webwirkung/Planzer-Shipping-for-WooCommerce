# Shipping via Planzer for WooCommerce
Tags: planzer, shipping, e-commerce, store, sales, sell, woo, shop, cart, checkout, woo commerce
Tested up to: 6.0 Stable
tag: 1.0.8
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

## Overview
Shipping via Planzer for WooCommerce automatically franks your orders thanks to the connection to Planzer. Besides, it directly generates a delivery bill or label. Save time and focus on growing your store.

### Advantages at a glance

* **Easy installation**<br />The plugin can be easily installed and filled with your data. Once the plugin is activated and in live mode, Planzer will pick up the packages from you.
* **More time for the important things**<br />With our plugin, you no longer have to manually enter orders in the Planzer portal. This saves you time and allows you to take care of your daily business.
* **Lower error rate**<br /> Since the orders are automatically transferred according to the input of your customers, (including shipping and billing addresses) the error rate will automatically decrease.
* **QR code with personalized delivery note**<br /> On request, a personalized delivery bill is generated instead of the simple label with QR code.

### Already a Planzer customer?

To use this plugin you must be a Planzer customer. By signing a contract with Planzer you will receive the necessary information you need to start using the plugin.

[Become a Planzer customer now](https://planzerhelp.zendesk.com/hc/en-us/requests/new)

**Tip:** You will find the following information in your Planzer contract:

* Department number
* Customer number
* Your responsible branch
* Account ID (tab connection to Planzer)

## Standard process
The default process if not chosen differently (manual transmission) the process will be the following:

1. Order is placed in your WooCommerce online store
2. Order is created in WooCommerce with status "On hold/In waiting", if e.g. payment is made by prepayment.
3. The status of the order is changed to "In process" (for some payment methods this step is done immediately, e.g. credit card)
4. The order is automatically transferred to the Planzer portal
5. A label or (if desired) a personalized delivery note (both with QR code) is generated
6. You pack the product and place the label or delivery note on it
7. Planzer picks up the package from you between 4:00 and 6:00 p.m. and delivers it

## Setup & Configuration

All settings for the plugin can be found under "WooCommerce > Settings", here a new tab "Planzer" is displayed in the right area.
In this tab you will find the following five categories:

### General

* Fill in at least the mandatory fields and save the changes
* Select if the transmission of the order to Planzer should happen manually. In case of manual transmission, the order status must then be manually set to "Transmit to Planzer" to transmit the order to Planzer
* Define under "Pick up orders" which orders should be picked up by Planzer on the same day.

For example: orders received in the online store before 12.00 noon will be picked up by Planzer at 6.00 p.m. on the same day and delivered to their customers the next day. Orders received after 12.00 o'clock will be picked up by Planzer the next day.

### Connection to Planzer (FTP)
* During the installation and configuration of the plugin, check "Enable test mode, this stops the transmission to Planzer" so that no data is transmitted to your live portal. The test mode is enabled by default.
* Add your account ID for proper connection to Planzer
* Uncheck Test Mode once you have made all the adjustments and you want the data to be sent to Planzer from now on.

Contact our [Support](mailto:support@webwirkung.ch) in the following cases:

* Your webshop is not hosted in one of the following countries: Switzerland, Liechtenstein, Germany, Austria, Italy or France.
* Despite correct configuration, orders are not displayed in your portal

**! Never make unsolicited changes to the server URL !**

### Notifications

* Decide if and which notifications should be sent to you and your customers.
* Determine how packages are delivered (e.g. always deposit)

### Delivery note/label

Decide whether a normal label with QR code or a personalized delivery note with QR code should be generated. The selected label will be sent to the specified email address. Separate multiple recipients with a comma. If you choose the personalized delivery note, you need to provide the following information.

* Enter the URL of your logo, it will be automatically printed on the delivery note
* Specify a general contact person, which will be printed on the delivery note

**Tip:** For an automatic printout of your delivery note, enter the e-mail address of your printer.

### Exclude products

You have individual products that should not be shipped with Planzer?

* Select the products that should not be sent with Planzer (e.g. vouchers).
* Orders with multiple products and only one excluded product will still be sent to Planzer
* If only one excluded product is ordered, you will receive the order but it will not be sent to Planzer.

## Other functions

### Status of the order and transmission

Go to the detailed view of an order in WordPress, here you can see the status of the transmission to Planzer in the right area.
This allows you to see if an order has not been transmitted and why the transmission has not taken place.

### Multiple delivery notes/labels per order

If you have an order for which you need multiple labels or delivery bills, you can generate additional ones as follows:

* In WooCommerce, go to the detail view for the corresponding order.
* At the top of the right area you have a selectbox for "Order actions"
* Select here "Create Planzer delivery note" and update the order

Then an additional delivery bill or label will be sent to you by e-mail. You can perform this action several times. A first delivery note/label is already generated during the first transmission, so for two packages per order, for example, only one additional delivery note/label needs to be generated.


## Frequently Asked Questions

### Must eval() be enabled to use the plugin?
Yes, your server MUST have the function `eval()` enabled - it is needed to send data to Planzer servers.

### What do I need to use this plugin?
For this plugin you need a contract with Planzer in advance. In this contract you will find all the information you need to configure the plugin.
You don't have a contract yet? Become a [Planzer customer](https://planzerhelp.zendesk.com/hc/en-us/requests/new)

### Where can I see the orders?
You can see all submitted orders [in your Planzer Portal](https://paketversenden.planzergroup.com/myorders).

### Why can't I connect to Planzer?
Is your webshop hosted in Switzerland, Liechtenstein, Germany, Austria, Italy or France?
If not, please contact our [Support](mailto:support@webwirkung.ch).

### Can I also send only selected orders with Planzer
Yes, you can select in the plugin settings in the "General" tab whether all orders or only selected ones should be transmitted.

### What happens with cancelled orders?
If an order is cancelled in WooCommerce by you or your customer, this will not be transmitted to Planzer. For this you have to ask Planzer directly to delete the order in your Planzer portal.

## Changelog

**0.0.1 2021-11-02**

* First plugin BETA release

**0.0.2 2021-11-29**

* Fix Company name and company extra on PDF note
* Fix QR code generation
* Change branch input to select
* Change translation setup to work for all German languages
* Change notifications setup
* Fix translations and typos, change various labels to be more clear
* Add "Manual transmission" feature - for disabling sending data automatically to Planzer on "Processing" order status
* Change/update content of data sent to Planzer
* Change test mode setup
* Change delivery note folder structure
* Add the possibility to choose the type of note - delivery note, label note, or none
* Add label note
* Fix wrong sequence number in QR and PDF/CSV
* Remove pickup date settings
* Add more info to order notes

**1.0.0 2021-12-09**

* First "full" release
* Change plugin name/slug
* Remove pickup data from CSV
* Update labels and translations
* Add toggle switcher field type to settings
* Change FTP test mode checkbox to toggle switcher
* Coding standard improvements

**1.0.1 2021-12-22**

* Fix folder naming
* Add PHP and WooCommerce checks
* Update readme - add _de translation

**1.0.2 2021-12-29**

* Fix company name when the shipping address is filled

**1.0.3 2022-02-18**

* Fix label PDF fields usage
* Use sender email in PDF footer instead WP admin email
* Fix hour detection for end of day

**1.0.4 2022-03-10**

* Fix weekend detection for pickup and delivery dates

**1.0.5 2022-05-20**

* Add customer note (if not empty) to delivery note PDF.
* Change QR code size on delivery note.

**1.0.6 2022-06-15**

* Change QR code size on the label note.
* Change the orientation page to landscape on the label note
* Change HTML structure in the label note
* Change page margin on the delivery note

**1.0.7 2022-08-02**

* Bugfix connected with wrong/empty SKU for variant products in the delivery note

**1.0.8 2022-10-13**

* Bugfix with wrong transmission data