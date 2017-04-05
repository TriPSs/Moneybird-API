<?php

namespace Moneybird;

use Moneybird\Exception\IncompatiblePlatformException;
use Moneybird\Resource\SalesInvoices;
use Moneybird\Resource\Undefined as UndefinedResource;

class Client {

    /**
     * Version of our client.
     */
    const CLIENT_VERSION = "1.0.0";

    /**
     * Endpoint of the remote API.
     */
    const API_ENDPOINT = "https://moneybird.com/api";

    /**
     * Extension of the remote API
     */
    const API_EXTENSION = ".json";

    /**
     * Version of the remote API.
     */
    const API_VERSION = "v2";

    const HTTP_GET    = "GET";
    const HTTP_POST   = "POST";
    const HTTP_DELETE = "DELETE";

    const HTTP_STATUS_NO_CONTENT = 204;

    /**
     * @var string
     */
    protected $apiEndpoint = self::API_ENDPOINT;

    /**
     * RESTful Sales invoices resource.
     *
     * @var SalesInvoices
     */
    public $salesInvoices;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var string
     */
    protected $administrationID;

    /**
     * @var resource
     */
    protected $ch;

    /**
     * @var int
     */
    protected $lastHttpResponseStatusCode;

    /**
     * @throws IncompatiblePlatformException
     */
    public function __construct() {
        $this->getCompatibilityChecker()
             ->checkCompatibility();

        $this->salesInvoices = new SalesInvoices($this);
    }

    /**
     * @param string $resourcePath
     *
     * @return UndefinedResource
     */
    public function __get($resourcePath) {
        $undefinedResource = new UndefinedResource($this);
        $undefinedResource->setResourcePath($resourcePath);

        return $undefinedResource;
    }

    /**
     * @param string $accessToken The access token generated in Moneybird
     *
     * @return $this
     */
    public function setAccessToken($accessToken) {
        $this->accessToken = trim($accessToken);

        return $this;
    }

    /**
     * @param string $administrationID ID of the administration
     *
     * @return $this
     */
    public function setAdministrationID($administrationID) {
        $this->administrationID = $administrationID;

        return $this;
    }

    /**
     * Perform an http call. This method is used by the resource specific classes.
     *
     * @param $httpMethod
     * @param $apiMethod
     * @param $httpBody
     *
     * @return string
     * @throws Exception
     */
    public function performHttpCall($httpMethod, $apiMethod, $httpBody = NULL) {
        if (empty($this->accessToken)) {
            throw new Exception("You have not set an access token. Please use setAccessToken() to set the access token.");
        }

        if (empty($this->ch) || !function_exists("curl_reset")) {
            /*
             * Initialize a cURL handle.
             */
            $this->ch = curl_init();
        } else {
            /*
             * Reset the earlier used cURL handle.
             */
            curl_reset($this->ch);
        }

        $url = $this->apiEndpoint . "/" . self::API_VERSION . "/{$this->administrationID}/" . $apiMethod;

        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($this->ch, CURLOPT_ENCODING, "");

        $requestHeaders = [
            "Accept: application/json",
            "Authorization: Bearer {$this->accessToken}",
        ];

        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $httpMethod);

        if ($httpBody !== NULL) {
            $requestHeaders[] = "Content-Type: application/json";
            curl_setopt($this->ch, CURLOPT_POST, 1);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $httpBody);
        }

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, TRUE);

        $body = curl_exec($this->ch);

        $this->lastHttpResponseStatusCode = (int)curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

        if (curl_errno($this->ch)) {
            $message = "Unable to communicate with Moneybird (" . curl_errno($this->ch) . "): " . curl_error($this->ch) . ".";

            $this->closeTcpConnection();
            throw new Exception($message);
        }

        if (!function_exists("curl_reset")) {
            /*
             * Keep it open if supported by PHP, else close the handle.
             */
            $this->closeTcpConnection();
        }

        return $body;
    }

    /**
     * Close the TCP connection to the Moneybird API.
     */
    private function closeTcpConnection() {
        if (is_resource($this->ch)) {
            curl_close($this->ch);
            $this->ch = NULL;
        }
    }

    /**
     * Close any cURL handles, if we have them.
     */
    public function __destruct() {
        $this->closeTcpConnection();
    }

    /**
     * @return CompatibilityChecker
     */
    protected function getCompatibilityChecker() {
        static $checker = NULL;

        if (!$checker) {
            $checker = new CompatibilityChecker();
        }

        return $checker;
    }

    /**
     * @deprecated Do not use this method, it should only be used internally
     *
     * @return int
     */
    public function getLastHttpResponseStatusCode() {
        return $this->lastHttpResponseStatusCode;
    }
}
