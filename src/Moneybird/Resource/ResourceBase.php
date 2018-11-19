<?php

namespace Moneybird\Resource;

use Moneybird\Client;
use Moneybird\Exception;
use Moneybird\Object\BaseObject;

abstract class ResourceBase {

    const REST_CREATE = Client::HTTP_POST;
    const REST_UPDATE = Client::HTTP_PATCH;
    const REST_READ   = Client::HTTP_GET;
    const REST_LIST   = Client::HTTP_GET;
    const REST_DELETE = Client::HTTP_DELETE;

    /**
     * Default number of objects to retrieve when listing all objects.
     */
    const DEFAULT_PER_PAGE = 50;

    /**
     * Default page number.
     */
    const DEFAULT_PAGE = 1;

    /**
     * @var Client
     */
    protected $api;

    /**
     * @var string
     */
    protected $resourcePath;

    /**
     * @var array|null
     */
    protected $child = [];

    /**
     * @param Client $api
     */
    public function __construct(Client $api) {
        $this->api = $api;

        if (empty($this->resourcePath)) {
            $classParts         = explode("\\", get_class($this));
            $this->resourcePath = $this->fromCamelCase(end($classParts));
        }
    }

    /**
     * @param array $filters
     *
     * @return string
     * @throws Exception
     */
    private function buildQueryString(array $filters) {
        if (empty($filters)) {
            return "";
        }

        // Force & because of some PHP 5.3 defaults.
        return "?" . http_build_query($filters, "", "&");
    }

    /**
     * @param string $restResource
     * @param        $body
     * @param array  $filters
     *
     * @return object
     * @throws Exception
     */
    private function restCreate($restResource, $body, array $filters) {
        $result = $this->performApiCall(
            self::REST_CREATE,
            $restResource,
            $this->buildQueryString($filters),
            $body
        );

        if ($this->api->getLastHttpResponseStatusCode() === Client::HTTP_ENTITY_CREATED) {
            return $this->copy($result, $this->getResourceObject());
        }

        return $result;
    }

    /**
     * Retrieves a single object from the REST API.
     *
     * @param string $restResource Resource name.
     * @param string $id           Id of the object to retrieve.
     * @param array  $filters
     *
     * @return object|boolean
     * @throws Exception
     */
    private function restRead($restResource, $id, array $filters) {
        if (empty($id)) {
            throw new Exception("Invalid resource id.");
        }

        $id     = urlencode($id);
        $result = $this->performApiCall(
            self::REST_READ,
            "{$restResource}/{$id}",
            $this->buildQueryString($filters)
        );

        return $result ? $this->copy($result, $this->getResourceObject()) : FALSE;
    }

    /**
     * Sends a DELETE request
     *
     * @param string $restResource
     * @param string $id
     *
     * @return boolean
     * @throws Exception
     */
    private function restDelete($restResource, $id) {
        if (empty($id)) {
            throw new Exception("Invalid resource id.");
        }

        $id = urlencode($id);
        $this->performApiCall(
            self::REST_DELETE,
            "{$restResource}/{$id}"
        );

        if ($this->api->getLastHttpResponseStatusCode() === Client::HTTP_ENTITY_DELETED) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Sends a PATCH request
     *
     * @param string $restResource
     * @param string $update This can ether a resource id or for example 'send_invoice', in the case of 'send_invoice'
     *                       the id has been put in front of the recourse
     * @param string $body
     *
     * @return object|boolean
     * @throws Exception
     */
    protected function restUpdate($restResource, $update, $body = NULL) {
        if (empty($update)) {
            throw new Exception("Invalid resource id.");
        }

        $update  = urlencode($update);
        $encoded = !is_null($body) ? json_encode($body) : $body;
        $result  = $this->performApiCall(
            self::REST_UPDATE,
            "{$restResource}/{$update}",
            NULL,
            $encoded
        );

        return $result ? $this->copy($result, $this->getResourceObject()) : FALSE;
    }

    /**
     * Get a collection of objects from the REST API.
     *
     * @param       $restResource
     * @param array $filters
     * @param int   $page
     * @param int   $perPage
     *
     * @return array
     */
    private function restList($restResource, array $filters, $page = self::DEFAULT_PAGE, $perPage = self::DEFAULT_PER_PAGE) {
        $filters = array_merge([
            "page"     => $page,
            "per_page" => $perPage,
        ], $filters);

        $result = $this->performApiCall(self::REST_LIST, $restResource, $this->buildQueryString($filters));

        $collection = [];
        foreach ($result as $dataResult) {
            $collection[] = $this->copy($dataResult, $this->getResourceObject());
        }

        return $collection;
    }

    /**
     * Copy the results received from the API into the PHP objects that we use.
     *
     * @param object $apiResult
     * @param object $object
     *
     * @return object|bool
     */
    protected function copy($apiResult, $object) {
        if (is_string($apiResult)) {
            return TRUE;
        }

        foreach ($apiResult as $property => $value) {
            if (is_object($value) || is_array($value)) {
                if (is_object($value)) {
                    $className = "Moneybird\\Object\\" . ucfirst($property);

                    if (class_exists($className)) {
                        $object->$property = $this->copy($value, new $className);
                    }

                } else if (is_array($value)) {
                    $className = "Moneybird\\Object\\" . ucfirst(substr($property, 0, -1));

                    if (class_exists($className)) {
                        foreach ($value as $valueObject) {
                            $object->$property[] = $this->copy($valueObject, new $className);
                        }
                    }

                } else {
                    $object->$property = $value;
                }

            } else {
                $object->$property = $value;
            }
        }

        return $object;
    }

    /**
     * Get the object that is used by this API. Every API uses one type of object.
     *
     * @return object
     */
    abstract protected function getResourceObject();

    /**
     * Create a resource with the remote API.
     *
     * @param BaseObject|array $data An array|BaseObject containing details on the resource. Fields supported depend on
     *                               the resource created.
     * @param array            $filters
     *
     * @return object
     * @throws Exception
     */
    public function create($data = NULL, array $filters = []) {
        if ($data instanceof BaseObject) {
            $data = [ $data->getKey() => $data->toArray() ];
        }

        $encoded = json_encode($data);

        return $this->restCreate($this->getResourcePath(), $encoded, $filters);
    }

    /**
     * Retrieve information on a single resource from Moneybird.
     *
     * Will throw a Exception if the resource cannot be found.
     *
     * @param string $resourceID
     * @param array  $filters
     *
     * @return object
     * @throws Exception
     */
    public function get($resourceID, array $filters = []) {
        return $this->restRead($this->getResourcePath(), $resourceID, $filters);
    }

    /**
     * Delete a single resource from Moneybird.
     *
     * Will throw a Exception if the resource cannot be found.
     *
     * @param string $resourceID
     *
     * @return bool
     * @throws Exception
     */
    public function delete($resourceID) {
        return $this->restDelete($this->getResourcePath(), $resourceID);
    }

    /**
     * Retrieve all objects of a certain resource.
     *
     * @param array $filters
     * @param int   $page
     * @param int   $perPage
     *
     * @return array
     */
    public function all(array $filters = [], $page = self::DEFAULT_PAGE, $perPage = self::DEFAULT_PER_PAGE) {
        return $this->restList($this->getResourcePath(), $filters, $page, $perPage);
    }

    /**
     * Perform an API call, and interpret the results and convert them to correct objects.
     *
     * @param      $httpMethod
     * @param      $apiMethod
     * @param      $queryString
     * @param null $httpBody
     *
     * @return object
     * @throws Exception
     */
    protected function performApiCall($httpMethod, $apiMethod, $queryString = "", $httpBody = NULL) {
        $body = $this->api->performHttpCall($httpMethod, $apiMethod, $queryString, $httpBody);

        if ($this->api->getLastHttpResponseStatusCode() == Client::HTTP_ENTITY_NOT_FOUND) {
            return NULL;
        }

        if (empty($body)) {
            throw new Exception("Unable to decode Moneybird response: '{$body}'.");
        }

        $object = @json_decode($body);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new Exception("Unable to decode Moneybird response: '{$body}'.");
        }

        if (!empty($object->error)) {
            $exception = new Exception("Error executing API call: {$object->error}.");

            if (!empty($object->errors)) {
                foreach ($object->errors as $errorKey => $errorMessage) {
                    $exception->setField($errorKey, $errorMessage);
                }
            }

            throw $exception;
        }

        $this->clear();

        return $object;
    }

    /**
     * @param string $resourcePath
     */
    public function setResourcePath($resourcePath) {
        $this->resourcePath = strtolower($resourcePath);
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function getResourcePath() {
        if (count($this->child) > 0) {
            $childString = implode("/", $this->child);

            return "{$this->resourcePath}/{$childString}";
        }

        return $this->resourcePath;
    }

    /**
     * @param string $child
     *
     * @return $this
     */
    public function withChild($child) {
        if (!in_array($child, $this->child)) {
            $this->child[] = $child;
        }

        return $this;
    }

    /**
     * Set the resource to use a certain parent. Use this method before performing a get() or all() call.
     *
     * @param string|object $child An object with an 'id' property
     *
     * @return $this
     */
    public function with($child) {
        return $this->withChild($child instanceof BaseObject ? $child->id : $child);
    }

    /**
     * Turns the class name into the required path name for Moneybird
     *
     * @param $clazz
     *
     * @return string
     */
    private function fromCamelCase($clazz) {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $clazz, $matches);
        $ret = $matches[ 0 ];

        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode('_', $ret);
    }

    /**
     * Clears some vars after the request
     */
    private function clear() {
        $this->child = [];
    }
}
