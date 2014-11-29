<?php
/**
 * This file is part of PHPWord - A pure PHP library for reading and writing
 * word processing documents.
 *
 * PHPWord is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/PHPWord/contributors.
 *
 * @link        https://github.com/PHPOffice/PHPWord
 * @copyright   2010-2014 PHPWord contributors
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */

namespace PhpOffice\PhpWord\Writer\RTF\Part;

use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Writer\RTF\Element\Container;

/**
 * RTF document part writer
 *
 * @since 0.11.0
 */
class Document extends AbstractPart
{
    /**
     * Write part
     *
     * @return string
     */
    public function write()
    {
        $content = '';

        $content .= $this->writeInfo();
        $content .= $this->writeFormatting();
        $content .= $this->writeSections();

        return $content;
    }

    /**
     * Write document information
     *
     * @return string
     */
    private function writeInfo()
    {
        $docProps = $this->getParentWriter()->getPhpWord()->getDocumentProperties();
        $properties = array('title', 'subject', 'category', 'keywords', 'comment',
            'author', 'operator', 'creatim', 'revtim', 'company', 'manager');
        $mapping = array('comment' => 'description', 'author' => 'creator', 'operator' => 'lastModifiedBy',
            'creatim' => 'created', 'revtim' => 'modified');
        $dateFields = array('creatim', 'revtim');

        $content = '';

        $content .= '{';
        $content .= '\info';
        foreach ($properties as $property) {
            $method = 'get' . (array_key_exists($property, $mapping) ? $mapping[$property] : $property);
            $value = $docProps->$method();
            $value = in_array($property, $dateFields) ? $this->getDateValue($value) : $value;
            $content .= "{\\{$property} {$value}}";
        }
        $content .= '}';
        $content .= PHP_EOL;

        return $content;
    }

    /**
     * Write document formatting properties
     *
     * @return string
     */
    private function writeFormatting()
    {
        $content = '';

        $content .= '\deftab720'; // Set the default tab size (720 twips)
        $content .= '\viewkind1'; // Set the view mode of the document

        $content .= '\uc1'; // Set the numberof bytes that follows a unicode character
        $content .= '\pard'; // Resets to default paragraph properties.
        $content .= '\nowidctlpar'; // No widow/orphan control
        $content .= '\lang1036'; // Applies a language to a text run (1036 : French (France))
        $content .= '\kerning1'; // Point size (in half-points) above which to kern character pairs
        $content .= '\fs' . (Settings::getDefaultFontSize() * 2); // Set the font size in half-points
        $content .= PHP_EOL;

        return $content;
    }

    /**
     * Write sections
     *
     * @return string
     */
    private function writeSections()
    {
        $content = '';

        $sections = $this->getParentWriter()->getPhpWord()->getSections();
        foreach ($sections as $section) {
            $writer = new Container($this->getParentWriter(), $section);
            $content .= $writer->write();
        }

        return $content;
    }

    /**
     * Get date value
     *
     * The format of date value is `\yr?\mo?\dy?\hr?\min?\sec?`
     *
     * @param int $value
     * @return string
     */
    private function getDateValue($value)
    {
        $dateParts = array(
            'Y' => 'yr',
            'm' => 'mo',
            'd' => 'dy',
            'H' => 'hr',
            'i' => 'min',
            's' => 'sec',
        );
        $result = '';
        foreach ($dateParts as $dateFormat => $controlWord) {
            $result .= '\\' . $controlWord . date($dateFormat, $value);
        }

        return $result;
    }
}
