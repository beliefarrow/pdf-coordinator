<?php
/**
 * PDFCoordinator
 *
 * Copyright (c) Shinya Kinoshita (http://www.shinyakinoshita.com)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Shinya Kinoshita (http://www.shinyakinoshita.com)
 * @link          http://www.shinyakinoshita.com PDFCordinator Project
 * @since         1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace SKinoshita\PDFCoordinator;

/**
 * Main class for PDFCoordinator.
 *
 * @author  Shinya Kinoshita <contact@shinyakinoshita.com>
 * @version 1.0.0
 */
class Coordinator
{
    private $templateDirectoryPath = '';

    private $metadataDirectoryPath = '';

    private $outputDirectoryPath   = '';

    private $pdf                   = null;

    /**
     * Constructor.
     *
     * @param string $templateDirectoryPath Base pdf file path
     * @param string $metadataDirectoryPath Metadata for cordinating pdf
     * @param string
     */
    public function __construct($templateDirectoryPath, $metadataDirectoryPath, $outputDirectoryPath)
    {
        $this->templateDirectoryPath = $templateDirectoryPath;
        $this->metadataDirectoryPath = $metadataDirectoryPath;
        $this->outputDirectoryPath   = $outputDirectoryPath;

        $this->pdf                   = new \FPDI();
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
    }

    /**
     * Cordinate PDF file.
     *
     * Based on metadata of PDF and values that you want to put on PDF,
     * it regenerates PDF file.
     *
     * @param  array  $metadataFileKey file key of metadata for PDF that you cordinate
     * @param  array  $values          values that you want to put on PDF
     */
    public function coordinate($metadataFileKey, $values)
    {
        // Get metadata for pdf from file.
        $metadataFile = file_get_contents($this->metadataDirectoryPath . '/' . $metadataFileKey . '.json');
        $metadata     = json_decode($metadataFile, true);

        // Get a base pdf from metadata.
        $fontSettings      = $metadata['font-settings'];
        $pageCount         = $metadata['page-count'];
        $coordinatingPages = $metadata['coordinating-pages'];

        for ($pageIndex = 1; $pageIndex <= $pageCount; $pageIndex++) {
            $pageInfo           = $coordinatingPages['page' . $pageIndex];
            $originalForm       = $this->templateDirectoryPath . '/' . $pageInfo['original-form'];
            $targetPage         = $pageInfo['target-page'];
            $coordinatingItems  = $pageInfo['coordinating-items'];
            $coordinatingValues = $values['coordinating-pages'];

            $this->pdf->AddPage();

            $this->pdf->setSourceFile($originalForm);
            $templateIndex = $this->pdf->importPage($targetPage);
            $this->pdf->useTemplate($templateIndex, null, null, null, null, true);

            $pageValues = $coordinatingValues['page'. $pageIndex];

            foreach ($coordinatingItems as $key => $attributes) {
                $fontType = isset($attributes['font-type']) ? $attributes['font-type'] : $fontSettings['font-type'];
                $this->pdf->SetFont($fontType);

                $fontSize = isset($attributes['font-size']) ? $attributes['font-size'] : $fontSettings['font-size'];
                $this->pdf->SetFontSize($fontSize);

                $fontColorRed   = isset($attributes['font-color']['red']) ? $attributes['font-color']['red'] : $fontSettings['font-color']['red'];
                $fontColorGreen = isset($attributes['font-color']['green']) ? $attributes['font-color']['green'] : $fontSettings['font-color']['green'];
                $fontColorBlue  = isset($attributes['font-color']['blue']) ? $attributes['font-color']['blue'] : $fontSettings['font-color']['blue'];
                $this->pdf->SetTextColor($fontColorRed, $fontColorGreen, $fontColorBlue);

                $this->pdf->SetXY($attributes['position']['x'], $attributes['position']['y']);

                $this->pdf->Write(0, isset($pageValues[$key]) ? $pageValues[$key] : '');
            }
        }

        $filePath = $this->outputDirectoryPath . '/' . uniqid() . '.pdf';
        $this->pdf->Output($filePath, 'F');
    }
}
