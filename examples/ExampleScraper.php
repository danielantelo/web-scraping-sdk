<?php

namespace Example\Scraper;

use Scraper\Sdk\WebScraper;

/**
 * Example Scraper that iterates products from a listing url,
 * follows each of their details link
 * and dumps the information to a csv file.
 *
 */
class ExampleScraper extends WebScraper
{
    /**
     * {@inheritdoc}
     */
    protected function gather(\DOMXPath $dom)
    {
        @mkdir(getcwd().'/scraped-data/', 0755, true);
        $filename = sprintf('%s/scraped-data/output-%s.csv', getcwd(), date('Ymd'));
        $file = fopen($filename, "w");

        // extract desired nodes using xpath
        $nodes = $dom->query(".//article[@class='product']");
        // iterate the property nodes
        foreach ($nodes as $node) {
            $item = new \stdClass();
            // extract name
            $item->name = $this->cleanText($node->getElementsByTagName('h2')->item(0)->nodeValue);
            // follow url and extract description
            $linkDom = $this->getLinkContent($node->getElementsByTagName('a')->item(0));
            $item->description = $this->cleanText($linkDom->query(".//p[@class='description']")->item(0)->nodeValue);
            // output to file
            fputcsv($file, (array) $item);
        }

        fclose($file);

        return true;
    }
}
