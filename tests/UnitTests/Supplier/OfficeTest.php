<?php
namespace PhpTwinfield\Secure;

use PhpTwinfield\DomDocuments\SuppliersDocument;
use PhpTwinfield\Office;
use PhpTwinfield\Supplier;

class OfficeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Office
     */
    private $office;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->office = Office::fromCode('TEST-001');
    }

    /**
     * @covers \PhpTwinfield\Supplier::setOffice()
     * @todo   Implement testSetOffice().
     */
    public function testSetOffice()
    {
        $customer = new Supplier;
        
        $customer->setOffice($this->office);
        
        $this->assertEquals($this->office, $customer->getOffice());
        
        return $customer;
    }
    
    /**
     * Checks if the office field is correctly serialized
     *
     * @depends testSetOffice
     */
    public function testSetOfficeAndSerializes(Supplier $customer)
    {
        $document = new SuppliersDocument;
        
        $document->addSupplier($customer);
        
        $xpath = new \DOMXPath($document);
        
        $this->assertEquals($xpath->query('/dimension/office')->item(0)->nodeValue, $this->office);
    }
}
