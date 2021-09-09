<?php

namespace PhpTwinfield\UnitTests;

use PhpTwinfield\Response\Response;
use PHPUnit\Framework\TestCase;

class ResponseUnitTest extends TestCase
{
    public function testMultipleItemsSentIsSuccessFul()
    {
        $response = Response::fromString('<?xml version="1.0"?>
<statements target="electronicstatements" importduplicate="0">
    <statement target="electronicstatements" result="1">
        <!-- ... -->
    </statement>
    <!-- etc... -->
</statements>');
        $response->assertSuccessful();

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testMultipleItemsSentIsNotSuccessFul()
    {
        $this->expectException(\PhpTwinfield\Exception::class);
        $this->expectExceptionMessage('Not all items were processed successfully by Twinfield: 0 success / 1 failed.');

        $response = Response::fromString('<?xml version="1.0"?>
<statements target="electronicstatements" importduplicate="0">
    <statement target="electronicstatements" result="0">
        <!-- ... -->
    </statement>
    <!-- etc... -->
</statements>');
        $response->assertSuccessful();

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testTransactionSuccessfulSuccesfulIsSuccesful()
    {
        $response = Response::fromString('<?xml version="1.0"?>
<transactions result="1">
    <transaction result="1" location="temporary">
        <!-- ... -->
    </transaction>
    <!-- etc... -->
</transactions>
');
        $response->assertSuccessful();

        $this->assertInstanceOf(Response::class, $response);
    }
}
