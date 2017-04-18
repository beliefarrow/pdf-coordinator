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
namespace SKinoshita\PDFCoordinator\Core;

/**
 * Main class for PDFCoordinator.
 *
 * @author  Shinya Kinoshita <contact@shinyakinoshita.com>
 * @version 1.0.0
 */
class Coordinator
{
    private $templateDirectoryPath = '';

    private $outputDirectoryPath   = '';

    private $pdf = null;

    /**
     * Constructor.
     *
     * @param string $templateDirectoryPath Base pdf file path
     * @param string $outputDirectoryPath   Output directory path for pdf
     */
    public function __construct($templateDirectoryPath, $outputDirectoryPath)
    {
        $this->templateDirectoryPath = $templateDirectoryPath;
        $this->outputDirectoryPath   = $outputDirectoryPath;

        $this->pdf = new \FPDI();
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
    }

    /**
     * Cordinate PDF file.
     *
     * Based on metadata of PDF, it regenerates PDF file.
     *
     * @param  array $metadata metadata for PDF
     */
    public function coordinate($metadata)
    {
        // Get a base pdf from metadata.
        $fontSettings      = $metadata['font-settings'];
        $coordinatingPages = $metadata['coordinating-pages'];

        foreach ($coordinatingPages as $pageKey => $pageAttributes) {
            $originalForm       = $this->templateDirectoryPath . '/' . $pageAttributes['original-form'];
            $targetPage         = $pageAttributes['target-page'];
            $coordinatingItems  = $pageAttributes['coordinating-items'];

            $this->pdf->AddPage();

            $this->pdf->setSourceFile($originalForm);
            $templateIndex = $this->pdf->importPage($targetPage);
            $this->pdf->useTemplate($templateIndex, null, null, null, null, true);

            foreach ($coordinatingItems as $coordinatingKey => $coordinatingAttributes) {
                $coordinatingFontSettings = isset($coordinatingAttributes['font-settings']) ?
                                                $coordinatingAttributes['font-settings'] : [];

                $fontType = isset($coordinatingFontSettings['font-type']) ?
                                $coordinatingFontSettings['font-type'] : $fontSettings['font-type'];
                $this->pdf->SetFont($fontType);

                $fontSize = isset($coordinatingFontSettings['font-size']) ?
                                $coordinatingFontSettings['font-size'] : $fontSettings['font-size'];
                $this->pdf->SetFontSize($fontSize);

                $fontColorRed   = isset($coordinatingFontSettings['font-color']['red']) ?
                                      $coordinatingFontSettings['font-color']['red'] : $fontSettings['font-color']['red'];
                $fontColorGreen = isset($coordinatingFontSettings['font-color']['green']) ?
                                      $coordinatingFontSettings['font-color']['green'] : $fontSettings['font-color']['green'];
                $fontColorBlue  = isset($coordinatingFontSettings['font-color']['blue']) ?
                                      $coordinatingFontSettings['font-color']['blue'] : $fontSettings['font-color']['blue'];
                $this->pdf->SetTextColor($fontColorRed, $fontColorGreen, $fontColorBlue);

                $this->pdf->SetXY($coordinatingAttributes['position']['x'], $coordinatingAttributes['position']['y']);

                $this->pdf->Write(0, $coordinatingAttributes['value']);
            }
        }

        $filePath = $this->outputDirectoryPath . '/' . uniqid() . '.pdf';
        $this->pdf->Output($filePath, 'F');
    }
}
