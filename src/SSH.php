<?php

namespace BrunoSurdi\SecureShell;

class SSH{

    /**
     * Inst�ncia do recurso de conex�o
     * @var resource
     */
    private $connection;

    /**
     * M�todo respons�vel por iniciar a conex�o SSH
     * @param  string  $host
     * @param  integer $port
     * @return boolean
     */
    public function connect($host,$port){
        //NOVA CONEX?O
        $this->connection = ssh2_connect($host,$port);

        //RETORNA O SUCESSO
        return $this->connection ? true : false;
    }

    /**
     * M?todo respons?vel por autenticar a conex?o utilizando usu?rio e senha
     * @param  string $user
     * @param  string $pass
     * @return boolean
     */
    public function authPassword($user,$pass){
        return $this->connection ? ssh2_auth_password($this->connection,$user,$pass) : false;
    }

    /**
     * M?todo respons?vel por autenticar a conex?o utilizando par de chaves SSH
     * @param  string $user
     * @param  string $publicKey
     * @param  string $privateKey
     * @param  string $passphrase
     * @return boolean
     */
    public function authPublicKeyFile($user,$publicKey,$privateKey,$passphrase = null){
        return $this->connection ? ssh2_auth_pubkey_file($this->connection,$user,$publicKey,$privateKey,$passphrase) : false;
    }

    /**
     * M?todo respons?vel por remover a conex?o atual
     * @return boolean
     */
    public function disconnect(){
        //DESCONECTA
        if($this->connection) ssh2_disconnect($this->connection);

        //LIMPA A CLASSE
        $this->connection = null;

        //SUCESSO
        return true;
    }

    /**
     * M?todo respons?vel por obter uma sa?da de uma stream
     * @param  resource $stream
     * @param  integer  $id
     * @return string
     */
    private function getOutput($stream,$id){
        //STREAM DA SA?DA
        $streamOut = ssh2_fetch_stream($stream,$id);

        //CONTE?DO DA SA?DA
        return stream_get_contents($streamOut);
    }

    /**
     * M?todo respons?vel por executar comandos SSH
     * @param  string $command
     * @param  string $stdErr
     * @return string
     */
    public function exec($command,&$stdErr = null){
        //VERIFICA A CONEX?O
        if(!$this->connection) return null;

        //EXECUTA O COMANDO SSH
        if(!$stream = ssh2_exec($this->connection,$command)){
            return null;
        }

        //BLOQUEIA A STREAM
        stream_set_blocking($stream,true);

        //SA?DA STDIO
        $stdIo = $this->getOutput($stream,SSH2_STREAM_STDIO);

        //SA?DA STDERR
        $stdErr = $this->getOutput($stream,SSH2_STREAM_STDERR);

        //DESBLOQUEIA A STREAM
        stream_set_blocking($stream,false);

        //RETORNA O STDIO
        return $stdIo;
    }
}