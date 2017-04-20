<?php
/**
 * PDF Coordinator
 *
 * Copyright (c) Shinya Kinoshita (http://www.shinyakinoshita.com)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Shinya Kinoshita (http://www.shinyakinoshita.com)
 * @link      http://www.shinyakinoshita.com PDFCordinator Project
 * @since     1.0.0
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace SKinoshita\PDFCoordinator\Provider;

/**
 * Metadata Provider for PDF Coordinator.
 *
 * @author  Shinya Kinoshita <contact@shinyakinoshita.com>
 * @version 1.0.0
 */
class MetadataProvider
{
    private $metadataDirectoryPath = '';

    /**
     * Constructor.
     *
     * @param string $metadataDirectoryPath Metadata for cordinating pdf
     */
    public function __construct($metadataDirectoryPath)
    {
        $this->metadataDirectoryPath = $metadataDirectoryPath;
    }

    /**
     * Get metadata.
     *
     * @param  string $key  File key of metadata for PDF that you cordinate
     * @return array  Metadata for PDF
     */
    public function provide($key)
    {
        // Get metadata for pdf from file.
        $target   = file_get_contents($this->metadataDirectoryPath . '/' . $key . '.json');
        $metadata = json_decode($target, true);

        return $metadata;
    }

    /**
     * Merge metadata from file with value you indicate.
     *
     * @param  string $key    File key of metadata for PDF that you cordinate
     * @param  array  $values Values you want to merge with metadata
     * @return array  merged metadata
     */
    public function merge($key, $values)
    {
        $metadata          = $this->provide($key);
        $coordinatingPages = $metadata['coordinating-pages'];

        foreach ($coordinatingPages as $pageKey => $pageAttributes) {
            $coordinatingItems  = $pageAttributes['coordinating-items'];

            foreach ($coordinatingItems as $coordinatingKey => $coordinatingAttributes) {
                if (isset($values[$pageKey][$coordinatingKey])) {
                    $metadataCoordinatingItemsPerPage = &$metadata['coordinating-pages'][$pageKey]['coordinating-items'];
                    $metadataCoordinatingItemsPerPage[$coordinatingKey]['value'] = $values[$pageKey][$coordinatingKey];
                }
            }
        }

        return $metadata;
    }
}
