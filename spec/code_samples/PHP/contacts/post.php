use Moneybird\Object\Contact as MoneybirdContact;

// Create a new contact
$moneybirdContact = new MoneybirdContact();
$moneybirdContact->company_name = "Test B.V.";  // Required
$moneybirdContact->email = "test@bv.nl";
$moneybirdContact->address1 = "Street 15";
$moneybirdContact->city = "City";
$moneybirdContact->zipcode = "1234AA";
$moneybirdContact->phone = "0612345678";
$moneybirdContact->firstname = "John";
$moneybirdContact->lastname = "Doe";

$moneybirdContact = $moneybirdClient->contacts->create($moneybirdContact);