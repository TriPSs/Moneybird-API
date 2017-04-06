// Get all invoices of last year
$moneybirdClient->salesInvoices->all(["filter" => "prev_year"]);

// Get all invoices of a contact
$moneybirdClient->salesInvoices->all([ "filter" => "contact_id:123456789101112234" ]);