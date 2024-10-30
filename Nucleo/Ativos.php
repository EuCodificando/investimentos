<?php
namespace Nucleo;
class Ativos
{
    public ?string $ativo = null;

    public function __construct(string $ativo = null)
    {
        $this->ativo = $ativo;
    }

    public function cadastrar_ativo(array $dados_ativo): bool|array
    {
        $banco = new BaseDados();
        return $banco->salvar_ativo($dados_ativo);
    }

    public function solicitar_tipos_investimento(): array
    {
        $banco = new BaseDados();
        return $banco->obter_tipos_investimento();

    }

    /**
     * Solicitar a lista de ativos irá fazer a pesquisa desses ativos no arquivo
     * b3Ativos.csv
     * @return void
     */
    public function solicitar_lista_ativos(string $ativo = null): array
    {
        $retorno = [];
        $banco = new BaseDados();
        $ativos_banco = $banco->obter_ativos($ativo);
        $retorno = $ativos_banco;
        // var_dump($retorno);exit;
        return $retorno;
    }

    public function solicitar_lista_posicoes(string $ativo = null): array
    {
        $retorno = [];
        $banco = new BaseDados();
        $retorno = $banco->obter_posicoes($ativo);
        // var_dump($this->lista_posicoes);exit;
        return $retorno;
    }

    public function obter_quantidade_contratos_ativos(array $lista_posicoes): array
    {
        $retorno = [];
        // var_dump($lista_posicoes);exit;
        if (!empty($lista_posicoes)) {
            for ($i = 0; $i < count($lista_posicoes); $i++) {
                // var_dump($value);//exit;
                if ($lista_posicoes[$i]['tipo_aquisicao'] == 'C') {
                    if (
                        !in_array($lista_posicoes[$i]['nome_ativo'], array_keys($retorno))
                        || (in_array($lista_posicoes[$i]['nome_ativo'], array_keys($retorno))
                            && !in_array('C', array_keys($retorno[$lista_posicoes[$i]['nome_ativo']])))
                    ) {
                        $retorno[$lista_posicoes[$i]['nome_ativo']]['C'] = $lista_posicoes[$i]['quantidade'];
                    } else {
                        $retorno[$lista_posicoes[$i]['nome_ativo']]['C'] += $lista_posicoes[$i]['quantidade'];
                    }
                } else {
                    if (
                        !in_array($lista_posicoes[$i]['nome_ativo'], array_keys($retorno))
                        || (in_array($lista_posicoes[$i]['nome_ativo'], array_keys($retorno))
                            && !in_array('V', array_keys($retorno[$lista_posicoes[$i]['nome_ativo']])))
                    ) {
                        $retorno[$lista_posicoes[$i]['nome_ativo']]['V'] = -$lista_posicoes[$i]['quantidade'];
                    } else {
                        $retorno[$lista_posicoes[$i]['nome_ativo']]['V'] -= $lista_posicoes[$i]['quantidade'];
                    }
                }
            }
        }
        return $retorno;
    }

    public function obter_posicoes_abertas(array $lista_posicoes): array
    {
        $retorno = [];
        $retorno_filtro = $this->obter_quantidade_contratos_ativos($lista_posicoes);
        $retorno_qntd = [];
        // var_dump($retorno_filtro);
        foreach ($retorno_filtro as $key => $value) {
            if (!in_array($key, array_keys($retorno_qntd))) {
                $retorno_qntd[$key] = array_sum($retorno_filtro[$key]);
            }
        }
        // var_dump($retorno_qntd);exit;
        $retorno = array_filter($retorno_qntd, function ($a) {
            return $a != 0;
        }, ARRAY_FILTER_USE_BOTH);
        // var_dump($retorno);exit;
        return $retorno;
    }

    public function obter_posicoes_fechadas(array $lista_posicoes)
    {
        $retorno = [];
        $retorno_filtro = $this->obter_quantidade_contratos_ativos($lista_posicoes);
        $retorno_qntd = [];
        // var_dump($retorno_filtro);
        foreach ($retorno_filtro as $key => $value) {
            if (!in_array($key, array_keys($retorno_qntd))) {
                $retorno_qntd[$key] = array_sum($retorno_filtro[$key]);
            }
        }
        $retorno = array_filter($retorno_qntd, function ($a) {
            return $a == 0;
        }, ARRAY_FILTER_USE_BOTH);
        // var_dump($retorno);exit;
        return $retorno;
    }

    public function obter_total_investido_posicoes_abertas(array $lista_posicoes, array $posicoes_abertas): float
    {
        $retorno = 0.0;
        // var_dump($lista_posicoes);// exit;
        foreach ($posicoes_abertas as $keyA => $valueA) {
            foreach ($lista_posicoes as $keyB => $valueB) {
                if ($keyA == $keyB) {
                    if ($lista_posicoes[$keyB]['quantidade_compra'] > abs($lista_posicoes[$keyB]['quantidade_venda'])) {
                        if (in_array('C', array_keys($valueB['total_investido']))) {
                            foreach ($valueB['total_investido']['C'] as $valueC) {
                                $retorno += $valueC;
                            }
                        }
                    } else {
                        if (in_array('V', array_keys($valueB['total_investido']))) {
                            foreach ($valueB['total_investido']['V'] as $valueC) {
                                $retorno += $valueC;
                            }
                        }
                    }
                }

            }
        }
        // var_dump($retorno);exit;
        return $retorno;
    }

    public function obter_total_investido_posicoes_fechadas(array $lista_posicoes, $posicoes_fechadas): float
    {
        $retorno = 0.0;
        // var_dump($posicoes_fechadas,$lista_posicoes['ABCB4F']);//exit;
        foreach ($posicoes_fechadas as $keyA => $valueA) {
            foreach ($lista_posicoes as $keyB => $valueB) {
                if ($keyA == $keyB && in_array('C', array_keys($valueB['total_investido']))) {
                    foreach ($valueB['total_investido']['C'] as $valueC) {
                        $retorno += $valueC;
                    }
                }
            }
        }
        // var_dump($retorno);exit;
        return $retorno;
    }

    public function solicitar_atualizacao_lista_ativos()
    {
        $retorno = [];
        $arquivo = new Arquivos();
        $retorno = $arquivo->obter_ativos_arquivo();
        // var_dump($this->lista_ativos);exit;
        if (!in_array('erro', array_keys($retorno))) {

        } else {
            // var_dump($retorno);
            echo $retorno['erro'];
        }
    }

    public function solicitar_total_investido(array $lista_ativos, array $lista_posicoes): array
    {
        $retorno = [];
        // var_dump($lista_ativos);exit;
        if (!empty($lista_posicoes)) {
            for ($i = 0; $i < count($lista_posicoes); $i++) {
                $temp_operacoes[$lista_posicoes[$i]['nome_ativo']] = 0;
            }
        } else {
            for ($i = 0; $i < count($lista_ativos); $i++) {
                $temp_operacoes[$lista_ativos[$i]['ticker']] = 0;
            }
        }
        if (!empty($lista_posicoes)) {
            // var_dump($temp_operacoes);exit;
            foreach ($temp_operacoes/*$posicoes_abertas*/ as $key => $value) {
                $historico = [];
                $historico['lado_posicionado'] = null;
                $historico['tipo_aquisicao'] = null;
                $historico['quantidade_total'] = 0;
                for ($i = 0; $i < count($lista_posicoes); $i++) {
                    if ($key == $lista_posicoes[$i]['nome_ativo']) {
                        if (is_null($historico['lado_posicionado'])) {
                            $historico['lado_posicionado'] = $lista_posicoes[$i]['tipo_aquisicao'];
                            $historico['tipo_aquisicao'] = $lista_posicoes[$i]['tipo_aquisicao'];
                            $historico['quantidade_total'] = $lista_posicoes[$i]['quantidade'];
                            $historico['valor_cotacao'] = $lista_posicoes[$i]['valor_cotacao'];
                            $historico['total_investido'] = $lista_posicoes[$i]['total_investido'];
                            $historico['preco_medio'] = $lista_posicoes[$i]['valor_cotacao'];
                            $historico['lp_posicao'] = 0.0;
                            $historico['lp_operacao'] = 0.0;
                            // var_dump($key, $historico['preco_medio']);
                        } else {
                            if ($historico['lado_posicionado'] == $lista_posicoes[$i]['tipo_aquisicao']) {
                                //AUMENTO DE POSIÇÃO
                                // var_dump($key. ' Aumento de posição');
                                $historico['quantidade_total'] += $lista_posicoes[$i]['quantidade'];
                                $calculo = ($lista_posicoes[$i]['valor_cotacao'] - $historico['preco_medio']) / $historico['quantidade_total'];
                                $lp_operacao = $historico['lado_posicionado'] == 'C' && $calculo > 0 ? $calculo : -$calculo;
                                // var_dump($historico['quantidade_total'],$lp_operacao,$calculo);
                                $historico['total_investido'] += $lista_posicoes[$i]['total_investido'];
                                $historico['preco_medio'] = ($historico['total_investido']) / $historico['quantidade_total'];
                                $historico['tipo_aquisicao'] = $lista_posicoes[$i]['tipo_aquisicao'];
                                $historico['valor_cotacao'] = $lista_posicoes[$i]['valor_cotacao'];
                                // var_dump($key, $historico);
                            } else if (
                                $historico['lado_posicionado'] !== $lista_posicoes[$i]['tipo_aquisicao']
                                && $lista_posicoes[$i]['quantidade'] < $historico['quantidade_total']
                            ) {
                                //REALIZAÇÃO
                                // var_dump($key. ' Realização');
                                $calculo = ($lista_posicoes[$i]['valor_cotacao'] - $historico['preco_medio']) * $lista_posicoes[$i]['quantidade'];
                                $lp_operacao = $historico['lado_posicionado'] == 'C' && $calculo > 0 ?
                                    -$calculo : $calculo;
                                $historico['lp_posicao'] = $historico['lado_posicionado'] == 'C' && $calculo > 0 ? $calculo : -$calculo;
                                // var_dump($calculo,$lp_operacao,($lista_posicoes[$i]['total_investido'] + $lp_operacao));
                                $historico['total_investido'] -= ($lista_posicoes[$i]['total_investido']);
                                $historico['quantidade_total'] -= $lista_posicoes[$i]['quantidade'];
                                $historico['preco_medio'] = ($historico['total_investido']) / $historico['quantidade_total'];
                                $historico['tipo_aquisicao'] = $lista_posicoes[$i]['tipo_aquisicao'];
                                // var_dump($key, $historico);
                            } else if (
                                $historico['lado_posicionado'] !== $lista_posicoes[$i]['tipo_aquisicao']
                                && $lista_posicoes[$i]['quantidade'] > $historico['quantidade_total']
                            ) {
                                //TROCA DE POSIÇÃO
                                // var_dump($key. ' Troca de posição');
                                $calculo = $historico['lado_posicionado'] == 'V'
                                    ? ($historico['preco_medio'] - $lista_posicoes[$i]['valor_cotacao']) * $historico['quantidade_total']
                                    : ($lista_posicoes[$i]['valor_cotacao'] - $historico['preco_medio']) * $historico['quantidade_total'];
                                $historico['lp_operacao'] = $calculo;
                                $historico['quantidade_total'] = $lista_posicoes[$i]['quantidade'] - $historico['quantidade_total'];
                                $historico['total_investido'] = ($lista_posicoes[$i]['valor_cotacao'] * $historico['quantidade_total']);
                                $historico['preco_medio'] = $lista_posicoes[$i]['valor_cotacao'];
                                $historico['tipo_aquisicao'] = $lista_posicoes[$i]['tipo_aquisicao'];
                                $historico['lado_posicionado'] = $lista_posicoes[$i]['tipo_aquisicao'];
                                // var_dump($key,$historico);
                            } else if (
                                $historico['lado_posicionado'] !== $lista_posicoes[$i]['tipo_aquisicao']
                                && $lista_posicoes[$i]['quantidade'] == $historico['quantidade_total']
                            ) {
                                //ENCERRAMENTO DE POSIÇÃO
                                // var_dump($key, " Encerrando posição");
                                $calculo = $historico['lado_posicionado'] == 'V'
                                    ? ($historico['preco_medio'] - $lista_posicoes[$i]['valor_cotacao']) * $lista_posicoes[$i]['quantidade']
                                    : ($lista_posicoes[$i]['valor_cotacao'] - $historico['preco_medio']) * $lista_posicoes[$i]['quantidade'];
                                $lp_operacao = $calculo > 0 ? -$calculo : $calculo;
                                $historico['total_investido'] -= ($lista_posicoes[$i]['total_investido']) - $lp_operacao;
                                $historico['quantidade_total'] -= $lista_posicoes[$i]['quantidade'];
                                $historico['preco_medio'] = 0;
                                $historico['tipo_aquisicao'] = $lista_posicoes[$i]['tipo_aquisicao'];
                            }
                        }
                    }
                }
                $retorno[$key] = $historico;
            }
        } else {
            $historico['lado_posicionado'] = null;
            $historico['tipo_aquisicao'] = null;
            $historico['quantidade_total'] = 0;
            $historico['valor_cotacao'] = 0.0;
            $historico['total_investido'] = 0.0;
            $historico['preco_medio'] = 0.0;
            $historico['lp_posicao'] = 0.0;
            $historico['lp_operacao'] = 0.0;
            foreach ($temp_operacoes as $key => $value) {
                $retorno[$key] = $historico;
            }
        }
        ksort($retorno);
        // var_dump($retorno);//exit;
        return $retorno;
    }

    public function solicitar_atualizacao_investimentos(): bool|array
    {
        $arquivos = new Arquivos();
        $banco = new BaseDados();
        $retorno = false;
        $retorno_solicitacao_limpeza = $banco->realizar_limpeza_tabela_investimentos();
        // var_dump($retorno_solicitacao_limpeza);
        if (!is_array($retorno_solicitacao_limpeza) && $retorno_solicitacao_limpeza) {
            // var_dump('Limpeza e reset do auto incremento feitos');exit;
            $dados_investimento = $arquivos->obter_dados_tabela_investimentos();
            // var_dump($dados_investimento);exit;
            $retorno = $banco->salvar_dados_atualizados_investimentos_banco($dados_investimento);
        }
        // var_dump($retorno);exit;
        return $retorno;
    }

    public function cadastrar_investimento(array $dados): bool|array
    {
        $banco = new BaseDados();
        return $banco->salvar_investimento($dados);
    }

    public function solicitar_setores(): array
    {
        $banco = new BaseDados();
        return $banco->obter_setores();
    }

    public function cadastrar_tipo_investimento(array $tipo_investimento): bool|array
    {
        $banco = new BaseDados();
        return $banco->salvar_tipo_investimento($tipo_investimento['tipo_investimento']);
    }
    public function solicitar_exclusao_tipo_investimento(array $tipo_investimento): bool|array
    {
        $banco = new BaseDados();
        return $banco->excluir_tipo_investimento($tipo_investimento['tipo_investimento']);
    }

    public function solicitar_ativos_setor(): bool|array
    {
        $retorno = [];
        $banco = new BaseDados();
        $retorno_solicitacao = $banco->obter_ativos_setor();
        if (!in_array('erro', array_keys($retorno_solicitacao))) {
            for ($i = 0; $i < count($retorno_solicitacao); $i++) {
                if (!in_array($retorno_solicitacao[$i]['descricao'], array_keys($retorno))) {
                    $retorno[$retorno_solicitacao[$i]['descricao']][] = $retorno_solicitacao[$i]['ticker'];
                } else {
                    $retorno[$retorno_solicitacao[$i]['descricao']][] = $retorno_solicitacao[$i]['ticker'];
                }
            }
        } else {
            $retorno = $retorno_solicitacao['erro'];
        }
        // var_dump($retorno);
        return $retorno;
    }

    public function solicitar_cotacoes(string $ativo = null):bool|array{
        $retorno = [];
        $arquivos = new Arquivos();
        $arquivos->obter_cotacoes($ativo);
        return $retorno;
    }
}