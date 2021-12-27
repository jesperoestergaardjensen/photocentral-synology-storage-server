<?php

namespace PhotoCentralSynologyStorageServer\Tests\Unit\Repository;

use PhotoCentralStorage\Photo;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Repository\PhotoRepository;
use PhotoCentralSynologyStorageServer\Service\UUIDService;
use PhotoCentralSynologyStorageServer\Tests\TestDatabaseService;
use PHPUnit\Framework\TestCase;

class PhotoRepositoryTest extends TestCase
{
    private static TestDatabaseService $test_database_service;
    private static DatabaseConnection $database_connection;

    public static function setUpBeforeClass(): void
    {
        self::$test_database_service = new TestDatabaseService();
        self::$test_database_service->uninstallDatabase();
        self::$database_connection = self::$test_database_service->installDatabase();
    }

    public function testAddAndGetByPhotoUuid()
    {
        // Prepare
        $photo_a = new Photo(UUIDService::create(), '1', 500, 500, 0, time(), time(), time(), time(), 'Apple', 'Iphone 12');
        $photo_b = new Photo(UUIDService::create(), '1', 200, 300, 0, time(), time(), time(), time(), 'Apple', 'Iphone 12');
        $photo_repository = new PhotoRepository(self::$database_connection);
        $photo_repository->connectToDb();

        // Execute and Test
        $photo_list = $photo_repository->list([$photo_a->getPhotoUuid(), $photo_b->getPhotoUuid()]);
        $this->assertEquals([], $photo_list);

        $photo_repository->add($photo_a);
        $photo_repository->add($photo_b);
        $photo_list = $photo_repository->list([$photo_a->getPhotoUuid(), $photo_b->getPhotoUuid()]);
        $this->assertEquals([$photo_a->getPhotoUuid() => $photo_a, $photo_b->getPhotoUuid() => $photo_b], $photo_list);

        $photo = $photo_repository->get($photo_a->getPhotoUuid(), 1);
        $this->assertEquals($photo_a, $photo, 'returned photo is a');
    }
}
