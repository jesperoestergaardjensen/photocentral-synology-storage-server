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

    private static function create_test_folders(): void
    {
        if (file_exists(self::getDataFolder() . '/status_files/') === false) {
            mkdir(self::getDataFolder() . '/status_files/');
        }

        if (file_exists(self::getDataFolder() . '/photos/.Trash-1000/') === false) {
            mkdir(self::getDataFolder() . '/photos/.Trash-1000/');
        }

        if (file_exists(self::getDataFolder() . '/photos/misc/') === false) {
            mkdir(self::getDataFolder() . '/photos/misc/');
        }
    }

    public static function setUpBeforeClass(): void
    {
        self::create_test_folders();
        self::$test_database_service = new TestDatabaseService();
        self::$test_database_service->uninstallDatabase();
        self::$database_connection = self::$test_database_service->installDatabase();

        self::$provider = new Provider('http://photocentral-synology-storage-server/api/', self::$database_connection, '', '');
        self::$provider->initialize();
    }

    public static function tearDownAfterClass(): void
    {
        $status_files = [
            self::getDataFolder() . '/status_files/' . "SynologyPhotoCollection-" . self::getPhotoCollectionId() . "-new.txt",
            self::getDataFolder() . '/status_files/' . "SynologyPhotoCollection-" . self::getPhotoCollectionId() . "-old.txt",
            self::getDataFolder() . '/status_files/' . "SynologyPhotoCollection-" . self::getExifSamplesPhotoCollectionId() . "-new.txt",
            self::getDataFolder() . '/status_files/' . "SynologyPhotoCollection-" . self::getExifSamplesPhotoCollectionId() . "-old.txt",
            self::getDataFolder() . '/status_files/' . "SynologyPhotoCollection-" . self::getDuplicatePhotosPhotoCollectionId() . "-new.txt",
            self::getDataFolder() . '/status_files/' . "SynologyPhotoCollection-" . self::getDuplicatePhotosPhotoCollectionId() . "-old.txt"
        ];

        foreach ($status_files as $status_file) {
            if (file_exists($status_file)) {
                unlink($status_file);
            }
        }

        // Undo move file
        rename(self::getDataFolder() . '/photos/misc/coffee-break.jpg', self::getDataFolder() . '/photos/coffee-break.jpg');

        // Undo move file to trash folder
        rename(self::getDataFolder() . '/photos/.Trash-1000/matrix-g3ebcd682d_640.jpg', self::getDataFolder() . '/photos/programming/matrix-g3ebcd682d_640.jpg');
    }

    public function testInitialImport()
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
        $file_system_diff_report_list = self::$provider->importPhotos();

        $file_system_diff_report = $file_system_diff_report_list->get(self::getPhotoCollectionId());
        $expected_files_added = $file_system_diff_report->getAddedLinuxFilesMap();
        $expected_files_moved = $file_system_diff_report->getMovedLinuxFilesMap();
        $expected_files_removed = $file_system_diff_report->getRemovedLinuxFilesMap();

        $this->assertCount(12, $expected_files_added);
        $this->assertCount(0, $expected_files_moved);
        $this->assertCount(0, $expected_files_removed);
    }

    /**
     * @depends testInitialImport
     */
    public function testNoChanges()
    {
        $file_system_diff_report_list = self::$provider->importPhotos();

        $file_system_diff_report = $file_system_diff_report_list->get(self::getPhotoCollectionId());
        $expected_files_added = $file_system_diff_report->getAddedLinuxFilesMap();
        $expected_files_moved = $file_system_diff_report->getMovedLinuxFilesMap();
        $expected_files_removed = $file_system_diff_report->getRemovedLinuxFilesMap();

        $this->assertCount(0, $expected_files_added);
        $this->assertCount(0, $expected_files_moved);
        $this->assertCount(0, $expected_files_removed);
    }

    /**
     * @depends testNoChanges
     */
    public function testMovePhoto()
    {
        // Move file
        rename(self::getDataFolder() . '/photos/coffee-break.jpg', self::getDataFolder() . '/photos/misc/coffee-break.jpg');
        $file_system_diff_report_list = self::$provider->importPhotos();

        $file_system_diff_report = $file_system_diff_report_list->get(self::getPhotoCollectionId());
        $expected_files_added = $file_system_diff_report->getAddedLinuxFilesMap();
        $expected_files_moved = $file_system_diff_report->getMovedLinuxFilesMap();
        $expected_files_removed = $file_system_diff_report->getRemovedLinuxFilesMap();

        $this->assertCount(0, $expected_files_added);
        $this->assertCount(1, $expected_files_moved);
        $this->assertCount(0, $expected_files_removed);
    }

    /**
     * @depends testMovePhoto
     */
    public function testPhotoMovedToTrash()
    {
        // Move file to trash folder
        rename(self::getDataFolder() . '/photos/programming/matrix-g3ebcd682d_640.jpg', self::getDataFolder() . '/photos/.Trash-1000/matrix-g3ebcd682d_640.jpg');
        $file_system_diff_report_list = self::$provider->importPhotos();

        $file_system_diff_report = $file_system_diff_report_list->get(self::getPhotoCollectionId());
        $expected_files_added = $file_system_diff_report->getAddedLinuxFilesMap();
        $expected_files_moved = $file_system_diff_report->getMovedLinuxFilesMap();
        $expected_files_removed = $file_system_diff_report->getRemovedLinuxFilesMap();

        $this->assertCount(0, $expected_files_added);
        $this->assertCount(0, $expected_files_moved);
        $this->assertCount(1, $expected_files_removed);
    }

    /**
     * @depends testPhotoMovedToTrash
     */
    public function testAddingNewPhotoCollection()
    {
        $synology_photo_collection_repository = new SynologyPhotoCollectionRepository(self::$database_connection);
        $synology_photo_collection_repository->connectToDb();

        $exif_samples_photo_collection = new SynologyPhotoCollection(
            self::getExifSamplesPhotoCollectionId(),
            'exif samples photos collection',
            true,
            'exif samples photos collection',
            time(),
            self::getExifSamplesFolder(),
            self::getDataFolder() . '/status_files/',
        );

        $synology_photo_collection_repository->add($exif_samples_photo_collection);

        $file_system_diff_report_list = self::$provider->importPhotos();

        $file_system_diff_report = $file_system_diff_report_list->get(self::getExifSamplesPhotoCollectionId());
        $files_added = $file_system_diff_report->getAddedLinuxFilesMap();
        $files_moved = $file_system_diff_report->getMovedLinuxFilesMap();
        $files_removed = $file_system_diff_report->getRemovedLinuxFilesMap();

        $this->assertCount(87, $files_added);
        $this->assertCount(0, $files_moved);
        $this->assertCount(0, $files_removed);
    }

    /**
     * @depends testAddingNewPhotoCollection
     */
    public function testAddingNewPhotoCollectionWithDuplicates()
    {
        $synology_photo_collection_repository = new SynologyPhotoCollectionRepository(self::$database_connection);
        $synology_photo_collection_repository->connectToDb();

        $duplicate_photos_collection = new SynologyPhotoCollection(
            self::getDuplicatePhotosPhotoCollectionId(),
            'Photo collection with duplicates',
            true,
            'Photo collection with duplicates',
            time(),
            self::getDuplicatePhotosFolder(),
            self::getDataFolder() . '/status_files/',
        );

        $synology_photo_collection_repository->add($duplicate_photos_collection);

        $file_system_diff_report_list = self::$provider->importPhotos();

        $file_system_diff_report = $file_system_diff_report_list->get(self::getDuplicatePhotosPhotoCollectionId());
        $files_added = $file_system_diff_report->getAddedLinuxFilesMap();
        $files_moved = $file_system_diff_report->getMovedLinuxFilesMap();
        $files_removed = $file_system_diff_report->getRemovedLinuxFilesMap();

        $this->assertCount(6, $files_added);
        $this->assertCount(0, $files_moved);
        $this->assertCount(0, $files_removed);

        rename(self::getDuplicatePhotosFolder() . "folder_a", self::getDataFolder() . "/tmp/folder_a");

        $file_system_diff_report_list = self::$provider->importPhotos();

        $file_system_diff_report = $file_system_diff_report_list->get(self::getDuplicatePhotosPhotoCollectionId());
        $files_added = $file_system_diff_report->getAddedLinuxFilesMap();
        $files_moved = $file_system_diff_report->getMovedLinuxFilesMap();
        $files_removed = $file_system_diff_report->getRemovedLinuxFilesMap();

        $this->assertCount(0, $files_added);
        $this->assertCount(0, $files_moved);
        $this->assertCount(3, $files_removed);

        rename(self::getDuplicatePhotosFolder() . "folder_b", self::getDataFolder() . "/tmp/folder_b");

        $file_system_diff_report_list = self::$provider->importPhotos();

        $file_system_diff_report = $file_system_diff_report_list->get(self::getDuplicatePhotosPhotoCollectionId());
        $files_added = $file_system_diff_report->getAddedLinuxFilesMap();
        $files_moved = $file_system_diff_report->getMovedLinuxFilesMap();
        $files_removed = $file_system_diff_report->getRemovedLinuxFilesMap();

        $this->assertCount(0, $files_added);
        $this->assertCount(0, $files_moved);
        $this->assertCount(3, $files_removed);

        rename(self::getDataFolder() . "/tmp/folder_a", self::getDuplicatePhotosFolder() . "folder_a");
        rename(self::getDataFolder() . "/tmp/folder_b", self::getDuplicatePhotosFolder() . "folder_b");
    }
}
