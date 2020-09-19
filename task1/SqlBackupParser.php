<?php


class SqlBackupParser
{
    public static function findBatchInserts(string $str): array
    {
        $batches = [];
        $pattern = '/INSERT INTO[\s\S]+?;/s';
        preg_match_all($pattern, $str, $batches);
        return !empty($batches) ? $batches[0] : $batches;
    }

    /**
     * @param string $insert
     * @return array
     */
    public static function getFieldNames(string $insert): array
    {
        $fields = [];
        $pattern = '/\((.|\s|\n)+?\)/';
        preg_match_all($pattern, $insert, $fields);
        if (!empty($fields[0])) {
            $fields = explode(', ', trim($fields[0][0], "()"));
            
            $fields = array_map(function ($elem) {
                return trim($elem, "`");
            }, $fields);
        }
        return $fields;
    }

    public static function createInsertWithTail(string $originalInsert): string
    {
        $fields = self::getFieldNames($originalInsert);
        $insertWithTail = '';
        if (!empty($fields)) {
            $updateFields = [];
            foreach ($fields as $field) {
                $updateFields[] = self::getFieldWithValue($field);
            }
            $originalInsert = rtrim($originalInsert, ";");
            $insertWithTail = $originalInsert . ' ON DUPLICATE KEY UPDATE ' . implode(', ', $updateFields) . ';';
        }
        return $insertWithTail;
    }

    public static function getFieldWithValue(string $fieldName): string
    {
        return "`$fieldName` = VALUES(`$fieldName`)";
    }

    public static function getDbTableName(string $queryCreateTable): string
    {
        {
            $matches = [];
            $dbTableName = '';
            $pattern = '/[a-zA-z0-9]+\.[a-zA-z0-9]+/';
            preg_match($pattern, $queryCreateTable, $matches);

            if (!empty($matches)) {
                $dbTableName = $matches[0];
            }

            return $dbTableName;
        }
    }

}