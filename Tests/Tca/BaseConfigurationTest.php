<?php

namespace Typo3Api\Tca;

use PHPUnit\Framework\TestCase;
use Typo3Api\Builder\TableBuilder;
use Typo3Api\Hook\SqlSchemaHookUtil;
use Typo3Api\PreparationForTypo3;

class BaseConfigurationTest extends TestCase
{
    use PreparationForTypo3;
    use SqlSchemaHookUtil;

    const BASE_SQL = [
        "uid int(11) NOT NULL auto_increment",
        "PRIMARY KEY (uid)",
        "pid INT(11) NOT NULL DEFAULT '0'",
        "INDEX pid (pid)",

        "deleted TINYINT(1) DEFAULT '0' NOT NULL",
        "tstamp INT(11) DEFAULT '0' NOT NULL",
        "crdate INT(11) DEFAULT '0' NOT NULL",
        "cruser_id INT(11) DEFAULT '0' NOT NULL",
        "origUid INT(11) DEFAULT '0' NOT NULL",
    ];

    const BASE_TCA = [
        'ctrl' => [
            'delete' => 'deleted',
            'tstamp' => 'tstamp',
            'crdate' => 'crdate',
            'cruser_id' => 'cruser_id',
            'origUid' => 'origUid',
            'title' => 'Test table',
            'label' => 'uid',
            'EXT' => [
                'typo3api' => [
                    'sql' => [
                        'test_table' => self::BASE_SQL
                    ]
                ]
            ]
        ],
        'interface' => [
            'showRecordFieldList' => '',
        ],
        'columns' => [],
        'types' => [
            '1' => []
        ],
        'palettes' => [],
    ];

    public function testConfiguration()
    {
        TableBuilder::create('test_table');
        // the base configuration is applied automatically

        $this->assertEquals(self::BASE_TCA, $GLOBALS['TCA']['test_table']);
        $this->assertSqlInserted(['test_table' => self::BASE_SQL]);
    }
}
