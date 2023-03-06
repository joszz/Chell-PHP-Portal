<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class DevicesMigration_500
 */
class DevicesMigration_500 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('devices', [
            'columns' => [
                new Column(
                    'id',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'unsigned' => true,
                        'notNull' => true,
                        'autoIncrement' => true,
                        'size' => 1,
                        'first' => true
                    ]
                ),
                new Column(
                    'name',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 255,
                        'after' => 'id'
                    ]
                ),
                new Column(
                    'ip',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => true,
                        'size' => 15,
                        'after' => 'name'
                    ]
                ),
                new Column(
                    'mac',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 18,
                        'after' => 'ip'
                    ]
                ),
                new Column(
                    'shutdown_method',
                    [
                        'type' => Column::TYPE_ENUM,
                        'default' => "none",
                        'notNull' => false,
                        'size' => "'none','rpc','adb'",
                        'after' => 'mac'
                    ]
                ),
                new Column(
                    'shutdown_user',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'shutdown_method'
                    ]
                ),
                new Column(
                    'shutdown_password',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'shutdown_user'
                    ]
                ),
                new Column(
                    'show_on_dashboard',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "1",
                        'unsigned' => true,
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'shutdown_password'
                    ]
                ),
                new Column(
                    'hypervadmin_url',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'show_on_dashboard'
                    ]
                ),
                new Column(
                    'hypervadmin_user',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'hypervadmin_url'
                    ]
                ),
                new Column(
                    'hypervadmin_password',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'hypervadmin_user'
                    ]
                ),
                new Column(
                    'broadcast',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 15,
                        'after' => 'hypervadmin_password'
                    ]
                ),
                new Column(
                    'devicescol',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 45,
                        'after' => 'broadcast'
                    ]
                ),
            ],
            'indexes' => [
                new Index('PRIMARY', ['id'], 'PRIMARY'),
            ],
            'options' => [
                'TABLE_TYPE' => 'BASE TABLE',
                'AUTO_INCREMENT' => '',
                'ENGINE' => 'InnoDB',
                'TABLE_COLLATION' => 'utf8mb3_general_ci',
            ],
        ]);
    }

    /**
     * Run the migrations
     *
     * @return void
     */
    public function up(): void
    {
    }

    /**
     * Reverse the migrations
     *
     * @return void
     */
    public function down(): void
    {
    }
}
