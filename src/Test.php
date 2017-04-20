<?php
    require_once('../vendor/autoload.php');
    require_once('./Core/Coordinator.php');
    require_once('./Provider/MetadataProvider.php');

    use SKinoshita\PDFCoordinator\Core\Coordinator;
    use SKinoshita\PDFCoordinator\Provider\MetadataProvider;

    $templateDirectoryPath = '/Users/skinoshita/Desktop/work/templates';
    $metadataDirectoryPath = '/Users/skinoshita/Desktop/work/metadatas';
    $outputDirectoryPath   = '/Users/skinoshita/Desktop/work/output';

    $metadataProvider = new MetadataProvider($metadataDirectoryPath);
    $coordinator      = new Coordinator($templateDirectoryPath, $outputDirectoryPath);

    $values = [
        "page1" => [
            //"first-name" => "太郎",
            "last-name" => "山田"
        ],
        "page2" => [
            //"first-name-kana" => "タロウ",
            "last-name-kana" => "ヤマダ"
        ]
    ];

    //$metadata = $metadataProvider->provide('template01');
    $metadata = $metadataProvider->merge('template01', $values);
    $ret = $coordinator->coordinate($metadata);

    echo $ret;

    exit;
