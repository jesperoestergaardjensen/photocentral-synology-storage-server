<?php

namespace PhotoCentralSynologyStorageServer\Tests\Unit\cron;

use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Model\SynologyPhotoCollection;
use PhotoCentralSynologyStorageServer\Provider;
use PhotoCentralSynologyStorageServer\Repository\SynologyPhotoCollectionRepository;
use PhotoCentralSynologyStorageServer\Tests\TestDatabaseService;
use PhotoCentralSynologyStorageServer\Tests\TestService;
use PHPUnit\Framework\TestCase;

/**
 * @property Provider $provider
 */
class ImportPhotosTest extends TestCase
{
    use TestService;

    private static TestDatabaseService $test_database_service;
    private static DatabaseConnection $database_connection;
    private static Provider $provider;

    public static function setUpBeforeClass(): void
    {
        self::$test_database_service = new TestDatabaseService();
        self::$test_database_service->uninstallDatabase();
        self::$database_connection = self::$test_database_service->installDatabase();

        self::$provider = new Provider(self::$database_connection, '', '');
        self::$provider->initialize();
    }

    public static function tearDownAfterClass(): void
    {
        unlink(self::getDataFolder() . '/status_files/' . "SynologyPhotoCollection-" . self::getPhotoCollectionId() . "-new.txt");
        unlink(self::getDataFolder() . '/status_files/' . "SynologyPhotoCollection-" . self::getPhotoCollectionId() . "-old.txt");
        // Undo move file
        rename(self::getDataFolder() . '/photos/misc/coffee-break.jpg', self::getDataFolder() . '/photos/coffee-break.jpg');
        // Undo move file to trash folder
        rename(self::getDataFolder() . '/photos/.Trash-1000/matrix-g3ebcd682d_640.jpg', self::getDataFolder() . '/photos/programming/matrix-g3ebcd682d_640.jpg');
    }

    public function testCronJobTimelineStep1()
    {
        $synology_photo_collection_repository = new SynologyPhotoCollectionRepository(self::$database_connection);
        $synology_photo_collection_repository->connectToDb();

        $synology_photo_collection = new SynologyPhotoCollection(
            self::getPhotoCollectionId(),
            'test collection',
            true,
            'Synology photo collection test',
            time(),
            self::getDataFolder() . '/photos/',
            self::getDataFolder() . '/status_files/',
        );

        $synology_photo_collection_repository->add($synology_photo_collection);
        $photo_import_result = self::$provider->importPhotos();

        $expected_files_added = $photo_import_result->getPhotoCollectionFolderDiffResult(self::getPhotoCollectionId())->getAddedLinuxFilesMap();
        $expected_files_moved = $photo_import_result->getPhotoCollectionFolderDiffResult(self::getPhotoCollectionId())->getMovedLinuxFilesMap();
        $expected_files_removed = $photo_import_result->getPhotoCollectionFolderDiffResult(self::getPhotoCollectionId())->getRemovedLinuxFilesMap();

        $this->assertCount(10, $expected_files_added);
        $this->assertCount(0, $expected_files_moved);
        $this->assertCount(0, $expected_files_removed);
    }

    /**
     * @depends testCronJobTimelineStep1
     */
    public function testCronJobTimelineStep2()
    {
        $photo_import_result = self::$provider->importPhotos();

        $expected_files_added = $photo_import_result->getPhotoCollectionFolderDiffResult(self::getPhotoCollectionId())->getAddedLinuxFilesMap();
        $expected_files_moved = $photo_import_result->getPhotoCollectionFolderDiffResult(self::getPhotoCollectionId())->getMovedLinuxFilesMap();
        $expected_files_removed = $photo_import_result->getPhotoCollectionFolderDiffResult(self::getPhotoCollectionId())->getRemovedLinuxFilesMap();

        $this->assertCount(0, $expected_files_added);
        $this->assertCount(0, $expected_files_moved);
        $this->assertCount(0, $expected_files_removed);
    }

    /**
     * @depends testCronJobTimelineStep2
     */
    public function testCronJobTimelineStep3()
    {
        // Move file
        rename(self::getDataFolder() . '/photos/coffee-break.jpg', self::getDataFolder() . '/photos/misc/coffee-break.jpg');
        $photo_import_result = self::$provider->importPhotos();

        $expected_files_added = $photo_import_result->getPhotoCollectionFolderDiffResult(self::getPhotoCollectionId())->getAddedLinuxFilesMap();
        $expected_files_moved = $photo_import_result->getPhotoCollectionFolderDiffResult(self::getPhotoCollectionId())->getMovedLinuxFilesMap();
        $expected_files_removed = $photo_import_result->getPhotoCollectionFolderDiffResult(self::getPhotoCollectionId())->getRemovedLinuxFilesMap();

        $this->assertCount(0, $expected_files_added);
        $this->assertCount(1, $expected_files_moved);
        $this->assertCount(0, $expected_files_removed);
    }

    /**
     * @depends testCronJobTimelineStep3
     */
    public function testCronJobTimelineStep4()
    {
        // Move file to trash folder
        rename(self::getDataFolder() . '/photos/programming/matrix-g3ebcd682d_640.jpg', self::getDataFolder() . '/photos/.Trash-1000/matrix-g3ebcd682d_640.jpg');
        $photo_import_result = self::$provider->importPhotos();

        $expected_files_added = $photo_import_result->getPhotoCollectionFolderDiffResult(self::getPhotoCollectionId())->getAddedLinuxFilesMap();
        $expected_files_moved = $photo_import_result->getPhotoCollectionFolderDiffResult(self::getPhotoCollectionId())->getMovedLinuxFilesMap();
        $expected_files_removed = $photo_import_result->getPhotoCollectionFolderDiffResult(self::getPhotoCollectionId())->getRemovedLinuxFilesMap();

        $this->assertCount(0, $expected_files_added);
        $this->assertCount(0, $expected_files_moved);
        $this->assertCount(1, $expected_files_removed);
    }
}
