<?php
namespace Moneybird\Exception;

class BaseException extends \Exception {

    /**
     * @var string
     */
    protected $_field;

    /**
     * @return string
     */
    public function getField() {
        return $this->_field;
    }

    /**
     * @param string $field
     */
    public function setField($field) {
        $this->_field = (string)$field;
    }

}
