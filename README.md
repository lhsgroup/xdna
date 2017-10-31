# EasAdmin Pro Doc

# Funzionamento del pre-routing

il file entrypoint è bootstrap.php, all'interno è possibile definire i plugin abilitati e la loro sequenza di avvio

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
xdna\core\plugin::enable("debug");
xdna\core\preRouter::init($_SERVER['REQUEST_URI']); //$_SERVER['REDIRECT_URL']
```
a seconda dell'architettura di PHP del server, è necessario individuare la variabile corretta che identifica l'URI

```
http://localhost:8080/THIS/IS/YOUR/URI
```

in genere la variabile da leggere è  

```php
$_SERVER['REQUEST_URI']
```
ma in alcune architetture, specialmente con nginx che fa da proxy, la variabile da passare come parametro è 

```php
$_SERVER['REDIRECT_URL']
```

all'interno della directory principale c'è un file .xdna che contine ele configurazioni di tutta l'appicazione

```
[database]
db_host = {db_host}
db_name = {db_name}
db_user = {db_user}
db_password = {db_password}

[application]

apps[/] = home
apps[/test] = test
```

apps è un array chiave valore che identifica il path iniziale (chiave) e la directori dell'applicazione di destinazione, in questo caso il path / è mappato sulla directory /apps/home/
quindi tutte le richieste fatte con /ANY/PATH vengono gestite dall'applicazione home

* nota bene
il path / è il path di default che viene eseguito quando non vivne trovata nessuna corrispondenza con altri path, difatti 
```
http://YOURHOST/test/mypage
```
anche se inizia con /, il sistema di pre_routing da precedenza all'applicazione /test dichiarata in .xdna, passando dunque la richiesta all'applicazione test

