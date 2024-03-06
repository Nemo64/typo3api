<?php

namespace Typo3Api\Hook;


use TYPO3\CMS\Backend\Wizard\NewContentElementWizardHookInterface;

class ContentElementWizardHook implements NewContentElementWizardHookInterface
{
    /**
     * Modifies WizardItems array
     *
     * @param array $wizardItems Array of Wizard Items
     * @param \TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController $parentObject Parent object New Content element wizard
     */
    public function manipulateWizardItems(&$wizardItems, &$parentObject)
    {
        if (!isset($GLOBALS['TCA']['tt_content']['ctrl']['EXT']['typo3api']['content_elements'])) {
            return;
        }

        $keys = array_keys($wizardItems);
        $values = array_values($wizardItems);

        foreach ($GLOBALS['TCA']['tt_content']['ctrl']['EXT']['typo3api']['content_elements'] as $section => $contentElements) {
            $sectionIndex = array_search($section, $keys);

            // create a new tab if it did not exist before
            if ($sectionIndex === false) {
                $sectionIndex = count($keys);
                $keys[$sectionIndex] = $section;
                $values[$sectionIndex] = ['header' => $section];
            }

            array_splice($values, $sectionIndex + 1, 0, $contentElements);
            array_splice($keys, $sectionIndex + 1, 0, array_map(function ($contentElement) use($section) {
                return $section . '_' . $contentElement['CType'];
            }, $contentElements));
        }

        $wizardItems = array_combine($keys, $values);
    }
}
