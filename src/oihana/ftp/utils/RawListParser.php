<?php

namespace oihana\ftp\utils ;

use oihana\ftp\enums\FtpFileType ;
use oihana\ftp\schema\FtpFile ;

/**
 * Parses the raw, Unix-style (`ls -l`) output of `ftp_rawlist` into {@see FtpFile} entries.
 *
 * Only the common Unix long-listing format is understood; `total N` headers and lines
 * that do not match are skipped. The listing date is intentionally not converted to a
 * timestamp (its format is locale- and server-dependent and ambiguous on year boundaries) —
 * prefer MLSD (`ftp_mlsd`) when the server supports it.
 *
 * @package oihana\ftp\utils
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
class RawListParser
{
    /**
     * The Unix long-listing line pattern.
     */
    private const string PATTERN =
        '/^([\-dlbcps])([rwxsStT\-]{9})\s+\d+\s+(\S+)\s+(\S+)\s+(\d+)\s+(\w{3}\s+\d{1,2}\s+[\d:]+)\s+(.+)$/' ;

    /**
     * Parses a list of raw listing lines into entries.
     *
     * @param array<int,string> $lines The raw lines returned by `ftp_rawlist`.
     *
     * @return array<int,FtpFile> The parsed entries (unparseable lines are skipped).
     */
    public static function parse( array $lines ) : array
    {
        $files = [] ;

        foreach ( $lines as $line )
        {
            $file = self::parseLine( (string) $line ) ;

            if ( $file !== null )
            {
                $files[] = $file ;
            }
        }

        return $files ;
    }

    /**
     * Parses a single raw listing line.
     *
     * @param string $line The raw line.
     *
     * @return FtpFile|null The parsed entry, or null when the line is a header or cannot be parsed.
     */
    public static function parseLine( string $line ) : ?FtpFile
    {
        $line = trim( $line ) ;

        if ( $line === '' || str_starts_with( strtolower( $line ) , 'total ' ) )
        {
            return null ;
        }

        if ( !preg_match( self::PATTERN , $line , $matches ) )
        {
            return null ;
        }

        $type   = FtpFileType::fromUnixChar( $matches[ 1 ] ) ;
        $name   = $matches[ 7 ] ;
        $target = null ;

        if ( $type === FtpFileType::LINK && str_contains( $name , ' -> ' ) )
        {
            [ $name , $target ] = explode( ' -> ' , $name , 2 ) ;
        }

        return new FtpFile(
            name        : $name ,
            type        : $type ,
            size        : (int) $matches[ 5 ] ,
            permissions : $matches[ 1 ] . $matches[ 2 ] ,
            owner       : $matches[ 3 ] ,
            group       : $matches[ 4 ] ,
            target      : $target ,
        ) ;
    }
}
