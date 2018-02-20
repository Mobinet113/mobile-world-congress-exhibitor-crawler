<?php

$t    = time();
$time = date("d-m-Y", $t);

$crawler = new MWVCrawl;
$crawler->getExhibitors();

$crawler->fileLocation = 'output/exhibitors_' . $time . '.json';
$crawler->saveToFile();

class MWVCrawl {

  public $target = 'https://www.mobileworldcongress.com/exhibition/2018-exhibitors/';
  public $fileLocation = 'output/exhibitors.json';

  private $exhibitorPageLinks  = [];
  private $exhibitors          = [];


  /**
   * Instantiate the class and grab all the companies and their websites.
   */
  public function getExhibitors(){

    $this->consoleMessage("\r\n \r\n Getting Archive Links... \r\n");

    $this->getArchiveLinks($this->target);

    $this->consoleMessage("\r\n \r\n Getting Company Information... \r\n");

    foreach( $this->exhibitorPageLinks as $pageLink ){
      $this->exhibitors[] = $this->getCompany($pageLink);
    }

  }

  /**
   * Save the exhbitor list to file
   */
  public function saveToFile(){

    $this->consoleMessage("Saving Company List To File \r\n");
    $this->consoleMessage($this->fileLocation . "\r\n");

    $fp = fopen($this->fileLocation, 'w');

    fwrite( $fp, json_encode($this->exhibitors) );
    fclose($fp);
  }

  private function debug($n){
    print_r('<pre>');
    print_r($n);
    print_r('</pre>');
  }

  /**
   * Grabs a page by it's URL and turns it into a PHP DOM Object
   *
   * @param $url
   * @return DOMDocument
   */
  private function getPage($url){
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    $output = curl_exec($curl);
    curl_close($curl);

    $dom = new DOMDocument;

    //Parse the HTML. The @ is used to suppress any parsing errors
    //that will be thrown if the $html string isn't valid XHTML.
    @$dom->loadHTML($output);

    return $dom;
  }

  /**
   * Gets all the page links on the exhibitor list
   *
   * @param $url
   * @return array
   */
  private function getArchiveLinks($url){

    $returnLinks = [];

    $dom = $this->getPage($url);

    $dom_xpath = new DOMXpath($dom);
    $elements = $dom_xpath->query("//div[@class='listing']");

    foreach($elements as $element) {

      $links = $element->getElementsByTagName('a');

      foreach ($links as $link) {

        $href = $link->getAttribute('href');

        $this->consoleMessage("- " . $href . "\r\n");

        $this->exhibitorPageLinks[] = $href;
        $returnLinks[] = $href;
      }
    }

    // If another page exists, then we need to get the links from that one too
    $pagiNextBtn = $dom_xpath->query("//a[contains(@class, 'next') and contains(@class, 'page-numbers')]");

    if ( $pagiNextBtn->length > 0 ){
      $nextUrl = $pagiNextBtn[0]->attributes->getNamedItem("href")->nodeValue;

      $this->getArchiveLinks($nextUrl);
    }


    return $returnLinks;

  }

  private function consoleMessage($msg){
    echo $msg;
    flush();
  }

  /**
   * Gets the name and website of a company from it's exhibitor page
   *
   * @param $url
   * @return array
   */
  private function getCompany($url){
    $dom = $this->getPage($url);

    $dom_xpath = new DOMXpath($dom);

    // Get the company name
    $elements = $dom_xpath->query("//div[@class='top-area-container']");

    $name = $elements[0]->getElementsByTagName('h2')->item(0);
    $name = $name->nodeValue;

    // Get the company website
    $elements = $dom_xpath->query("//a[@class='web-site-link']");
    $website  = $elements[0]->attributes->getNamedItem("href")->nodeValue;

    $this->consoleMessage("- Name: " . $name    . "\r\n");
    $this->consoleMessage("- Webs: " . $website . "\r\n");

    return array('name' => $name, 'website' => $website);
  }


}