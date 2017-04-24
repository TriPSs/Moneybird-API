<?php

namespace Moneybird\Resource;

use Moneybird\Exception;
use Moneybird\Object\BaseObject;
use Moneybird\Object\Contact;
use Moneybird\Object\Payment;
use Moneybird\Object\SalesInvoice;

/**
 * Class SalesInvoices
 *
 * @package Moneybird\Resource
 * @method SalesInvoice create($data = NULL, array $filters = [])
 */
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
     * @var Payment
     */
    private $payment;

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
     * Set a payment for the invoice
     *
     * @param Payment $payment
     *
     * @return $this
     */
    public function setPayment(Payment $payment) {
        $this->payment = $payment;

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
        if (empty($invoice) && empty($this->invoice)) {
            throw new Exception("No valid sales invoice given!");
        }

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

        return $this->restUpdate($this->getResourcePath(), "send_invoice", [ "sales_invoice_sending" => $body ]);
    }

    /**
     * Registers a payment
     *
     * @param SalesInvoice|string $invoice
     *
     * @return bool|object
     * @throws Exception
     */
    public function registerPayment($invoice = NULL) {
        if (empty($invoice) && empty($this->invoice)) {
            throw new Exception("No valid sales invoice given!");
        }

        $this->with(empty($invoice) ? $this->invoice : $invoice);

        if (empty($this->payment)) {
            throw new Exception("No valid payment given!");
        }

        return $this->restUpdate($this->getResourcePath(), "register_payment", [ $this->payment->getKey() => $this->payment->toArray() ]);
    }

}
