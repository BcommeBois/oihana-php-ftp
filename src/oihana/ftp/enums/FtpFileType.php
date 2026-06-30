<?php

namespace oihana\ftp\enums ;

use oihana\reflect\traits\ConstantsTrait ;

/**
 * Enumerates the kinds of entry returned by a remote directory listing, and maps
 * the various server representations (MLSD facts, `ls -l` type characters) onto them.
 *
 * @package oihana\ftp\enums
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
class FtpFileType
{
    use ConstantsTrait ;

    /**
     * A directory.
     */
    public const string DIRECTORY = 'dir' ;

    /**
     * A regular file.
     */
    public const string FILE = 'file' ;

    /**
     * A symbolic link.
     */
    public const string LINK = 'link' ;

    /**
     * An entry whose kind could not be determined.
     */
    public const string UNKNOWN = 'unknown' ;

    /**
     * Maps an MLSD `type` fact to a file-type constant.
     *
     * The `cdir` (current) and `pdir` (parent) MLSD types are reported as directories.
     *
     * @param string $type The MLSD type fact.
     *
     * @return string One of the file-type constants.
     */
    public static function fromMlsd( string $type ) : string
    {
        return match ( strtolower( $type ) )
        {
            'file'                 => self::FILE ,
            'dir' , 'cdir' , 'pdir' => self::DIRECTORY ,
            default                => self::UNKNOWN ,
        } ;
    }

    /**
     * Maps the leading character of a Unix `ls -l` line to a file-type constant.
     *
     * @param string $char The first character of the permissions field.
     *
     * @return string One of the file-type constants.
     */
    public static function fromUnixChar( string $char ) : string
    {
        return match ( $char )
        {
            'd'     => self::DIRECTORY ,
            'l'     => self::LINK ,
            '-'     => self::FILE ,
            default => self::UNKNOWN ,
        } ;
    }
}
