<?php

namespace PhotoCentralSynologyStorageServer\Tests\Unit\cron;

use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\SimpleDatabaseConnection;
use PhotoCentralSynologyStorageServer\Model\SynologyPhotoCollection;
use PhotoCentralSynologyStorageServer\Provider;
use PhotoCentralSynologyStorageServer\Repository\SynologyPhotoCollectionRepository;
use PhotoCentralSynologyStorageServer\Service\UUIDService;
use PhotoCentralSynologyStorageServer\Tests\TestDatabaseService;
use PhotoCentralSynologyStorageServer\Tests\TestFoldersService;
use PHPUnit\Framework\TestCase;

/**
 * @property Provider $provider
 */
class ImportPhotosTest extends TestCase
{
    use TestFoldersService;

    private static TestDatabaseService $test_database_service;
    private static SimpleDatabaseConnection $database_connection;
    private static Provider $provider;
    private const SYNOLOGY_PHOTO_COLLECTION_UD = '2b613e8d-dd2a-4f1c-85a5-6e708290c200';

    public static function setUpBeforeClass(): void
    {
        self::$test_database_service = new TestDatabaseService();
        self::$test_database_service->uninstallDatabase();
        self::$database_connection = self::$test_database_service->installDatabase();

        self::$provider = new Provider(self::$database_connection);
        self::$provider->initialize();
    }

    public function testCronJob()
    {
        $synology_photo_collection_repository = new SynologyPhotoCollectionRepository(self::$database_connection);
        $synology_photo_collection_repository->connectToDb();

        $synology_photo_collection = new SynologyPhotoCollection(
            self::SYNOLOGY_PHOTO_COLLECTION_UD,
            'test collection',
            'Synology photo collection test',
            true,
            $this->getDataFolder() . '/photos/',
            $this->getDataFolder() . '/status_files/',
        );

        $synology_photo_collection_repository->add($synology_photo_collection);
        self::$provider->importPhotos();
    }
}
