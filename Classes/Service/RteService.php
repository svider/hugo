<?php


/***************************************************************
 *  Copyright notice
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

namespace SourceBroker\Hugo\Service;

use SourceBroker\Hugo\Configuration\Configurator;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class RteService
 *
 * @package SourceBroker\Hugo\Service
 */
class RteService implements SingletonInterface
{

    /**
     * @var
     */
    protected $urlService;

    /**
     * @var Configurator
     */
    protected $configurator;

    /**
     * RteService constructor.
     */
    public function __construct()
    {
        $this->urlService = GeneralUtility::makeInstance(Typo3UrlService::class);
    }

    /**
     * @param string $html
     * @param Configurator $configurator
     *
     * @return string
     */
    public function parse(string $html, Configurator $configurator): string
    {
        $this->configurator = $configurator;
        return $this->makeLinks($html);
    }

    /**
     * @param string $html
     *
     * @return string
     */
    protected function makeLinks(string $html): string
    {
        $html = str_replace('href="t3://page?uid=1"',
            'href=\'t3://page?uid=1\'', $html);

        $html = preg_replace_callback('/<a\s.+?<\/a>/', function ($aTag) {
            $aTag = preg_replace_callback('/(href="(t3:\/\/.*?)")|(href=\'(t3:\/\/.*?)\')/',
                function ($matches) {
                    $t3Url = $matches[4] ?? $matches[2];
                    $attrQuoteChar = $matches[4] ? '\'' : '"';

                    return 'href='
                        . $attrQuoteChar
                        . $this->urlService->linkArray('', $t3Url, null,
                            $this->configurator)['href']
                        . $attrQuoteChar;
                }, $aTag[0]);

            return $aTag;
        }, $html);

        return $html;
    }
}
