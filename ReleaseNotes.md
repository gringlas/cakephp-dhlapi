# Release Notes

## 1.2.0 (21.03.2018)

* Wenn die DHLApi mit einem Fehler antwortet und die Antwort als Fehler gekenntzeichnet wird, schreibt die Funktion
DHLAPIRequest::errorRequestAndResponseToFile() den Request und die Response in eine xml Datein in logs/ap_errors

## 1.3.0 (13.06.2019)

* Das Versandlabel liefert "na", wenn die Felder data['contact'] oder data['phone'] leer sein sollten.

## 1.4.0 (09.07.2019)

* Beim Versandlabel kam es vor, dass nach der Einbindung ins MY dort Praxennamen mit einem "&" Zeichen einen xml Fehler
erzeugten. Daher wird nun im ShipmentDHLApiRequest::ensureData() für data['praxis'] das "&" in ein und umgewandelt.

## 1.5.0 (29.08.2019)

* Die replaceUmlauts Funktion wurde um den Gedankenstrich (–) durch Bindestrich zu ersetzen (-)
* Das Shipmentlabel wendet die replaceUmlauts Funktion auf phone und conact an
