<?php

require_once('Db.php');
require_once ('SqlBackupParser.php');
require_once ('RestoreDb.php');

$sqlDirName = 'sql';
RestoreDb::restore("$sqlDirName/db_test.sql", "$sqlDirName/table_test.sql", "$sqlDirName/test2.sql");