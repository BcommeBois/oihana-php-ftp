<?php

namespace oihana\ftp\schema ;

use DateTimeImmutable ;
use DateTimeZone ;

use oihana\ftp\enums\FtpFileType ;

/**
 * A typed description of a single entry in a remote directory listing.
 *
 * Instances are produced either from a structured MLSD entry ({@see fromMlsd()}) or
 * by parsing a raw `ls -l` line (see {@see \oihana\ftp\utils\RawListParser}).
 *
 * @package oihana\ftp\schema
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
class FtpFile
{
    /**
     * Creates a new listing entry.
     *
     * @param string      $name         The entry name.
     * @param string      $type         One of the {@see FtpFileType} constants.
     * @param int         $size         The size in bytes (0 when unknown).
     * @param int|null    $modifiedTime The last-modified Unix timestamp, or null when unknown.
     * @param string|null $permissions  The permission string (e.g. `drwxr-xr-x`) or mode, when known.
     * @param string|null $owner        The owner, when known.
     * @param string|null $group        The group, when known.
     * @param string|null $target       The link target for a symbolic link, when known.
     */
    public function __construct(
        public string  $name         = '' ,
        public string  $type         = FtpFileType::UNKNOWN ,
        public int     $size         = 0 ,
        public ?int    $modifiedTime = null ,
        public ?string $permissions  = null ,
        public ?string $owner        = null ,
        public ?string $group        = null ,
        public ?string $target       = null ,
    )
    {
    }

    /**
     * Builds an entry from a structured MLSD record (as returned by `ftp_mlsd`).
     *
     * @param array<string,mixed> $entry The MLSD facts (`name`, `type`, `size`, `modify`, …).
     *
     * @return self The hydrated entry.
     */
    public static function fromMlsd( array $entry ) : self
    {
        $modify = isset( $entry[ 'modify' ] ) ? self::parseMlsdTime( (string) $entry[ 'modify' ] ) : null ;

        return new self(
            name         : (string) ( $entry[ 'name' ] ?? '' ) ,
            type         : FtpFileType::fromMlsd( (string) ( $entry[ 'type' ] ?? '' ) ) ,
            size         : (int) ( $entry[ 'size' ] ?? 0 ) ,
            modifiedTime : $modify ,
            permissions  : isset( $entry[ 'UNIX.mode' ] ) ? (string) $entry[ 'UNIX.mode' ] : ( $entry[ 'perm' ] ?? null ) ,
            owner        : isset( $entry[ 'UNIX.owner' ] ) ? (string) $entry[ 'UNIX.owner' ] : null ,
            group        : isset( $entry[ 'UNIX.group' ] ) ? (string) $entry[ 'UNIX.group' ] : null ,
        ) ;
    }

    /**
     * Indicates whether the entry is a directory.
     *
     * @return bool True for a directory.
     */
    public function isDirectory() : bool
    {
        return $this->type === FtpFileType::DIRECTORY ;
    }

    /**
     * Indicates whether the entry is a regular file.
     *
     * @return bool True for a file.
     */
    public function isFile() : bool
    {
        return $this->type === FtpFileType::FILE ;
    }

    /**
     * Indicates whether the entry is a symbolic link.
     *
     * @return bool True for a symbolic link.
     */
    public function isLink() : bool
    {
        return $this->type === FtpFileType::LINK ;
    }

    // ----------- Private

    /**
     * Parses an MLSD `modify` fact (`YYYYMMDDHHMMSS`, UTC) into a Unix timestamp.
     *
     * @param string $modify The MLSD modify fact.
     *
     * @return int|null The Unix timestamp, or null when the value cannot be parsed.
     */
    private static function parseMlsdTime( string $modify ) : ?int
    {
        $date = DateTimeImmutable::createFromFormat( '!YmdHis' , $modify , new DateTimeZone( 'UTC' ) ) ;

        return $date === false ? null : $date->getTimestamp() ;
    }
}
