<?php
namespace Moneybird\Resource;

use Moneybird\Object\Product;

class Products extends ResourceBase {

    /**
     * @return Product
     */
    protected function getResourceObject() {
        return new Product;
    }

}
