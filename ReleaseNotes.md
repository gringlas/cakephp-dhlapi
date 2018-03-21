# Release Notes

## 1.2.0 (21.03.2018)

* Wenn die DHLApi mit einem Fehler antwortet und die Antwort als Fehler gekenntzeichnet wird, schreibt die Funktion
DHLAPIRequest::errorRequestAndResponseToFile() den Request und die Response in eine xml Datein in logs/ap_errors
