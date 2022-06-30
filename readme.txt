=== Shipping via Planzer for WooCommerce ===
Tags: planzer, shipping, e-commerce, store, sales, sell, woo, shop, cart, checkout, woo commerce
Tested up to: 6.0
Stable tag: 1.0.5
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

‘Planzer Parcel Service & Plugin’ is as straightforward as it sounds: you install the plugin into your WooCommerce e-commerce solution. You can now send your online store parcels up to 30 kg via our Swiss parcel service. We collect your parcels daily on working days by appointment. Incidentally, we primarily store, pick, pack and transport goods within Switzerland. And 60% travel by eco-friendly rail.

== Description ==

Planzer Parcel is exactly what you need for your online store: quick collection, punctual delivery, friendly end customer contact, an all-round unparalleled delivery experience.

We deliver parcels of various sizes up to 30 kg to your recipient’s door in Switzerland – by the following evening and in person. Unless you would like something quicker or more specific. Interesting additional options let you tailor the delivery to your customer’s wishes.

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
* Settings for notifications to you and your customers
* Generate a label or a personalized delivery note with your logo (both with Planzer QR-Code) and send it to an email address of your choice
* Exclude products which should not be shipped via Planzer (e.g. Vouchers)
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
