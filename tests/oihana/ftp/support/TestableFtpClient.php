<?php

namespace tests\oihana\ftp\support ;

use oihana\ftp\FtpClient ;

/**
 * An {@see FtpClient} whose backoff pause is recorded instead of slept, so the
 * retry logic can be tested instantly.
 *
 * @package tests\oihana\ftp\support
 */
class TestableFtpClient extends FtpClient
{
    /**
     * The backoff durations requested, in order.
     * @var array<int,int>
     */
    public array $waits = [] ;

    protected function waitBeforeRetry( int $seconds ) : void
    {
        $this->waits[] = $seconds ;
    }
}
