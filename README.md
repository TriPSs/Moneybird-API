# Moneybird API client for PHP

> Moneybird API client for PHP

## Getting started

Requiring the included autoloader. If you're using Composer, you can skip this step.

```php
	require "Moneybird/Autoloader.php";
```
	
Initializing the Moneybird API client, and setting your access key and administration id.

```php
	$moneybirdClient = new Client;
	$moneybirdClient->setAccessToken("YOUR_ACCESS_TOKEN")
                    ->setAdministrationID("YOUR_ADMINISTRATION_ID");
```

##### Examples:
```php
// Get all invoices of last year
$moneybirdClient->salesInvoices->all(["filter" => "prev_year"]);

// Get all invoices of a contact
$moneybirdClient->salesInvoices->all([ "filter" => "contact_id:123456789101112234" ]);
```
## Development

If you'd like to contribute to this project, all you need to do is clone [this repo](https://github.com/TriPSs/Moneybird-API) 

## [License](https://github.com/TriPSs/Moneybird-API/blob/master/LICENSE)

> Internet Systems Consortium license
> ===================================
>
> The MIT License (MIT)
>  
> Copyright (c) 2015 David Zukowski
>  
> Permission is hereby granted, free of charge, to any person obtaining a copy
> of this software and associated documentation files (the "Software"), to deal
> in the Software without restriction, including without limitation the rights
> to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
> copies of the Software, and to permit persons to whom the Software is
> furnished to do so, subject to the following conditions:
>  
> The above copyright notice and this permission notice shall be included in all
> copies or substantial portions of the Software.
>  
> THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
> IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
> FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
> AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
> LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
> OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
> SOFTWARE.

## Collaboration

If you have questions or issues, please [open an issue](https://github.com/TriPSs/Moneybird-API/issues)!