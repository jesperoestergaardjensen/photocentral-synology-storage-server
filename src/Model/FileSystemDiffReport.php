<?php

namespace PhotoCentralSynologyStorageServer\Model;

use PhotoCentralSynologyStorageServer\Factory\LinuxFileFactory;
use PhotoCentralSynologyStorageServer\Model\FileSystemDiffReport\FileAdded;
use PhotoCentralSynologyStorageServer\Model\FileSystemDiffReport\FileMoved;
use PhotoCentralSynologyStorageServer\Model\FileSystemDiffReport\FileRemoved;

class FileSystemDiffReport
{
    /**
     * [inode_index => LinuxFile, inode_index => LinuxFile ...]
     *
     * @var LinuxFile[]
     */
    private array $added_linux_files_map = [];

    /**
     * @var FileAdded[]
     */
    private array $added_entries;

    /**
     * [inode_index => diff line, inode_index => diff line ... ]
     * E.g.
     * [
     *   293177 => '293177;2016-03-20;/home/webadmin/www/photoCentral/storage/app/SimplePhotoSourceExampleImages/Extra added/23199745_P1150924_1920xX.JPG'
     *   ...
     * ]
     *
     * @var array
     */
    private array $removed_linux_files_map = [];

    /**
     * @var FileRemoved[]
     */
    private array $removed_entries;

    /**
     * [inode_index => LinuxFile, inode_index => LinuxFile ...]
     *
     * @var LinuxFile[]
     */
    private array $moved_linux_files_map = [];

    /**
     * @var FileMoved[]
     */
    private array $moved_entries;


    public function addEntryToRemovedMap(string $linux_file_entry_to_add_to_map)
    {
        $explodedFileData = explode(';', trim($linux_file_entry_to_add_to_map));
        $inode_index = $explodedFileData[0];
        $this->removed_linux_files_map[$inode_index] = $linux_file_entry_to_add_to_map;

        $this->removed_entries[$inode_index] = new FileRemoved(
            new FileSystemDiffReportLine($linux_file_entry_to_add_to_map)
        );
    }

    public function addEntryToAddedMap(string $linux_file_entry_to_add_to_map, SynologyPhotoCollection $synology_photo_collection)
    {
        $linux_file = LinuxFileFactory::createLinuxFileFromDiffEntry($linux_file_entry_to_add_to_map,$synology_photo_collection);
        $this->added_linux_files_map[$linux_file->getInodeIndex()] = $linux_file;

        $this->added_entries[$linux_file->getInodeIndex()] = new FileAdded(
            $linux_file,
            new FileSystemDiffReportLine($linux_file_entry_to_add_to_map)
        );
    }

    public function addEntryToMovedMap(LinuxFile $linux_file)
    {
        $inode_index = $linux_file->getInodeIndex();
        $this->moved_linux_files_map[$inode_index] = $linux_file;
        $this->moved_entries[$inode_index] = new FileMoved(
            $linux_file,
            $this->removed_entries[$inode_index]->getRemovedFrom(),
            $this->added_entries[$inode_index]->getAddedTo()
        );
    }

    public function getMovedLinuxFilesMap(): array
    {
        return $this->moved_linux_files_map;
    }

    public function getAddedLinuxFilesMap(): array
    {
        return $this->added_linux_files_map;
    }

    public function getRemovedLinuxFilesMap(): array
    {
        return $this->removed_linux_files_map;
    }

    public function removeEntryFromRemovedLinuxFilesMap(int $inode_index): string
    {
        $linux_file = $this->removed_linux_files_map[$inode_index];
        unset($this->removed_linux_files_map[$inode_index]);
        return $linux_file;
    }

    public function removeEntryFromAddedLinuxFilesMap(int $inode_index): LinuxFile
    {
        $linux_file = $this->added_linux_files_map[$inode_index];
        unset($this->added_linux_files_map[$inode_index]);
        return $linux_file;
    }
}
