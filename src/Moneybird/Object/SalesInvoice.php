<?php

namespace Moneybird\Object;

class SalesInvoice {

    /**
     * The invoice has been paid.
     */
    const STATUS_PAID = "paid";

    public $id;

    public $administration_id;

    public $contact_id;

    public $contact;

    public $invoice_id;

    public $recurring_sales_invoice_id;

    public $workflow_id;

    public $document_style_id;

    public $identity_id;

    public $draft_id;

    public $state;

    public $invoice_date;

    public $due_date;

    public $payment_conditions;

    public $reference;

    public $language;

    public $currency;

    public $discount;

    public $original_sales_invoice_id;

    public $paid_at;

    public $sent_at;

    public $created_at;

    public $updated_at;

    public $details;

    public $payments;

    public $total_paid;

    public $total_unpaid;

    public $total_unpaid_base;

    public $prices_are_incl_tax;

    public $total_price_excl_tax;

    public $total_price_excl_tax_base;

    public $total_price_incl_tax;

    public $total_price_incl_tax_base;

    public $url;

    public $custom_fields;

    public $notes;

    public $attachments;

    public $events;

    public function isPaid() {
        return $this->state === self::STATUS_PAID;
    }
}
