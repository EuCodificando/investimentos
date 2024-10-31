<?php

namespace Nucleo;

use PDO;
use PDOException;
use PDOStatement;

include_once __DIR__ . "/Constants.php";

class BaseDados
{

    private string $string_conexao;
    public PDO $conexao;

    public function __construct()
    {
    }


    private function obter_conexao(): PDO|bool
    {
        $retorno = false;
        $this->string_conexao = MYSQL_DSN . ":host=" . MYSQL_HOST . ";dbname=" . MYSQL_BANCO . ";charset=utf8";
        try {
            $this->conexao = new PDO(
                $this->string_conexao,
                MYSQL_USUARIO,
                MYSQL_SENHA
            );
            $this->conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $retorno = $this->conexao;
            // var_dump($retorno);
        } catch (PDOException $e) {
            $retorno = $e->getMessage();
            // var_dump($retorno);
        }
        return $retorno;
    }

    public function contectar(): PDO|bool
    {
        $solicitacao = $this->obter_conexao();
        return $solicitacao;
    }
    public function fechar_conexao()
    {
        if (isset($this->conexao)) {
            unset($this->conexao);
        }
    }

    public function checar_conexao(): bool
    {
        $retorno = false;
        if (isset($this->conexao)) {
            $retorno = true;
        }
        return $retorno;
    }

    public function validar_login(array $dados): bool
    {
        var_dump($dados);
        $retorno = false;
        if (!$this->checar_conexao()) {
            $this->contectar();
        }
        if (isset($this->conexao)) {
            try {
                $sql = "SELECT senha FROM usuarios 
                WHERE email = :email";
                $usuario = "{$dados['usuario']}";
                $stmt = $this->conexao->prepare($sql);
                $stmt->bindParam(':email', $usuario);
                $stmt->execute();
                $retorno = $stmt->fetch(PDO::FETCH_ASSOC);
                $senha_usuario_salva = !$retorno
                    ? false : $retorno['senha'];
                $senha = $dados['senha'];
                $validar_senha = password_verify($senha, $senha_usuario_salva);
                if ($validar_senha) {
                    $retorno = true;
                }
                var_dump($sql);
            } catch (PDOException $e) {
                $retorno = false;
            }
        }
        return $retorno;
    }

    public function validar_cadastro_usuario(array $dados): bool|string
    {
        $retorno = false;
        $retorno_sql = false;
        if (!$this->checar_conexao()) {
            $this->contectar();
        }
        if (isset($this->conexao)) {
            $verifica_existencia_usuario = $this->verificar_usuario($dados['usuario']);
            if (!$verifica_existencia_usuario) {
                $gerar_hash_senha = password_hash($dados['senha'], PASSWORD_DEFAULT);
                try {
                    $sql = "INSERT INTO usuarios (email,senha) VALUES (:email,:senha)";
                    $email = "{$dados['usuario']}";
                    $senha = "$gerar_hash_senha";
                    $stmt = $this->conexao->prepare($sql);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':senha', $senha);
                    $stmt->execute();
                    $retorno_sql = $stmt->fetch(PDO::FETCH_ASSOC);
                    $retorno = "Cadastro realizado para o email $email.";
                } catch (PDOException $e) {
                    $retorno = "Algo deu errado; " . $e->getMessage();
                }
            } else {
                $retorno = "Email {$dados['usuario']} já está cadastrado.";
            }
        }
        var_dump($retorno);
        return $retorno;
    }

    public function verificar_usuario(string $pEmail): bool|string
    {
        $retorno = false;
        if (!$this->checar_conexao()) {
            $this->contectar();
        }
        if (isset($this->conexao)) {
            try {
                $sql = "SELECT email FROM usuarios
        WHERE email = :email";
                $email = "$pEmail";
                $stmt = $this->conexao->prepare($sql);
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $retorno_sql = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $retorno = "Algo deu errado; " . $e->getMessage();
            }
        }
        if ($retorno_sql != false) {
            $retorno = true;
        }
        $this->fechar_conexao();
        return $retorno;
    }

    public function obter_total_investido(string $ativo = null): float|array|null
    {
        if (!isset($this->conexao)) {
            $this->contectar();
        }
        if (is_null($ativo)) {
            $sql = "SELECT sum(total_investido) as total_investimento FROM investimentos;";
            $stmt = $this->conexao->prepare($sql);
            $stmt->execute();
            $retorno_total_investimento = $stmt->fetch(PDO::FETCH_ASSOC)['total_investimento'];
            // var_dump($retorno_total_investimento);exit;
            $retorno = $retorno_total_investimento;
        } else {
            try {
                if (is_null($ativo)) {
                    $sql_id_ativo = "SELECT id FROM ativos;";
                } else {
                    $sql_id_ativo = "SELECT id FROM ativos
            WHERE ticker = :ativo;";
                }
                var_dump($sql_id_ativo);
                $stmt_ativo = $this->conexao->prepare($sql_id_ativo);
                if (!is_null($ativo)) {
                    $stmt_ativo->bindParam(":ativo", $ativo);
                }

                $stmt_ativo->execute();
                $id_ativo = $stmt_ativo->fetch(PDO::FETCH_ASSOC);
                var_dump($id_ativo);
                exit;
                if ($id_ativo !== false && !is_null($id_ativo)) {
                    $sql_total_ativo = "SELECT sum(valor_investido) total_investimento FROM investimentos
                WHERE ativo = :ativo;";
                    $stmt = $this->conexao->prepare($sql_total_ativo);
                    $stmt->bindParam(":ativo", $id_ativo);
                    $stmt->execute();
                    $retorno_total_investimento = $stmt->fetch(PDO::FETCH_ASSOC)['total_investimento'];
                    var_dump($retorno_total_investimento);
                    if (!is_null($retorno_total_investimento)) {
                        $retorno = $retorno_total_investimento;
                    } else {
                        $retorno['erro'] = "O ativo $ativo não foi encontrado.";
                    }
                }
            } catch (PDOException $e) {
                $retorno['erro'] = "Algo deu errado; " . $e->getMessage();
            }
        }
        // var_dump($retorno);
        $this->fechar_conexao();
        return $retorno;
    }

    public function realizar_limpeza_tabela_investimentos(): bool|array
    {
        $retorno = false;
        if (!$this->checar_conexao()) {
            $this->contectar();
        }
        if (isset($this->conexao)) {
            try {
                $sql_limpar_tabela = "DELETE FROM investimentos";
                $stmt_limpar_tabela = $this->conexao->prepare($sql_limpar_tabela);
                $stmt_limpar_tabela->execute();
                $sql_reset_auto_increment = "ALTER TABLE investimentos AUTO_INCREMENT = 1;";
                $stmt_reset_auto_increment = $this->conexao->prepare($sql_reset_auto_increment);
                $stmt_reset_auto_increment->execute();
                $retorno = true;
            } catch (PDOException $e) {
                $retorno = [];
                $retorno['erro'] = "Algo deu errado; " . $e->getMessage();
            }
        }
        return $retorno;
    }

    public function obter_ativos(string $ativo = null): array
    {
        $retorno = [];
        if (!$this->checar_conexao()) {
            $this->contectar();
        }
        if (is_null($ativo)) {
            $sql_ativos = "SELECT * FROM ativos;";
            $stmt_ativos = $this->conexao->prepare($sql_ativos);
        } else if (!is_null($ativo)) {
            $sql_ativos = "SELECT * FROM ativos
            WHERE ticker = :ativo;";
            $stmt_ativos = $this->conexao->prepare($sql_ativos);
            $stmt_ativos->bindParam(":ativo", $ativo);
        }
        if (isset($this->conexao)) {
            try {
                $stmt_ativos->execute();
                $retorno_ativos = $stmt_ativos->fetchAll(PDO::FETCH_ASSOC);
                $retorno = $retorno_ativos;
            } catch (PDOException $e) {
                $retorno['erro'] = "Algo deu errado; " . $e->getMessage();
            }
        }
        // var_dump($retorno);exit;
        return $retorno;
    }

    public function salvar_dados_atualizados_investimentos_banco(array $dados): bool|array
    {
        $retorno = false;
        if (!$this->checar_conexao()) {
            $this->contectar();
        }
        $valores = '';
        if (isset($this->conexao)) {
            for ($i = 0; $i < count($dados); $i++) {
                if ($i == array_key_last($dados)) {
                    $valores .= "('" . implode("','", $dados[$i]) . "')";
                } else {
                    $valores .= "('" . implode("','", $dados[$i]) . "'),";
                }
            }
            // var_dump($valores);exit;
            try {
                $sql_atualizar_investimentos = "INSERT INTO investimentos 
                (id,ativo,tipo_aquisicao,data_operacao,quantidade,valor_cotacao) 
                VALUES $valores;";
                $stmt_atualizar_investimentos = $this->conexao->prepare($sql_atualizar_investimentos);
                $stmt_atualizar_investimentos->execute();
                $stmt_atualizar_investimentos->fetchAll(PDO::FETCH_ASSOC);
                $retorno = true;
            } catch (PDOException $e) {
                $retorno = [];
                $retorno['erro'] = "Algo deu errado; " . $e->getMessage();
            }
        }
        // var_dump($retorno);exit;
        return $retorno;
    }

    public function obter_investimentos(): array
    {
        $retorno = [];
        if (!$this->checar_conexao()) {
            $this->contectar();
        }
        if (isset($this->conexao)) {
            try {
                $sql_investimentos = "SELECT * FROM investimentos;";
                $stmt_investimentos = $this->conexao->prepare($sql_investimentos);
                $stmt_investimentos->execute();
                $retorno_investimentos = $stmt_investimentos->fetchAll(PDO::FETCH_ASSOC);
                // var_dump($retorno_investimentos);exit;
                if (!is_array($retorno_investimentos)) {
                    $retorno['erro'] = "Não foram encontrados investimentos. Atualize a lista dos seus investimentos.";
                } else {
                    $retorno = $retorno_investimentos;
                }
            } catch (PDOException $e) {
                $retorno['erro'] = "Algo deu errado; " . $e->getMessage();
            }
        }
        // var_dump($retorno);exit;
        return $retorno;
    }

    public function obter_posicoes(string $ativo = null): array
    {
        $retorno = [];
        if (!$this->checar_conexao()) {
            $this->contectar();
        }
        if (isset($this->conexao)) {
            try {
                if (is_null($ativo)) {
                    $sql_posicoes =
                        "SELECT 
                    investimentos.id,
                    ativos.ticker AS nome_ativo,
                    setores.descricao AS setor,
                    tipo_aquisicao,
                    data_operacao,
                    quantidade,
                    valor_cotacao,
                    total_investido
                FROM
                    investimentos
                        JOIN
                    ativos ON ativos.id = ativo
                        JOIN
                    setores ON setores.id = ativos.setor_id
                    ORDER BY data_operacao ASC;";
                } else {
                    $sql_posicoes =
                        "SELECT 
                    investimentos.id,
                    ativos.ticker AS nome_ativo,
                    setores.descricao AS setor,
                    tipo_aquisicao,
                    data_operacao,
                    quantidade,
                    valor_cotacao,
                    total_investido
                    FROM
                        investimentos
                    JOIN
                        ativos ON ativos.id = ativo
                    JOIN
                        setores ON setores.id = ativos.setor_id
                    WHERE 
                        ativos.ticker = :ativo
                    ORDER BY 
                        data_operacao ASC;";
                }
                // var_dump($sql_posicoes);
                $stmt_posicoes = $this->conexao->prepare($sql_posicoes);
                if (!is_null($ativo)) {
                    $stmt_posicoes->bindParam("ativo", $ativo);
                }
                $stmt_posicoes->execute();
                $retorno = $stmt_posicoes->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $retorno['erro'] = "Algo deu errado; " . $e->getMessage();
            }
        }
        // var_dump($retorno);exit;
        return $retorno;
    }

    public function bkp_obter_lista_posicoes(): array
    {
        $retorno = [];
        if (!$this->checar_conexao()) {
            $this->contectar();
        }
        $ativos = $this->obter_ativos();
        // var_dump($ativos);exit;
        if (isset($this->conexao)) {
            try {
                $retono_lista_investimentos = $this->obter_investimentos();
                // var_dump($retono_lista_investimentos);//exit;
            } catch (PDOException $e) {
                $retorno['erro'] = "Algo deu errado; " . $e->getMessage();
            }
            $dados_ativo = [];
            for ($i = 0; $i < count($ativos); $i++) {
                if (!in_array($ativos[$i]['ticker'], array_keys($dados_ativo))) {
                    $dados_ativo[$ativos[$i]['ticker']]['numero_operacoes']['C'] = 0;
                    $dados_ativo[$ativos[$i]['ticker']]['numero_operacoes']['V'] = 0;
                    $dados_ativo[$ativos[$i]['ticker']]['quantidade_total'] = 0;
                    $dados_ativo[$ativos[$i]['ticker']]['quantidade_compra'] = 0;
                    $dados_ativo[$ativos[$i]['ticker']]['quantidade_venda'] = 0;
                    $dados_ativo[$ativos[$i]['ticker']]['data_investimento'] = [];
                    $dados_ativo[$ativos[$i]['ticker']]['valor_cotacao'] = [];
                    $dados_ativo[$ativos[$i]['ticker']]['total_investido'] = [];
                    $dados_ativo[$ativos[$i]['ticker']]['preco_medio'] = 0;
                    $dados_ativo[$ativos[$i]['ticker']]['lucro_posicao'] = 0;
                    $dados_ativo[$ativos[$i]['ticker']]['prejuizo_posicao'] = 0;
                    $dados_ativo[$ativos[$i]['ticker']]['saldo_posicao'] = 0;
                    $dados_ativo[$ativos[$i]['ticker']]['lucro_operacao'] = 0;
                    $dados_ativo[$ativos[$i]['ticker']]['prejuizo_operacao'] = 0;
                    $dados_ativo[$ativos[$i]['ticker']]['saldo_operacao'] = 0;
                }
                $temp_operacoes = [];
                $temp_operacoes['valor_cotacao_anterior'] = 0.0;
                $temp_operacoes['total_investido_anterior'] = 0.0;
                $temp_operacoes['tipo_operacao_anterior'] = null;
                for ($a = 0; $a < count($retono_lista_investimentos); $a++) {
                    if ($retono_lista_investimentos[$a]['ativo'] == $ativos[$i]['id']) {
                        if ($retono_lista_investimentos[$a]['tipo_aquisicao'] == 'C') {
                            $dados_ativo[$ativos[$i]['ticker']]['numero_operacoes']['C'] += 1;
                            if ($retono_lista_investimentos[$a]['quantidade'] > $dados_ativo[$ativos[$i]['ticker']]['quantidade_total']) {
                                // var_dump("característica de mudança de posição");exit;
                                $dados_ativo[$ativos[$i]['ticker']]['total_investido']['C'][] = (float) $retono_lista_investimentos[$a]['total_investido'] - $temp_operacoes['valor_cotacao_anterior'];
                            } else {
                                $dados_ativo[$ativos[$i]['ticker']]['total_investido']['C'][] = (float) $retono_lista_investimentos[$a]['total_investido'];
                            }
                            $dados_ativo[$ativos[$i]['ticker']]['quantidade_total'] += (int) $retono_lista_investimentos[$a]['quantidade'];
                            $dados_ativo[$ativos[$i]['ticker']]['quantidade_compra'] += $retono_lista_investimentos[$a]['quantidade'];
                            $dados_ativo[$ativos[$i]['ticker']]['data_investimento']['C'][] = $retono_lista_investimentos[$a]['data_operacao'];
                            $dados_ativo[$ativos[$i]['ticker']]['valor_cotacao']['C'][] = (float) $retono_lista_investimentos[$a]['valor_cotacao'];
                            // $dados_ativo[$ativos[$i]['ticker']]['total_investido']['C'][] = (float) $retono_lista_investimentos[$a]['total_investido'];

                        } else {
                            $dados_ativo[$ativos[$i]['ticker']]['numero_operacoes']['V'] += 1;
                            if ($retono_lista_investimentos[$a]['quantidade'] > $dados_ativo[$ativos[$i]['ticker']]['quantidade_total']) {
                                $dados_ativo[$ativos[$i]['ticker']]['total_investido']['V'][] = (float) $retono_lista_investimentos[$a]['total_investido'] - $temp_operacoes['valor_cotacao_anterior'];
                            } else {
                                $dados_ativo[$ativos[$i]['ticker']]['total_investido']['V'][] = (float) $retono_lista_investimentos[$a]['total_investido'];
                            }
                            $dados_ativo[$ativos[$i]['ticker']]['quantidade_total'] -= (int) $retono_lista_investimentos[$a]['quantidade'];
                            $dados_ativo[$ativos[$i]['ticker']]['quantidade_venda'] -= $retono_lista_investimentos[$a]['quantidade'];
                            $dados_ativo[$ativos[$i]['ticker']]['data_investimento']['V'][] = $retono_lista_investimentos[$a]['data_operacao'];
                            $dados_ativo[$ativos[$i]['ticker']]['valor_cotacao']['V'][] = (float) $retono_lista_investimentos[$a]['valor_cotacao'];
                        }
                        if ($retono_lista_investimentos[$a]['quantidade'] > 0) {
                            if ($temp_operacoes['tipo_operacao_anterior'] == null) {
                                var_dump("Inicianco dados");
                                $dados_ativo[$ativos[$i]['ticker']]['preco_medio'] =
                                    ((float) $retono_lista_investimentos[$a]['total_investido']
                                        / abs($dados_ativo[$ativos[$i]['ticker']]['quantidade_total']));
                                $temp_operacoes['valor_cotacao_anterior'] = (float) $retono_lista_investimentos[$a]['valor_cotacao'];
                                $temp_operacoes['total_investido_anterior'] += (float) $retono_lista_investimentos[$a]['total_investido'];
                                $temp_operacoes['tipo_operacao_anterior'] = $retono_lista_investimentos[$a]['tipo_aquisicao'];
                                var_dump(
                                    $ativos[$i]['ticker'],
                                    $dados_ativo[$ativos[$i]['ticker']]['quantidade_total'],
                                    (int) $retono_lista_investimentos[$a]['quantidade']
                                );
                            } else if ($temp_operacoes['tipo_operacao_anterior'] != null) {
                                var_dump("Dados já foram iniciados");
                                var_dump($dados_ativo[$ativos[$i]['ticker']]['quantidade_total'], $retono_lista_investimentos[$a]['quantidade']);//exit;
                                if ($retono_lista_investimentos[$a]['tipo_aquisicao'] == $temp_operacoes['tipo_operacao_anterior']) {
                                    var_dump("Mesmo tipo de operação");
                                    exit;
                                } else if (
                                    ($retono_lista_investimentos[$a]['tipo_aquisicao'] != $temp_operacoes['tipo_operacao_anterior']
                                        && $retono_lista_investimentos[$a]['quantidade'] == $dados_ativo[$ativos[$i]['ticker']]['quantidade_total'])
                                    || $dados_ativo[$ativos[$i]['ticker']]['quantidade_total'] == 0
                                ) {
                                    var_dump("Encerramento de operação por quantidade de contratos iguais");
                                    //-------------------------------------------------------------------------
                                    // ENCERRANDO OPERAÇÃO E OBTENDO LUCRO OU PREJUÍZO
                                    //-------------------------------------------------------------------------
                                    if ($retono_lista_investimentos[$a]['tipo_aquisicao'] == 'C') {
                                        if ($dados_ativo[$ativos[$i]['ticker']]['preco_medio'] > (float) $retono_lista_investimentos[$a]['valor_cotacao']) {
                                            $temp_calculo_lucro_prejuizo =
                                                ($dados_ativo[$ativos[$i]['ticker']]['preco_medio'] - (float) $retono_lista_investimentos[$a]['valor_cotacao']);
                                            $dados_ativo[$ativos[$i]['ticker']]['lucro_operacao'] += $temp_calculo_lucro_prejuizo;
                                        } else {
                                            $temp_calculo_lucro_prejuizo =
                                                ($dados_ativo[$ativos[$i]['ticker']]['preco_medio'] - (float) $retono_lista_investimentos[$a]['valor_cotacao']);
                                            $dados_ativo[$ativos[$i]['ticker']]['prejuizo_operacao'] += $temp_calculo_lucro_prejuizo;
                                        }
                                    } else {
                                        if ($dados_ativo[$ativos[$i]['ticker']]['preco_medio'] < (float) $retono_lista_investimentos[$a]['valor_cotacao']) {
                                            $temp_calculo_lucro_prejuizo =
                                                ((float) $retono_lista_investimentos[$a]['valor_cotacao'] - $dados_ativo[$ativos[$i]['ticker']]['preco_medio']);
                                            $dados_ativo[$ativos[$i]['ticker']]['lucro_operacao'] += $temp_calculo_lucro_prejuizo;
                                        } else {
                                            $temp_calculo_lucro_prejuizo =
                                                ($dados_ativo[$ativos[$i]['ticker']]['preco_medio'] - (float) $retono_lista_investimentos[$a]['valor_cotacao']);
                                            $dados_ativo[$ativos[$i]['ticker']]['prejuizo_operacao'] += $temp_calculo_lucro_prejuizo;
                                        }
                                    }
                                    $dados_ativo[$ativos[$i]['ticker']]['preco_medio'] = 0.0;
                                    $dados_ativo[$ativos[$i]['ticker']]['saldo_operacao'] =
                                        $dados_ativo[$ativos[$i]['ticker']]['lucro_operacao'] + $dados_ativo[$ativos[$i]['ticker']]['prejuizo_operacao'];
                                    $temp_operacoes['valor_cotacao_anterior'] = 0.0;
                                    $temp_operacoes['total_investido_anterior'] = 0.0;
                                    $temp_operacoes['tipo_operacao_anterior'] = null;
                                    var_dump(
                                        "Realizado o encerramento da posição",
                                        $ativos[$i]['ticker'],
                                        $temp_calculo_lucro_prejuizo,
                                        $dados_ativo[$ativos[$i]['ticker']]
                                    );
                                } else if (
                                    $retono_lista_investimentos[$a]['tipo_aquisicao'] != $temp_operacoes['tipo_operacao_anterior']
                                    && $retono_lista_investimentos[$a]['quantidade'] <= $dados_ativo[$ativos[$i]['ticker']]['quantidade_total']
                                ) {
                                    var_dump("Manutenção de posição e realização de preço médio");
                                    if ($retono_lista_investimentos[$a]['tipo_aquisicao'] == 'V') {
                                        if ((float) $retono_lista_investimentos[$a]['valor_cotacao'] > abs($dados_ativo[$ativos[$i]['ticker']]['preco_medio'])) {
                                            $temp_calculo_lucro_prejuizo =
                                                (((float) $retono_lista_investimentos[$a]['valor_cotacao'] - $dados_ativo[$ativos[$i]['ticker']]['preco_medio'])
                                                    * $retono_lista_investimentos[$a]['quantidade']);
                                            $dados_ativo[$ativos[$i]['ticker']]['lucro_posicao'] += $temp_calculo_lucro_prejuizo;
                                        } else {
                                            $temp_calculo_lucro_prejuizo =
                                                (($dados_ativo[$ativos[$i]['ticker']]['preco_medio'] - (float) $retono_lista_investimentos[$a]['valor_cotacao'])
                                                    * $retono_lista_investimentos[$a]['quantidade']);
                                            $dados_ativo[$ativos[$i]['ticker']]['prejuizo_posicao'] += $temp_calculo_lucro_prejuizo;
                                        }
                                    } else {
                                        if ((float) $retono_lista_investimentos[$a]['valor_cotacao'] < abs($dados_ativo[$ativos[$i]['ticker']]['preco_medio'])) {
                                            $temp_calculo_lucro_prejuizo =
                                                (($dados_ativo[$ativos[$i]['ticker']]['preco_medio'] - (float) $retono_lista_investimentos[$a]['valor_cotacao'])
                                                    * $retono_lista_investimentos[$a]['quantidade']);
                                            $dados_ativo[$ativos[$i]['ticker']]['lucro_posicao'] += $temp_calculo_lucro_prejuizo;
                                        } else {
                                            $temp_calculo_lucro_prejuizo =
                                                (((float) $retono_lista_investimentos[$a]['valor_cotacao'] - $dados_ativo[$ativos[$i]['ticker']]['preco_medio'])
                                                    * $retono_lista_investimentos[$a]['quantidade']);
                                            $dados_ativo[$ativos[$i]['ticker']]['prejuizo_posicao'] += $temp_calculo_lucro_prejuizo;
                                        }
                                    }
                                    $dados_ativo[$ativos[$i]['ticker']]['preco_medio'] =
                                        (($temp_operacoes['total_investido_anterior'] - ((float) $retono_lista_investimentos[$a]['total_investido'] + abs($temp_calculo_lucro_prejuizo)))
                                            / $dados_ativo[$ativos[$i]['ticker']]['quantidade_total']);
                                    var_dump(
                                        $temp_operacoes,
                                        (float) $retono_lista_investimentos[$a]['valor_cotacao'],
                                        $dados_ativo[$ativos[$i]['ticker']]
                                    );//exit;
                                } else if (
                                    $dados_ativo[$ativos[$i]['ticker']]['quantidade_total'] != 0
                                    && $retono_lista_investimentos[$a]['tipo_aquisicao'] != $temp_operacoes['tipo_operacao_anterior']
                                    && $retono_lista_investimentos[$a]['quantidade'] > $dados_ativo[$ativos[$i]['ticker']]['quantidade_total']
                                ) {
                                    var_dump("Mudança de posição");//exit;
                                    //-----------------------------------------------------------------------
                                    //MUDANÇA DE POSIÇÃO / QNTD DE COMPRA/VENDA MAIOR QUE A QUANTIDADE ATUAL
                                    //-----------------------------------------------------------------------
                                    if ($retono_lista_investimentos[$a]['tipo_aquisicao'] == 'C') {
                                        if ((float) $retono_lista_investimentos[$a]['valor_cotacao'] < $dados_ativo[$ativos[$i]['ticker']]['preco_medio']) {
                                            $temp_calculo_lucro_prejuizo =
                                                ($dados_ativo[$ativos[$i]['ticker']]['preco_medio'] - (float) $retono_lista_investimentos[$a]['valor_cotacao'])
                                                * ($dados_ativo[$ativos[$i]['ticker']]['quantidade_total'] - $retono_lista_investimentos[$a]['quantidade']);
                                            $dados_ativo[$ativos[$i]['ticker']]['lucro_operacao'] += $temp_calculo_lucro_prejuizo;
                                        } else {
                                            $temp_calculo_lucro_prejuizo =
                                                ((float) $retono_lista_investimentos[$a]['valor_cotacao'] - $dados_ativo[$ativos[$i]['ticker']]['preco_medio'])
                                                * ($dados_ativo[$ativos[$i]['ticker']]['quantidade_total'] - $retono_lista_investimentos[$a]['quantidade']);
                                            $dados_ativo[$ativos[$i]['ticker']]['prejuizo_operacao'] += $temp_calculo_lucro_prejuizo;
                                        }
                                    } else {
                                        if ($dados_ativo[$ativos[$i]['ticker']]['preco_medio'] < (float) $retono_lista_investimentos[$a]['valor_cotacao']) {
                                            $temp_calculo_lucro_prejuizo =
                                                ((float) $retono_lista_investimentos[$a]['valor_cotacao'] - $dados_ativo[$ativos[$i]['ticker']]['preco_medio'])
                                                * ($dados_ativo[$ativos[$i]['ticker']]['quantidade_total'] - $retono_lista_investimentos[$a]['quantidade']);
                                            $dados_ativo[$ativos[$i]['ticker']]['lucro_operacao'] += $temp_calculo_lucro_prejuizo;
                                        } else {
                                            $temp_calculo_lucro_prejuizo =
                                                ($dados_ativo[$ativos[$i]['ticker']]['preco_medio'] - (float) $retono_lista_investimentos[$a]['valor_cotacao'])
                                                * ($dados_ativo[$ativos[$i]['ticker']]['quantidade_total'] - $retono_lista_investimentos[$a]['quantidade']);
                                            $dados_ativo[$ativos[$i]['ticker']]['prejuizo_operacao'] += $temp_calculo_lucro_prejuizo;
                                        }
                                    }
                                    if ($temp_calculo_lucro_prejuizo < 0) {
                                        $dados_ativo[$ativos[$i]['ticker']]['preco_medio'] += abs($temp_calculo_lucro_prejuizo);
                                    } else {
                                        $temp_indice = array_key_last($dados_ativo[$ativos[$i]['ticker']]['total_investido']['C']);
                                        var_dump(
                                            $dados_ativo[$ativos[$i]['ticker']]['total_investido']['C'][$temp_indice],
                                            $dados_ativo[$ativos[$i]['ticker']]['quantidade_total']
                                        );//exit;
                                        $dados_ativo[$ativos[$i]['ticker']]['preco_medio'] =
                                            ($dados_ativo[$ativos[$i]['ticker']]['total_investido']['C'][$temp_indice] / $dados_ativo[$ativos[$i]['ticker']]['quantidade_total']);
                                    }
                                    $temp_operacoes['valor_cotacao_anterior'] = (float) $retono_lista_investimentos[$a]['valor_cotacao'];
                                    $temp_operacoes['total_investido_anterior'] = (float) $retono_lista_investimentos[$a]['total_investido'];
                                    $temp_operacoes['tipo_operacao_anterior'] = $retono_lista_investimentos[$a]['tipo_aquisicao'];
                                    var_dump($ativos[$i]['ticker'], $temp_calculo_lucro_prejuizo, $dados_ativo[$ativos[$i]['ticker']]);//exit;

                                }
                                // $temp_operacoes['valor_cotacao_anterior'] = (float) $retono_lista_investimentos[$a]['valor_cotacao'];
                                // $temp_operacoes['tipo_operacao_anterior'] = $retono_lista_investimentos[$a]['tipo_aquisicao'];
                            }
                        }

                    }
                }
            }
            ksort($dados_ativo);//exit;
            $retorno = $dados_ativo;
            // var_dump($dados_ativo['ABCB4F']);exit;
        }
        return $retorno;
    }

    public function salvar_investimento(array $dados): bool|array
    {
        $retorno = false;
        if (!$this->checar_conexao()) {
            $this->contectar();
        }
        $valores = '';
        if (isset($this->conexao)) {
            try {
                $dados_investimento = [];
                $dados_ativo = [];
                $dados_ativo['setor'] = $dados['setor'];
                $dados_ativo['ticker'] = $dados['ticker'];
                $dados_ativo['tipo_investimento'] = $dados['tipo_investimento'];
                $dados_ativo['nacionalidade'] = $dados['nacionalidade'];

                foreach ($dados as $key => $value) {
                    if (!in_array($key, array_keys($dados_ativo))) {
                        $dados_investimento[$key] = $value;
                    }
                }
                $temp_id = $this->obter_id_ativo($dados_ativo);
                $salvar_ativo = false;
                if (!is_array($temp_id) || !$temp_id) {
                    $salvar_ativo = $this->salvar_ativo($dados_ativo);
                    $temp_id = $this->obter_id_ativo($dados_ativo);
                }
                if (($temp_id !== 0)) {
                    array_unshift($dados_investimento, $temp_id['id']);
                    $dados_investimento['total_investido'] = $dados_investimento['quantidade'] * $dados_investimento['valor_cotacao'];
                    $valores = implode("','", $dados_investimento);
                    $sql_salvar_investimento = "INSERT INTO investimentos (ativo,tipo_aquisicao,data_operacao,quantidade,valor_cotacao,total_investido)
                VALUES ('$valores')";
                    // var_dump($sql_salvar_investimento);exit;
                    $stmt_salvar_investimento = $this->conexao->prepare($sql_salvar_investimento);
                    $stmt_salvar_investimento->execute();
                    // var_dump($retorno_insercao);exit;
                    $retorno = true;
                }
            } catch (PDOException $e) {
                $retorno = [];
                $retorno['erro'] = "Algo deu errado; " . $e->getMessage();
            }
        }
        // var_dump($retorno);exit;
        return $retorno;
    }

    public function obter_id_ativo(array|string $dados): int|bool|array
    {
        $retorno = 0;
        if (!$this->checar_conexao()) {
            $this->contectar();
        }
        if (isset($this->conexao)) {
            try {
                $sql_id_ativo = "SELECT id FROM ativos
                WHERE ticker = '{$dados['ticker']}'";
                // var_dump($sql_id_ativo);
                $stmt_id_ativo = $this->conexao->prepare($sql_id_ativo);
                $stmt_id_ativo->execute();
                $retorno_sql = $stmt_id_ativo->fetch(PDO::FETCH_ASSOC);
                if (!$retorno_sql) {
                    $retorno_sql = false;
                } else {
                    $retorno = $retorno_sql;
                }
            } catch (PDOException $e) {
                $retorno = [];
                $retorno['erro'] = "Algo deu errado; " . $e->getMessage();
            }
        }
        return $retorno;
    }

    public function salvar_ativo(array|string $dados): bool|array
    {
        $retorno = false;
        if (!$this->checar_conexao()) {
            $this->contectar();
        }
        if (isset($this->conexao)) {
            try {
                $sql_ativo = "SELECT ticker FROM ativos where ticker = '{$dados['ticker']}';";
                $stmt_ativo = $this->conexao->prepare($sql_ativo);
                $stmt_ativo->execute();
                $retorno_ativo = $stmt_ativo->fetchAll(PDO::FETCH_ASSOC);
                if (!$retorno_ativo) {

                    $valores = implode("','", $dados);
                    $sql_salvar_ativo = "INSERT INTO ativos (ticker, tipo_investimento_id,setor_id,nacionalidade)                
            VALUES ('$valores')";
                    // var_dump($sql_salvar_ativo);exit;
                    $stmt_salvar_ativo = $this->conexao->prepare($sql_salvar_ativo);
                    $stmt_salvar_ativo->execute();
                    $retorno = true;
                } else {
                    $retorno = [];
                    $retorno['erro'] = "O ativo '{$dados['ticker']}' já esta cadastrado.";
                }
            } catch (PDOException $e) {
                $retorno = [];
                $retorno['erro'] = "Algo deu errado;" . $e->getMessage();
            }
        }
        // var_dump($retorno);exit;
        return $retorno;
    }
    public function obter_setores(): array
    {
        $retorno = [];
        if (!$this->checar_conexao()) {
            $this->contectar();
        }
        if (isset($this->conexao)) {
            try {
                $sql_setores = "SELECT * FROM setores";
                $stmt_setores = $this->conexao->prepare($sql_setores);
                $stmt_setores->execute();
                $retorno_sql = $stmt_setores->fetchAll(PDO::FETCH_ASSOC);
                if (!$retorno_sql) {
                    $retorno['erro'] = "Nenhum setor foi encontrado. Atualize a lista de setores";
                } else {
                    $retorno = $retorno_sql;
                }
            } catch (PDOException $e) {
                $retorno['erro'] = "Algo deu errado; " . $e->getMessage();
            }
        }
        return $retorno;
    }
    public function obter_tipos_investimento(): array
    {
        $retorno = [];
        if (!$this->checar_conexao()) {
            $this->contectar();
        }
        if (isset($this->conexao)) {
            try {
                $sql_tipo_investimento = "SELECT * FROM tipos_investimento ORDER BY descricao ASC;";
                $stmt_tipo_investimento = $this->conexao->prepare($sql_tipo_investimento);
                $stmt_tipo_investimento->execute();
                $retorno = $stmt_tipo_investimento->fetchAll(PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                $retorno['erro'] = "Algo deu errado; " . $e->getMessage();
            }
        }
        return $retorno;
    }

    public function salvar_tipo_investimento(string $tipo_investimento): bool|array
    {
        $retorno = false;
        if (!$this->checar_conexao()) {
            $this->contectar();
        }
        if (isset($this->conexao)) {
            try {
                $sql_checagem = "SELECT descricao FROM tipos_investimento
                WHERE descricao = :tipo;";
                // var_dump($tipo_investimento,$sql_checagem);exit;
                $stmt_checagem = $this->conexao->prepare($sql_checagem);
                $stmt_checagem->bindParam(":tipo", $tipo_investimento);
                $stmt_checagem->execute();
                $retorno_checagem = $stmt_checagem->fetch(PDO::FETCH_ASSOC);
                // var_dump($retorno_checagem);exit;
                if ($retorno_checagem == false) {
                    $sql_cadastro = "INSERT INTO tipos_investimento (descricao) 
                    VALUES(:tipo);";
                    // var_dump($sql_cadastro);exit;
                    $stmt_cadastro = $this->conexao->prepare($sql_cadastro);
                    $stmt_cadastro->bindParam(":tipo", $tipo_investimento);
                    $stmt_cadastro->execute();
                    $retorno = true;
                } else {
                    $retorno = [];
                    $retorno['erro'] = "O tipo de investimento já está cadastrado.";
                }
            } catch (PDOException $e) {
                $retorno = [];
                $retorno['erro'] = "Algo deu errado; " . $e->getMessage();
            }
        }
        // var_dump($retorno);exit;
        return $retorno;
    }

    public function excluir_tipo_investimento(string $tipo_investimento): bool|array
    {
        $retorno = false;
        if (!$this->checar_conexao()) {
            $this->contectar();
        }
        if (isset($this->conexao)) {
            try {
                $sql_excluir = "DELETE FROM tipos_investimento 
                WHERE id = :id";
                // var_dump($tipo_investimento, $sql_excluir);
                $stmt_excluir = $this->conexao->prepare($sql_excluir);
                $stmt_excluir->bindParam(":id", $tipo_investimento);
                $stmt_excluir->execute();
                $retorno = true;
            } catch (PDOException $e) {
                $retorno = [];
                $retorno['erro'] = "Algo deu errado; " . $e->getMessage();
            }
        }
        // var_dump($retorno);
        return $retorno;
    }

    public function obter_ativos_setor(): bool|array
    {
        $retorno = false;
        if (!$this->checar_conexao()) {
            $this->contectar();
        }
        if (isset($this->conexao)) {
            try {
                $sql_setor_ativo = "SELECT setores.descricao, ticker 
                FROM ativos
                JOIN 
                setores ON setores.id = ativos.setor_id
                ORDER BY 
                ativos.ticker ASC";
                $stmt_setor_ativo = $this->conexao->prepare($sql_setor_ativo);
                $stmt_setor_ativo->execute();
                $retorno = $stmt_setor_ativo->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $retorno = [];
                $retorno['erro'] = "Algo deu errado; " . $e->getMessage();
            }
        }
        // var_dump($retorno);exit;
        return $retorno;
    }
    public function editar_ativo(array|string $dados_ativo): bool|array
    {
        var_dump($dados_ativo);//exit;
        $retorno = false;
        if (!$this->checar_conexao()) {
            $this->contectar();
        }
        if (isset($this->conexao)) {
            try {
                $sql_editar = "UPDATE ativos
                SET ticker = :nome_ativo,
                setor_id = :setor,
                tipo_investimento_id = :tipo_investimento,
                nacionalidade = :nacionalidade
                WHERE id = :id";
                $stmt_editar = $this->conexao->prepare($sql_editar);
                $stmt_editar->bindParam(":nome_ativo", $dados_ativo['nome_ativo']);
                $stmt_editar->bindParam(":setor", $dados_ativo['setor'], PDO::PARAM_INT);
                $stmt_editar->bindParam(":tipo_investimento", $dados_ativo['tipo_investimento'], PDO::PARAM_INT);
                $stmt_editar->bindParam(":nacionalidade", $dados_ativo['nacionalidade']);
                $stmt_editar->bindParam(":id", $dados_ativo['id']);
                $stmt_editar->execute();
                $retorno = true;
            } catch (PDOException $e) {
                $retorno = [];
                $retorno['erro'] = "Algo deu errado; " . $e->getMessage();
            }
        }
        var_dump($retorno);
        return $retorno;
    }
}