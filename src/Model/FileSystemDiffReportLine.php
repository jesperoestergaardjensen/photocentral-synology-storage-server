<?php

namespace PhotoCentralSynologyStorageServer\Model;

class FileSystemDiffReportLine
{
    private string $diff_line;

    public function __construct(string $diff_line)
    {
        $this->diff_line = $diff_line;
    }

    public function getDiffLine(): string
    {
        return $this->diff_line;
    }

}