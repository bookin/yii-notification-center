<?php

class m150531_125913_add_indices_for_notifications extends CDbMigration
{
	public function up()
	{
        $this->execute(
            'ALTER TABLE {{notify}} ADD INDEX (`date_show`); '.
            'ALTER TABLE {{notify}} ADD INDEX (`date_end`); '.
            'ALTER TABLE {{notify}} ADD INDEX (`date_create`); '.
            'ALTER TABLE {{notify}} ADD INDEX (`group_id`); '.
            'ALTER TABLE {{notify_status}} ADD INDEX (`date_showed`); '
        );
	}

	public function down()
	{
        $this->execute(
            'ALTER TABLE {{notify}} DROP INDEX `date_show`; '.
            'ALTER TABLE {{notify}} DROP  INDEX `date_end`; '.
            'ALTER TABLE {{notify}} DROP  INDEX `date_create`; '.
            'ALTER TABLE {{notify}} DROP  INDEX `group_id`; '.
            'ALTER TABLE {{notify_status}} DROP INDEX `date_showed`; '
        );
		return true;
	}

}