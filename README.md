Web Scraping PHP SDK
========================

This is a composer package that simplifies web content scraping providing a lightweight and easy to use code base.

Simply extend the Scraper class provided and implement the gather() method to extract the desired content using xpaths.
You can then output this content to a file, store in a database, return a json string, etc.

Highlights:

* XPath driven extraction of content
* Just one method to implement
* Allows easy file writing, database storage or formatted string/object return
* PSR2 coding standards
* Uses cURL to retrieve content from specified source
* Configurable failed attempts retry count and pause time 
* Easily follow links to get additional content

Packagist link: 
https://packagist.org/packages/daa/web-scraping-sdk


Usage
-----

Add the following requirement to your composer file and do a composer install/update:

```
  "require": {
        ...
        "daa/web-scraping-sdk: "1.*"
  },
```

Write your own scraper class which extends Scraper\Sdk\WebScraper and implements the gather method:

```
namespace Your\Package\Scraper;

use Scraper\Sdk\WebScraper;

class YourScraper extends WebScraper 
{
    /**
     * {@inheritdoc}
     */
    protected function gather(\DOMXPath $dom)
    {
        $nodes = $dom->query(".//article[@class='product']");
        foreach ($nodes as $node) {
            ...
            // follow a url and extract more data
            $linkDom = $this->getLinkContent($node->getElementsByTagName('a')->item(0));
            $linkDom->query...

        }
    }
}

```

Now call your class, for example from a script that is executed by a cron job:

```
require __DIR__.'/../vendor/autoload.php';

$scraper = new Your\Package\Scraper\YourScraper('http://www.someurl.com/with/content/');
$scraper->execute();

```

With troublesome sources you can specify the retry configuration (default is 3 retries with a 3 second pause in between)

```
$scraper = new Your\Package\Scraper\YourScraper('http://www.someurl.com/with/content/', $retryAttempts, $pauseSeconds);
$scraper->execute();

```

You can use the same instance to scrape several urls with the same structure:

```
$pages = array(
    'http://www.someurl.com/section-one/',
    'http://www.someurl.com/section-two/page1',
    'http://www.someurl.com/section-one/page2'
); 

$scraper = new Your\Package\Scraper\YourScraper();

foreach ($pages as $url) {
    $scraper->setSource($url);
    $scraper->execute();
}

```

Check out the examples folder for more details and fully working examples.