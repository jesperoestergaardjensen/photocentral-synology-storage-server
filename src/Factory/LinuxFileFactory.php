<?php

namespace PhotoCentralSynologyStorageServer\Factory;

use PhotoCentralSynologyStorageServer\Model\LinuxFile;
use PhotoCentralSynologyStorageServer\Model\SynologyPhotoCollection;
use PhotoCentralSynologyStorageServer\Service\UUIDService;

class LinuxFileFactory
{
    /**
     * Expected diff entry format is:
     *
     * inode_index;date;path_and_filename
     * e.g.
     * 293177;2016-03-20;/home/webadmin/www/photoCentral/storage/app/SimplePhotoSourceExampleImages/Extra added/23199745_P1150924_1920xX.JPG
     *
     * @param string                  $diff_entry
     * @param SynologyPhotoCollection $synology_photo_collection
     *
     * @return LinuxFile
     */
    public static function createLinuxFileFromDiffEntry(string $diff_entry, SynologyPhotoCollection $synology_photo_collection): LinuxFile
    {
        /* Get filename and inodeIndex */
        $explodedFileDate = explode(';', trim($diff_entry));
        $inodeIndex = $explodedFileDate[0];
        $fileModifyDate = $explodedFileDate[1];
        $filename = $explodedFileDate[2];
        $filenameParts = pathinfo($filename);

        $linux_file_path = self::generate_file_path($filenameParts['dirname'], $synology_photo_collection->getImageSourcePath());

        return new LinuxFile(
            $synology_photo_collection->getId(),
            $inodeIndex,
            strtotime($fileModifyDate),
            $filenameParts['basename'],
            $linux_file_path,
            PhotoUuidFactory::generatePhotoUuid($synology_photo_collection->getImageSourcePath() . $linux_file_path . $filenameParts['basename']),
            UUIDService::create()
        );
    }

    /**
     * Removes base path from complete path and adjust slashed
     *
     * @param string $complete_file_path
     * @param string $image_source_path
     *
     * @return string
     */
    private static function generate_file_path(string $complete_file_path, string $image_source_path): string
    {
        // Strip trailing '/'
        $adjusted_image_source_path = rtrim($image_source_path, '/');

        // Remove source_path from file_path
        $file_path = str_replace($adjusted_image_source_path, '', $complete_file_path);

        // If "extra" path found move slash from beginning to end to match rest of the system
        if ($file_path !== '') {
            $file_path = ltrim($file_path, '/') . DIRECTORY_SEPARATOR;
        }

        return $file_path;
    }
}
