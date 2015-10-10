<?php

class m150526_225027_add_support_groups_to_notify extends CDbMigration
{
	public function up()
	{
        $this->execute('ALTER TABLE {{_notify}} ADD `group_id` int(11) NOT NULL AFTER `user_id`');
	}

	public function down()
	{
        $this->execute('ALTER TABLE {{_notify}} DROP COLUMN `group_id`');
	}
}