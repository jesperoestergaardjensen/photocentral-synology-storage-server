<?php

namespace PhotoCentralSynologyStorageServer\Repository;

use PhotoCentralSynologyStorageServer\Exception\PhotoCentralSynologyServerException;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use PhotoCentralSynologyStorageServer\Model\DatabaseTables\LinuxFileDatabaseTable;
use PhotoCentralSynologyStorageServer\Model\LinuxFile;
use TexLab\MyDB\DB;
use TexLab\MyDB\DbEntity;

/**
 * @internal
 */
class LinuxFileRepository
{
    private DbEntity $database_table;
    private DatabaseConnection $database_connection;

    public function __construct(DatabaseConnection $database_connection)
    {
        $this->database_connection = $database_connection;
    }

    public function connectToDb(): void
    {
        $link = DB::link([
            'host'     => $this->database_connection->getHost(),
            'username' => $this->database_connection->getUsername(),
            'password' => $this->database_connection->getPassword(),
            'dbname'   => $this->database_connection->getDatabaseName(),
        ]);

        $this->database_table = new DbEntity(LinuxFileDatabaseTable::NAME, $link);
    }

    /**
     * @param string     $search_string
     * @param int        $limit
     * @param array|null $allowed_photo_collection_ids
     *
     * @return LinuxFile[]
     */
    public function search(string $search_string, int $limit, array $allowed_photo_collection_ids = null): array
    {
        $linux_files = [];

        if ($allowed_photo_collection_ids === null) {
            $sql = "SELECT * FROM LinuxFile WHERE MATCH (file_name, file_path) AGAINST ('{$search_string}') LIMIT {$limit}";

        } else {
            $synology_photo_collection_ids = implode("','", $allowed_photo_collection_ids);
            $sql = "SELECT * FROM LinuxFile WHERE " . LinuxFileDatabaseTable::ROW_SYNOLOGY_PHOTO_COLLECTION_ID . " IN ('{$synology_photo_collection_ids}') AND MATCH (file_name, file_path) AGAINST ('{$search_string}') LIMIT {$limit}";
        }

        $linux_files_data = $this->database_table->runSQL($sql);

        foreach ($linux_files_data as $linux_file_data) {
            $linux_file = LinuxFile::fromArray($linux_file_data);
            $linux_files[] = $linux_file;
        }

        return $linux_files;
    }

    public function add(LinuxFile $linux_file): void
    {
        $linux_file_array = $linux_file->toArray();
        $linux_file_array[LinuxFileDatabaseTable::ROW_DUPLCIATE] = (int) $linux_file_array[LinuxFileDatabaseTable::ROW_DUPLCIATE];
        $linux_file_array[LinuxFileDatabaseTable::ROW_SKIPPED] = (int) $linux_file_array[LinuxFileDatabaseTable::ROW_SKIPPED];
        $linux_file_array[LinuxFileDatabaseTable::ROW_IMPORTED] = (int) $linux_file_array[LinuxFileDatabaseTable::ROW_IMPORTED];
        $linux_file_array[LinuxFileDatabaseTable::ROW_SCHEDULED_FOR_DELETION] = (int) $linux_file_array[LinuxFileDatabaseTable::ROW_SCHEDULED_FOR_DELETION];
        unset($linux_file_array[LinuxFileDatabaseTable::ROW_IMPORT_DATE_TIME]);
        $this->database_table->add($linux_file_array);
    }

    public function update(LinuxFile $linux_file): void
    {
        $condition = [
            LinuxFileDatabaseTable::ROW_INODE_INDEX                  => $linux_file->getInodeIndex(),
            LinuxFileDatabaseTable::ROW_SYNOLOGY_PHOTO_COLLECTION_ID => $linux_file->getSynologyPhotoCollectionId(),
        ];

        $this->database_table->edit($condition,
            [
                LinuxFileDatabaseTable::ROW_FILE_NAME => $linux_file->getFileName(),
                LinuxFileDatabaseTable::ROW_FILE_PATH => $linux_file->getFilePath(),
            ]
        );
    }

    public function delete(string $synology_photo_collection_id, int $inode_index): void
    {
        $table_name = LinuxFileDatabaseTable::NAME;
        $where_clause = LinuxFileDatabaseTable::ROW_SYNOLOGY_PHOTO_COLLECTION_ID . " = '{$synology_photo_collection_id}' AND " . LinuxFileDatabaseTable::ROW_INODE_INDEX . " = {$inode_index}";
        $this->database_table->runSQL("DELETE FROM {$table_name} WHERE {$where_clause};");
    }

    /**
     * @throws PhotoCentralSynologyServerException
     */
    public function getByInode(int $inode_index, string $synology_photo_collection_id): LinuxFile
    {
        $linux_files_data = ($this->database_table
            ->reset()
            ->setSelect('*')
            ->setWhere(LinuxFileDatabaseTable::ROW_INODE_INDEX . " = $inode_index")
            ->addWhere(LinuxFileDatabaseTable::ROW_SYNOLOGY_PHOTO_COLLECTION_ID . " = '$synology_photo_collection_id'")
            ->get());

        if (isset($linux_files_data[0])) {
            return LinuxFile::fromArray($linux_files_data[0]);
        } else {
            throw new PhotoCentralSynologyServerException("Cannot find Linux file with inode_index = $inode_index and photo collection id $synology_photo_collection_id");
        }
    }

    public function getByPhotoUuid(string $photo_uuid, ?string $synology_photo_collection_id): LinuxFile
    {
        $sql = ($this->database_table
            ->reset()
            ->setSelect('*')
            ->setWhere(LinuxFileDatabaseTable::ROW_PHOTO_UUID . " = '$photo_uuid'")
        );

        if ($synology_photo_collection_id) {
            $sql = $sql->addWhere(LinuxFileDatabaseTable::ROW_SYNOLOGY_PHOTO_COLLECTION_ID . " = '$synology_photo_collection_id'");
        }

        $linux_files_data = $sql->get();

        if (isset($linux_files_data[0])) {
            return LinuxFile::fromArray($linux_files_data[0]);
        } else {
            throw new PhotoCentralSynologyServerException("Cannot find Linux file with photo_uuid = $photo_uuid and photo collection id $synology_photo_collection_id");
        }
    }

    /**
     * @param int $inode_index
     *
     * @return LinuxFile[]
     */
    public function list(int $inode_index): array
    {
        $linux_files = [];
        $linux_files_data = ($this->database_table
            ->reset()
            ->setSelect('*')
            ->setWhere(LinuxFileDatabaseTable::ROW_INODE_INDEX . " = $inode_index")
            ->get());

        foreach ($linux_files_data as $linux_file_data) {
            $linux_file = LinuxFile::fromArray($linux_file_data);
            $linux_files[] = $linux_file;
        }

        return $linux_files;
    }

    public function bulkAdd(array $linux_files_to_bulk_insert, int $bulk_size = 1000): void
    {
        $table_name = LinuxFileDatabaseTable::NAME;
        $table_columns =
            LinuxFileDatabaseTable::ROW_FILE_UUID . ',' .
            LinuxFileDatabaseTable::ROW_PHOTO_UUID . ',' .
            LinuxFileDatabaseTable::ROW_ROW_ADDED_DATA_TIME . ',' .
            LinuxFileDatabaseTable::ROW_SYNOLOGY_PHOTO_COLLECTION_ID . ',' .
            LinuxFileDatabaseTable::ROW_INODE_INDEX . ',' .
            LinuxFileDatabaseTable::ROW_LAST_MODIFIED_DATE . ',' .
            LinuxFileDatabaseTable::ROW_FILE_NAME . ',' .
            LinuxFileDatabaseTable::ROW_IMPORTED . ',' .
            LinuxFileDatabaseTable::ROW_IMPORT_DATE_TIME . ',' .
            LinuxFileDatabaseTable::ROW_FILE_PATH;

        while (count($linux_files_to_bulk_insert) !== 0) {
            // Insert in portions of bulk size - default 1000
            $sql_values = '';
            $duplicate_check = [];
            for ($count = 0; $count < $bulk_size && count($linux_files_to_bulk_insert) == ! 0; $count++) {

                /** @var LinuxFile $linux_file */
                $linux_file = array_shift($linux_files_to_bulk_insert);
                $duplicate_check[$linux_file->getSynologyPhotoCollectionId()][$linux_file->getPhotoUuid()][$linux_file->getInodeIndex()] = $linux_file;
                $row_added_date_time = time();

                $sql_values .= "(
                    '{$linux_file->getFileUuid()}',                
                    '{$linux_file->getPhotoUuid()}',                
                    {$row_added_date_time}, 
                    '{$linux_file->getSynologyPhotoCollectionId()}',
                    {$linux_file->getInodeIndex()},
                    {$linux_file->getLastModifiedDate()},
                    '{$linux_file->getFileName()}',
                    0,
                    NULL,
                    '{$linux_file->getFilePath()}'
                ),";
            }

            $sql_values = rtrim($sql_values, ','); // strip last comma
            $this->database_table->runSQL("INSERT INTO {$table_name} ({$table_columns}) VALUES {$sql_values} ON DUPLICATE KEY UPDATE duplicate = true;");
            $this->handleDuplicateInserts($duplicate_check);
        };
    }

    private function handleDuplicateInserts(array $duplicate_check): void
    {
        $duplicate_linux_file_list = $this->listDuplicates();

        if (count($duplicate_linux_file_list) > 0) {
            foreach ($duplicate_linux_file_list as $duplicate_linux_file) {
                // Remove duplicate flag in database
                $this->removeDuplicateFlag($duplicate_linux_file);
                // Remove linux file entries not to insert again
                $photo_collection_id = $duplicate_linux_file->getSynologyPhotoCollectionId();
                $photo_uuid = $duplicate_linux_file->getPhotoUuid();
                $inode_index = $duplicate_linux_file->getInodeIndex();
                unset($duplicate_check[$photo_collection_id][$photo_uuid][$inode_index]);

                foreach ($duplicate_check[$photo_collection_id][$photo_uuid] as $linux_file) {
                    /** @var LinuxFile $linux_file */
                    $error_message = 'Duplicate error. An image with this photo collection id and photo uuid have allready been imported';
                    $linux_file->setSkipped(true);
                    $linux_file->setSkippedError($error_message);
                    $linux_file->setRowAddedDateTime(time());
                    $this->add($linux_file);
                }
            }
        }
    }

    private function removeDuplicateFlag(LinuxFile $linux_file): void
    {
        $linux_file->setDuplicate(false);
        $this->setDuplicate($linux_file->getInodeIndex(), $linux_file->getSynologyPhotoCollectionId(), false);
    }

    /**
     * @return LinuxFile[]
     */
    private function listDuplicates(): array
    {
        $linux_files = [];
        $linux_files_data = ($this->database_table
            ->reset()
            ->setSelect('*')
            ->setWhere(LinuxFileDatabaseTable::ROW_DUPLCIATE . " = 1")
            ->get());

        foreach ($linux_files_data as $linux_file_data) {
            $linux_file = LinuxFile::fromArray($linux_file_data);
            $linux_files[] = $linux_file;
        }

        return $linux_files;
    }

    /**
     * @param string $synology_photo_collection_id
     * @param int    $limit
     *
     * @return LinuxFile[]
     */
    public function listLinuxFilesNotImported(string $synology_photo_collection_id, int $limit): array
    {
        $linux_files = [];
        $linux_files_data = ($this->database_table
            ->reset()
            ->setSelect('*')
            ->setWhere(LinuxFileDatabaseTable::ROW_SYNOLOGY_PHOTO_COLLECTION_ID . " = '$synology_photo_collection_id'")
            ->addWhere(LinuxFileDatabaseTable::ROW_IMPORTED . " = 0")
            ->addWhere(LinuxFileDatabaseTable::ROW_SKIPPED . " = 0")
            ->setLimit($limit)
            ->get());

        foreach ($linux_files_data as $linux_file_data) {
            $linux_file = LinuxFile::fromArray($linux_file_data);
            $linux_files[] = $linux_file;
        }

        return $linux_files;
    }

    public function setSkippedError(int $inode_index, string $synology_photo_collection_id, string $error_message)
    {
        $this->database_table->edit([
            LinuxFileDatabaseTable::ROW_INODE_INDEX                  => $inode_index,
            LinuxFileDatabaseTable::ROW_SYNOLOGY_PHOTO_COLLECTION_ID => $synology_photo_collection_id,
        ], [
            LinuxFileDatabaseTable::ROW_SKIPPED       => true,
            LinuxFileDatabaseTable::ROW_SKIPPED_ERROR => $error_message,
        ]);
    }

    public function setDuplicate(int $inode_index, string $synology_photo_collection_id, bool $status)
    {
        $this->database_table->edit([
            LinuxFileDatabaseTable::ROW_INODE_INDEX                  => $inode_index,
            LinuxFileDatabaseTable::ROW_SYNOLOGY_PHOTO_COLLECTION_ID => $synology_photo_collection_id,
        ], [
            LinuxFileDatabaseTable::ROW_DUPLCIATE => (int) $status,
        ]);
    }

    public function bulkSetImported(string $synology_photo_collection_id, array $inode_list, int $import_date_time)
    {
        $table_name = LinuxFileDatabaseTable::NAME;

        $rows_to_update = LinuxFileDatabaseTable::ROW_IMPORTED . ' = 1,' . LinuxFileDatabaseTable::ROW_IMPORT_DATE_TIME . ' = ' . $import_date_time;
        $inode_list_string = implode("','", $inode_list);
        $where_clause = LinuxFileDatabaseTable::ROW_SYNOLOGY_PHOTO_COLLECTION_ID . " = '{$synology_photo_collection_id}' AND " . LinuxFileDatabaseTable::ROW_INODE_INDEX . " IN ('{$inode_list_string}')";

        $this->database_table->runSQL("UPDATE {$table_name} SET {$rows_to_update} WHERE {$where_clause} ");
    }
}
