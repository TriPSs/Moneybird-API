<?php

namespace Moneybird\Resource;

use Moneybird\Client;
use Moneybird\Exception;
use Moneybird\Object\ObjectList;

abstract class ResourceBase {

    const API_EXTENSION = Client::API_EXTENSION;

    const REST_CREATE = Client::HTTP_POST;
    const REST_UPDATE = Client::HTTP_POST;
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
     * @var string|null
     */
    protected $parentId;

    /**
     * @param Client $api
     */
    public function __construct(Client $api) {
        $this->api = $api;
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
            $restResource . $this->buildQueryString($filters),
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
     * @param string $restResource
     * @param string $id
     * @param string $body
     *
     * @return object
     * @throws Exception
     */
    protected function restUpdate($restResource, $id, $body) {
        if (empty($id)) {
            throw new Exception("Invalid resource id.");
        }

        $id     = urlencode($id);
        $result = $this->performApiCall(
            self::REST_UPDATE,
            "{$restResource}/{$id}",
            $body
        );

        return $this->copy($result, $this->getResourceObject());
    }

    /**
     * Get a collection of objects from the REST API.
     *
     * @param       $restResource
     * @param array $filters
     * @param int   $page
     * @param int   $perPage
     *
     * @return ObjectList
     */
    private function restList($restResource, array $filters, $page = self::DEFAULT_PAGE, $perPage = self::DEFAULT_PER_PAGE) {
        $filters = array_merge([
                                   "page"     => $page,
                                   "per_page" => $perPage,
                               ], $filters);

        $apiPath = $restResource . $this->buildQueryString($filters);

        $result = $this->performApiCall(self::REST_LIST, $apiPath);

        /** @var ObjectList $collection */
        $collection = new ObjectList();

        $collection->page     = $page;
        $collection->per_page = $perPage;
        $collection->filters  = $filters;

        foreach ($result as $dataResult) {
            $collection[] = $this->copy($dataResult, $this->getResourceObject());
        }

        print_r($collection);
        die;

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
            if (is_object($value) || is_array($value)) {
                if (is_object($value)) {
                    $className = "Moneybird\\Object\\" . ucfirst($property);

                    if (class_exists($className))
                        $object->$property = $this->copy($value, new $className);

                } else if (is_array($value)) {
                    $className = "Moneybird\\Object\\" . ucfirst(substr($property, 0, -1));

                    if (class_exists($className)) {
                        foreach ($value as $valueObject) {
                            $object->$property[] = $this->copy($valueObject, new $className);
                        }
                    }

                } else
                    $object->$property = $value;

            } else
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

        return $this->restCreate($this->getResourcePath(), $encoded, $filters);
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
     * @param array $filters
     * @param int   $page
     * @param int   $perPage
     *
     * @return ObjectList
     */
    public function all(array $filters = [], $page = self::DEFAULT_PAGE, $perPage = self::DEFAULT_PER_PAGE) {
        return $this->restList($this->getResourcePath(), $filters, $page, $perPage);
    }

    /**
     * Perform an API call, and interpret the results and convert them to correct objects.
     *
     * @param      $httpMethod
     * @param      $apiMethod
     * @param null $httpBody
     *
     * @return object
     * @throws Exception
     */
    protected function performApiCall($httpMethod, $apiMethod, $httpBody = NULL) {
        $body = $this->api->performHttpCall($httpMethod, $apiMethod, $httpBody);

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
    abstract protected function getResourcePath();

    /**
     * @param string $parent_id
     *
     * @return $this
     */
    public function withParentId($parent_id) {
        $this->parentId = $parent_id;

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
