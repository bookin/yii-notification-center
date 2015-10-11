<?php

class m150529_215923_add_dates_for_notify extends CDbMigration
{
    public function up()
    {
        $this->addColumn('{{notify_status}}', 'date_showed', 'datetime not null');
        $this->execute('UPDATE {{notify_status}} s
                        LEFT JOIN {{notify}} n ON s.notify_id = n.id
                        SET s.date_showed = n.date_show');
        $this->addColumn('{{notify}}', 'date_end', 'datetime not null after date_show');
    }

    public function down()
    {
        $this->dropColumn('{{notify_status}}', 'date_showed');
        $this->dropColumn('{{notify}}', 'date_end');
        return true;
    }
}