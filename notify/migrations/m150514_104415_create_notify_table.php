<?php

class m150514_104415_create_notify_table extends CDbMigration
{
	public function up()
	{
        $tableName = '{{_notify}}';
        $this->createTable($tableName, [
            'id'=>'pk',
            'date_show'=>'datetime NOT NULL',
            'date_create'=>'datetime NOT NULL',
            'header'=>'varchar(255) NOT NULL',
            'description'=>'text NOT NULL',
            'img'=>'text NOT NULL',
            'user_id'=>'int(11) NOT NULL',
            'read'=>'tinyint(4) NOT NULL DEFAULT 0',
            'repeat'=>'tinyint(4) NOT NULL DEFAULT 0',
            'type'=>'tinyint(4) NOT NULL DEFAULT 1',
            'route'=>'text NOT NULL',
            'route_params'=>'text NOT NULL',
        ], 'ENGINE=MyISAM  DEFAULT CHARSET=utf8');
        $this->createIndex('user_id', $tableName, 'user_id');
	}

	public function down()
	{
		$this->dropTable('{{_notify}}');
		return true;
	}
}