<?php
namespace Moneybird\Resource;

use Moneybird\Object\SalesInvoice;

class SalesInvoices extends ResourceBase {

    /**
     * @return SalesInvoice
     */
    protected function getResourceObject() {
        return new SalesInvoice;
    }

    protected function getResourcePath() {
        return "sales_invoices" . self::API_EXTENSION;
    }

}
