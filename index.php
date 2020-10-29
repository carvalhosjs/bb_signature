<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    require_once __DIR__ . '/vendor/autoload.php';

    use Source\Core\Certificate;
    use Source\Core\Signer;

    $cert = (new Certificate())->set("nome-do-certificado-pfx", "senha-do-certificado")->open();
    $signer = (new Signer($cert))->mergePDF('test/arquivo.pdf')->sign()->withStamp()->show();

