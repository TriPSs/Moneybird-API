<?php

namespace Moneybird\Object;

class Product extends BaseObject {

    public $id;

    public $administration_id;

    public $description;

    public $price;

    public $currency;

    public $frequency;

    public $frequency_type;

    public $tax_rate_id;

    public $ledger_account_id;

    public $created_at;

    public $updated_at;

}
