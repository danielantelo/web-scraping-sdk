<?php

require __DIR__.'/../vendor/autoload.php';

$scraper = new Example\Scraper\ExampleScraper('http://www.danielanteloagra.com/dummy/scrape-list.html');
$scraper->execute();
//$scraper->setSource('http://www.danielanteloagra.com/dummy/scrape-list-2.html');
//$scraper->execute();

// create a new scraper which retries connections at least 5 times with a 10 second pause between attempts
$scraper = new Example\Scraper\ExampleScraperToo('http://www.danielanteloagra.com/dummy/scrape-list.html', 5, 10);
echo $scraper->execute();
