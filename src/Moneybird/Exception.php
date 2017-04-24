<?php

namespace Moneybird;

class Exception extends \Exception {

    /**
     * @var string
     */
    protected $fields = [];

    /**
     * @return string
     */
    public function getFields() {
        return $this->fields;
    }

    /**
     * @param string $errorField
     * @param string $errorMessage
     */
    public function setField($errorField, $errorMessage) {
        $this->fields[$errorField] = (string)$errorMessage;
    }



}
