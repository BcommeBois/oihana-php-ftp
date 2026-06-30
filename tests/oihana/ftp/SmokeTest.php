<?php

namespace tests\oihana\ftp;

use PHPUnit\Framework\TestCase;

/**
 * Verifies the test harness and autoloader are correctly wired.
 *
 * Replaced by real coverage as soon as the first sources land (Lot 1).
 *
 * @package tests\oihana\ftp
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
final class SmokeTest extends TestCase
{
    public function testTestSuiteIsWired() : void
    {
        $this->assertTrue( class_exists( TestCase::class ) ) ;
    }
}
