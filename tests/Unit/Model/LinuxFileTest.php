<?php

namespace PhotoCentralSynologyStorageServer\Tests\Unit\Model;

use PhotoCentralSynologyStorageServer\Model\LinuxFile;
use PhotoCentralSynologyStorageServer\Service\UUIDService;
use PHPUnit\Framework\TestCase;

class LinuxFileTest extends TestCase
{
    public function testConstructorAndGetters()
    {
        $expected_synology_photo_collection_id = '';
        $expected_inode_index = 112232;
        $expected_last_modified_date = time();
        $expected_file_name = 'file_name';
        $expected_file_path = 'file_path/';
        $expected_photo_uuid = UUIDService::create();
        $expected_file_uuid = UUIDService::create();

        $linux_file = new LinuxFile(
            $expected_synology_photo_collection_id,
            $expected_inode_index,
            $expected_last_modified_date,
            $expected_file_name,
            $expected_file_path,
            $expected_photo_uuid,
            $expected_file_uuid
        );

        $this->assertEquals($expected_synology_photo_collection_id, $linux_file->getSynologyPhotoCollectionId());
        $this->assertEquals($expected_inode_index, $linux_file->getInodeIndex());
        $this->assertEquals($expected_last_modified_date, $linux_file->getLastModifiedDate());
        $this->assertEquals($expected_file_name, $linux_file->getFileName());
        $this->assertEquals($expected_file_path, $linux_file->getFilePath());

        $this->assertEquals($expected_photo_uuid, $linux_file->getPhotoUuid());
        $this->assertEquals($expected_file_uuid, $linux_file->getFileUuid());
    }

    public function testToArrayAndSetter()
    {
        $expected_synology_photo_collection_id = '';
        $expected_inode_index = 112232;
        $expected_last_modified_date = time();
        $expected_file_name = 'file_name';
        $expected_file_path = 'file_path/';
        $expected_photo_uuid = UUIDService::create();
        $expectec_file_uuid = UUIDService::create();
        $expected_row_added_date_time = time();

        $expected_array = [
            'synology_photo_collection_id' => $expected_synology_photo_collection_id,
            'inode_index'                  => $expected_inode_index,
            'last_modified_date'           => $expected_last_modified_date,
            'file_name'                    => $expected_file_name,
            'file_path'                    => $expected_file_path,
            'photo_uuid'                   => $expected_photo_uuid,
            'file_uuid'                    => $expectec_file_uuid,
            'row_added_date_time'          => $expected_row_added_date_time,
            'imported'                     => false,
            'import_date_time'             => null,
            'skipped_error'                => null,
            'scheduled_for_deletion'       => false,
            'skipped'                      => false,
            'duplicate'                    => false,
        ];

        $linux_file = new LinuxFile(
            $expected_synology_photo_collection_id,
            $expected_inode_index,
            $expected_last_modified_date,
            $expected_file_name,
            $expected_file_path,
            $expected_photo_uuid,
            $expectec_file_uuid
        );

        $linux_file->setRowAddedDateTime($expected_row_added_date_time);

        $this->assertEquals($expected_array, $linux_file->toArray());

        $expected_updated_photo_uuid = UUIDService::create();

        $linux_file->setPhotoUuid($expected_updated_photo_uuid);

        $this->assertEquals($expected_updated_photo_uuid, $linux_file->getPhotoUuid());
    }
}