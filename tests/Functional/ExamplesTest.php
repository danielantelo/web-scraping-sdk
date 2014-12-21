<?php

namespace Scraper\Sdk\Tests;

use Example\Scraper\ExampleScraper;
use Example\Scraper\ExampleScraperToo;

/**
 * ScraperTest
 *
 */
class ExamplesTest extends \PHPUnit_Framework_TestCase
{
    const URL = 'http://www.danielanteloagra.com/dummy/scrape-list.html';
    const EXPECTED_NUMBER_RESULTS = 4;
    const EXPECTED_HTML_CONTENT = "<title>Example Page For Content Scraping SDK</title>";

    protected $scraper;

    /**
     * Ensure only valid urls are fetched
     *
     * @expectedException InvalidArgumentException
     */
    public function testInvalidUrl()
    {
        $scraper = new ExampleScraper('site.invalid');
        $scraper->execute();
    }

    /**
     * Tests json scraper behaves correctly
     * 
     * @test
     */
    public function testJsonScrape()
    {
        setup:
            $scraper = new ExampleScraperToo(self::URL);
        
        when:
            $reply = $scraper->execute();
            $content = $scraper->getCurrentHttpResponse()->content;
        then:
            // we must be dealing with html
            $this->assertNotEquals(strip_tags($content), $content, "Retrieved html");    
            // the html contains html we expected
            $this->assertContains(self::EXPECTED_HTML_CONTENT, $content, "Retrieved html contains expected content"); 
            // reply is valid json
            $this->assertJson($reply, "Reply is json");
            // result contains expected number of items
            $this->assertTrue(self::EXPECTED_NUMBER_RESULTS == sizeof(json_decode($reply)), "Reply contains expected number of items");
        
    }
    
    /**
     * Tests csv scraper behaves correctly
     * 
     * @test
     */
    public function testCsvScrape()
    {
        setup:
            @mkdir(getcwd().'/scraped-data/', 0755, true);
            $csvfile = sprintf('%s/scraped-data/output-%s.csv', getcwd(), date('Ymd'));
            $scraper = new ExampleScraper(self::URL);
        
        when:
            $reply = $scraper->execute();
            $content = $scraper->getCurrentHttpResponse()->content;
        then:
            // we must be dealing with html
            $this->assertNotEquals(strip_tags($content), $content, "Retrieved html");   
            // the html contains html we expected
            $this->assertContains(self::EXPECTED_HTML_CONTENT, $content, "Retrieved html contains expected content"); 
            // reply is true
            $this->assertTrue($reply == true, "Reply was true"); 
            // csv file contains expected number of items
            $this->assertTrue(self::EXPECTED_NUMBER_RESULTS == count(file($csvfile)), "Csv contains expected number of items");
        
    }    

    /**
     * Used to access and test non public methods
     *
     * @param Object $obj
     * @param String $name
     * @param array  $args
     */
    protected static function callMethod($obj, $name, array $args = array())
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }
}
