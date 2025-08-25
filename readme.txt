=== Shipping via Planzer for WooCommerce ===
Tags: planzer, shipping, e-commerce, store, sales, sell, woo, shop, cart, checkout, woo commerce
Tested up to: 6.8.2
Stable tag: 1.0.30
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

‘Planzer Parcel Service & Plugin’ is as straightforward as it sounds: you install the plugin into your WooCommerce e-commerce solution. You can now send your online store parcels up to 30 kg via our Swiss parcel service. We collect your parcels daily on working days by appointment. Incidentally, we primarily store, pick, pack and transport goods within Switzerland. And 60% travel by eco-friendly rail.

== Description ==

Planzer Parcel is exactly what you need for your online store: quick collection, punctual delivery, friendly end customer contact, an all-round unparalleled delivery experience.

We deliver parcels of various sizes up to 30 kg to your recipient’s door in Switzerland – by the following evening. Unless you would like something quicker or more specific. Interesting additional options let you tailor the delivery to your customer’s wishes.

To provide this outstanding B2C service, we combine our decades of experience in transport and logistics with new technologies and maximum e-commerce efficiency.

[More informationen about Plug & Planzer Parcel](https://plug-n.planzer-paket.ch/en/)

== Now the ball’s in your court ==

Whether you already work with Planzer Parcel or are new to us today, we look forward to getting to know you and your end customers.

For this we need some information. Please fill out [this form](https://plug-n.planzer-paket.ch/en/installation-en/#register). We will contact you immediately and discuss all the information with you personally. The costs per shipment depend on the number of shipments per week.

[Register now](https://plug-n.planzer-paket.ch/en/installation-en/#register)

= Necessary information you need =

* Department number
* Customer number
* Your responsible branch
* Account ID (tab connection to Planzer)

== Setup & Configuration ==

All settings for the plugin can be found under "WooCommerce > Settings", here a new tab "Planzer" is displayed in the right area where you can make all configurations.

[More information about the process](https://plug-n.planzer-paket.ch/en/installation-en/)

== Functions ==
* Manually or automatic transmission of orders to Planzer
* In case of manual transmission, select and transmit multiple orders via the list view (bulk action)
* Settings for notifications to you and your customers
* Generate a label or a personalized delivery note with your logo (both with Planzer QR-Code) and send it to an email address of your choice
* Customize texts in the personalized delivery note
* Exclude products which should not be shipped via Planzer (e.g. Vouchers)
* Exclude shipping methods that should not be shipped via Planzer (e.g. Vouchers)
* View the status of the order and transmission
* Multiple delivery notes/labels per order
* Testmode that prevents sending orders to Planzer

== General ==
Contact our [Support](mailto:support@webwirkung.ch) in the following cases:

* Your webshop is not hosted in one of the following countries: Switzerland, Liechtenstein, Germany, Austria, Italy or France.
* Despite correct information, orders are not displayed in your portal

**! Never make unsolicited changes to the server URL !**

== Documentation ==
Please check out our Github Repository to view our full documentation:
https://github.com/Webwirkung/Planzer-Shipping-for-WooCommerce

Or visit our page [Plug & Planzer parcel](https://plug-n.planzer-paket.ch/en/)

== Frequently Asked Questions ==

= Must eval() be enabled to use the plugin? =

Yes, your server MUST have the function `eval()` enabled - it is needed to send data to Planzer servers.

= What do I need to use this plugin? =

For this plugin you need a contract with Planzer in advance. In this contract you will find all the information you need to configure the plugin. You don't have a contract yet? Become a [Planzer customer](https://planzerhelp.zendesk.com/hc/en-us/requests/new)

= Where can I see the orders? =

You can see all submitted orders [in your Planzer Portal](https://paketversenden.planzergroup.com/myorders).

= Why can't I connect to Planzer? =

Is your webshop hosted in Switzerland, Liechtenstein, Germany, Austria, Italy or France?
If not, please contact our [Support](mailto:support@webwirkung.ch).

= Can I also send only selected orders with Planzer =

Yes, you can select in the plugin settings in the "General" tab whether all orders or only selected ones should be transmitted.

= What happens with cancelled orders? =

If an order is cancelled in WooCommerce by you or your customer, this will not be transmitted to Planzer. For this you have to ask Planzer directly to delete the order in your Planzer portal.

== Screenshots ==

1. settings tab "General"
2. settings "connection to Planzer" tab
3. settings tab "notifications"
4. settings tab "delivery note/label"
5. settings tab "exclude products"
6. process


== Changelog ==

= 0.0.1 2021-11-02 =

* First plugin BETA release

= 0.0.2 2021-11-29 =

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

= 1.0.0 2021-12-09 =

* First "full" release
* Change plugin name/slug
* Remove pickup data from CSV
* Update labels and translations
* Add toggle switcher field type to settings
* Change FTP test mode checkbox to toggle switcher
* Coding standard improvements

= 1.0.1 2021-12-22 =

* Fix folder naming
* Add PHP and WooCommerce checks
* Update readme - add _de translation

= 1.0.2 2021-12-29 =

* Fix company name when the shipping address is filled

* Fix company name when the shipping address is filled

= 1.0.3 2022-02-18 =

* Fix label PDF fields usage
* Use sender email in PDF footer instead WP admin email
* Fix hour detection for end of day

= 1.0.4 2022-03-10 =

* Fix weekend detection for pickup and delivery dates

= 1.0.5 2022-05-20 =

* Add customer note (if not empty) to delivery note PDF.
* Change QR code size on delivery note.

= 1.0.6 2022-06-15 =

* Change QR code size on the label note.
* Change the orientation page to landscape on the label note
* Change HTML structure in the label note
* Change page margin on the delivery note

= 1.0.7 2022-08-02 =

* Bugfix connected with wrong/empty SKU for variant products in the delivery note

= 1.0.8 2022-10-13 =

* Bugfix with wrong transmission data

= 1.0.9 2022-11-07 =

* Add FR translation
* Add new margins to the delivery note.

= 1.0.10 2022-12-21 =

* Remove fully refunded items from the delivery note.

= 1.0.11 2023-01-26 =

* Remove from delivery note products that are refunded AND have a price of 0.
* Make all default texts on the delivery note editable.
* Add the order number to the delivery note.
* Add the action for transmitting orders to Planzer to Bulk actions on the list view.
* Make shipping with planzer dependent on the shipping option.
* Add a text field for the deposit notice.

= 1.0.12 2023-02-01 =

* Add a text for the deposit notice in receive section.

= 1.0.13 2023-04-12 =

* Prevent generating two packages with the same package number.

= 1.0.14 2023-05-08 =

* Update the carbon library

= 1.0.15 2023-08-01 =

* Add new branches: Kölliken, Winterthur
* New delivery option: Saturday delivery
* New delivery option: Additional service for guaranteed delivery time: Next day, Delivery by 10 o'clock, Delivery by 12 o'clock

= 1.0.16 2023-10-17 =

* Bugfix connected with wrong data type in in_array function.

= 1.0.17 2023-10-30 =

* Bugfix connected with wrong data type in in_array function in the delivery note template.
* Check compatibility with WordPress 6.4

= 1.0.18 2024-01-10 =

* Replace QR code API generator source
* Check compatibility with WordPress 6.4.2

= 1.0.19 2024-01-31 =

* Fix package number generator feature.
* Check compatibility with WordPress 6.4.3

= 1.0.20 2024-02-07 =

* Bugfix related with sequence number value in the database.

= 1.0.21 2024-02-19 =

* Description correction

= 1.0.22 2024-04-22 =

* Test mode generates a demo delivery note and sends it with [TEST MODE] in subject line
* Delivery note template path is now filterable to allow custom templates
* Custom field "planzer_tracking_code" with the tracking code is set in the order

= 1.0.23 2024-05-09 =

* Fix fatal error when bulk update orders

= 1.0.24 2024-08-06 =

* Fix "Excluded shippings" function

= 1.0.25 2024-12-12 =

* Compatibility changes with WordPress 6.7.1 features

= 1.0.26 2024-12-30 =

* Fixed a security issue in input handling.
* This is an important update. Please update to the latest version immediately.

= 1.0.27 2025-01-24 =

* Added: exclusion service with centrally managed exclusion rules
* Added: shipping class based exclusion rule, respecting the WC calculation settings
* Improved: admin notes on why an order was excluded
* Fix: Doesn't set the order status to Planzer if excluded

= 1.0.28 2025-02-13 =

* Added HPOS compatibility

= 1.0.29 2025-03-03 =

* Added Tracking link support

= 1.0.30 2024-08-25 =

* Fix bulk action feature