<?php
namespace Nucleo;
class Usuarios
{
    private BaseDados $base_dados;
    public function __construct()
    {

    }
    private function solicitar_conexao(): \PDO|bool
    {
        $retorno = false;
        $this->base_dados = new BaseDados();
        $this->base_dados->contectar();
        if ($this->base_dados->checar_conexao()) {
            $retorno = true;
        }
        return $retorno;
    }
    public function logar(array $dados): bool
    {
        $retorno = false;
        if ($this->solicitar_conexao()) {
            if ($this->logar_usuario($dados)) {
                $this->criar_sessao_usuario($dados['usuario']);
                $retorno = true;
            }
        }
        var_dump($retorno);
        $this->base_dados->fechar_conexao();
        return $retorno;
    }

    protected function criar_sessao_usuario(string $pUsuario){
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
            $_SESSION['usuario'] = $pUsuario;
            var_dump($_SESSION);
        }
    }

    private function logar_usuario(array $dados): bool
    {
        return $this->base_dados->validar_login($dados);
    }

    public function cadastrar_usuario(array $dados): bool|string
    {
        $retorno = false;
        if ($this->solicitar_conexao()) {
            $retorno = $this->base_dados->validar_cadastro_usuario($dados);
        }

        return $retorno;
    }

    public function buscar_usuario(string $email):bool{
        $retorno = false;
        if($this->solicitar_conexao()){
            $retorno = $this->base_dados->verificar_usuario($email);
        }
        return $retorno;
    }
}