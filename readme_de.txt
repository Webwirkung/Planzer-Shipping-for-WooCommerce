=== Shipping via Planzer for WooCommerce ===
Tags: planzer, shipping, e-commerce, store, sales, sell, woo, shop, cart, checkout, woo commerce
Requires at least: 5.7
Tested up to: 5.8.2
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Shipping via Planzer for WooCommerce frankiert Ihre Bestellungen automatisch danke der Verknüpfung zum Planzer. Ausserdem generiert es direkt einen frankierten Lieferschein oder eine Etikette. Sparen Sie Zeit und konzentrieren Sie sich auf das Wachstum Ihres Shops.

== Beschreibung ==

Alle Bestellungen, welche in Ihrem WooCommerce Onlineshop eingehen, werden mit diesem Plugin automatisch an [Planzer Paket](https://planzer-paket.ch/de/) übermittelt. Die Bestellung ist sofort in ihrem Planzer Portal ersichtlich und Ihr Paket wird von Planzer, bei Ihnen vor Ort, zur Auslieferung abgeholt.

== Vorteile im Überblick ==

* **Einfache Installation**<br />Das Plugin kann einfach installiert und mit Ihren Daten befüllt werden. Sobald das Plugin aktiviert und im Livemodus ist, holt Planzer die Pakete bei Ihnen ab.
* **Mehr Zeit für die wichtigen Dinge**<br /> Mit unserem Plugin müssen Sie keine Bestellungen mehr manuell im Planzer Portal erfassen. Damit sparen Sie Zeit und können sich um Ihr Tagesgeschäft kümmern.
* **Tiefere Fehlerquote**<br /> Da die Bestellungen automatisch gemäss der Eingabe Ihrer Kunden übertragen werden, (inkl. Liefer- und Rechnungsadressen) wird die Fehlerquote automatisch sinken.
* **QR-Code mit personalisiertem Lieferschein**<br /> Auf Wunsch wird anstelle der einfachen Etikette mit QR-Code ein personalisierter Lieferschein generiert.

== Bereits Planzer Kunde? ==

Für die Verwendung dieses Plugins müssen Sie Planzer Kunde sein. Durch den Vertrag mit Planzer erhalten Sie die notwendigen Angaben, welche Sie für die Inbetriebnahme des Plugins benötigen.

[Jetzt Planzer Kunde werden](https://planzerhelp.zendesk.com/hc/de/requests/new)

== Standard Prozess ==

1. Bestellung geht in Ihrem WooCommerce Onlineshop ein
2. Bestellung wird im WooCommerce mit Status «On hold/In Wartestellung» erstellt, falls z.B. per Vorauskasse bezahlt wird
3. Der Status der Bestellung wird auf «In Bearbeitung» umgestellt (Bei einigen Bezahlmethoden erfolgt dieser Schritt sofort, z.B. Kreditkarte)
4. Die Bestellung wird automatisch ins Planzer Portal übermittelt
5. Es wird ein QR-Code oder (wenn gewünscht) ein personalisierter Lieferschein generiert
6. Sie verpacken das Produkt und platzieren darauf die Etikette oder den Lieferschein mit QR-Code
7. Planzer holt das Paket bei Ihnen zwischen 16.00 und 18.00 Uhr ab und liefert es aus

== Inbetriebnahme & Konfiguration ==

Alle Einstellungen zum Plugin finden Sie unter «WooCommerce > Einstellungen», hier wird im rechten Bereich ein neuer Reiter «Planzer» angezeigt.
In diesem Reiter finden sie die folgenden fünf Kategorien:

= Allgemein =

* Füllen Sie mindestens die Pflichtfelder aus und speichern Sie die Änderungen
* Wählen Sie ob die Übermittlung der Bestellung an Planzer manuell passieren soll. Bei einer manuellen Übermittlung muss dann der Bestellstatus manuell auf «An Planzer übermitteln» gestellt werden.
* Definieren Sie unter «Bestellungen abholen», welche Bestellungen noch am selben Tag von Planzer abgeholt werden sollen

Ein Beispiel: Bestellungen, welche bis 12.00 Uhr im Onlineshop eingehen, werden am selben Tag um 18.00 Uhr von Planzer abgeholt und am nächsten Tag an ihren Kunden ausgeliefert. Bestellungen die nach 12.00 Uhr eingehen, werden am nächsten Tag von Planzer abgeholt


**Tipp:** Folgende Angaben finden Sie in Ihrem Planzer Vertrag:

* Abteilungsnummer
* Kundennummer
* Ihre zuständige Filiale
* Account-ID (Tab Verbindung zu Planzer)

= Verbindung zu Planzer =
* Setzen Sie während der Installation und Konfiguration des Plugins, den Haken bei «Testmodus aktivieren, dies stoppt die Übermittlung an Planzer», damit keine Daten an ihr Live-Portal übermittelt werden. Der Testmodus ist standardmässig aktiviert.
* Fügen Sie Ihre Account-ID für die korrekte Verbindung zu Planzer ein
* Entfernen Sie den Haken bei Testmodus, sobald Sie alle Anpassungen gemacht haben und die Daten ab sofort an Planzer übermittelt werden sollen

Nehmen Sie in folgenden Fällen Kontakt mit unserem [Support](mailto:support@webwirkung.ch) auf:

* Ihr Webshop wird nicht in einem der folgenden Länder gehostet: Schweiz, Liechtenstein, Deutschland, Österreich, Italien oder Frankreich
* Trotz korrekter Angaben, werden Bestellungen nicht in Ihrem Portal angezeigt

**! Nehmen Sie nie unaufgefordert Änderungen an der Server URL vor !**

= Benachrichtigungen =

* Entscheiden Sie, ob und welche Benachrichtigungen an Sie und Ihre Kunden versendet werden sollen
* Bestimmen Sie, wie Pakete ausgeliefert werden (z.b. immer Deponieren)

= Lieferschein/Etikette =

Entscheiden Sie, ob eine normale Etikette mit QR-Code oder ein personalisierter Lieferschein mit QR-Code generiert werden soll. Das gewählte Label wird an die angegebene E-Mail Adresse gesendet. Trennen Sie mehrere Empfänger mit einem Komma. Wenn Sie sich für den personalisierten Lieferschein entscheiden, müssen Sie noch folgende Angaben machen.

* Hinterlegen Sie die URL ihres Logos, dieses wird automatisch auf den Lieferschein aufgedruckt
* Geben Sie eine allgemeine Kontaktperson an, welche auf den Lieferschein aufgedruckt wird.

**Tipp:** Für einen automatischen Ausdruck Ihres Lieferscheins, geben Sie die E-Mail Adresse Ihres Druckers an.

= Produkte ausschliessen =

Sie haben einzelne Produkte, welche nicht mit Planzer versendet werden sollen?

* Wählen Sie die Produkte an, welche nicht mit Planzer versendet werden sollen (z.b. Gutscheine)
* Bestellungen mit mehreren Produkten und nur einem ausgeschlossenen Produkt, werden trotzdem an Planzer übermittelt
* Wird nur ein ausgeschlossenes Produkt bestellt, geht die Bestellung bei Ihnen ein, wird aber nicht an Planzer übermittelt

== Weitere Funktionen ==

= Status zur Bestellung und Übermittlung =

Gehen Sie im Wordpress auf die Detailansicht einer Bestellung, hier sehen Sie im rechten Bereich den Status der Übermittlung an Planzer.
Damit ist es Ihnen möglich zu sehen, ob eine Bestellung nicht übermittelt wurde und sie sehen wieso die Übermittlung nicht stattgefunden hat.

= Mehrere Lieferscheine/Etiketten pro Bestellung =

Wenn Sie eine Bestellung haben, für welche Sie mehrere Etiketten oder Lieferscheine benötigen, können Sie wie folgt zusätzliche generieren:

* Gehen Sie im WooCommerce bei der entsprechenden Bestellung in die Detailansicht
* Oben im rechten Bereich haben sie eine Selectbox für «Bestellung Aktionen»
* Wählen Sie hier «Planzer Lieferschein erstellen» aus und aktualisieren Sie die Bestellung

Dann wird Ihnen ein zusätzlicher Lieferschein oder eine zusätzliche Etikette per E-Mail zugesendet. Diese Aktion können Sie mehrmals ausführen. Ein erster Lieferschein/Etikette wird bei der ersten Übermittlung bereits generiert, für zwei Pakete pro Bestellung muss also zum Beispiel nur noch ein Zusätzlicher generiert werden.


== Frequently Asked Questions ==

= Muss eval() aktiviert sein, um das Plugin zu verwenden? =

Ja, ihr Server MUSS die Funktion `eval()` aktiviert haben - sie wird benötigt, um Daten an Planzer-Server zu senden.

= Was benötige ich um dieses Plugin zu benutzen? =

Für dieses Plugin benötigen Sie vorab einen Vertrag mit Planzer. In diesem Vertrag finden Sie dann auch alle Ihre Angaben, welche sie zur Konfiguration des Plugins brauchen. Sie haben noch keinen Vertrag? Werden Sie [Planzer Kunde](https://planzerhelp.zendesk.com/hc/de/requests/new).

= Wo kann ich die Bestellungen sehen? =

Sie können alle übermittelten Bestellungen [in ihrem Planzer Portal](https://paketversenden.planzergroup.com/myorders) sehen.

= Wieso kann ich mich nicht mit Planzer verbinden? =

Ist ihr Webshop in der Schweiz, Liechtenstein, Deutschland, Österreich, Italien oder Frankreich gehostet?
Wenn nicht, nehmen Sie bitte mit unserem [Support](mailto:support@webwirkung.ch) Kontakt auf.

= Kann ich auch nur ausgewählte Bestellungen mit Planzer versenden =

Ja, Sie können in den Plugin Einstellungen im Reiter «Allgemein» auswählen ob alle Bestellungen oder nur ausgewählte übermittelt werden sollen.

= Was passiert mit stornierten Bestellungen? =

Wird im WooCommerce eine Bestellung von ihnen oder ihrem Kunden storniert, wird dies nicht an Planzer übermittelt. Dazu müssen Sie Planzer direkt um die Löschung des Auftrags in Ihrem Planzer Portal bitten.


== Screenshots ==

1. Einstellungen Reiter «Allgemein»
2. Einstellungen Reiter «Verbindung zu Planzer»
3. Einstellungen Reiter «Benachrichtigungen»
4. Einstellungen Reiter «Lieferschein/Etikette»
5. Einstellungen Reiter «Produkte ausschliessen»


== Changelog ==

Siehe die Haupt-Readme-Datei für das Änderungsprotokoll.