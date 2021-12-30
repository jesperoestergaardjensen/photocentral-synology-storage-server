<?php

namespace PhotoCentralSynologyStorageServer\Controller;

use PhotoCentralStorage\Model\ImageDimensions;
use PhotoCentralSynologyStorageServer\Controller;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Repository\LinuxFileRepository;
use PhotoCentralSynologyStorageServer\Service\PhotoRetrivalService;

class DisplayPhotoController extends Controller
{
    private DatabaseConnection $database_connection;
    private PhotoRetrivalService $photo_retrival_service;
    private LinuxFileRepository $linux_file_repository;

    public function __construct(
        DatabaseConnection $database_connection,
        PhotoRetrivalService $photo_retrival_service,
        LinuxFileRepository $linux_file_repository
    ) {
        $this->database_connection = $database_connection;
        $this->photo_retrival_service = $photo_retrival_service;
        $this->linux_file_repository = $linux_file_repository;
    }

    public function run(bool $testing = false): void
    {
/*
        $post_data = $this->sanitizePost(['photo_uuid' => 'd49e611175d3e896e6936bf7404a6c9d', 'photo_collection_id' => 'a24e57e0-a1e9-4d8e-a671-67eb165a6b1d', 'image_dimensions' => ImageDimensions::createThumb()->toArray()],
            $this->database_connection);
*/
        $get_data = $_GET;

        $this->linux_file_repository->connectToDb();
        $linux_file = $this->linux_file_repository->getByPhotoUuid($get_data['photo_uuid'], $get_data['photo_collection_id']);
/*
        if ($testing === false) {
            header('Content-Type: application/json');
        }
*/
        $photo_path = $this->photo_retrival_service->getPhotoPath($linux_file, ImageDimensions::createFromId($get_data['image_dimensions_id'] ?? ImageDimensions::THUMB_ID));

        header('Content-Type: image/jpeg');
        readfile($photo_path);
    }
}
