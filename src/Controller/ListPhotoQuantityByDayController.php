<?php

namespace PhotoCentralSynologyStorageServer\Controller;

use PhotoCentralSynologyStorageServer\Controller;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Repository\PhotoQuantityRepository;

class ListPhotoQuantityByDayController extends Controller
{
    private PhotoQuantityRepository $photo_quantity_repository;
    private DatabaseConnection $database_connection;

    public function __construct(
        DatabaseConnection $database_connection,
        PhotoQuantityRepository $photo_quantity_repository
    ) {
        $this->photo_quantity_repository = $photo_quantity_repository;
        $this->database_connection = $database_connection;
    }

    public function run(bool $testing = false): void
    {
        $this->photo_quantity_repository->connectToDb();
        $post_data = $this->sanitizePost(['photo_collection_id_list' => null, 'year' => 2021, 'month' => 10], $this->database_connection);
        $photo_collection_id_list = $post_data['photo_collection_id_list'];
        $year = $post_data['year'];
        $month = $post_data['month'];
        $photo_quantity_day_list = $this->photo_quantity_repository->listPhotoQuantityByDay($month, $year, $photo_collection_id_list);

        $return_array = [];
        foreach ($photo_quantity_day_list as $photo_quantity_day) {
            $return_array[] = $photo_quantity_day->toArray();
        }

        echo json_encode($return_array);
    }
}
