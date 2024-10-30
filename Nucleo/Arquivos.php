<?php

namespace Nucleo;

include_once __DIR__ . "/Constants.php";

class Arquivos
{
    public function __construct()
    {

    }

    /**
     * Diferente de obter ativos do banco de dados, a lista dos ativos será
     * obtida através do arquivo b3Ativos.csv
     * @param string $ativo
     * @return array
     */
    public function obter_ativos_arquivo(string $ativo = null): array
    {
        $retorno = [];
        $verifica_existencia_arquivo_ativos = file_exists(DIRETORIO_ARQUIVOS . "b3Ativos.csv");
        if (!$verifica_existencia_arquivo_ativos) {
            $verifica_existencia_arquivo_operacoes = file_exists(DIRETORIO_ARQUIVOS . "b3Operacoes.csv");
            if (!$verifica_existencia_arquivo_operacoes) {
                $retorno['erro'] = '<p>O arquivo ( b3Operacoes.csv ) não foi encontrado no diretório.<br> Não será possível obter a lista de ativos, é necessário ao menos o arquivo da b3Operações.csv no diretório</p>.';
            } else {
                $temp_ativos = $this->ler_arquivo('operacoes');
                var_dump($temp_ativos);
                $this->criar_arquivo_ativos($temp_ativos);
                $this->salvar_lista_atualizada_banco($temp_ativos);
                $retorno = $temp_ativos;
                // var_dump($arquivo_operacoes, $temp_ativos);exit;

            }
        } else {
            $temp_ativos = $this->ler_arquivo('ativos');
            $retorno = $temp_ativos;
            $this->salvar_lista_atualizada_banco($temp_ativos);

        }
        // var_dump($retorno);exit;
        return $retorno;
    }

    protected function criar_arquivo_ativos(array $dados)
    {
        // var_dump($dados);exit;
        $arquivo_ativos_criado = fopen(DIRETORIO_ARQUIVOS . "b3Ativos.csv", 'w+');
        $temp_cabecalho = false;
        foreach ($dados as $keyA => $valueA) {
            if (str_contains($keyA, '34')) {
                $nacionalidade = "Investimento estrangeiro";
            } else {
                $nacionalidade = "Investimento nacional";
            }
            if (!$temp_cabecalho) {
                $id_ativo = 1;
                fputs($arquivo_ativos_criado, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
                fputcsv($arquivo_ativos_criado, array('id', 'ticker', 'tipo_investimento', 'nacionalidade'), ";");
                fputcsv($arquivo_ativos_criado, array($id_ativo, "$keyA", "$valueA", $nacionalidade), ";");
                $temp_cabecalho = true;
            } else {
                $id_ativo++;
                fputcsv($arquivo_ativos_criado, array($id_ativo, "$keyA", "$valueA", $nacionalidade), ";");
            }
        }
    }

    protected function ler_arquivo(string $nome_arquivo): array
    {
        switch ($nome_arquivo) {
            case 'operacoes':
                $arquivo = fopen(DIRETORIO_ARQUIVOS . "b3Operacoes.csv", 'r');
                $nome_arquivo = 'b3Operacoes';
                $temp_colunas = [2, 5];
                break;
            case 'ativos':
                $arquivo = fopen(DIRETORIO_ARQUIVOS . "b3Ativos.csv", 'r');
                $nome_arquivo = 'b3Ativos';
                $temp_colunas = [1];
                break;
        }
        $numero_linhas = count(file(DIRETORIO_ARQUIVOS . "$nome_arquivo.csv"));
        $contador = 0;
        $temp_ativos = [];
        if ($nome_arquivo == 'b3Operacoes') {
            while ($contador < $numero_linhas) {
                $temp_linha = (array) fgetcsv($arquivo, null, ';');
                // var_dump($temp_linha);//exit;
                if ($contador > 0) {
                    if (count($temp_ativos) < 1) {
                        // var_dump($temp_linha[5]);exit;
                        $temp_ativos[$temp_linha[5]] = $temp_linha[2];
                    } else if (!in_array($temp_linha[5], array_keys($temp_ativos))) {
                        $temp_ativos[$temp_linha[5]] = $temp_linha[2];
                    }
                }
                $contador++;
            }
        } elseif ($nome_arquivo == 'b3Ativos') {
            $numero_linhas = count(file(DIRETORIO_ARQUIVOS . "$nome_arquivo.csv"));
            $contador = 0;
            while ($contador < $numero_linhas) {
                $temp_linha = (array) fgetcsv($arquivo, null, ';');
                // var_dump($temp_linha);//exit;
                if ($contador > 0) {
                    if (count($temp_ativos) < 1) {
                        // var_dump($temp_linha[5]);exit;
                        $temp_ativos[$temp_linha[1]][] = $temp_linha[2];
                        $temp_ativos[$temp_linha[1]][] = $temp_linha[3];
                    } else if (!in_array($temp_linha[2], array_keys($temp_ativos))) {
                        $temp_ativos[$temp_linha[1]][] = $temp_linha[2];
                        $temp_ativos[$temp_linha[1]][] = $temp_linha[3];
                    }
                }
                $contador++;
            }
        }
        ksort($temp_ativos);
        if ($nome_arquivo == 'b3Ativos') {
            // var_dump($temp_ativos);exit;
        }

        fclose($arquivo);
        return $temp_ativos;
    }

    protected function salvar_lista_atualizada_banco(array $dados): bool|array
    {
        //  var_dump($dados);exit;
        $retorno = false;
        $retorno_limpezar_tabela = $this->limpar_tabela_ativos();
        if (!is_array($retorno_limpezar_tabela)) {
            $banco = new BaseDados();
            if (!$banco->checar_conexao()) {
                $banco->contectar();
            }
            try {
                $valores = '';
                foreach ($dados as $keyA => $valueA) {
                    if (array_key_last($dados) == $keyA) {
                        $valores .= "('$keyA','" . implode("','", $valueA) . "')";
                    } else {
                        $valores .= "('$keyA','" . implode("','", $valueA) . "'),";
                    }

                }
                // $valores = implode("'),('", $dados);
                // $tickers = implode(',',array_keys($dados));
                // var_dump($valores);exit;
                $sql_inserir_ativos = "INSERT INTO ativos (ticker,tipo_investimento,nacionalidade) VALUES $valores";
                // var_dump($sql_inserir_ativos);
                $stmt_inserir_ativos = $banco->conexao->prepare($sql_inserir_ativos);
                $stmt_inserir_ativos->execute();
                $retorno = true;
            } catch (\PDOException $e) {
                $retorno = [];
                $retorno['erro'] = "Algo deu errado; " . $e->getMessage();
            }

        }
        // var_dump($retorno);exit;
        return $retorno;
    }

    protected function limpar_tabela_ativos(): bool|array
    {
        $retorno = false;
        $banco = new BaseDados();
        if (!$banco->checar_conexao()) {
            $banco->contectar();
        }
        try {
            $sql_truncate = "DELETE FROM ativos";
            $stmt_truncate = $banco->conexao->prepare($sql_truncate);
            $stmt_truncate->execute();
            // $retorno_truncate = $stmt->fetch(\PDO::FETCH_ASSOC);
            $sql_reset_incremento = "ALTER TABLE ativos AUTO_INCREMENT = 1;";
            $stmt_reset_incremento = $banco->conexao->prepare($sql_reset_incremento);
            $stmt_reset_incremento->execute();
            $retorno = true;
        } catch (\PDOException $e) {
            $retorno = [];
            $retorno['erro'] = "Algo deu errado; " . $e->getMessage();
        }
        $banco->fechar_conexao();
        // var_dump($retorno);exit;
        return $retorno;
    }

    public function solicitar_lista_ativos(string $ativo = null): array
    {
        $retorno = [];
        $banco = new BaseDados();
        $retorno = $banco->obter_ativos();
        // var_dump($retorno);
        return $retorno;
    }

    public function atualizar_ativos_banco(string $ativo = null): bool
    {
        $retorno = false;
        $this->obter_ativos_arquivo($ativo);
        return $retorno;
    }

    public function solicitar_limpeza_tabela_investimentos(): bool|array
    {
        $banco = new BaseDados();
        $retorno_limpeza = $banco->realizar_limpeza_tabela_investimentos();
        return $retorno_limpeza;
    }

    public function obter_dados_tabela_investimentos(): array
    {
        $retorno = [];
        $lista_ativos = [];
        $banco = new BaseDados();
        $temp_ativos = $banco->obter_ativos();
        // var_dump($temp_ativos);exit;
        if (in_array('erro', $temp_ativos)) {
            $retorno = $temp_ativos;
        } else if (empty($temp_ativos)) {
            $temp_ativos = $this->obter_ativos_arquivo();
            $lista_ativos = $this->salvar_lista_atualizada_banco($temp_ativos);
        }
        // var_dump($temp_ativos);exit;
        $verificar_existencia_operacoes = file_exists(DIRETORIO_ARQUIVOS . "b3Operacoes.csv");
        if (
            $verificar_existencia_operacoes
            && (!in_array('erro', $lista_ativos) || $lista_ativos)
        ) {
            $numero_linhas = count((array) file(DIRETORIO_ARQUIVOS . "b3Operacoes.csv"));
            $arquivo_operacoes = fopen(DIRETORIO_ARQUIVOS . "b3Operacoes.csv", "r");
            $contador = 0;
            $id = 1;
            $temp_dados = [];
            $teste = 0;
            while ($contador <= $numero_linhas) {
                $linha = (array) fgetcsv($arquivo_operacoes, null, ";");
                // var_dump($linha);exit;
                if ($contador > 0 && $linha[0] !== false) {
                    for ($i = 0; $i < count($temp_ativos); $i++) {

                        if ($temp_ativos[$i]['ticker'] == $linha[5]) {
                            // var_dump($temp_ativos[$i],$linha[5],$linha[0]);//exit;
                            $temp_id_ativo = $temp_ativos[$i]['id'];
                            $temp_dados[$contador - 1]['id'] = $id;
                            $temp_dados[$contador - 1]['ativo'] = $temp_id_ativo;
                            $temp_dados[$contador - 1]['tipo_aquisicao'] = ($linha[1] == 'Compra' ? 'C' : 'V');
                            $temp_dados[$contador - 1]['data_operacao'] = $linha[0];
                            $temp_dados[$contador - 1]['quantidade'] = $linha[6];
                            if (substr_count($linha[8], '.') > 1) {
                                $temp_dados[$contador - 1]['valor_cotacao'] = trim(substr(preg_replace('/\./', '', $linha[8], 1), 3));
                            } else {
                                $temp_dados[$contador - 1]['valor_cotacao'] = trim(substr($linha[8], 3));
                            }
                            // var_dump($temp_ativos[$i]['ticker'], $linha[5], $linha[6]);
                            if ($temp_ativos[$i]['ticker'] == "AMBP3F") {
                                /*var_dump($ativos[$i]['ticker'],
                                    $retono_lista_investimentos[$a]['tipo_aquisicao'],
                                    $retono_lista_investimentos[$a]['quantidade'],
                                    $qntd_contratos_ativo[$ativos[$i]['ticker']]
                                );*/
                                $teste++;
                            }
                        }

                    }
                    // exit;
                    // var_dump($temp_dados, $linha[5]);exit;
                    $id++;

                }
                $contador++;
            }
            $retorno = $temp_dados;
        } else if (!$verificar_existencia_operacoes) {
            $retorno['erro'] = "Arquivo ( b3Operacoes ) não foi encontrado no diretório.";
        }
        // var_dump($retorno);exit;
        return $retorno;
    }
}