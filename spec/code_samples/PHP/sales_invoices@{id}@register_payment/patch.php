// Create Payment
$moneybirdPayment = new MoneybirdPayment();
$moneybirdPayment->price = 200;
$moneybirdPayment->payment_date = date("Y-m-d");

// Register the Payment
$moneybirdClient->salesInvoices->setPayment($moneybirdPayment)
                ->registerPayment("invoice ID" || $invoice);