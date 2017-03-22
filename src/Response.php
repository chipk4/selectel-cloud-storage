<?php namespace Chipk4\Selectel;

use Chipk4\Selectel\Contract\Response as iResponse;

class Response implements iResponse
{
    private $resource;
    private $headers = array();
    private $response;
    private $body;

    /**
     * Response constructor.
     * @param resource $resource
     * @param $body
     */
    public function __construct($resource, $body)
    {
        if(false === is_resource($resource)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Argument must be a valid resource type. %s given.',
                    gettype($resource)
                )
            );
        }

        $this->init($resource, $body);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        if(!$this->headers) {
            foreach (explode("\r\n", $this->response) as $i => $line) {
                if ($i === 0) { continue; }

                $line = trim($line);
                if (empty($line)) { continue; }

                $arrResult = explode(': ', $line);
                $this->headers[$arrResult[0]] = isset($arrResult[1]) ? $arrResult[1] : '';
            }
        }
        return $this->headers;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    public function getStatusCode()
    {
        return curl_getinfo($this->resource, CURLINFO_HTTP_CODE);
    }

    protected function init($resource, $response)
    {
        $this->body = substr($response, curl_getinfo($resource, CURLINFO_HEADER_SIZE));
        $this->resource = $resource;
        $this->response = $response;
    }
}