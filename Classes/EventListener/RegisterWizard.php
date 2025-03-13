<?php

declare(strict_types=1);

namespace Typo3Api\EventListener;

use TYPO3\CMS\Core\TypoScript\IncludeTree\Event\ModifyLoadedPageTsConfigEvent;

class RegisterWizard
{
    private const DISABLE_TYPO3_API_NEEDLE = '###DISABLE_TYPO3_API###';

    public function __invoke(ModifyLoadedPageTsConfigEvent $event): void
    {
        if (!isset($GLOBALS['TCA']['tt_content']['ctrl']['EXT']['typo3api']['content_elements'])) {
            return;
        }

        $tsConfig = $event->getTsConfig();

        foreach ($tsConfig as $key => $value) {
            $disableTypo3APi = str_contains($value, self::DISABLE_TYPO3_API_NEEDLE);
            if ($disableTypo3APi) {
                return;
            }
        }

        foreach ($GLOBALS['TCA']['tt_content']['ctrl']['EXT']['typo3api']['content_elements'] as $section => $contentElements) {
            foreach ($contentElements as $contentElement) {
                $event->addTsConfig(
                    <<<EOD
mod.wizards.newContentElement.wizardItems.{$section} {
  elements {
    {$contentElement['CType']} {
      iconIdentifier = {$contentElement['iconIdentifier']}
      title = {$contentElement['title']}
      description = {$contentElement['description']}
      tt_content_defValues {
        CType = {$contentElement['CType']}
      }
    }
  }
  show := addToList({$contentElement['CType']})
}
EOD
                );
            }
        }
    }
}
