<?php

use Phalcon\Db\Column;
use Phalcon\Db\Exception;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Migrations\Mvc\Model\Migration;

/**
 * Class SnmpRecordsMigration_500
 */
class SnmpRecordsMigration_500 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     * @throws Exception
     */
    public function morph(): void
    {
        $this->morphTable('snmp_records', [
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
                    'snmp_host_id',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'unsigned' => true,
                        'notNull' => true,
                        'size' => 1,
                        'after' => 'id'
                    ]
                ),
                new Column(
                    'label',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'snmp_host_id'
                    ]
                ),
                new Column(
                    'label_oid',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'label'
                    ]
                ),
                new Column(
                    'value_oid',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'label_oid'
                    ]
                ),
                new Column(
                    'show_dashboard',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'default' => "1",
                        'unsigned' => true,
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'value_oid'
                    ]
                ),
                new Column(
                    'group_value',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'unsigned' => true,
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'show_dashboard'
                    ]
                ),
                new Column(
                    'position',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'unsigned' => true,
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'group_value'
                    ]
                ),
                new Column(
                    'divisor',
                    [
                        'type' => Column::TYPE_INTEGER,
                        'unsigned' => true,
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'position'
                    ]
                ),
                new Column(
                    'divisor_decimals',
                    [
                        'type' => Column::TYPE_TINYINTEGER,
                        'unsigned' => true,
                        'notNull' => false,
                        'size' => 1,
                        'after' => 'divisor'
                    ]
                ),
                new Column(
                    'value_unit',
                    [
                        'type' => Column::TYPE_VARCHAR,
                        'notNull' => false,
                        'size' => 255,
                        'after' => 'divisor_decimals'
                    ]
                ),
            ],
            'indexes' => [
                new Index('PRIMARY', ['id'], 'PRIMARY'),
                new Index('id_UNIQUE', ['id'], 'UNIQUE'),
                new Index('snmp_host_id_idx', ['snmp_host_id'], ''),
            ],
            'references' => [
                new Reference(
                    'snmp_host_id',
                    [
                        'referencedTable' => 'snmp_hosts',
                        'columns' => ['snmp_host_id'],
                        'referencedColumns' => ['id'],
                        'onUpdate' => 'NO ACTION',
                        'onDelete' => 'CASCADE'
                    ]
                ),
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
