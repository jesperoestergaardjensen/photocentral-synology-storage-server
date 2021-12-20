<?php

namespace PhotoCentralSynologyStorageServer\Service;

use LinuxFileSystemHelper\FileHelper;
use LinuxFileSystemHelper\FolderHelper;
use PhotoCentralSynologyStorageServer\Repository\SynologyPhotoCollectionRepository;

class PhotoImportService
{
    private const EXCLUDE_PICASA_ORIGINALS_FOLDERS     = ' -name .picasaoriginals -prune -o ';
    private const EXCLUDE_SYNOLOGY_MEDIE_INDEX_FOLDERS = ' -name @eaDir -prune -o ';
    private const EXCLUDE_SYNOLOGY_TRASH_BIN_FOLDERS   = ' -name \'#recycle\' -prune -o ';

    private SynologyPhotoCollectionRepository $synology_photo_collection_repository;

    public function __construct(SynologyPhotoCollectionRepository $synology_photo_collection_repository)
    {
        $this->synology_photo_collection_repository = $synology_photo_collection_repository;
    }
    
    public function import()
    {
        $this->synology_photo_collection_repository->connectToDb();
        $synology_photo_collection_list = $this->synology_photo_collection_repository->list();

        foreach ($synology_photo_collection_list as $synology_photo_collection) {

            $newFilenameAndPath = $this->getNewStatusFileName($synology_photo_collection->getId(), $synology_photo_collection->getStatusFilesPath());
            $oldFilenameAndPath = $this->getOldStatusFileName($synology_photo_collection->getId(), $synology_photo_collection->getStatusFilesPath());

            FileHelper::createFileIfNotExists($oldFilenameAndPath);
            FileHelper::createFileIfNotExists($newFilenameAndPath);

            // If source path is e.g. unmounted return
            if (!file_exists($synology_photo_collection->getImageSourcePath())) {
                return;
            }

            $file_list = FolderHelper::listFilesRecursiveFromFolder($synology_photo_collection->getImageSourcePath(), '.jpg');
            file_put_contents($newFilenameAndPath, implode(PHP_EOL,$file_list));
            $diffBetweenFiles = FileHelper::diffFiles($newFilenameAndPath, $oldFilenameAndPath);

            // TODO : Next steps are propbably
            //$this->handleMovedOrRemovedFiles($diffFiles, $photo_source_uuid);
            //$this->handleNewFiles($diffFiles, $photo_source_uuid);
        }
    }

    private function getNewStatusFileName(string $synology_photo_collection_id, string $status_files_path): string
    {
        return $status_files_path . "SynologyPhotoCollection-" . $synology_photo_collection_id . "-new.txt";
    }

    private function getOldStatusFileName(string $synology_photo_collection_id, string $status_files_path): string
    {
        return $status_files_path . "SynologyPhotoCollection-" . $synology_photo_collection_id . "-old.txt";
    }
}
