<?php

namespace PhotoCentralSynologyStorageServer;

use mysqli;
use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\DatabaseConnection;
use TexLab\MyDB\DB;

abstract class Controller
{
    public function sanitizePost(array $post_data_keys, DatabaseConnection $database_connection): array
    {
        $database_link = $this->getDabaseLink($database_connection);
        $sanitized_post_data = [];
        foreach($post_data_keys as $post_data_key => $default_value) {
            if (isset($_POST[$post_data_key])) {
                if (is_array($_POST[$post_data_key])) {
                    foreach ($_POST[$post_data_key] as $array_key => $array_value) {
                        $sanitized_post_data[$post_data_key][$array_key] = mysqli_real_escape_string($database_link, $array_value);
                    }
                    $sanitized_post_data[$post_data_key] = $_POST[$post_data_key];
                } else {
                    $sanitized_post_data[$post_data_key] = mysqli_real_escape_string($database_link, $_POST[$post_data_key]);
                }
            } else {
                $sanitized_post_data[$post_data_key] = $default_value;
            }
        }
        return $sanitized_post_data;
    }

    public function run(bool $testing = false): void
    {
        // TODO catch exceptions to avoid info to be displayed to wrong persons
    }

    private function getDabaseLink(DatabaseConnection $database_connection): mysqli
    {
        return DB::link([
            'host'     => $database_connection->getHost(),
            'username' => $database_connection->getUsername(),
            'password' => $database_connection->getPassword(),
            'dbname'   => $database_connection->getDatabaseName(),
        ]);
    }
}
