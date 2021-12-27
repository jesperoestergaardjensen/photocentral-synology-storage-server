<?php

namespace PhotoCentralSynologyStorageServer\Controller;

use PhotoCentralSynologyStorageServer\Controller;
use PhotoCentralSynologyStorageServer\Repository\PhotoRepository;
use PhotoCentralSynologyStorageServer\Service\PhotoFilterOverrideService;

class ListPhotosController extends Controller
{
    private PhotoRepository $photo_repository;

    public function __construct(
        PhotoRepository $photo_repository
    ) {
        $this->photo_repository = $photo_repository;
    }

    public function run(bool $testing = false): void
    {
        $photo_filters = null;
        $photo_sorting_parameters = null;

        if (isset($_POST['photo_filters'])) {
            foreach ($_POST['photo_filters'] as $filter_class_name => $filter_as_array) {
                $override_filter_class_name = PhotoFilterOverrideService::map($filter_class_name);
                $photo_filters[] = $override_filter_class_name::fromArray($filter_as_array, $override_filter_class_name);
            }
        }

        if (isset($_POST['photo_sorting_parameters'])) {
            foreach ($_POST['photo_sorting_parameters'] as $sorting_class_name => $filter_as_array) {
                $photo_sorting_parameters[] = $sorting_class_name::fromArray($filter_as_array);
            }
        }

        $this->photo_repository->connectToDb();
        $photo_list = $this->photo_repository->listWithFilters($photo_filters, $photo_sorting_parameters, $_POST['limit']);

        if ($testing === false) {
            header('Content-Type: application/json');
        }

        echo json_encode($photo_list);
    }

}