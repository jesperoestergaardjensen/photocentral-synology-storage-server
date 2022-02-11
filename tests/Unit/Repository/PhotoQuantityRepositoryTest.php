<?php

namespace PhotoCentralSynologyStorageServer\Tests\Unit\Repository;

use PhotoCentralStorage\Model\PhotoQuantity\PhotoQuantityDay;
use PhotoCentralStorage\Model\PhotoQuantity\PhotoQuantityMonth;
use PhotoCentralStorage\Model\PhotoQuantity\PhotoQuantityYear;
use PhotoCentralStorage\Photo;
use PhotoCentralSynologyStorageServer\Factory\PhotoUrlFactory;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Repository\PhotoQuantityRepository;
use PhotoCentralSynologyStorageServer\Repository\PhotoRepository;
use PhotoCentralSynologyStorageServer\Service\UUIDService;
use PhotoCentralSynologyStorageServer\Tests\TestDatabaseService;
use PHPUnit\Framework\TestCase;

class PhotoQuantityRepositoryTest extends TestCase
{
    private static TestDatabaseService $test_database_service;
    private static DatabaseConnection $database_connection;

    public static function setUpBeforeClass(): void
    {
        self::$test_database_service = new TestDatabaseService();
        self::$test_database_service->uninstallDatabase();
        self::$database_connection = self::$test_database_service->installDatabase();

        $photo_url_factory = new PhotoUrlFactory(dirname(__FILE__) . "/public/api");
        $photo_repository = new PhotoRepository(self::$database_connection, $photo_url_factory);
        $photo_repository->connectToDb();

        $photo_a = new Photo(UUIDService::create(), '1', 500, 500, 0, time(), time(), time(), strtotime('2012-01-01'), 'Apple', 'Iphone 10');
        $photo_b = new Photo(UUIDService::create(), '1', 200, 300, 0, time(), time(), time(), strtotime('2021-01-02'), 'Apple', 'Iphone 12');
        $photo_c = new Photo(UUIDService::create(), '1', 250, 310, 0, time(), time(), time(), strtotime('2021-01-03'), 'Apple', 'Iphone 12');

        $photo_repository->add($photo_a);
        $photo_repository->add($photo_b);
        $photo_repository->add($photo_c);
    }

    public function testlistPhotoQuantityByYear()
    {
        // Prepare
        $photo_quantity_repository = new PhotoQuantityRepository(self::$database_connection);
        $photo_quantity_repository->connectToDb();
        $expected_output = [
            new PhotoQuantityYear('2021', 2021, 2),
            new PhotoQuantityYear('2012', 2012, 1)
        ];

        // Execute and Test
        $result = $photo_quantity_repository->listPhotoQuantityByYear(null);
        $this->assertEquals($expected_output, $result, 'Expect year 2012 and 2021 with one photo and two photos');
    }

    public function testlistPhotoQuantityByMonth()
    {
        // Prepare
        $photo_quantity_repository = new PhotoQuantityRepository(self::$database_connection);
        $photo_quantity_repository->connectToDb();
        $expected_output = [
            new PhotoQuantityMonth('1', 01, 2),
        ];

        // Execute and Test
        $result = $photo_quantity_repository->listPhotoQuantityByMonth(2021, null);
        $this->assertEquals($expected_output, $result, 'Expect month is 1 with two photos');
    }

    public function testlistPhotoQuantityByDay()
    {
        // Prepare
        $photo_quantity_repository = new PhotoQuantityRepository(self::$database_connection);
        $photo_quantity_repository->connectToDb();
        $expected_output = [
            new PhotoQuantityDay('2', 2, 1),
            new PhotoQuantityDay('3', 3, 1),
        ];

        // Execute and Test
        $result = $photo_quantity_repository->listPhotoQuantityByDay(2021, 1, null);
        $this->assertEquals($expected_output, $result, 'Expect days are 2 and 3 with one photo in each');
    }
}
