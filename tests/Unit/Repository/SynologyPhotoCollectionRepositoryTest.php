<?php

namespace Unit\Repository;

use PhotoCentralSynologyStorageServer\Exception\PhotoCentralSynologyServerException;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\SimpleDatabaseConnection;
use PhotoCentralSynologyStorageServer\Model\SynologyPhotoCollection;
use PhotoCentralSynologyStorageServer\Repository\SynologyPhotoCollectionRepository;
use PhotoCentralSynologyStorageServer\Service\UUIDService;
use PhotoCentralSynologyStorageServer\Tests\TestDatabaseService;
use PHPUnit\Framework\TestCase;

class SynologyPhotoCollectionRepositoryTest extends TestCase
{
    private static TestDatabaseService $test_database_service;
    private static SimpleDatabaseConnection $database_connection;

    public static function setUpBeforeClass(): void
    {
        self::$test_database_service = new TestDatabaseService();
        self::$test_database_service->uninstallDatabase();
        self::$database_connection = self::$test_database_service->installDatabase();
    }

    public function testAddAndGetMethods(): void
    {
        // Prepare
        $synology_photo_collection_repository = new SynologyPhotoCollectionRepository(self::$database_connection);
        $synology_photo_collection_repository->connectToDb();
        $expected_synology_photo_collection_uuid = UUIDService::create();

        // Execute
        $synology_photo_collection = new SynologyPhotoCollection($expected_synology_photo_collection_uuid,
            'Photos from my iphone', 'Synology NAS folder', true, 'folder_name', 'another_folder_name');
        $synology_photo_collection_repository->add($synology_photo_collection);

        // Compare
        $synology_photo_collection = $synology_photo_collection_repository->get($expected_synology_photo_collection_uuid);
        $this->assertEquals($expected_synology_photo_collection_uuid, $synology_photo_collection->getId());
    }

    public function testGetException()
    {
        // Prepare
        $synology_photo_collection_repository = new SynologyPhotoCollectionRepository(self::$database_connection);
        $synology_photo_collection_repository->connectToDb();
        $expected_synology_photo_collection_uuid = UUIDService::create();

        // Execute/Compare
        $this->expectException(PhotoCentralSynologyServerException::class);
        $synology_photo_collection_repository->get($expected_synology_photo_collection_uuid);
    }
}
