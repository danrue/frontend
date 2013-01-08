<?php

$status = true;

/* add actor columns */
$tables = array(
  'action','activity','album','albumGroup','credential','elementAlbum','elementGroup','elementTag',
  'group','groupMember','photo','photoVersion','resourceMap','tag','webhook'
);

foreach($tables as $table)
{
  $sql = <<<SQL
    ALTER TABLE `{$this->mySqlTablePrefix}{$table}` ADD `actor` VARCHAR( 127 ) NOT NULL AFTER `owner` ;
SQL;
  $status = $status && mysql_4_0_0($sql);

  $sql = <<<SQL
    UPDATE `{$this->mySqlTablePrefix}{$table}` SET `actor`=`owner`;
SQL;
  $status = $status && mysql_4_0_0($sql);

}

$sql = <<<SQL
  ALTER TABLE `{$this->mySqlTablePrefix}activity` ADD `permission` BOOLEAN NOT NULL AFTER `data` 
;
$status = $status && mysql_4_0_0($sql);

$sql = <<<SQL
  ALTER TABLE `{$this->mySqlTablePrefix}activity` ADD `elementId` VARCHAR( 6 ) NOT NULL AFTER `type`;
SQL;
$status = $status && mysql_4_0_0($sql);


$sql = <<<SQL
  ALTER TABLE `{$this->mySqlTablePrefix}activity` DROP PRIMARY KEY , ADD PRIMARY KEY ( `owner` , `id` ) ;
SQL;
$status = $status && mysql_4_0_0($sql);


$sql = <<<SQL
  CREATE TABLE `{$this->mySqlTablePrefix}relationship` (
   `actor` varchar(127) NOT NULL,
   `follows` varchar(127) NOT NULL,
   `dateCreated` datetime NOT NULL,
   PRIMARY KEY (`actor`,`follows`)
  ) ENGINE=InnoDB;
SQL;
$status = $status && mysql_4_0_0($sql);


$sql = <<<SQL
  UPDATE `{$this->mySqlTablePrefix}admin` SET `value`=:version WHERE `key`=:key
SQL;
$status = $status && mysql_4_0_0($sql, array(':key' => 'version', ':version' => '4.0.0'));

function mysql_4_0_0($sql, $params = array())
{
  try
  {
    getDatabase()->execute($sql, $params);
    getLogger()->info($sql);
  }
  catch(Exception $e)
  {
    getLogger()->crit($e->getMessage()); 
    return false;
  }
  return true;
}

return $status;


