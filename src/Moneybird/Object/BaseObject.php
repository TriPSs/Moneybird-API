<?php

namespace Moneybird\Object;

abstract class BaseObject {

    /**
     * Converts all the filled vars to a array
     *
     * @return array
     */
    public function toArray() {
        $array     = [];
        $variables = get_object_vars($this);

        foreach ($variables as $key => $value) {
            if (!empty($value) && !is_null($value)) {
                if (is_array($value)) {
                    foreach ($value as $child) {
                        if ($child instanceof BaseObject) {
                            $array[ "{$key}_attributes" ][] = $child->toArray();
                        } else {
                            $array[ "{$key}_attributes" ][] = $child;
                        }
                    }
                } else {
                    $array[ $key ] = $value;
                }
            }
        }

        return $array;
    }

    /**
     * Turns the class name into the required key for Moneybird
     *
     * @return string
     */
    public function getKey() {
        $classParts = explode("\\", get_class($this));
        $clazz      = end($classParts);

        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $clazz, $matches);
        $ret = $matches[ 0 ];

        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode('_', $ret);
    }

}

