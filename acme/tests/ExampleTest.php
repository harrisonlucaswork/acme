<?php
use PHPUnit\Framework\TestCase;
use App\Example;

class ExampleTest extends TestCase
{
    public function testTrueIsTrue()
    {
        $this->assertTrue(true);
    }

    public function testAdd()
    {
        $example = new Example;

        $this->assertEquals(4, $example->add(2, 2));
    }
}
