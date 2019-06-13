# Release Notes

## 1.2.0 (21.03.2018)

* Wenn die DHLApi mit einem Fehler antwortet und die Antwort als Fehler gekenntzeichnet wird, schreibt die Funktion
DHLAPIRequest::errorRequestAndResponseToFile() den Request und die Response in eine xml Datein in logs/ap_errors

## 1.3.0 (13.06.2019)

* Das Versandlabel liefert "na", wenn die Felder data['contact'] oder data['phone'] leer sein sollten.