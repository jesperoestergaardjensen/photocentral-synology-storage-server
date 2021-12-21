<?php

namespace PhotoCentralSynologyStorageServer\Service;

use LinuxFileSystemHelper\FileHelper;
use LinuxFileSystemHelper\FolderHelper;
use PhotoCentralSynologyStorageServer\Exception\PhotoCentralSynologyServerException;
use PhotoCentralSynologyStorageServer\Factory\LinuxFileFactory;
use PhotoCentralSynologyStorageServer\Model\PhotoCollectionFolderDiffResult;
use PhotoCentralSynologyStorageServer\Model\SynologyPhotoCollection;
use PhotoCentralSynologyStorageServer\Repository\LinuxFileRepository;
use PhotoCentralSynologyStorageServer\Repository\SynologyPhotoCollectionRepository;

class PhotoImportService
{
    private const EXCLUDE_PICASA_ORIGINALS_FOLDERS = '.picasaoriginals';
    private const EXCLUDE_SYNOLOGY_MEDIE_INDEX_FOLDERS = '@eaDir';
    private const EXCLUDE_SYNOLOGY_TRASH_BIN_FOLDERS = '\'#recycle\'';

    private SynologyPhotoCollectionRepository $synology_photo_collection_repository;
    private LinuxFileRepository $linux_file_repository;

    public function __construct(
        SynologyPhotoCollectionRepository $synology_photo_collection_repository,
        LinuxFileRepository $linux_file_repository
    ) {
        $this->synology_photo_collection_repository = $synology_photo_collection_repository;
        $this->linux_file_repository = $linux_file_repository;
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
            if (! file_exists($synology_photo_collection->getImageSourcePath())) {
                return;
            }

            $file_list = FolderHelper::listFilesRecursiveFromFolder($synology_photo_collection->getImageSourcePath(),
            '.jpg', [
                self::EXCLUDE_PICASA_ORIGINALS_FOLDERS,
                self::EXCLUDE_SYNOLOGY_MEDIE_INDEX_FOLDERS,
                self::EXCLUDE_SYNOLOGY_TRASH_BIN_FOLDERS,
            ]);

            // Save new status file
            file_put_contents($newFilenameAndPath, implode(PHP_EOL, $file_list));

            // Diff new and old status file to get changes in photo collection folder
            $diffInPhotoCollectionFolder = FileHelper::diffFiles($newFilenameAndPath, $oldFilenameAndPath);

            $this->updateDatabaseWithFileSystemChanges($diffInPhotoCollectionFolder, $synology_photo_collection);

            // Make new file the old file
            FileHelper::copyFile($oldFilenameAndPath, $newFilenameAndPath);
        }
    }

    private function updateDatabaseWithFileSystemChanges(
        array $diffInPhotoCollectionFolder,
        SynologyPhotoCollection $synology_photo_collection
    ): void {
        $photo_collection_folder_diff_result = new PhotoCollectionFolderDiffResult();

        foreach ($diffInPhotoCollectionFolder as $changed_linux_file_entry) {
            if ($this->isNewLinuxFile($changed_linux_file_entry)) {
                $photo_collection_folder_diff_result->addEntryToAddedMap(trim($changed_linux_file_entry, '< '),
                    $synology_photo_collection);
            } else {
                if ($this->isRemovedLinuxFile($changed_linux_file_entry)) {
                    $photo_collection_folder_diff_result->addEntryToRemovedMap(trim($changed_linux_file_entry, '> '));
                }
            }
        }

        $this->linux_file_repository->connectToDb();

        $this->updateMovedFilesInDatabase($photo_collection_folder_diff_result);
        $this->removeDeltedFilesFromDatabase($photo_collection_folder_diff_result, $synology_photo_collection);
        // TODO : Can we handle this exception in a nice manner?
        $this->addNewFilesToDatabase($photo_collection_folder_diff_result);
    }

    private function isRemovedLinuxFile(string $diffEntry): bool
    {
        return mb_substr($diffEntry, 0, 1, 'utf-8') == '>';
    }

    private function isNewLinuxFile(string $diffEntry): bool
    {
        return (mb_substr($diffEntry, 0, 1, 'utf-8') == '<');
    }

    private function getNewStatusFileName(string $synology_photo_collection_id, string $status_files_path): string
    {
        return $status_files_path . "SynologyPhotoCollection-" . $synology_photo_collection_id . "-new.txt";
    }

    private function getOldStatusFileName(string $synology_photo_collection_id, string $status_files_path): string
    {
        return $status_files_path . "SynologyPhotoCollection-" . $synology_photo_collection_id . "-old.txt";
    }

    private function updateMovedFilesInDatabase(PhotoCollectionFolderDiffResult $photo_collection_folder_diff_result
    ): void {
        $removed_map = $photo_collection_folder_diff_result->getRemovedLinuxFilesMap();
        $added_map = $photo_collection_folder_diff_result->getAddedLinuxFileMap();
        foreach ($removed_map as $inode_index => $dont_care) {
            if (array_key_exists($inode_index, $added_map)) {
                $updated_linux_file = $added_map[$inode_index];
                $updated_linux_file->setImported(true);
                $updated_linux_file->setImportDate(time());
                $this->linux_file_repository->update($updated_linux_file);
                $photo_collection_folder_diff_result->removeEntryFromRemovedLinuxMap($inode_index);
            }
        }
    }

    private function removeDeltedFilesFromDatabase(
        PhotoCollectionFolderDiffResult $photo_collection_folder_diff_result,
        SynologyPhotoCollection $synology_photo_collection
    ): void {
        $removed_map = $photo_collection_folder_diff_result->getRemovedLinuxFilesMap();
        foreach ($removed_map as $inode_index => $diff_entry) {

            $this->linux_file_repository->delete($synology_photo_collection->getId(), $inode_index);

            try {
                $this->linux_file_repository->list($inode_index);
            } catch (PhotoCentralSynologyServerException $photo_central_synology_server_exception) {
                // If last linux file of photo is removed - remove entry in Photo db table as well
                $linux_file = LinuxFileFactory::createLinuxFileFromDiffEntry($diff_entry, $synology_photo_collection);
                // $photo_repository = new PhotoRepository($this->database_connection);
                // $photo_repository->delete($synology_photo_collection->getId(), $linux_file->getPhotoUuid());
            }
        }
    }

    /**
     * @throws PhotoCentralSynologyServerException
     */
    private function addNewFilesToDatabase(PhotoCollectionFolderDiffResult $photo_collection_folder_diff_result): void
    {
        $linux_files_to_bulk_insert = $photo_collection_folder_diff_result->getAddedLinuxFileMap();

        try {
            $this->linux_file_repository->bulkAdd($linux_files_to_bulk_insert);
        } catch (PhotoCentralSynologyServerException $photo_central_synology_server_exception) {
            throw new PhotoCentralSynologyServerException("Cannot bulk add linux files. {$photo_central_synology_server_exception->getMessage()}");
        }
    }
}
