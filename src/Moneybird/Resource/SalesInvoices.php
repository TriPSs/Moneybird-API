<?php

namespace Moneybird\Resource;

use Moneybird\Exception;
use Moneybird\Object\BaseObject;
use Moneybird\Object\Contact;
use Moneybird\Object\SalesInvoice;

class SalesInvoices extends ResourceBase {

    /**
     * @var string
     * @example 2017-02-22
     */
    private $scheduleDate;

    /**
     * @var Contact
     */
    private $contact;

    /**
     * @var SalesInvoice
     */
    private $invoice;

    /**
     * @return SalesInvoice
     */
    protected function getResourceObject() {
        return new SalesInvoice;
    }

    /**
     * Set the contact to send the invoice to
     *
     * @param Contact $contact
     *
     * @return $this
     */
    public function setContact(Contact $contact) {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Set the sales invoice to send
     *
     * @param SalesInvoice $invoice
     *
     * @return $this
     */
    public function setInvoice(SalesInvoice $invoice) {
        $this->invoice = $invoice;

        return $this;
    }

    public function setScheduleDate($scheduleDate) {
        $this->scheduleDate = $scheduleDate;
    }

    /**
     * Send a invoice to the contact
     *
     * @param SalesInvoice|string $invoice
     *
     * @return bool|object
     * @throws Exception
     */
    public function send($invoice = NULL) {
        if (empty($invoice) && empty($this->invoice))
            throw new Exception("No valid sales invoice id given!");

        $this->with(empty($invoice) ? $this->invoice : $invoice);

        $body = [];
        if (!empty($this->contact)) {
            if (empty($this->contact->email))
                throw new Exception("Contact has no email address!");

            if (empty($this->contact->delivery_method))
                throw new Exception("Contact has no delivery method!");

            $body[ "email_address" ]   = $this->contact->email;
            $body[ "delivery_method" ] = $this->contact->delivery_method;
        }

        if (!empty($this->scheduleDate)) {
            $body[ "sending_scheduled" ] = TRUE;
            $body[ "invoice_date" ]      = $this->scheduleDate;
        }

        return $this->restUpdate($this->getResourcePath(), "send_invoice", $body);
    }

}
