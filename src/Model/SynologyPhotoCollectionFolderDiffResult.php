<?php

namespace PhotoCentralSynologyStorageServer\Model;

use PhotoCentralSynologyStorageServer\Factory\LinuxFileFactory;

class SynologyPhotoCollectionFolderDiffResult
{
    /**
     * [inode_index => LinuxFile, inode_index => LinuxFile ...]
     *
     * @var LinuxFile[]
     */
    private array $added_linux_files_map = [];

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
     * [inode_index => LinuxFile, inode_index => LinuxFile ...]
     *
     * @var LinuxFile[]
     */
    private array $moved_linus_files_map = [];

    public function addEntryToRemovedMap(string $linux_file_entry_to_add_to_map)
    {
        $explodedFileData = explode(';', trim($linux_file_entry_to_add_to_map));
        $inode_index = $explodedFileData[0];

        $this->removed_linux_files_map[$inode_index] = $linux_file_entry_to_add_to_map;
    }

    public function addEntryToAddedMap(string $linux_file_entry_to_add_to_map, SynologyPhotoCollection $synology_photo_collection)
    {
        $linux_file = LinuxFileFactory::createLinuxFileFromDiffEntry($linux_file_entry_to_add_to_map,$synology_photo_collection);
        $this->added_linux_files_map[$linux_file->getInodeIndex()] = $linux_file;
    }

    public function addEntryToMovedMap(LinuxFile $linux_file)
    {
        $this->moved_linus_files_map[$linux_file->getInodeIndex()] = $linux_file;
    }

    public function getMovedLinuxFilesMap(): array
    {
        return $this->moved_linus_files_map;
    }

    public function getAddedLinuxFilesMap(): array
    {
        return $this->added_linux_files_map;
    }

    public function getRemovedLinuxFilesMap(): array
    {
        return $this->removed_linux_files_map;
    }

    public function removeEntryFromRemovedLinuxFilesMap(int $inode_index)
    {
        unset($this->removed_linux_files_map[$inode_index]);
    }

    public function removeEntryFromAddedLinuxFilesMap(int $inode_index)
    {
        unset($this->added_linux_files_map[$inode_index]);
    }
}
