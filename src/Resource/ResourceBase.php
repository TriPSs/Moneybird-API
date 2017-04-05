<?php

namespace Moneybird\Resource;

use Moneybird\Client;
use Moneybird\Exception;

abstract class ResourceBase {

    const REST_CREATE = Client::HTTP_POST;
    const REST_UPDATE = Client::HTTP_POST;
    const REST_READ   = Client::HTTP_GET;
    const REST_LIST   = Client::HTTP_GET;
    const REST_DELETE = Client::HTTP_DELETE;

    /**
     * Default number of objects to retrieve when listing all objects.
     */
    const DEFAULT_LIMIT = 50;

    /**
     * @var Client
     */
    protected $api;

    /**
     * @var string
     */
    protected $resourcePath;

    /**
     * @var string|null
     */
    protected $parent_id;

    /**
     * @param Client $api
     */
    public function __construct(Client $api) {
        $this->api = $api;

        if (empty($this->resourcePath)) {
            $classParts         = explode("/", get_class($this));
            $this->resourcePath = strtolower(end($classParts));
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
     * @param string $rest_resource
     * @param        $body
     * @param array  $filters
     *
     * @return object
     * @throws Exception
     */
    private function rest_create($rest_resource, $body, array $filters) {
        $result = $this->performApiCall(
            self::REST_CREATE,
            $rest_resource . $this->buildQueryString($filters),
            $body
        );

        return $this->copy($result, $this->getResourceObject());
    }

    /**
     * Retrieves a single object from the REST API.
     *
     * @param string $rest_resource Resource name.
     * @param string $id            Id of the object to retrieve.
     * @param array  $filters
     *
     * @return object
     * @throws Exception
     */
    private function restRead($rest_resource, $id, array $filters) {
        if (empty($id)) {
            throw new Exception("Invalid resource id.");
        }

        $id     = urlencode($id);
        $result = $this->performApiCall(
            self::REST_READ,
            "{$rest_resource}/{$id}" . $this->buildQueryString($filters)
        );

        return $this->copy($result, $this->getResourceObject());
    }

    /**
     * Sends a DELETE request to a single Molle API object.
     *
     * @param string $rest_resource
     * @param string $id
     *
     * @return object
     * @throws Exception
     */
    private function restDelete($rest_resource, $id) {
        if (empty($id)) {
            throw new Exception("Invalid resource id.");
        }

        $id     = urlencode($id);
        $result = $this->performApiCall(
            self::REST_DELETE,
            "{$rest_resource}/{$id}"
        );

        if ($result === NULL) {
            return NULL;
        }

        return $this->copy($result, $this->getResourceObject());
    }

    /**
     * Sends a POST request to a single Molle API object to update it.
     *
     * @param string $rest_resource
     * @param string $id
     * @param string $body
     *
     * @return object
     * @throws Exception
     */
    protected function rest_update($rest_resource, $id, $body) {
        if (empty($id)) {
            throw new Exception("Invalid resource id.");
        }

        $id     = urlencode($id);
        $result = $this->performApiCall(
            self::REST_UPDATE,
            "{$rest_resource}/{$id}",
            $body
        );

        return $this->copy($result, $this->getResourceObject());
    }

    /**
     * Get a collection of objects from the REST API.
     *
     * @param       $rest_resource
     * @param int   $offset
     * @param int   $limit
     * @param array $filters
     *
     * @return Moneybird_API_Object_List
     */
    private function restList($rest_resource, $offset = 0, $limit = self::DEFAULT_LIMIT, array $filters) {
        $filters = array_merge([
                                   "offset" => $offset,
                                   "count"  => $limit,
                               ], $filters);

        $api_path = $rest_resource . $this->buildQueryString($filters);

        $result = $this->performApiCall(self::REST_LIST, $api_path);

        /** @var Moneybird_API_Object_List $collection */
        $collection = $this->copy($result, new Moneybird_API_Object_List);

        foreach ($result->data as $data_result) {
            $collection[] = $this->copy($data_result, $this->getResourceObject());
        }

        return $collection;
    }

    /**
     * Copy the results received from the API into the PHP objects that we use.
     *
     * @param object $apiResult
     * @param object $object
     *
     * @return object
     */
    protected function copy($apiResult, $object) {
        foreach ($apiResult as $property => $value) {
            $object->$property = $value;
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
     * @param array $data An array containing details on the resource. Fields supported depend on the resource created.
     * @param array $filters
     *
     * @return object
     * @throws Exception
     */
    public function create(array $data = [], array $filters = []) {
        $encoded = json_encode($data);

        if (version_compare(phpversion(), "5.3.0", ">=")) {
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new Exception("Error encoding parameters into JSON: '" . json_last_error() . "'.");
            }
        } else {
            if ($encoded === FALSE) {
                throw new Exception("Error encoding parameters into JSON.");
            }
        }

        return $this->rest_create($this->getResourcePath(), $encoded, $filters);
    }

    /**
     * Retrieve information on a single resource from Moneybird.
     *
     * Will throw a Exception if the resource cannot be found.
     *
     * @param string $resource_id
     * @param array  $filters
     *
     * @return object
     * @throws Exception
     */
    public function get($resource_id, array $filters = []) {
        return $this->restRead($this->getResourcePath(), $resource_id, $filters);
    }

    /**
     * Delete a single resource from Moneybird.
     *
     * Will throw a Exception if the resource cannot be found.
     *
     * @param string $resource_id
     *
     * @return object
     * @throws Exception
     */
    public function delete($resource_id) {
        return $this->restDelete($this->getResourcePath(), $resource_id);
    }

    /**
     * Retrieve all objects of a certain resource.
     *
     * @param int   $offset
     * @param int   $limit
     * @param array $filters
     *
     * @return Moneybird_API_Object_List
     */
    public function all($offset = 0, $limit = 0, array $filters = []) {
        return $this->restList($this->getResourcePath(), $offset, $limit, $filters);
    }

    /**
     * Perform an API call, and interpret the results and convert them to correct objects.
     *
     * @param      $http_method
     * @param      $api_method
     * @param null $http_body
     *
     * @return object
     * @throws Exception
     */
    protected function performApiCall($http_method, $api_method, $http_body = NULL) {
        $body = $this->api->performHttpCall($http_method, $api_method, $http_body);

        if ($this->api->getLastHttpResponseStatusCode() == Client::HTTP_STATUS_NO_CONTENT) {
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
            $exception = new Exception("Error executing API call ({$object->error->type}): {$object->error->message}.");

            if (!empty($object->error->field)) {
                $exception->setField($object->error->field);
            }

            throw $exception;
        }

        return $object;
    }

    /**
     * @param string $resource_path
     */
    public function setResourcePath($resource_path) {
        $this->resourcePath = strtolower($resource_path);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getResourcePath() {
        if (strpos($this->resourcePath, "_") !== FALSE) {
            list($parent_resource, $child_resource) = explode("_", $this->resourcePath, 2);

            if (!strlen($this->parent_id)) {
                throw new Exception("Subresource '{$this->resourcePath}' used without parent '$parent_resource' ID.");
            }

            return "$parent_resource/{$this->parent_id}/$child_resource";
        }

        return $this->resourcePath;
    }

    /**
     * @param string $parent_id
     *
     * @return $this
     */
    public function withParentId($parent_id) {
        $this->parent_id = $parent_id;

        return $this;
    }

    /**
     * Set the resource to use a certain parent. Use this method before performing a get() or all() call.
     *
     * @param Moneybird_API_Object_Payment|object $parent An object with an 'id' property
     *
     * @return $this
     */
    public function with($parent) {
        return $this->withParentId($parent->id);
    }
}
