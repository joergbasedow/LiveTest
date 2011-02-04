<?php

namespace LiveTest\TestRun;

use Base\Http\ConnectionStatus;

use Base\Www\Uri;

use LiveTest\Extensions\Extension;
use LiveTest\TestCase\Exception;
use LiveTest\TestRun\Result\ResultSet;
use LiveTest\TestRun\Result\Result;

use Base\Http\Response;
use Base\Http\Client;

class Run
{
  /**
   * All properties for the test run
   * 
   * @var LiveTest\TestRun\Properties
   */
  private $properties;
  
  private $extensions = array();
  
  public function addExtension(Extension $extension)
  {
    $this->extensions[] = $extension;
  }
  
  public function __construct(Properties $properties)
  {
    $this->properties = $properties;
  }
  
  private function extensionsPostRun()
  {
    foreach ($this->extensions as $extension)
    {
      $extension->postRun();
    }
  }
  
  private function handleResult(Result $result, \Zend_Http_Response $response)
  {
    foreach ($this->extensions as $extension)
    {
      $extension->handleResult($result, $response);
    }
  }
  
  private function handleConnectionStatus(ConnectionStatus $status)
  {
    foreach ($this->extensions as $extension)
    {
      $extension->handleConnectionStatus($status);
    }
  }
  
  private function extensionsPreRun()
  {
    foreach ($this->extensions as $extension)
    {
      $extension->preRun($this->properties);
    }
  }
  
  private function runTests(TestSet $testSet, \Zend_Http_Response $response)
  {
    foreach ($testSet->getTests() as $test)
    {
      $testCaseName = $test->getClass();
      try
      {
        $testCaseObject = new $testCaseName($test->getParameter());
        $testCaseObject->test($response, new Uri($testSet->getUrl()));
        $result = new Result($test, Result::STATUS_SUCCESS, '', $testSet->getUrl());
      }
      catch ( \LiveTest\TestCase\Exception $e )
      {
        $result = new Result($test, Result::STATUS_FAILED, $e->getMessage(), $testSet->getUrl());
      }
      catch ( Exception $e )
      {
        $result = new Result($test, Result::STATUS_ERROR, $e->getMessage(), $testSet->getUrl());
      }
      catch ( \Base\Www\Exception $e )
      {
        $result = new Result($test, Result::STATUS_ERROR, $e->getMessage(), $testSet->getUrl());
      }
      $this->handleResult($result, $response);
    }
  }
  
  public function run()
  {
    $this->extensionsPreRun();
    
    $testSets = $this->properties->getTestSets();
    $client = new \Zend_Http_Client();
    
    foreach ($testSets as $testSet)
    {
      try
      {
        $client->setUri($testSet->getUrl());
        $response = $client->request();
        $this->handleConnectionStatus(new ConnectionStatus(ConnectionStatus::SUCCESS));
      }
      catch ( \Zend_Http_Client_Adapter_Exception $e )
      {
        $this->handleConnectionStatus(new ConnectionStatus(ConnectionStatus::ERROR, $e->getMessage()));
        continue;
      }
      $this->runTests($testSet, $response);
    }
    $this->extensionsPostRun();
  }
}