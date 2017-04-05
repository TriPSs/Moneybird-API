# Moneybird API client for PHP

```php
require './src/Moneybird/Autoloader.php';

$moneybirdClient = new MoneybirdClient();
$moneybirdClient->setAccessToken("YOUR_ACCESS_TOKEN")
                ->setAdministrationID("YOUR_ADMINISTRATION_ID");

// Get all invoices from last year
$moneybirdClient->salesInvoices->all(["filter" => "prev_year"]);
```