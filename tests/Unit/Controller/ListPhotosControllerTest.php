<?php

namespace PhotoCentralSynologyStorageServer\Tests\Unit\Controller;

use PhotoCentralStorage\Model\PhotoFilter\PhotoCollectionIdFilter;
use PhotoCentralStorage\Model\PhotoSorting\BasicSorting;
use PhotoCentralStorage\Model\PhotoSorting\SortByAddedTimestamp;
use PhotoCentralStorage\Photo;
use PhotoCentralSynologyStorageServer\Controller\ListPhotosController;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Provider;
use PhotoCentralSynologyStorageServer\Tests\TestDatabaseService;
use PhotoCentralSynologyStorageServer\Tests\TestService;
use PHPUnit\Framework\TestCase;

class ListPhotosControllerTest extends TestCase
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

        self::$provider = new Provider(
            self::$database_connection,
            self::getDataFolder() . '/photos/',
            self::getPublicFolder() . '/images/cache/'
        );
        self::$provider->initialize();
    }

    public function testRunController()
    {
        // Prepare
        self::$test_database_service->addLinuxFileFixture('search_test_fixture.sql');

        // Simulate post request - prepare data for controller
        $_POST['photo_filters'] = [
            PhotoCollectionIdFilter::class => (new PhotoCollectionIdFilter(['11efa610-5378-4964-b432-d891aef00eb9']))->toArray()
        ];
        $_POST['photo_sorting_parameters'] = [
            SortByAddedTimestamp::class => (new SortByAddedTimestamp(BasicSorting::DESC))->toArray()
        ];
        $_POST['limit'] = 50;

        // Excute
        $json_content = self::$provider->runController(ListPhotosController::class, true); // Code coverage / debugging
        $photo_array_list = json_decode($json_content, true);

        $photo_list = [];
        foreach ($photo_array_list as $photo_array) {
            $photo_list[] = Photo::fromArray($photo_array);
        }

        $this->assertEquals('c3db925a9c3f19f6285f7038dcd9844e', $photo_list[0]->getPhotoUuid(), 'The correct photo was returned');
    }
}
