<?php

namespace Flynax\Plugins\ExportImport\Adapters;

use Flynax\Utils\Archive as FlynaxArchive;

/**
 * // todo: Remove after plugin compatible will be >=4.7.0
 *
 * Class ZipArchiveAdapter
 *
 * @since 3.6.1
 */
class ZipArchiveAdapter
{
    /**
     * Extract archive to the provided destination
     *
     * @param string $archive            - Path to archive
     * @param string $destination        - Destination where do you want to extract archive
     * @param bool   $shoulRemoveArchive - Should script remove zip archive after extracting it
     *
     * @return void
     */
    public static function extract($archive, $destination, $shoulRemoveArchive = true)
    {
        if (!is_dir($destination)) {
            $GLOBALS['reefless']->rlMkdir($destination);
        }
        chmod($archive, 0644);

        if (class_exists(FlynaxArchive::class)) {
            FlynaxArchive::unpack($archive, $destination, $shoulRemoveArchive);
            return;
        }

        if (!$GLOBALS['rlDebug']) {
            $GLOBALS['refless']->loadClass('Debug');
        }

        $zipFile = new \PhpZip\ZipFile();
        try {
            $zipFile->openFile($archive)->extractTo($destination);
        } catch (\PhpZip\Exception\ZipException $e) {
            $GLOBALS['rlDebug']->logger("Import/Export plugin reading archive error: {$e->getMessage()}");
        } finally {
            $zipFile->close();
        }
    }
}
