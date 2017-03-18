<?php namespace Chipk4\Selectel;

class Api
{
    private $apiKey;
    private $apiEndpoint;
    private $apiPass;
    private $timeout;
    private $token;

    private $requestSuccessful = false;
    private $lastError         = '';
    private $lastResponse      = array();
    private $lastRequest       = array();

    public function __construct(array $config)
    {
        $this->apiKey = $config['authUser'];
        $this->apiPass = $config['authKey'];
        $this->apiEndpoint = $config['apiUrl'];
        $this->timeout = $config['timeout'];
    }

    protected function auth()
    {

    }

    /**
     * Performs the underlying HTTP request. Not very exciting.
     * @param  string $http_verb The HTTP verb to use: get, post, put, patch, delete
     * @param  array $args Assoc array of parameters to be passed
     * @param  array $headers array of parameters to be passed in header
     * @return array|false Assoc array of decoded result
     * @throws \Exception
     */
    protected function makeRequest($http_verb, $args = array(), $headers = array())
    {
        if (!function_exists('curl_init') || !function_exists('curl_setopt')) {
            throw new \Exception("cURL support is required, but can't be found.");
        }

        $baseHeader = [
            'Accept: application/vnd.api+json',
            'Content-Type: application/vnd.api+json',
            'X-Auth-User:' . $this->apiKey,
            'X-Auth-Key:' . $this->apiPass
        ];

        $headers = array_merge($baseHeader, $headers);

        $url = $this->apiEndpoint;

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
            'url'     => $url,
            'body'    => '',
            'timeout' => $this->timeout,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
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
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $query);
                break;
        }

        $responseContent = curl_exec($ch);

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

        $formattedResponse = $this->formatResponse($response);

        $this->determineSuccess($response, $formattedResponse, $this->timeout);

        return $formattedResponse;
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

            list($key, $value) = explode(': ', $line);

            if ($key == 'Link') {
                $value = array_merge(
                    array('_raw' => $value),
                    $this->getLinkHeaderAsArray($value)
                );
            }

            $headers[$key] = $value;
        }

        return $headers;
    }

    /**
     * Extract all rel => URL pairs from the provided Link header value
     *
     * Mailchimp only implements the URI reference and relation type from
     * RFC 5988, so the value of the header is something like this:
     *
     * 'https://us13.api.mailchimp.com/schema/3.0/Lists/Instance.json; rel="describedBy", <https://us13.admin.mailchimp.com/lists/members/?id=XXXX>; rel="dashboard"'
     *
     * @param string $linkHeaderAsString
     * @return array
     */
    private function getLinkHeaderAsArray($linkHeaderAsString)
    {
        $urls = array();

        if (preg_match_all('/<(.*?)>\s*;\s*rel="(.*?)"\s*/', $linkHeaderAsString, $matches)) {
            foreach ($matches[2] as $i => $relName) {
                $urls[$relName] = $matches[1][$i];
            }
        }

        return $urls;
    }

    /**
     * Decode the response and format any error messages for debugging
     * @param array $response The response from the curl request
     * @return array|false    The JSON decoded into an array
     */
    private function formatResponse($response)
    {
        $this->lastResponse = $response;

        if (!empty($response['body'])) {
            return json_decode($response['body'], true);
        }

        return false;
    }

    /**
     * Check if the response was successful or a failure. If it failed, store the error.
     * @param array $response The response from the curl request
     * @param array|false $formattedResponse The response body payload from the curl request
     * @param int $timeout The timeout supplied to the curl request.
     * @return bool     If the request was successful
     */
    private function determineSuccess($response, $formattedResponse, $timeout)
    {
        $status = $this->findHTTPStatus($response, $formattedResponse);

        if ($status >= 200 && $status <= 299) {
            $this->requestSuccessful = true;
            return true;
        }

        if (isset($formattedResponse['detail'])) {
            $this->lastError = sprintf('%d: %s', $formattedResponse['status'], $formattedResponse['detail']);
            return false;
        }

        if( $timeout > 0 && $response['headers'] && $response['headers']['total_time'] >= $timeout ) {
            $this->lastError = sprintf('Request timed out after %f seconds.', $response['headers']['total_time'] );
            return false;
        }

        $this->lastError = 'Unknown error, call getLastResponse() to find out what happened.';
        return false;
    }

    /**
     * Find the HTTP status code from the headers or API response body
     * @param array $response The response from the curl request
     * @param array|false $formattedResponse The response body payload from the curl request
     * @return int  HTTP status code
     */
    private function findHTTPStatus($response, $formattedResponse)
    {
        if (!empty($response['headers']) && isset($response['headers']['http_code'])) {
            return (int) $response['headers']['http_code'];
        }

        if (!empty($response['body']) && isset($formattedResponse['status'])) {
            return (int) $formattedResponse['status'];
        }

        return 418;
    }
}