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
 * @since     0.1.0
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace SKinoshita\PDFCoordinator\Provider;

/**
 * Metadata Provider for PDF Coordinator.
 *
 * @author  Shinya Kinoshita <contact@shinyakinoshita.com>
 * @version 0.1.0
 */
class MetadataProvider
{
    /**
     * Directory Path for Metadata.
     *
     * @var string The path for metadata
     */
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
     * @param  array  $values Values you want to combine into metadata
     * @return array  Metadata for PDF
     */
    public function provide($key, $values = [])
    {
        // Get metadata for pdf from file.
        $target   = file_get_contents($this->metadataDirectoryPath . '/' . $key . '.json');
        $metadata = json_decode($target, true);

        // Combine values if variable named '$values' is not empty.
        if (!empty($values)) {
            $metadata = $this->combine($metadata, $values);
        }

        return $metadata;
    }

    /**
     * Combine values into metadata.
     *
     * @param  string $metadata Metadata for PDF
     * @param  array  $values   Values you want to combine into metadata you indicate
     * @return array  Combined metadata
     */
    public function combine($metadata, $values)
    {
        $coordinatingPages = $metadata['coordinating-pages'];

        foreach ($coordinatingPages as $pageKey => $pageAttributes) {
            $coordinatingItems = $pageAttributes['coordinating-items'];

            foreach ($coordinatingItems as $coordinatingKey => $coordinatingAttributes) {
                if (isset($values[$pageKey][$coordinatingKey])) {
                    $metadataCoordinatingItemsPerPage = &$metadata['coordinating-pages'][$pageKey]['coordinating-items'];
                    $metadataCoordinatingItemsPerPage[$coordinatingKey]['value'] = $values[$pageKey][$coordinatingKey];
                }
            }
        }

        return $metadata;
    }

    /**
     * Copy the attribute of the specified page to the target metadata.
     *
     * @param  array   $metadata Metadata for PDF
     * @param  integer $page     Page number you want to copy
     * @return array   Metadata that has completely copied
     */
    public function copyPageAttribute($metadata, $page = 1)
    {
        $newMetadata       = $metadata;
        $coordinatingPages = $metadata['coordinating-pages'];

        $newMetadata['page' . $page + 1] = $coordinatingPages['page' . $page];

        return $newMetadata;
    }
}
