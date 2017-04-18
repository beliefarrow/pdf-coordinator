<?php
    require_once('../vendor/autoload.php');
    require_once('./Coordinator.php');

    use SKinoshita\PDFCoordinator\Coordinator;

    $templateDirectoryPath = '/Users/skinoshita/Desktop/work/templates';
    $metadataDirectoryPath = '/Users/skinoshita/Desktop/work/metadatas';
    $outputDirectoryPath   = '/Users/skinoshita/Desktop/work/output';

    $coordinator = new Coordinator($templateDirectoryPath, $metadataDirectoryPath, $outputDirectoryPath);

    $formValues = [
        "coordinating-pages" => [
            "page1" => [
                "first-name" => "太郎",
                "last-name" => "山田"
            ],
            "page2" => [
                "first-name-kana" => "タロウ",
                "last-name-kana" => "ヤマダ"
            ]
        ]
    ];

    $coordinator->coordinate('template01', $formValues);
    exit;
