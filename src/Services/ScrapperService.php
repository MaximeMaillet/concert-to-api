<?php

namespace App\Services;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7\Request;

/**
 * Class ScrapperService
 * @package App\Services
 */
class ScrapperService
{
    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var Client
     */
    protected $client;

    /**
     * ScrapperService constructor.
     * @param $enabled
     * @param string $baseUrl
     * @param $apiKey
     */
    public function __construct($enabled, $baseUrl, $apiKey)
    {
        $this->enabled = $enabled;
        $this->baseUrl = $baseUrl;
        $this->client = new Client();
        $this->headers = [
            'Content-Type' => 'application/json'
        ];

        if ($apiKey) {
            $this->addHeader('X-API-KEY', $apiKey);
        }
    }

    /**
     * @param $key
     * @param $value
     */
    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    /**
     * @param string $artistName
     * @return FulfilledPromise|RejectedPromise
     */
    public function scrapArtist(?string $artistName)
    {
        try {
            $this->send('POST', '/scrap/artists', [
                'name' => $artistName
            ]);
        } catch(\Exception $e) {}
    }

    public function scrapEvent($name, $startDate)
    {
        try {
            $this->send('POST', '/scrap/artists', [
                'name' => $name,
                'start_date' => $startDate
            ]);
        } catch(\Exception $exception) {}
    }

    /**
     * @param $method
     * @param $endpoint
     * @param null $body
     * @return FulfilledPromise|RejectedPromise
     */
    protected function send($method, $endpoint, $body=null)
    {
        if (!$this->enabled) {
            return new FulfilledPromise([]);
        }

        $message = null;
        if (!$this->requestReady($message)) {
            return new RejectedPromise($message);
        }

        if (in_array($method, ['POST', 'PUT', 'PATCH']) && $body == null) {
            return new RejectedPromise('Body is empty');
        }

        return $this->client->sendAsync(
            new Request($method, $this->baseUrl . $endpoint, $this->headers, (empty($body) ? null : json_encode($body)))
        )->wait();
    }

    /**
     * @param $message
     * @return bool
     */
    protected function requestReady(&$message)
    {
        if (empty($this->baseUrl)) {
            $message = 'No url found';
            return false;
        }

        return true;
    }

}
