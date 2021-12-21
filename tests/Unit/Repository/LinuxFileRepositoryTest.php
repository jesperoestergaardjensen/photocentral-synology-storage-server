<?php

namespace PhotoCentralSynologyStorageServer\Tests\Unit\Repository;

use PhotoCentralSynologyStorageServer\Exception\PhotoCentralSynologyServerException;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\SimpleDatabaseConnection;
use PhotoCentralSynologyStorageServer\Model\LinuxFile;
use PhotoCentralSynologyStorageServer\Repository\LinuxFileRepository;
use PhotoCentralSynologyStorageServer\Tests\TestDatabaseService;
use PHPUnit\Framework\TestCase;

class LinuxFileRepositoryTest extends TestCase
{
    private static TestDatabaseService $test_database_service;
    private static SimpleDatabaseConnection $database_connection;

    public static function setUpBeforeClass(): void
    {
        self::$test_database_service = new TestDatabaseService();
        self::$test_database_service->uninstallDatabase();
        self::$database_connection = self::$test_database_service->installDatabase();
    }

    public static function tearDownAfterClass(): void
    {
        // self::$test_database_service->uninstallDatabase();
    }

    public function testSearch()
    {
        // Prepare
        $expected_linux_file_uuid = '2e869946-8be0-4193-a4a2-72ff6b0f5e93';
        self::$test_database_service->addLinuxFileFixture('search_test_fixture.sql');

        // Execute
        $linux_file_repository = new LinuxFileRepository(self::$database_connection);
        $linux_file_repository->connectToDb();
        $linux_file_found_from_search = $linux_file_repository->search('SamsungS6', 10);

        // Compare
        $this->assertEquals($expected_linux_file_uuid, $linux_file_found_from_search[0]->getFileUuid(),
            'Search returns the correct Linux file ');
    }

    public function testBulkAddAndGet()
    {
        // Prepare
        $linux_file_repository = new LinuxFileRepository(self::$database_connection);
        $linux_file_repository->connectToDb();

        $linux_file_a = new LinuxFile('id-a', 1234567, time()-500, 'test-file-name-a.jpg', 'test-folder-a');
        $linux_file_b = new LinuxFile('id-b', 1234568, time()-100, 'test-file-name-b.jpg', 'test-folder-b');

        $linux_files_to_bulk_add = [
            $linux_file_a,
            $linux_file_b,
        ];

        // Execute
        $linux_file_repository->bulkAdd($linux_files_to_bulk_add);
        $expect_linux_file_a = $linux_file_repository->get($linux_file_a->getInodeIndex(), $linux_file_a->getPhotoCollectionId());
        // Adjust time to be equal
        $linux_file_a->setRowAddedDateTime($expect_linux_file_a->getRowAddedDateTime());

        // Compare
        $this->assertEquals($linux_file_a, $expect_linux_file_a, 'Linux file a should be returned');
    }

    public function testGetException()
    {
        $linux_file_repository = new LinuxFileRepository(self::$database_connection);
        $linux_file_repository->connectToDb();

        $this->expectException(PhotoCentralSynologyServerException::class);
        $linux_file_repository->get(112, 'not-existing-id');
    }

    public function testBulkAddAndListOne()
    {
        // Prepare
        $linux_file_repository = new LinuxFileRepository(self::$database_connection);
        $linux_file_repository->connectToDb();

        $linux_file_a = new LinuxFile('id-a', 1234567, time()-500, 'test-file-name-a.jpg', 'test-folder-a');
        $linux_file_b = new LinuxFile('id-b', 1234568, time()-100, 'test-file-name-b.jpg', 'test-folder-b');

        $linux_files_to_bulk_add = [
            $linux_file_a,
            $linux_file_b,
        ];

        // Execute
        $linux_file_repository->bulkAdd($linux_files_to_bulk_add);
        $linux_file_list = $linux_file_repository->list($linux_file_a->getInodeIndex());

        // Compare
        $this->assertCount(1, $linux_file_list);

        $expect_linux_file_a = $linux_file_list[0];
        // Adjust time to be equal
        $linux_file_a->setRowAddedDateTime($expect_linux_file_a->getRowAddedDateTime());

        $this->assertEquals($linux_file_a, $expect_linux_file_a, 'Linux file a should be returned');
    }

    public function testBulkAddAndListMultiple()
    {
        // Prepare
        $linux_file_repository = new LinuxFileRepository(self::$database_connection);
        $linux_file_repository->connectToDb();

        // Same file added in two different photo collections
        $linux_file_a = new LinuxFile('id-a', 1234567, time()-500, 'test-file-name-a.jpg', 'test-folder-a');
        $linux_file_b = new LinuxFile('id-b', 1234567, time()-100, 'test-file-name-b.jpg', 'test-folder-b');

        $linux_files_to_bulk_add = [
            $linux_file_a,
            $linux_file_b,
        ];

        // Execute
        $linux_file_repository->bulkAdd($linux_files_to_bulk_add);
        $linux_file_list = $linux_file_repository->list($linux_file_a->getInodeIndex());

        // Compare
        $this->assertCount(2, $linux_file_list);

        $expect_linux_file_a = $linux_file_list[0];
        $expect_linux_file_b = $linux_file_list[1];
        // Adjust time to be equal
        $linux_file_a->setRowAddedDateTime($expect_linux_file_a->getRowAddedDateTime());
        $linux_file_b->setRowAddedDateTime($expect_linux_file_b->getRowAddedDateTime());

        $this->assertEquals($linux_file_a, $expect_linux_file_a, 'Linux file a should be returned');
        $this->assertEquals($linux_file_b, $expect_linux_file_b, 'Linux file b should be returned');
    }
}
