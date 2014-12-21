<?php

namespace Scraper\Sdk\Core;

/**
 * A simple Curl wrapper
 *
 */
class HttpClient
{
    private $handler;
    private $retryAttempts;
    private $retryPause;

    /**
     * Constructor
     *
     * @param type $retryAttempts
     * @param type $retryPause
     */
    public function __construct($retryAttempts = 3, $retryPause = 3)
    {
        $this->retryAttempts = $retryAttempts;
        $this->retryPause = $retryPause;
    }

    /**
     * Sets the number of retries for connection failures
     *
     * @param int $attemtps
     */
    public function setRetryAttempts($attemtps)
    {
        if (!ctype_digit($attemtps)) {
            throw new \Exception("Invalid parameter, setConnectionRetryAttempts expects an integer ");
        }
        $this->setConnectionRetryAttempts($attemtps);

        return $this;
    }

    /**
     * Sets the pause time in seconds between connection attempts
     *
     * @param int $seconds
     */
    public function setRetryPause($seconds)
    {
        if (!ctype_digit($seconds)) {
            throw new \Exception("Invalid parameter, setConnectionRetryPause expects an integer ");
        }
        $this->setConnectionRetryPause($seconds);

        return $this;
    }

    /**
     * Gets content from a given url
     *
     * @param  string    $url
     * @return \stdClass $response
     */
    public function get($url)
    {
        $this->init();
        curl_setopt($this->handler, CURLOPT_URL, $url);

        return $this->execute();
    }

    /**
     * Inits the connection
     *
     * @throws \Exception
     */
    private function init()
    {
        if (gettype($this->handler) != 'resource') {
            if (in_array('curl', get_loaded_extensions())) {
                $this->handler = curl_init();
                curl_setopt($this->handler, CURLOPT_HEADER, false);
                curl_setopt($this->handler, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($this->handler, CURLOPT_FAILONERROR, true);
                // attempt to prevent being blocked as bot by emulating a browser
                $agent = "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12";
                curl_setopt($this->handler, CURLOPT_USERAGENT, $agent);
                curl_setopt($this->handler, CURLOPT_AUTOREFERER, true);
                curl_setopt($this->handler, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($this->handler, CURLOPT_COOKIE, "");
                curl_setopt($this->handler, CURLOPT_COOKIEFILE, tempnam("/tmp", "cookie"));
                curl_setopt($this->handler, CURLOPT_SSL_VERIFYPEER, false);
            } else {
                throw new \Exception("Missing CURL extension. Check your PHP configuration.");
            }
        }
    }

    /**
     * Closes the connection and returns the response as an stdClass
     *
     * @return \stdClass
     * @throws \Exception
     */
    private function execute($attempt = 0)
    {
        $result = curl_exec($this->handler);
        if (($result === false || curl_getinfo($this->handler, CURLINFO_HTTP_CODE) != 200)) {
            if ($attempt <= $this->retryAttempts) {
                sleep($this->retryPause);
                $this->execute($attempt+1);
            } else {
                throw new \Exception(curl_error($this->handler));
            }
        }

        $response = new \stdClass();
        $response->code = curl_getinfo($this->handler, CURLINFO_HTTP_CODE);
        $response->info = (object) curl_getinfo($this->handler);
        $response->content = $result;
        curl_close($this->handler);

        return $response;
    }
}
