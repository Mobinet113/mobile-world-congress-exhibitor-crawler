<?php

$crawler = new MWVCrawl;
$crawler->getExhibitors();

class MWVCrawl {

  public $target = 'https://www.mobileworldcongress.com/exhibition/2018-exhibitors/';

  private $exhibitors = [];

  function _construct(){

  }

  public function getExhibitors(){
    $pageLinks = $this->getArchiveLinks($this->target);

    foreach( $pageLinks as $pageLink ){
      $company = $this->getCompany($pageLink);

      $this->exhibitors[] = $company;
    }

    $this->debug($this->exhibitors);

  }

  private function debug($n){
    print_r('<pre>');
    print_r($n);
    print_r('</pre>');
  }

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

  private function getArchiveLinks($url){

    $returnLinks = [];

    $dom = $this->getPage($url);

    $dom_xpath = new DOMXpath($dom);
    $elements = $dom_xpath->query("//div[@class='listing']");

    foreach($elements as $element) {

      $links = $element->getElementsByTagName('a');

      foreach ($links as $link) {
        $returnLinks[] = $link->getAttribute('href');
      }
    }

    return $returnLinks;
  }

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


    return array('name' => $name, 'website' => $website);
  }

  private function goToNextPage(){

  }

}