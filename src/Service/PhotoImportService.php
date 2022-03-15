<?php

namespace PhotoCentralSynologyStorageServer\Service;

use LinuxFileSystemHelper\FileHelper;
use LinuxFileSystemHelper\FolderHelper;
use PhotoCentralSynologyStorageServer\Exception\PhotoCentralSynologyServerException;
use PhotoCentralSynologyStorageServer\Factory\LinuxFileFactory;
use PhotoCentralSynologyStorageServer\Model\FileSystemDiffReport;
use PhotoCentralSynologyStorageServer\Model\FileSystemDiffReportList;
use PhotoCentralSynologyStorageServer\Model\SynologyPhotoCollection;
use PhotoCentralSynologyStorageServer\Repository\LinuxFileRepository;
use PhotoCentralSynologyStorageServer\Repository\PhotoRepository;
use PhotoCentralSynologyStorageServer\Repository\SynologyPhotoCollectionRepository;

class PhotoImportService
{
    private const EXCLUDE_PICASA_ORIGINALS_FOLDERS = '.picasaoriginals';
    private const EXCLUDE_SYNOLOGY_MEDIE_INDEX_FOLDERS = '@eaDir';
    private const EXCLUDE_SYNOLOGY_TRASH_BIN_FOLDERS = '\'#recycle\'';
    private const EXCLUDE_LINUX_TRASH_FOLDERS = '.Trash-1000';
    private const EXCLUDE_LINUX_SYSTEM_FOLDERS = 'lost+found';

    private SynologyPhotoCollectionRepository $synology_photo_collection_repository;
    private LinuxFileRepository $linux_file_repository;
    private FileSystemDiffReportList $file_system_diff_report_list;
    private PhotoBulkAddService $photo_add_service;
    private PhotoRepository $photo_repository;

    public function __construct(
        SynologyPhotoCollectionRepository $synology_photo_collection_repository,
        LinuxFileRepository $linux_file_repository,
        PhotoBulkAddService $photo_factory,
        PhotoRepository $photo_repository
    ) {
        $this->synology_photo_collection_repository = $synology_photo_collection_repository;
        $this->linux_file_repository = $linux_file_repository;
        $this->file_system_diff_report_list = new FileSystemDiffReportList();
        $this->photo_add_service = $photo_factory;
        $this->photo_repository = $photo_repository;
    }

    public function import(): FileSystemDiffReportList
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
                return new FileSystemDiffReportList(); // Return empty result
            }

            $file_list = FolderHelper::listFilesRecursiveFromFolder($synology_photo_collection->getImageSourcePath(),
            '.jpg', [
                self::EXCLUDE_PICASA_ORIGINALS_FOLDERS,
                self::EXCLUDE_SYNOLOGY_MEDIE_INDEX_FOLDERS,
                self::EXCLUDE_SYNOLOGY_TRASH_BIN_FOLDERS,
                self::EXCLUDE_LINUX_TRASH_FOLDERS,
                self::EXCLUDE_LINUX_SYSTEM_FOLDERS
            ]);

            // Save new status file
            file_put_contents($newFilenameAndPath, implode(PHP_EOL, $file_list));

            // Diff new and old status file to get changes in photo collection folder
            $diffInPhotoCollectionFolder = FileHelper::diffFiles($newFilenameAndPath, $oldFilenameAndPath);

            $file_system_diff_report = $this->updateDatabaseWithFileSystemChanges($diffInPhotoCollectionFolder, $synology_photo_collection);

            // Make new file the old file
            FileHelper::copyFile($newFilenameAndPath, $oldFilenameAndPath);

            $this->file_system_diff_report_list->add($file_system_diff_report, $synology_photo_collection->getId());

            $this->addPhotosToDatabase($synology_photo_collection, 1000);
        }

        // TODO : Consider to make an import report from the result here, with count imported and count on skipped
        return $this->file_system_diff_report_list;
    }

    private function updateDatabaseWithFileSystemChanges(
        array $diffInPhotoCollectionFolder,
        SynologyPhotoCollection $synology_photo_collection
    ): FileSystemDiffReport {
        $file_system_diff_report = new FileSystemDiffReport();

        foreach ($diffInPhotoCollectionFolder as $changed_linux_file_entry) {
            if ($this->isNewLinuxFile($changed_linux_file_entry)) {
                $file_system_diff_report->addEntryToAddedMap(trim($changed_linux_file_entry, '< '),
                    $synology_photo_collection);
            } else {
                if ($this->isRemovedLinuxFile($changed_linux_file_entry)) {
                    $file_system_diff_report->addEntryToRemovedMap(trim($changed_linux_file_entry, '> '));
                }
            }
        }

        $this->linux_file_repository->connectToDb();

        $this->updateMovedFilesInDatabase($file_system_diff_report);
        $this->removeDeletedFilesFromDatabase($file_system_diff_report, $synology_photo_collection);
        $this->addNewFilesToDatabase($file_system_diff_report);

        return $file_system_diff_report;
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

    private function updateMovedFilesInDatabase(FileSystemDiffReport $file_system_diff_report
    ): void {
        $removed_map = $file_system_diff_report->getRemovedLinuxFilesMap();
        $added_map = $file_system_diff_report->getAddedLinuxFilesMap();
        foreach ($removed_map as $inode_index => $dont_care) {
            if (array_key_exists($inode_index, $added_map)) {
                $updated_linux_file = $added_map[$inode_index];
                $updated_linux_file->setImported(true);
                $updated_linux_file->setImportDate(time());
                $this->linux_file_repository->update($updated_linux_file);
                $file_system_diff_report->removeEntryFromRemovedLinuxFilesMap($inode_index);
                $file_system_diff_report->removeEntryFromAddedLinuxFilesMap($inode_index);
                $file_system_diff_report->addEntryToMovedMap($updated_linux_file);
            }
        }
    }

    private function removeDeletedFilesFromDatabase(
        FileSystemDiffReport $file_system_diff_report,
        SynologyPhotoCollection $synology_photo_collection
    ): void {
        $removed_map = $file_system_diff_report->getRemovedLinuxFilesMap();
        foreach ($removed_map as $inode_index => $diff_entry) {

            $deleted_linux_file = $this->linux_file_repository->getByInode($inode_index, $synology_photo_collection->getId());
            $this->linux_file_repository->delete($synology_photo_collection->getId(), $inode_index);

            try {
                $this->linux_file_repository->getByPhotoUuid($deleted_linux_file->getPhotoUuid(), $synology_photo_collection->getId());
            } catch (PhotoCentralSynologyServerException $photo_central_synology_server_exception) {
                // If last linux file of photo is removed - remove entry in Photo db table as well
                $this->photo_repository->delete($deleted_linux_file->getPhotoUuid(), $synology_photo_collection->getId());
            }
        }
    }

    private function addNewFilesToDatabase(FileSystemDiffReport $photo_collection_folder_diff_result): void
    {
        $linux_files_to_bulk_insert = $photo_collection_folder_diff_result->getAddedLinuxFilesMap();
        $this->linux_file_repository->bulkAdd($linux_files_to_bulk_insert);
    }

    private function addPhotosToDatabase(SynologyPhotoCollection $synology_photo_collection, int $limit = 100): void
    {
        $linux_file_list = $this->linux_file_repository->listLinuxFilesNotImported($synology_photo_collection->getId(), $limit);
        $this->photo_add_service->addPhotosToDatabase($linux_file_list, $synology_photo_collection);
    }
}
