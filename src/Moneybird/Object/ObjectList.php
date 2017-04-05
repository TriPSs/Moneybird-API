<?php

namespace Moneybird\Object;

class ObjectList extends \ArrayObject {

    /**
     * Page
     *
     * @var int
     */
    public $page;

    /**
     * Max number or rows the result could have
     *
     * @var int
     */
    public $per_page;

    /**
     * Filters who where used in the request
     *
     * @var array
     */
    public $filters;

}
