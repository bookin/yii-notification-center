<?php

class m150525_150051_change_notifications_table extends CDbMigration
{
    protected $tableNotify='{{notify}}';
    protected $tableNotifyStatus='{{notify_status}}';

	public function up()
	{
        $this->execute("ALTER TABLE {$this->tableNotify} DROP COLUMN `read`;");
        $this->execute("ALTER TABLE {$this->tableNotify} DROP COLUMN `repeat`;");
        $this->execute("ALTER TABLE {$this->tableNotify} ENGINE = InnoDB;");
        $this->createTable($this->tableNotifyStatus, [
            'id'=>'pk',
            'notify_id'=>'int(11) NOT NULL',
            'user_id'=>'int(11) NOT NULL',
            'read_status'=>'tinyint(4) NOT NULL DEFAULT 0'
        ], 'ENGINE=InnoDB  DEFAULT CHARSET=utf8');
        $this->createIndex('user_id', $this->tableNotifyStatus, 'user_id');
        $this->createIndex('notify_id', $this->tableNotifyStatus, 'notify_id');
        $this->addForeignKey('notify_status_fk_notify_id', $this->tableNotifyStatus, 'notify_id', $this->tableNotify, 'id', 'CASCADE', 'CASCADE');
	}

	public function down()
    {
        $this->dropTable($this->tableNotifyStatus);
        $this->execute("ALTER TABLE {$this->tableNotify} ADD `read` tinyint(4) NOT NULL DEFAULT 0;");
        $this->execute("ALTER TABLE {$this->tableNotify} ADD `repeat` tinyint(4) NOT NULL DEFAULT 0;");
        $this->execute("ALTER TABLE {$this->tableNotify} ENGINE = MyISAM;");
        return true;
	}
}