<?php

namespace PhotoCentralSynologyStorageServer\Tests\Unit\Controller;

use PhotoCentralStorage\Model\PhotoQuantity\PhotoQuantityMonth;
use PhotoCentralSynologyStorageServer\Controller\ListPhotoQuantityByMonthController;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Provider;
use PhotoCentralSynologyStorageServer\Tests\TestDatabaseService;
use PhotoCentralSynologyStorageServer\Tests\TestService;
use PHPUnit\Framework\TestCase;

class ListPhotoQuantityByMonthControllerTest extends TestCase
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
            'http://photocentral-synology-storage-server/api/',
            self::$database_connection,
            self::getDataFolder() . '/photos/'
        );
        self::$provider->initialize();
        unset($_POST);
    }

    public function testRunController()
    {
        // Prepare
        self::$test_database_service->addLinuxFileFixture('list_photo_quantiry_test_fixture.sql');

        // Simulate post request - prepare data for controller
        $_POST['photo_collection_id_list'] = ['id1'];
        $_POST['year'] = 2021;

        // Excute
        $json_content = self::$provider->runController(ListPhotoQuantityByMonthController::class, true); // Code coverage / debugging
        $photo_array_list = json_decode($json_content, true);

        $photo_quantity_month_list = [];
        foreach ($photo_array_list as $photo_array) {
            $photo_quantity_month_list[] = PhotoQuantityMonth::fromArray($photo_array);
        }

        $this->assertEquals(1, $photo_quantity_month_list[0]->getQuantity(), 'The correct number of photo(s) was returned');
    }
}
