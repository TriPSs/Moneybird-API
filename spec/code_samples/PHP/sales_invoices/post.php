use Moneybird\Object\SalesInvoice as MoneybirdSalesInvoice;
use Moneybird\Object\Contact as MoneybirdContact;
use Moneybird\Object\Detail as MoneybirdDetail;

// Create the invoice
$nInvoice = new MoneybirdSalesInvoice();
$nInvoice->reference = "Invoice created with Moneybird API Client for PHP";

// Create products
$productOne = new MoneybirdDetail();
$productOne->description = "First product";
$productOne->price = 10.5;

$productTwo = new MoneybirdDetail();
$productTwo->description = "Second product";
$productTwo->price = 15;

// Add products to invoice
$nInvoice->addDetail($productOne);
$nInvoice->addDetail($productTwo);

// OR
$nInvoice->addDetails([ $productOne, $productTwo ]);

// Add a contact to the invoice
$contact = new MoneybirdContact()
$contact->id = 12345678;

$nInvoice->addContact($contact);

// OR

$nInvoice->addContact("12345678");

// Create the invoice
$moneybirdInvoice = $moneybirdClient->salesInvoices->create($nInvoice)