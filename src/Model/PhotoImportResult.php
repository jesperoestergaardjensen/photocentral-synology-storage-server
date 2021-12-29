<?php

namespace PhotoCentralSynologyStorageServer\Model;

class PhotoImportResult
{
    /**
     * @var SynologyPhotoCollectionFolderDiffResult[]
     */
    private array $complete_result_map = [];

    public function add(SynologyPhotoCollectionFolderDiffResult $collection_folder_diff_result, string $synology_photo_collection_id)
    {
        $this->complete_result_map[$synology_photo_collection_id] = $collection_folder_diff_result;
    }

    public function getCompleteResultMap(): array
    {
        return $this->complete_result_map;
    }

    public function getPhotoCollectionFolderDiffResult(string $synology_photo_collection_id): SynologyPhotoCollectionFolderDiffResult
    {
        return $this->complete_result_map[$synology_photo_collection_id];
    }
}
