<?php

namespace PhotoCentralSynologyStorageServer\Service;

use PhotoCentralSynologyStorageServer\Exception\PhotoCentralSynologyServerException;
use PhotoCentralSynologyStorageServer\Factory\PhotoFactory;
use PhotoCentralSynologyStorageServer\Model\LinuxFile;
use PhotoCentralSynologyStorageServer\Model\SynologyPhotoCollection;
use PhotoCentralSynologyStorageServer\Repository\LinuxFileRepository;
use PhotoCentralSynologyStorageServer\Repository\PhotoRepository;

class PhotoBulkAddService
{
    private LinuxFileRepository $linux_file_repository;
    private PhotoRepository $photo_repository;
    private PhotoFactory $photo_factory;

    public function __construct(
        LinuxFileRepository $linux_file_repository,
        PhotoRepository $photo_repository,
        PhotoFactory $photo_factory
    ) {
        $this->linux_file_repository = $linux_file_repository;
        $this->photo_repository = $photo_repository;
        $this->photo_factory = $photo_factory;
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
        $photo_list_to_bulk_insert = [];
        $inode_list_used_to_bulk_update = [];
        $this->photo_repository->connectToDb();

        foreach ($linux_files as $linux_file) {

            $new_photo = $this->photo_factory->create($linux_file, $synology_photo_collection);

            if ($new_photo) {
                $photo_list_to_bulk_insert[] = $new_photo;
                $inode_list_used_to_bulk_update[] = $linux_file->getInodeIndex();
            }
        }

        $mysql_warnings = $this->photo_repository->bulkAdd($photo_list_to_bulk_insert);
        $this->linux_file_repository->bulkSetImported($synology_photo_collection->getId(),
            $inode_list_used_to_bulk_update, time());
    }
}
