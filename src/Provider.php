<?php

namespace PhotoCentralSynologyStorageServer;

use PhotoCentralSynologyStorageServer\Factory\PhotoUrlFactory;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Model\FileSystemDiffReportList;
use PhotoCentralSynologyStorageServer\Service\PhotoBulkAddService;
use PhotoCentralSynologyStorageServer\Service\PhotoImportService;
use PhotoCentralSynologyStorageServer\Service\PhotoRetrivalService;
use Slince\Di\Container;

class Provider
{
    private Container $di_container;
    private DatabaseConnection $database_connection;
    private string $image_cache_path;
    private string $synology_nas_host_address;

    public function __construct(
        string $synology_nas_host_address,
        DatabaseConnection $database_connection,
        string $image_cache_path
    ) {
        $this->database_connection = $database_connection;
        $this->image_cache_path = $image_cache_path;
        $this->synology_nas_host_address = $synology_nas_host_address;
    }

    public function initialize()
    {
        $this->di_container = $this->registerDependencies();
    }

    public function importPhotos(bool $debug = false): FileSystemDiffReportList
    {
        /** @var PhotoImportService $photo_import_service */
        $photo_import_service = $this->di_container->get(PhotoImportService::class);
        return $photo_import_service->import($debug);
    }

    public function runController(string $controller_class, bool $testing =  false)
    {
        /**
         * @var Controller $controller
         */
        $controller = $this->di_container->get($controller_class);

        if ($testing) {
            ob_start();
            $controller->run($testing);
            return ob_get_clean();
        }
        $controller->run($testing);
    }

    private function registerDependencies(): Container
    {
        $container = new Container();

        $container->register(DatabaseConnection::class, function () {
            return $this->database_connection;
        });

        $this->registerServices($container);
        $this->registerFactories($container);

        $container->register(PhotoImportService::class);

        return $container;
    }

    private function registerServices(Container $container)
    {
        $container->register(PhotoBulkAddService::class);
        $container->register(PhotoRetrivalService::class)->setArguments(
            [
                'image_cache_path' => $this->image_cache_path,
//                'synology_photo_collection_repository' => $container->getParameter(/*SynologyPhotoCollectionRepository::class*/'gg'),
            ]
        );
    }

    private function registerFactories(Container $container)
    {
        $container->register(PhotoUrlFactory::class)->setArgument('controller_public_path', $this->synology_nas_host_address);
    }
}
