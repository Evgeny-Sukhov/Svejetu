<?php

namespace PhpZip\Stream;

use PhpZip\Exception\ZipException;
use PhpZip\Model\ZipEntry;
use PhpZip\Model\ZipModel;

/**
 * Read zip file.
 *
 * @author Ne-Lexa alexey@nelexa.ru
 * @license MIT
 */
interface ZipInputStreamInterface
{
    /**
     * @return ZipModel
     */
    public function readZip();

    /**
     * Read central directory entry.
     *
     * @param resource $stream
     *
     * @throws ZipException
     *
     * @return ZipEntry
     */
    public function readCentralDirectoryEntry($stream);

    /**
     * @param ZipEntry $entry
     *
     * @throws ZipException
     *
     * @return string
     */
    public function readEntryContent(ZipEntry $entry);

    /**
     * @return resource
     */
    public function getStream();

    /**
     * Copy the input stream of the LOC entry zip and the data into
     * the output stream and zip the alignment if necessary.
     *
     * @param ZipEntry                 $entry
     * @param ZipOutputStreamInterface $out
     */
    public function copyEntry(ZipEntry $entry, ZipOutputStreamInterface $out);

    /**
     * @param ZipEntry                 $entry
     * @param ZipOutputStreamInterface $out
     */
    public function copyEntryData(ZipEntry $entry, ZipOutputStreamInterface $out);

    public function close();
}
