=== Shipping via Planzer for WooCommerce ===
Tags: planzer, shipping, e-commerce, store, sales, sell, woo, shop, cart, checkout, woo commerce
Tested up to: 6.4.2
Stable tag: 1.0.18
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

‘Planzer Parcel Service & Plugin’ est aussi simple que cela puisse paraître : vous installez le plugin dans votre solution e-commerce WooCommerce. Vous pouvez désormais envoyer vos colis de boutique en ligne jusqu'à 30 kg via notre service colis suisse. Nous récupérons vos colis quotidiennement les jours ouvrés sur rendez-vous. Par ailleurs, nous stockons, prélevons, emballons et transportons principalement des marchandises en Suisse. Et 60 % voyagent en train écologique.

== La description ==

Planzer Parcel est exactement ce dont vous avez besoin pour votre boutique en ligne : une collecte rapide, une livraison ponctuelle, un contact amical avec le client final, une expérience de livraison inégalée.

Nous livrons des colis de différentes tailles jusqu'à 30 kg à la porte de votre destinataire en Suisse - le lendemain soir et en personne. A moins que vous ne vouliez quelque chose de plus rapide ou de plus précis. Des options supplémentaires intéressantes vous permettent d'adapter la livraison aux souhaits de votre client.

Pour fournir ce service B2C exceptionnel, nous combinons nos décennies d'expérience dans le transport et la logistique avec les nouvelles technologies et l'efficacité maximale du commerce électronique.

[Plus d'informations sur Plug & Planzer Colis](https://plug-n.planzer-paket.ch/fr/)

== Maintenant la balle est dans ton camp ==

Que vous travailliez déjà avec Planzer Parcel ou que vous soyez nouveau chez nous aujourd'hui, nous nous réjouissons de faire votre connaissance ainsi que celle de vos clients finaux.

Pour cela, nous avons besoin de quelques informations. Veuillez remplir [ce formulaire](https://plug-n.planzer-paket.ch/fr/installation-fr/#register). Nous vous contacterons immédiatement et discuterons personnellement de toutes les informations avec vous. Les coûts par envoi dépendent du nombre d'envois par semaine.

[S'inscrire maintenant](https://plug-n.planzer-paket.ch/fr/installation-fr/#register)

= Les informations nécessaires dont vous avez besoin =

* Numéro de département
* Numéro de client
* Votre branche responsable
* ID de compte (connexion de l'onglet à Planzer)

== Installation et configuration ==

Tous les paramètres du plugin se trouvent sous "WooCommerce > Paramètres", ici un nouvel onglet "Planzer" s'affiche dans la bonne zone où vous pouvez effectuer toutes les configurations.

[Plus d'informations sur le processus](https://plug-n.planzer-paket.ch/fr/installation-fr/)

== Fonctions ==
* Transmission manuelle ou automatique des commandes à Planzer
* En cas de transmission manuelle, sélectionner et transmettre plusieurs commandes via la vue en liste (Bulk-Action)
* Paramètres pour les notifications destinées à vous et à vos clients
* Générez une étiquette ou un bon de livraison personnalisé avec votre logo (les deux avec Planzer QR-Code) et envoyez-le à une adresse e-mail de votre choix
* Adapter les textes dans le bon de livraison personnalisé
* Exclure les produits qui ne doivent pas être expédiés via Planzer (par exemple, les bons)
* Exclure les modes d'expédition qui ne doivent pas être envoyés via Planzer (par exemple. enlèvement)
* Voir l'état de la commande et de la transmission
* Plusieurs bons de livraison/étiquettes par commande
* Testmode qui empêche l'envoi de commandes à Planzer

== Général ==
Contactez notre [Support](mailto:support@webwirkung.ch) dans les cas suivants :

* Votre boutique en ligne n'est pas hébergée dans l'un des pays suivants : Suisse, Liechtenstein, Allemagne, Autriche, Italie ou France.
* Malgré des informations correctes, les commandes ne s'affichent pas sur votre portail

** ! N'apportez jamais de modifications non sollicitées à l'URL du serveur !**

== Documents ==
Veuillez consulter notre référentiel Github pour consulter notre documentation complète :
https://github.com/Webwirkung/Planzer-Shipping-for-WooCommerce

Ou visitez notre page [Plug & Planzer Colis](https://plug-n.planzer-paket.ch/fr/)

== Foire Aux Questions ==

= Faut-il activer eval() pour utiliser le plugin ? =

Oui, votre serveur DOIT avoir la fonction `eval()` activée - elle est nécessaire pour envoyer des données aux serveurs Planzer.

= De quoi ai-je besoin pour utiliser ce plugin ? =

Pour ce plugin, vous avez besoin d'un contrat préalable avec Planzer. Dans ce contrat, vous trouverez toutes les informations dont vous avez besoin pour configurer le plugin. Vous n'avez pas encore de contrat ? Devenir [client Planzer](https://planzerhelp.zendesk.com/hc/en-us/requests/new)

= Où puis-je voir les commandes ? =

Vous pouvez voir toutes les commandes soumises [dans votre portail Planzer](https://paketversenden.planzergroup.com/myorders).

= Pourquoi ne puis-je pas me connecter à Planzer ? =

Votre boutique en ligne est-elle hébergée en Suisse, au Liechtenstein, en Allemagne, en Autriche, en Italie ou en France ?
Si ce n'est pas le cas, veuillez contacter notre [Support](mailto:support@webwirkung.ch).

= Puis-je également envoyer uniquement des commandes sélectionnées avec Planzer =

Oui, vous pouvez sélectionner dans les paramètres du plugin dans l'onglet "Général" si toutes les commandes ou seulement celles sélectionnées doivent être transmises.

= Que se passe-t-il avec les commandes annulées ? =

Si une commande est annulée dans WooCommerce par vous ou votre client, cela ne sera pas transmis à Planzer. Pour cela, vous devez demander directement à Planzer de supprimer la commande dans votre portail Planzer.

== Captures d'écran ==

1. onglet paramètres "Général"
2. paramètres onglet "connexion à Planzer"
3. onglet paramètres "notifications"
4. onglet paramètres "bon de livraison/étiquette"
5. onglet paramètres "exclure les produits"
6. processus


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