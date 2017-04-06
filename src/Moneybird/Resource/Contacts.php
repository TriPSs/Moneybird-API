<?php
namespace Moneybird\Resource;

use Moneybird\Object\Contact;

class Contacts extends ResourceBase {

    /**
     * @return Contact
     */
    protected function getResourceObject() {
        return new Contact;
    }

}
