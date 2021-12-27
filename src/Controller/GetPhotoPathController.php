<?php

namespace PhotoCentralSynologyStorageServer\Controller;

use PhotoCentralStorage\Model\ImageDimensions;
use PhotoCentralSynologyStorageServer\Controller;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Repository\LinuxFileRepository;
use PhotoCentralSynologyStorageServer\Service\PhotoRetrivalService;

class GetPhotoPathController extends Controller
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
        $post_data = $this->sanitizePost(['photo_uuid' => null, 'photo_collection_id' => null, 'image_dimensions' => null],
            $this->database_connection);

        $this->linux_file_repository->connectToDb();
        $linux_file = $this->linux_file_repository->getByPhotoUuid($post_data['photo_uuid'], $post_data['photo_collection_id']);

        if ($testing === false) {
            header('Content-Type: application/json');
        }

        $photo_path = $this->photo_retrival_service->getPhotoPath($linux_file, ImageDimensions::fromArray($post_data['image_dimensions']));

        // Strip away project public path
        $project_base_path = dirname(__FILE__, 3) . '/public/';
        $adjusted_path = str_replace($project_base_path, '', $photo_path);

        echo json_encode($adjusted_path);
    }
}
