#Assinatura em arquivo PDF com certificado A1 no padrão ICP Brasil.
Biblioteca criada para assinar documentos PDF através do PHP.

Nesse pacote terá 1 arquivos de teste.

* Index.php - Arquivo para exemplificar o uso dos métodos.

##### Certificate.php
Classe onde está localizado todo a rotina de certificado.

* __construct($path=null)
* set(string $owner, string $password): Certificate
* addInfo($opt=[])
* open(): Certificate
* getErrors()
* getInfoCert()

##### Signer.php
Classe onde está localizado todo a rotina de assinatura de PDF.

*__construct(Certificate $certificate, $opt=[])
* mergePDF(string $filename)
* sign()
* withStamp()
* show()
* download()
* save()
* savePDF($filename)


#### Config.php
Arquivo contém as configurações da pastas.

#### Teste de Verificador
https://verificador.iti.gov.br/
