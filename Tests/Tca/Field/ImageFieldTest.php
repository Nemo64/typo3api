<?php

namespace Typo3Api\Tca\Field;


use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use Typo3Api\Builder\Context\TableBuilderContext;
use Typo3Api\PreparationForTypo3;

class ImageFieldTest extends FileFieldTest
{
    use PreparationForTypo3; // tt_content is needed here

    protected function createFieldInstance(string $name, array $options = []): AbstractField
    {
        return new ImageField($name, $options);
    }

    protected function assertBasicCtrlChange(AbstractField $field)
    {
        $stubTable = new TableBuilderContext('stub_table', '1');

        $ctrl = [];
        $field->modifyCtrl($ctrl, $stubTable);
        $this->assertEquals([
            'thumbnail' => $field->getName()
        ], $ctrl, "ctrl change");
    }

    protected function assertBasicColumns(AbstractField $field)
    {
        $stubTable = new TableBuilderContext('stub_table', '1');

        $expectedColumns = [
            $field->getName() => [
                'label' => $field->getOption('label'),
                'config' => [
                    'type' => 'file',
                    'allowed' => ['common-image-types'],
                    'minitems' => 0,
                    'maxitems' => 100,
                    'appearance' => [
                        'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference',
                        'collapseAll' => true,
                        'showPossibleLocalizationRecords' => true,
                        'showAllLocalizationLink' => true,
                        'showSynchronizationLink' => true,
                        'enabledControls' => [
                            'localize' => true,
                            'hide' => true,
                        ],
                    ],
                    'overrideChildTca' => [
                        'types' => [
                            '0' => [
                                'showitem' => '
                                --palette--;;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\FileType::TEXT->value => [
                                'showitem' => '
                                --palette--;;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\FileType::IMAGE->value => [
                                'showitem' => '
                                --palette--;;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\FileType::AUDIO->value => [
                                'showitem' => '
                                --palette--;;audioOverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\FileType::VIDEO->value => [
                                'showitem' => '
                                --palette--;;videoOverlayPalette,
                                --palette--;;filePalette'
                            ],
                            \TYPO3\CMS\Core\Resource\FileType::APPLICATION->value => [
                                'showitem' => '
                                --palette--;;imageoverlayPalette,
                                --palette--;;filePalette'
                            ]
                        ]
                    ],
                ]
            ]
        ];


        $actualColumns = $field->getColumns($stubTable);

        $this->assertEquals($expectedColumns, $actualColumns);
    }

    /**
     * @dataProvider validNameProvider
     * @param string $fieldName
     */
    public function testThumbnail(string $fieldName): void
    {
        $stubTable = new TableBuilderContext('stub_table', '1');
        $altFieldName = $fieldName . '_2';

        $ctrl = [];
        $field = $this->createFieldInstance($fieldName, ['useAsThumbnail' => false]);
        $field->modifyCtrl($ctrl, $stubTable);
        $this->assertEmpty($ctrl, "No thumbnail modified");

        $ctrl = [];
        $field = $this->createFieldInstance($fieldName, ['useAsThumbnail' => true]);
        $field->modifyCtrl($ctrl, $stubTable);
        $this->assertEquals(['thumbnail' => $fieldName], $ctrl, "thumbnail added");

        $ctrl = [];
        $field = $this->createFieldInstance($fieldName);
        $field->modifyCtrl($ctrl, $stubTable);
        $this->assertEquals(['thumbnail' => $fieldName], $ctrl, "thumbnail added even if not specified");

        // $ctrl = []; // left out on purpose
        $field = $this->createFieldInstance($altFieldName);
        $field->modifyCtrl($ctrl, $stubTable);
        $this->assertEquals(['thumbnail' => $fieldName], $ctrl, "thumbnail not overwritten");

        // $ctrl = []; // left out on purpose
        $field = $this->createFieldInstance($altFieldName, ['useAsThumbnail' => 'force']);
        $field->modifyCtrl($ctrl, $stubTable);
        $this->assertEquals(['thumbnail' => $altFieldName], $ctrl, "thumbnail force overwritten");


    }
}
