<?php

namespace Example\Scraper;

use Scraper\Sdk\WebScraper;

/**
 * Example Scraper that iterates products from a listing url
 * and returns information in json format
 *
 */
class ExampleScraperToo extends WebScraper
{
    /**
     * {@inheritdoc}
     */
    protected function gather(\DOMXPath $dom)
    {
        $items = array();

        // extract desired nodes using xpath
        $nodes = $dom->query(".//article[@class='product']");
        // iterate the property nodes
        foreach ($nodes as $node) {
            $item = new \stdClass();
            // extract name and price
            $item->name = $this->cleanText($node->getElementsByTagName('h2')->item(0)->nodeValue);
            $item->price = $node->getElementsByTagName('div')->item(0)->nodeValue;
            // output to file
            $items[] = $item;
        }

        return json_encode($items);
    }
}
