# DHLApi plugin for CakePHP

## About

Das Plugin ermöglicht das Anfragen des SOAP artigen DHL API.

## Installation

Das Plugin wird aus unserem bitbucket geladen und nicht mit composer (da wir kein lokales packagist haben).
Die composer.json des gewünschten Projekts bitte folgendermaßen erweitern: (Achtung BITBUCKETNAME durch eigenen Accountnamen erstzen.)

```
"require" : {
    "gringlas/cakephp-dhlapi" : "dev-master"
},
"config" : {
    "secure-http" : false
},
"repositories" : [
    {
        "type" : "vcs",
        "url" : "http://BITBUCKETNAME@jira.phihochzwei.com:7990/scm/cpp/dhlapi.git"
    }
]
```

Danach wie gewohnt 

```
composer update
```
