<?php
require_once('Db.php');
require_once('SqlBackupParser.php');

class RestoreDb
{
    /**
     * @param string $dataBaseBackupFile
     * @param string $tableSchemaBackupFile
     * @param string $tableDataBackupFile
     * @throws Exception
     */
    public static function restore(string $dataBaseBackupFile = '', string $tableSchemaBackupFile = '', string $tableDataBackupFile = '')
    {
        if (!empty($dataBaseBackupFile)) {
            self::restoreDataBase($dataBaseBackupFile);
        }
        if (!empty($tableSchemaBackupFile)) {
            self::restoreTableSchema($tableSchemaBackupFile);
        }
        if (!empty($tableDataBackupFile)) {
            self::restoreTableData($tableDataBackupFile);
        }
    }

    /**
     * @param $dataBaseBackupFile
     * @throws Exception
     */
    public static function restoreDataBase($dataBaseBackupFile): bool
    {
        $result = false;
        if (!empty($dataBaseBackupFile)) {
            if (file_exists($dataBaseBackupFile)) {
                $content = file_get_contents($dataBaseBackupFile);

                if (($result = Db::connection()->query($content)) !== true) {
                    throw new Exception('Error execution query ' . Db::connection()->error);
                }
            }
        }
        return $result;
    }

    /**
     * @param $tableSchemaBackupFile
     * @return bool
     * @throws Exception
     */
    public static function restoreTableSchema($tableSchemaBackupFile)
    {
        $result = false;
        if (file_exists($tableSchemaBackupFile)) {
            $content = file_get_contents($tableSchemaBackupFile);
            $dbTableName = SqlBackupParser::getDbTableName($content);
            if ($dbTableName) {
                $queryDescribe = "DESCRIBE $dbTableName";
                $resultDescribe = Db::connection()->query($queryDescribe);
                if ($resultDescribe && $resultDescribe->num_rows > 0) {
                    Db::connection()->query("DROP TABLE $dbTableName");
                }
                if (($result = Db::connection()->query($content)) !== true) {
                    throw new Exception('Error execution query ' . Db::connection()->error);
                } else {
                    $result = true;
                }
            }
        }

        return $result;
    }

    /**
     * @param $tableDataBackupFile
     * @return false
     * @throws Exception
     */
    public static function restoreTableData($tableDataBackupFile)
    {
        $content = file_get_contents($tableDataBackupFile);
        $batches = SqlBackupParser::findBatchInserts($content);

        foreach ($batches as $batch) {
            $insertWithTail = SqlBackupParser::createInsertWithTail($batch);

            if (!empty($insertWithTail)) {
                if (($result = Db::connection()->query($insertWithTail)) !== true) {
                    throw new Exception('Error execution query ' . Db::connection()->error);
                }
            } else {
                return false;
            }
        }
    }
}