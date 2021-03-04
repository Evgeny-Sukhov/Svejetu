<?php

namespace PhpZip\Extra;

use PhpZip\Exception\ZipException;
use PhpZip\Extra\Fields\ApkAlignmentExtraField;
use PhpZip\Extra\Fields\DefaultExtraField;
use PhpZip\Extra\Fields\JarMarkerExtraField;
use PhpZip\Extra\Fields\NtfsExtraField;
use PhpZip\Extra\Fields\WinZipAesEntryExtraField;
use PhpZip\Extra\Fields\Zip64ExtraField;
use PhpZip\Model\ZipEntry;
use PhpZip\Util\StringUtil;

/**
 * Extra Fields Factory.
 *
 * @author Ne-Lexa alexey@nelexa.ru
 * @license MIT
 */
class ExtraFieldsFactory
{
    /** @var array|null */
    protected static $registry;

    private function __construct()
    {
    }

    /**
     * @param string        $extra
     * @param ZipEntry|null $entry
     *
     * @throws ZipException
     *
     * @return ExtraFieldsCollection
     */
    public static function createExtraFieldCollections($extra, ZipEntry $entry = null)
    {
        $extraFieldsCollection = new ExtraFieldsCollection();

        if ($extra !== null) {
            $extraLength = \strlen($extra);

            if ($extraLength > 0xffff) {
                throw new ZipException('Extra Fields too large: ' . $extraLength);
            }
            $pos = 0;
            $endPos = $extraLength;

            while ($endPos - $pos >= 4) {
                $unpack = unpack('vheaderId/vdataSize', substr($extra, $pos, 4));
                $pos += 4;
                $headerId = (int) $unpack['headerId'];
                $dataSize = (int) $unpack['dataSize'];
                $extraField = self::create($headerId);

                if ($extraField instanceof Zip64ExtraField && $entry !== null) {
                    $extraField->setEntry($entry);
                }

                if ($dataSize > 0) {
                    $content = substr($extra, $pos, $dataSize);

                    if ($content !== false) {
                        $extraField->deserialize($content);
                        $extraFieldsCollection[$headerId] = $extraField;
                    }
                }

                $pos += $dataSize;
            }
        }

        return $extraFieldsCollection;
    }

    /**
     * @param ExtraFieldsCollection $extraFieldsCollection
     *
     * @throws ZipException
     *
     * @return string
     */
    public static function createSerializedData(ExtraFieldsCollection $extraFieldsCollection)
    {
        $extraData = '';

        foreach ($extraFieldsCollection as $extraField) {
            $data = $extraField->serialize();
            $extraData .= pack('vv', $extraField::getHeaderId(), \strlen($data));
            $extraData .= $data;
        }

        $size = \strlen($extraData);

        if ($size < 0x0000 || $size > 0xffff) {
            throw new ZipException('Size extra out of range: ' . $size . '. Extra data: ' . $extraData);
        }

        return $extraData;
    }

    /**
     * A static factory method which creates a new Extra Field based on the
     * given Header ID.
     * The returned Extra Field still requires proper initialization, for
     * example by calling ExtraField::readFrom.
     *
     * @param int $headerId an unsigned short integer (two bytes) which indicates
     *                      the type of the returned Extra Field
     *
     * @throws ZipException if headerId is out of range
     *
     * @return ExtraField a new Extra Field or null if not support header id
     */
    public static function create($headerId)
    {
        if ($headerId < 0x0000 || $headerId > 0xffff) {
            throw new ZipException('headerId out of range');
        }

        if (isset(self::getRegistry()[$headerId])) {
            $extraClassName = self::getRegistry()[$headerId];
            /** @var ExtraField $extraField */
            $extraField = new $extraClassName();

            if ($headerId !== $extraField::getHeaderId()) {
                throw new ZipException('Runtime error support headerId ' . $headerId);
            }
        } else {
            $extraField = new DefaultExtraField($headerId);
        }

        return $extraField;
    }

    /**
     * Registered extra field classes.
     *
     * @return array
     */
    protected static function getRegistry()
    {
        if (self::$registry === null) {
            self::$registry[WinZipAesEntryExtraField::getHeaderId()] = WinZipAesEntryExtraField::class;
            self::$registry[NtfsExtraField::getHeaderId()] = NtfsExtraField::class;
            self::$registry[Zip64ExtraField::getHeaderId()] = Zip64ExtraField::class;
            self::$registry[ApkAlignmentExtraField::getHeaderId()] = ApkAlignmentExtraField::class;
            self::$registry[JarMarkerExtraField::getHeaderId()] = JarMarkerExtraField::class;
        }

        return self::$registry;
    }

    /**
     * @return WinZipAesEntryExtraField
     */
    public static function createWinZipAesEntryExtra()
    {
        return new WinZipAesEntryExtraField();
    }

    /**
     * @return NtfsExtraField
     */
    public static function createNtfsExtra()
    {
        return new NtfsExtraField();
    }

    /**
     * @param ZipEntry $entry
     *
     * @return Zip64ExtraField
     */
    public static function createZip64Extra(ZipEntry $entry)
    {
        return new Zip64ExtraField($entry);
    }

    /**
     * @param ZipEntry $entry
     * @param int      $padding
     *
     * @return ApkAlignmentExtraField
     */
    public static function createApkAlignExtra(ZipEntry $entry, $padding)
    {
        $padding = (int) $padding;
        $multiple = 4;

        if (StringUtil::endsWith($entry->getName(), '.so')) {
            $multiple = ApkAlignmentExtraField::ANDROID_COMMON_PAGE_ALIGNMENT_BYTES;
        }
        $extraField = new ApkAlignmentExtraField();
        $extraField->setMultiple($multiple);
        $extraField->setPadding($padding);

        return $extraField;
    }
}
