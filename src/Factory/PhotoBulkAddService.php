<?php

namespace PhotoCentralSynologyStorageServer\Factory;

use Exception;
use PhotoCentralStorage\Exception\ExifFactoryException;
use PhotoCentralStorage\Exception\PhotoCentralStorageException;
use PhotoCentralStorage\Factory\ExifDataFactory;
use PhotoCentralStorage\Photo;
use PhotoCentralSynologyStorageServer\Exception\PhotoCentralSynologyServerException;
use PhotoCentralSynologyStorageServer\Model\LinuxFile;
use PhotoCentralSynologyStorageServer\Model\SynologyPhotoCollection;
use PhotoCentralSynologyStorageServer\Repository\LinuxFileRepository;
use PhotoCentralSynologyStorageServer\Repository\PhotoRepository;

class PhotoBulkAddService
{
    private LinuxFileRepository $linux_file_repository;
    private PhotoRepository $photo_repository;

    public function __construct(LinuxFileRepository $linux_file_repository, PhotoRepository $photo_repository)
    {
        $this->linux_file_repository = $linux_file_repository;
        $this->photo_repository = $photo_repository;
    }

    /**
     * @param LinuxFile[]             $linux_files
     * @param SynologyPhotoCollection $synology_photo_collection
     *
     * @return void
     * @throws PhotoCentralSynologyServerException
     */
    public function addPhotosToDatabase(array $linux_files, SynologyPhotoCollection $synology_photo_collection): void
    {
        $base_path = $synology_photo_collection->getImageSourcePath();
        $photo_list_to_bulk_insert = [];
        $inode_list_used_to_bulk_update = [];
        $this->photo_repository->connectToDb();

        foreach ($linux_files as $linux_file) {

            try {
                // TODO : We are not saving the GPS data - why not?
                $exif_data = ExifDataFactory::createExifData($linux_file->getFullFileNameAndPath($base_path));
            } catch (PhotoCentralStorageException | ExifFactoryException $exception) {
                $this->linux_file_repository->setSkippedError($linux_file->getInodeIndex(),
                    $synology_photo_collection->getId(), $exception->getMessage());
                continue;
            }
// TODO : Do we need this - should it be handled in ExifDataFactory::ThrowExceptionIfValidationFails method ? - Update 16-02-22 : ExifDataFactory is not the right place to put the check!
            if (
                $exif_data->getWidth() === null ||
                $exif_data->getHeight() === null ||
                $exif_data->getOrientation() === null
            ) {
                $this->linux_file_repository->setSkippedError($linux_file->getInodeIndex(), $synology_photo_collection->getId(), 'Image with, height or orientation is NULL');
                continue;
            }

            try {
                $new_photo = new Photo(
                    $linux_file->getPhotoUuid(),
                    $synology_photo_collection->getId(),
                    $exif_data->getWidth(),
                    $exif_data->getHeight(),
                    $exif_data->getOrientation(),
                    time(),
                    $exif_data->getExifDateTime(),
                    $linux_file->getLastModifiedDate(),
                    null,
                    $exif_data->getCameraBrand(),
                    $exif_data->getCameraModel(),
                );
            } catch (PhotoCentralStorageException $exception) {
                $this->linux_file_repository->setSkippedError($linux_file->getInodeIndex(),
                    $synology_photo_collection->getId(), $exception->getMessage());
                continue;
            }

            $photo_list_to_bulk_insert[] = $new_photo;
            $inode_list_used_to_bulk_update[] = $linux_file->getInodeIndex();
        }

        $mysql_warnings = $this->photo_repository->bulkAdd($photo_list_to_bulk_insert);
        $this->linux_file_repository->bulkSetImported($synology_photo_collection->getId(), $inode_list_used_to_bulk_update, time());
    }
}
