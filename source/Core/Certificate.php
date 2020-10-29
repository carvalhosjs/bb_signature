<?php namespace Source\Core;
/**
 * Class Certificate A1 - BB_Signature by Carlos Mateus Carvalho
 * @package Source\Core
 */
class Certificate {

    /**
     * Atributo para armazenar o arquivo PFX na pasta selecionada no disco.
     * @var string|null
     */
    private $path;

    /**
     * Atributo para pegar o nome do arquivo pfx.
     * @var string
     */
    private $owner;

    /**
     * Atributo para armazenar informações adicionais no PDF.
     * @var array
     */
    private $addInfo;

    /**
     * Atibuto onde é armazenado o certificado.
     * @var string
     */
    private $certificate;

    /**
     * Atributo para mostrar a informação do certificado.
     * @var array
     */
    private $infoCert;

    /**
     * Atributo para guardar a senha privada do certificado
     * @var string
     */
    private $password;

    /**
     * Atributo para registrar todos erros dessa classe;
     * @var array;
     */
    private $errors;

    /**
     * Certificate constructor.
     * @param null $path
     */
    public function __construct($path=null)
    {
        $this->path = (empty($path) ? PATH_MAIN : $path);
    }

    /**
     * Método responsável por "setar" o arquivo na pasta selecionada
     * @param string $owner - Nome do arquivo a quem pertence o certificado.
     * @return $this|void
     * @throws \Exception
     */
    public function set(string $owner, string $password): Certificate
    {
        $this->owner = $owner;
        $file = $this->owner . '.pfx';
        $this->password = $password;
        if(!file_exists($this->path . $this->owner . '.pfx')){
            $this->errors = (object)['msg' => "O certificado não existe na pasta, por favor enviar novamente se o erro persistir contate o administrador", 'success' =>false];
            return $this;
        }

        $this->certificate = 'file://' . realpath($this->path . $file);
        return $this;
    }


    /**
     * Método responsável por adicionar informações extras a assinatura no PDF.
     * @param array $opt - Um array contendo a chave e valor das parametros.
     * @return $this
     */
    public function addInfo($opt=[])
    {
        $this->addInfo = $opt;
        return $this;
    }


    /**
     * Método responsável por pegar todos valores pertinentes ao certificado.
     * @return Certificate
     * @throws \Exception
     */
    public function open(): Certificate
    {

        if(!file_exists($this->path . $this->owner . '.pfx')){
            $this->errors = (object)['msg' => "O certificado não existe na pasta, por favor enviar novamente se o erro persistir contate o administrador", 'success' =>false];
            return $this;
        }

        $pfxContent = file_get_contents($this->certificate);
        if (!openssl_pkcs12_read($pfxContent, $x509certdata, $this->password)) {
            $this->errors = (object)['msg' => "O certificado não pode ser lido, por favor selecione outro.", 'success' =>false];
            return $this;
        }else{
            $cert['info'] = (object)openssl_x509_parse(openssl_x509_read($x509certdata['cert']));
            $cert['private'] = (object)['key' => $x509certdata['pkey']];
            //convert pfx to crt
            $encode = "";
            if(isset($x509certdata['extracerts'])){
                $encode = $x509certdata['pkey'] . $x509certdata['cert'] . implode('', $x509certdata['extracerts']);
            }else{
                $encode = $x509certdata['pkey'] . $x509certdata['cert'];
            }
            file_put_contents($this->path . "{$this->owner}.crt", $encode);
            $mycert  = "file://" . realpath($this->path . $this->owner . ".crt");
            $cert['filename'] =(object)['certificate_path' => $mycert, 'password' => $this->password];
            $cert['more'] = (object)['certificate_add_info' => $this->addInfo ?? []];
            $this->infoCert = (object)$cert;
            $this->errors = (object)['msg' => "Certificado OK", "success" => true];
            return $this;
        }

    }

    /**
     * Método responsável por retornar todos errros do certificado.
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Método responsável por retornar todas a informações do Certificado.
     * @return array
     */
    public function getInfoCert()
    {
        return $this->infoCert;
    }
}