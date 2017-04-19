<?php

namespace Moneybird\Object;

class SalesInvoice extends BaseObject {

    /**
     * The invoice status.
     */
    const STATUS_PAID    = "paid";
    const STATUS_DRAFT   = "draft";
    const STATUS_OVERDUE = "late";
    const STATUS_OPEN    = "open";

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

    /**
     * Is this invoice open
     *
     * @return bool
     */
    public function isOpen() {
        return $this->state === self::STATUS_OPEN;
    }

    /**
     * Is this invoice over due
     *
     * @return bool
     */
    public function isOverDue() {
        return $this->state === self::STATUS_OVERDUE;
    }

    /**
     * Is this invoice a draft
     *
     * @return bool
     */
    public function isDraft() {
        return $this->state === self::STATUS_DRAFT;
    }

    /**
     * Is this invoice paid
     *
     * @return bool
     */
    public function isPaid() {
        return $this->state === self::STATUS_PAID;
    }

    /**
     * Adds a contact to the invoice
     *
     * @param Contact|integer $contact
     *
     * @return $this
     */
    public function addContact($contact) {
        if ($contact instanceof Contact) {
            $this->contact_id = $contact->id;

        } else {
            $this->contact_id = $contact;
        }

        return $this;
    }

    /**
     * Adds a detail row to the invoice
     *
     * @param $detail
     *
     * @return $this
     */
    public function addDetail(Detail $detail) {
        $this->details[] = $detail->toArray();

        return $this;
    }

    /**
     * Adds multiple detail rows to the invoice
     *
     * @param $details
     *
     * @return $this
     */
    public function addDetails(array $details) {
        foreach ($details as $detail) {
            $this->addDetail($detail);
        }

        return $this;
    }
}
