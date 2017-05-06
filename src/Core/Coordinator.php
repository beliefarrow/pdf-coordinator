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
namespace SKinoshita\PDFCoordinator\Core;

/**
 * Main class for PDF Coordinator.
 *
 * @author  Shinya Kinoshita <contact@shinyakinoshita.com>
 * @version 1.0.0
 */
class Coordinator
{
    /**
     * Destination type for PDF Output (Browser Preview).
     *
     * @var string Destination type for PDF Output (Browser Preview)
     */
    const DESTINATION_TYPE_PREVIEW  = 'I';

    /**
     * Destination type for PDF Output (Browser Download).
     *
     * @var string Destination type for PDF Output (Browser Download)
     */
    const DESTINATION_TYPE_DOWNLOAD = 'D';

    /**
     * Destination type for PDF Output (File).
     *
     * @var string Destination type for PDF Output (File)
     */
    const DESTINATION_TYPE_FILE     = 'F';

    /**
     * Destination type for PDF Output (String).
     *
     * @var string Destination type for PDF Output (String)
     */
    const DESTINATION_TYPE_STRING   = 'S';

    /**
     * The path for base pdf.
     *
     * @var string The path for base pdf
     */
    private $templateDirectoryPath = '';

    /**
     * The path for pdf you want to save.
     *
     * @var string The path for pdf you want to save
     */
    private $outputDirectoryPath   = '';

    /**
     * FPDI Object.
     *
     * @var \FPDI FPDI Object
     */
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
     * @param  array         $metadata        metadata for PDF
     * @param  string        $destinationType Destination type for PDF Output
     * @return string | void String of pdf content when you chose $destinationType for DESTINATION_TYPE_STRING
     *                       void when you chose $destinationType for except DESTINATION_TYPE_STRING
     */
    public function coordinate($metadata, $destinationType = self::DESTINATION_TYPE_STRING)
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

                $positionX = $coordinatingAttributes['position']['x'];
                $positionY = $coordinatingAttributes['position']['y'];
                $value     = $coordinatingAttributes['value'];

                if (isset($coordinatingAttributes['width'])) {
                   $this->pdf->writeHTMLCell($coordinatingAttributes['width'], 1, $positionX, $positionY, $value);
                } else {
                   $this->pdf->SetXY($positionX, $positionY);
                   $this->pdf->Write(0, $value);
                }
            }
        }

        $filePath = $this->outputDirectoryPath . '/' . uniqid() . '.pdf';
        return $this->pdf->Output($filePath, $destinationType);
    }
}
