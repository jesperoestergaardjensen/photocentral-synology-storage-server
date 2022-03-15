<?php

namespace PhotoCentralSynologyStorageServer\Factory;

use PhotoCentralStorage\Exception\ExifFactoryException;
use PhotoCentralStorage\Exception\PhotoCentralStorageException;
use PhotoCentralStorage\Factory\ExifDataFactory;
use PhotoCentralStorage\Photo;
use PhotoCentralSynologyStorageServer\Model\LinuxFile;
use PhotoCentralSynologyStorageServer\Model\SynologyPhotoCollection;
use PhotoCentralSynologyStorageServer\Repository\LinuxFileRepository;

class PhotoFactory
{
    private LinuxFileRepository $linux_file_repository;

    public function __construct(LinuxFileRepository $linux_file_repository)
    {
        $this->linux_file_repository = $linux_file_repository;
    }

    public function create(LinuxFile $linux_file, SynologyPhotoCollection $synology_photo_collection): ?Photo
    {
        $inode_index = $linux_file->getInodeIndex();
        $collection_id = $synology_photo_collection->getId();
        $base_path = $synology_photo_collection->getImageSourcePath();

        try {
            // TODO : We are not saving the GPS data - why not?
            $exif_data = ExifDataFactory::createExifData($linux_file->getFullFileNameAndPath($base_path));
        } catch (PhotoCentralStorageException | ExifFactoryException $exception) {
            $this->linux_file_repository->setSkippedError($inode_index, $collection_id, $exception->getMessage());
            return null;
        }

        if ($exif_data->getWidth() === null || $exif_data->getHeight() === null || $exif_data->getOrientation() === null) {
            $this->linux_file_repository->setSkippedError($inode_index, $collection_id, 'Image width, height or orientation is NULL');
            return null;
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
                $linux_file->getLastModifiedDateTime(),
                null,
                $exif_data->getCameraBrand(),
                $exif_data->getCameraModel(),
            );
        } catch (PhotoCentralStorageException $exception) {
            $this->linux_file_repository->setSkippedError($inode_index, $collection_id, $exception->getMessage());
            return null;
        }

        return $new_photo;
    }
}