<?php namespace Chipk4\Selectel;

/**
 * TODO: add http status code
 *
 * Class Api
 * @package Chipk4\Selectel
 */

class Api
{
    private $apiKey;
    private $apiEndpoint;
    private $apiPass;
    private $timeout;
    private $token = '';
    private $storageUrl = '';
    private $returnView;

    private $requestSuccessful = false;
    private $lastError         = '';
    private $lastResponse      = array();
    private $lastRequest       = array();

    /**
     * User login in header for auth
     */
    const HEADER_AUTH_USER = 'X-Auth-User';

    /**
     * User password in header for auth
     */
    const HEADER_AUTH_PASSWORD = 'X-Auth-Key';

    /**
     * Auth identification in response
     */
    const HEADER_TOKEN = 'X-Auth-Token';

    /**
     * Storage url
     */
    const HEADER_STORAGE_URL = 'X-Storage-Url';

    const HEADER_CONTAINER_TYPE = 'X-Container-Meta-Type';

    /**
     * Api constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->apiKey = $config['authUser'];
        $this->apiPass = $config['authKey'];
        $this->apiEndpoint = $config['apiUrl'];
        $this->timeout = $config['timeout'];
        $this->returnView = $config['returnView'];
        $this->storageUrl = $config['storageUrl'];
    }

    /**
     * TODO: add exception if Forbidden
     * @return mixed
     */
    public function auth()
    {
        $auth = [
            self::HEADER_AUTH_USER . ':' . $this->apiKey,
            self::HEADER_AUTH_PASSWORD . ':' . $this->apiPass
        ];

        $result = $this->makeRequest('get', [], $auth, $this->apiEndpoint);

        if(!$this->getStorageUrl()) {
            $this->storageUrl = $result[self::HEADER_STORAGE_URL];
        }
        return $this->token = $result[self::HEADER_TOKEN];
    }

    /**
     * Without auth
     *
     * @param $http_verb
     * @param array $args
     * @return array|false
     */
    public function makePublicRequest($http_verb, $args = array(), $headers = array(), $additionalUrlPath = '')
    {
        return $this->makeRequest($http_verb, $args, $headers, $this->storageUrl . '/' . $additionalUrlPath);
    }

    /**
     * With auth
     *
     * @param string $http_verb
     * @param array $args
     * @param array $headers
     * @param string $additionalUrlPath
     * @return array|false
     */
    public function makePrivateRequest($http_verb, array $args, array $headers, $additionalUrlPath = '')
    {
        if(!$this->getToken()) {
            $this->auth();
        }

        return $this->makeRequest($http_verb, $args, array_merge($headers, [
            self::HEADER_TOKEN . ': ' . $this->token
        ]), $this->storageUrl . '/' . $additionalUrlPath);
    }

    /**
     * Performs the underlying HTTP request. Not very exciting.
     * @param  string $http_verb The HTTP verb to use: get, post, put, patch, delete
     * @param  array $args Assoc array of parameters to be passed
     * @param  array $headers array of parameters to be passed in header
     * @param  string $endPoint
     * @return array|false Assoc array of decoded result
     * @throws \Exception
     */
    protected function  makeRequest($http_verb, $args = array(), $headers = array(), $endPoint)
    {
        if (!function_exists('curl_init') || !function_exists('curl_setopt')) {
            throw new \Exception("cURL support is required, but can't be found.");
        }

        $this->lastError = '';
        $this->requestSuccessful = false;
        $response = array(
            'headers'     => null, // array of details from curl_getinfo()
            'httpHeaders' => null, // array of HTTP headers
            'body'        => null // content of the response
        );
        $this->lastResponse = $response;

        $this->lastRequest = array(
            'method'  => $http_verb,
            'url'     => $endPoint,
            'body'    => '',
            'timeout' => $this->timeout,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endPoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, 'chipk4/selectel-api(github.com/chipk4/selectel_cloud_storage/)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        switch ($http_verb) {
            case 'get':
                $query = http_build_query($args, '', '&');
                curl_setopt($ch, CURLOPT_URL, $endPoint . '?' . $query);
                break;
            case 'head':
                curl_setopt($ch, CURLOPT_NOBODY, true);
                break;
            case 'put':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_PUT, true);
                $this->attachRequestPayload($ch, $args);
                break;
            case 'delete':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }

        $responseContent = curl_exec($ch);

        if(isset($fp)) {
            fclose($fp);
        }

        $response['headers'] = curl_getinfo($ch);

        if ($responseContent === false) {
            $this->lastError = curl_error($ch);
        } else {
            $headerSize = $response['headers']['header_size'];
            $response['httpHeaders'] = $this->getHeadersAsArray(substr($responseContent, 0, $headerSize));
            $response['body'] = substr($responseContent, $headerSize);

            if (isset($response['headers']['request_header'])) {
                $this->lastRequest['headers'] = $response['headers']['request_header'];
            }
        }

        curl_close($ch);

        if($response['body']) {
            return $response['body'];
        }
        return $response['httpHeaders'];
    }

    /**
     * Get the HTTP headers as an array of header-name => header-value pairs.
     *
     * The "Link" header is parsed into an associative array based on the
     * rel names it contains. The original value is available under
     * the "_raw" key.
     *
     * @param string $headersAsString
     * @return array
     */
    private function getHeadersAsArray($headersAsString)
    {
        $headers = array();

        foreach (explode("\r\n", $headersAsString) as $i => $line) {
            if ($i === 0) { // HTTP code
                continue;
            }

            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $arrResult = explode(': ', $line);

            $headers[$arrResult[0]] = isset($arrResult[1]) ? $arrResult[1] : '';
        }

        return $headers;
    }

    /**
     * Encode the data and attach it to the request
     * @param   resource $ch cURL session handle, used by reference
     * @param   array $data Assoc array of data to attach
     */
    private function attachRequestPayload(&$ch, array $data)
    {
        if(key_exists('file', $data)) {
            $fp = fopen($data['file'], "rb");
            curl_setopt($ch, CURLOPT_INFILE, $fp);
            curl_setopt($ch, CURLOPT_INFILESIZE, filesize($data['file']));
        }
    }

    /**
     * TODO: check for token expire date
     * @return string
     */
    protected function getToken()
    {
        return $this->token;
    }

    public function getStorageUrl()
    {
        return $this->storageUrl;
    }

    public function getReturnView()
    {
        return $this->returnView;
    }
}