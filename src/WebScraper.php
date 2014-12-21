<?php

namespace Scraper\Sdk;

use Curl\Sdk\HttpClient;

/**
 * Scraper
 *
 */
abstract class WebScraper implements ScraperInterface
{
    protected $source;
    protected $currentResponse;

    /**
     * Constructor
     *
     * @param string $source
     * @param string $outputType
     */
    public function __construct($source = '', $retryAttempts = 3, $retryPause = 3)
    {
        $this->client = new HttpClient($retryAttempts, $retryPause);
        $this->source = $source;
    }

    /**
     * Execute scraper and return the results from output()
     *
     * @return mixed
     */
    public function execute()
    {
        if (!empty($this->source) && filter_var($this->source, FILTER_VALIDATE_URL)) {
            // get the content of the source url into a variable
            $this->currentResponse = $this->client->get($this->source);
            // convert content into a DOM
            $domXpath = $this->convertHtmlToDomXpath($this->currentResponse->content);
            // extract desired content
            return $this->gather($domXpath);
        } else {
            throw new \InvalidArgumentException('No valid source set.');
        }
    }

    /**
     * Changes the scrape source:
     * Allows you to iterate multiple urls with same scraper instance,
     * simply re-call execute() after source change
     *
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Returns the response from the last http request
     *
     * @return \stdClass $response
     */
    public function getCurrentHttpResponse()
    {
        return $this->currentResponse;
    }

    /**
     * Extract the desired data using xpaths.
     * This method must be implemented
     *
     * @param  DOMXPath $dom
     * @return Array    $data
     */
    abstract protected function gather(\DOMXPath $dom);

    /**
     * Returns the content from a given link node.
     * Used when following links
     *
     * @param  \DOMNode  $link
     * @return \DOMXPath
     */
    protected function getLinkContent(\DOMNode $link)
    {
        $href = $link->getAttribute('href');
        // if the url does not start with http
        if (strpos($href, 'http') === false) {
            if (substr($href, 0, 1) == '/') {
                // if it starts with / prefix the domain
                $href = sprintf('%s%s', $this->getDomainFromSource(), $href);
            } else {
                // prefix current uri
                $href = sprintf('%s%s%s', $this->getDomainFromSource(), $this->getCurrentPathFromSource(), $href);
            }
        }
        $response = $this->client->get($href);
        $domXpath = $this->convertHtmlToDomXpath($response->content);

        return $domXpath;
    }

    /**
     * Checks if data has been etxracted from the resource.
     * Used to ensure output and similar are not executed without data
     *
     * @return boolean
     */
    protected function checkData()
    {
        // check we have data
        if (!is_array($this->data) || sizeof($this->data) < 1) {
            throw new \Exception("Failed to find data in $this->source");
        }

        return true;
    }

    /**
     * Cleans an extracted value into plane text
     *
     * @return string $value
     */
    protected function cleanText($value)
    {
        // strip html
        $tagless = strip_tags($value);
        // remove multiple white sapces, tabs and new lines
        $spaceless = preg_replace("/\s+/", " ", $tagless);

        return trim($spaceless);
    }

    /**
     * Converts Html obtained into a xpath queryable dom
     *
     * @param  string    $html
     * @return \DOMXPath
     */
    private function convertHtmlToDomXpath($html)
    {
        // create a new DOM document
        $dom = new \DOMDocument();
        // load the scraped HTML into the DOM doc
        @$dom->loadHTML($html);

        return new \DOMXPath($dom);
    }

    /**
     * Returns the domain from source url
     *
     * @return string $url
     */
    private function getDomainFromSource()
    {
        $parts = parse_url($this->source);

        return $parts['scheme']."://".$parts['host'];
    }

    /**
     * Returns the current path from source url
     *
     * @return string $url
     */
    private function getCurrentPathFromSource()
    {
        $parts = parse_url($this->source);
        $pathParts = explode("/", $parts['path']);
        unset($pathParts[sizeof($pathParts)-1]);

        return implode('/', $pathParts).'/';
    }
}
