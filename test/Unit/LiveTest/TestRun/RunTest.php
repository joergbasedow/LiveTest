<?php
namespace Unit\LiveTest\TestRun;

use Unit\LiveTest\TestRun\Mockups\TestExtension;
use Unit\LiveTest\TestRun\Mockups\ResponseMockup;
use Unit\LiveTest\TestRun\Mockups\HttpClientMockup;
use Base\Http\Client;
use LiveTest\TestRun\Run;
use LiveTest\TestRun\Properties;
use Base\Config\Yaml;

use Base\Www\Uri;
use Base\Config\Config;
use LiveTest\Extensions\Null;


/**
 * Test class for Run.
 * Generated by PHPUnit on 2011-02-08 at 08:05:37.
 */
class RunTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Run
     */
    protected $object;
    protected $uri;
    protected $configProperties;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
      $this->config = new Yaml( __DIR__.'/Fixtures/config.yml', true);
      $this->uri = new Uri('http://www.example.com/index.html');
      
      $this->configProperties = new Properties($this->config, $this->uri);
      $this->object = new Run($this->configProperties, new HttpClientMockup(new ResponseMockup()));
    }
    
     /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testAddExtensionException()
    {
       $this->object->addExtension();
    }
    
    /**
     * @expectedException Unit\LiveTest\TestRun\Mockups\SuccessException
     */
    public function testAddExtension()
    {
       $this->object = new Run($this->configProperties, new HttpClientMockup(new ResponseMockup()));
       $this->object->addExtension(new TestExtension(1));
       $this->object->run();
       
       
    }
    
     /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testConstruct()
    {
       new Run(array());
       
       
    }
    
     
    public function testRunZendHttpClientAdapterException()
    {
      $this->object->addExtension(new TestExtension(2));
       
    }
}
?>
