<?php
namespace Moneybird\Resource;

class Undefined extends ResourceBase {

    /**
     * @return \stdClass
     */
    protected function getResourceObject() {
        return new \stdClass();
    }

}
