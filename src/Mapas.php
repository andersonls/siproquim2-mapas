<?php

namespace Siproquim;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Constraints\Factory;
use JsonSchema\SchemaStorage;
use JsonSchema\Validator;
use stdClass;

class Mapas
{
    const OPERACAO_COMPRA = 'EC';
    const OPERACAO_RECEBIMENTO_TRANSFERENCIA = 'ET';
    const OPERACAO_RECEBIMENTO_DOACAO = 'ED';
    const OPERACAO_RECEBIMENTO_PRODUTO_ARMAZENADO = 'EA';
    const OPERACAO_RECEBIMENTO_PRODUTO_INDUSTRIALIZADO = 'EP';
    const OPERACAO_RECEBIMENTO_PRODUTO_PARA_INDUSTRIALIZACAO = 'EI';
    const OPERACAO_OUTROS_RECEBIMENTOS = 'ER';

    const OPERACAO_VENDA = 'SV';
    const OPERACAO_TRANSFERENCIA = 'ST';
    const OPERACAO_DEVOLUCAO_PRODUTO_ARMAZENADO = 'SA';
    const OPERACAO_DEVOLUCAO_PRODUTO_INDUSTRIALIZADO = 'SI';
    const OPERACAO_DOACAO = 'SD';
    const OPERACAO_REMESSA_ARMAZENAGEM = 'SR';
    const OPERACAO_REMESSA_PRODUTO_PARA_INDUSTRIALIZACAO = 'SP';
    const OPERACAO_OUTRAS_REMESSAS = 'SO';

    const CONSUMO_LIMPEZA_MANUTENCAO = 1;
    const CONSUMO_ANALISE_LABORATORIAL = 2;
    const CONSUMO_OUTROS = 3;
    const CONSUMO_PROCESSO_PRODUTIVO = 4;
    const CONSUMO_TRATAMENTO_AFLUENTES_EFLUENTES = 5;

    /**
     * @var stdClass
     */
    protected $std;

    /**
     * Mapas constructor.
     *
     * @param stdClass $std
     * @throws \InvalidArgumentException
     */
    public function __construct(stdClass $std)
    {
        $this->validInputData($std);

        $this->std = $std;
    }

    /**
     * Validates json data from json Schema
     *
     * @param stdClass $data
     * @return boolean
     * @throws \InvalidArgumentException
     */
    protected function validInputData(stdClass $data) : bool
    {
        $jsonSchema = realpath(__DIR__  . '/../resources/mapas.schema');
        $definitions = realpath(__DIR__  . '/../resources/definitions.schema');

        $jsonSchemaObject = json_decode((string)file_get_contents($jsonSchema));
        if ($jsonSchemaObject === null) {
            throw new \RuntimeException('Json schema is invalid');
        }
        $schemaStorage = new SchemaStorage();
        $schemaStorage->addSchema("file:{$definitions}", $jsonSchemaObject);
        $jsonValidator = new Validator(new Factory($schemaStorage));
        $jsonValidator->validate($data, $jsonSchemaObject, Constraint::CHECK_MODE_COERCE_TYPES);
        if ($jsonValidator->isValid()) {
            return true;
        }
        $errors = $jsonValidator->getErrors();

        $msg = '';
        foreach ($errors as $error) {
            $msg .= sprintf("- [%s] %s\n", $error['property'], $error['message'] . ';');
        }
        throw new \InvalidArgumentException($msg);
    }

    public function __toString() : string
    {
        $year = substr($this->std->data, 0, 4);
        $month = (int) substr($this->std->data, 5, 2);
        $month = Common::getMonthAbbreviation($month);

        $txt = 'EM'
             . $this->std->cnpj
             . $month
             . $year
             . (isset($this->std->movimentacao_nacional) ? 1 : 0) //Comercialização Nacional
             . (isset($this->std->movimentacao_internacional) ? 1 : 0) //Comercialização Internacional
             . '0' //TODO Produção
             . '0' //TODO Transformação
             . (isset($this->std->consumo) ? 1 : 0) //Consumo
             . '0' //TODO Fabricação
             . '0' //TODO Transporte
             . '0' //TODO Armazenamento
             . PHP_EOL
        ;

        $txt .= 'DG' . PHP_EOL;

        if (isset($this->std->demonstrativo_geral->produto_controlado)) {
            foreach ($this->std->demonstrativo_geral->produto_controlado as $produto) {
                $txt .= 'PR'
                     .  mb_str_pad($produto->codigo_ncm, 13, ' ', STR_PAD_RIGHT)
                     .  mb_str_pad($produto->nome_comercial, 70, ' ', STR_PAD_RIGHT)
                     .  mb_str_pad($produto->concentracao, 3, '0', STR_PAD_LEFT)
                     .  Common::formatDensidade($produto->densidade)
                     .  PHP_EOL
                ;
            }
        }

        if (isset($this->std->demonstrativo_geral->produto_composto)) {
            foreach ($this->std->demonstrativo_geral->produto_composto as $produto) {
                $txt .= 'PC'
                    .  vsprintf('%s%s%s%s.%s%s.%s%s', str_split($produto->ncm))
                    .  mb_str_pad(ucfirst(strtolower($produto->nome_comercial)), 70, ' ', STR_PAD_RIGHT)
                    .  Common::formatDensidade($produto->densidade)
                    .  PHP_EOL
                ;

                foreach ($produto->substancia_controlada as $substancia) {
                    $txt .= 'SC'
                         .  mb_str_pad($substancia->codigo_ncm, 13, ' ', STR_PAD_RIGHT)
                         .  mb_str_pad($substancia->concentracao, 2, '0', STR_PAD_LEFT)
                         .  PHP_EOL
                    ;
                }
            }
        }

        if (isset($this->std->demonstrativo_geral->residuo_controlado)) {
            foreach ($this->std->demonstrativo_geral->residuo_controlado as $residuo) {
                $txt .= 'RC'
                     .  mb_str_pad($residuo->codigo_ncm, 13, ' ', STR_PAD_RIGHT)
                     .  mb_str_pad($residuo->nome_comercial, 70, ' ', STR_PAD_RIGHT)
                     .  mb_str_pad($residuo->concentracao, 3, '0', STR_PAD_LEFT)
                     .  Common::formatDensidade($residuo->densidade)
                     .  PHP_EOL
                ;
            }
        }

        if (isset($this->std->demonstrativo_geral->residuo_composto)) {
            foreach ($this->std->demonstrativo_geral->residuo_composto as $residuo) {
                $txt .= 'RS'
                    .  vsprintf('%s%s%s%s.%s%s.%s%s', str_split($residuo->ncm))
                    .  mb_str_pad(ucfirst(strtolower($residuo->nome_comercial)), 70, ' ', STR_PAD_RIGHT)
                    .  Common::formatDensidade($residuo->densidade)
                    .  PHP_EOL
                ;

                foreach ($residuo->substancia_controlada as $substancia) {
                    $txt .= 'RB'
                         .  mb_str_pad($substancia->codigo_ncm, 13, ' ', STR_PAD_RIGHT)
                         .  mb_str_pad($substancia->concentracao, 2, '0', STR_PAD_LEFT)
                         .  PHP_EOL
                    ;
                }
            }
        }

        foreach ($this->std->movimentacao_nacional as $movimentacao) {
            if ($movimentacao->entrada_saida === 'E' &&
                substr($movimentacao->operacao, 0, 1) !== $movimentacao->entrada_saida
            ) {
                throw new \InvalidArgumentException(
                    'Tipo de operação (' . $movimentacao->operacao . ') inválida para movimentação de ' .
                    ($movimentacao->entrada_saida === 'E' ? 'entrada' : 'saída')
                );
            }

            if ($movimentacao->entrada_saida === 'E' && ! in_array($movimentacao->armazenagem, ['S', 'N'])) {
                throw new \InvalidArgumentException('Valor do campo "armazenagem" inválido para operação de entrada');
            }
            if ($movimentacao->entrada_saida === 'S' && ! in_array($movimentacao->armazenagem, ['F', 'T'])) {
                throw new \InvalidArgumentException('Valor do campo "armazenagem" inválido para operação de saída');
            }

            $txt .= 'MVN'
                 .  $movimentacao->entrada_saida
                 .  $movimentacao->operacao
                 .  $movimentacao->cnpj
                 .  mb_str_pad($movimentacao->razao_social, 69, ' ', STR_PAD_RIGHT)
                 .  mb_str_pad($movimentacao->nota_fiscal, 10, '0', STR_PAD_LEFT)
                 . \DateTime::createFromFormat('Y-m-d', $movimentacao->data_emissao_nf)->format('d/m/Y')
                 .  $movimentacao->armazenagem
                 .  $movimentacao->transporte
                 .  PHP_EOL
            ;

            foreach ($movimentacao->movimento as $movimento) {
                $tipo = $this->searchTipoProduto($this->std->demonstrativo_geral, $movimento);

                if ($tipo === null) {
                    throw new \InvalidArgumentException(
                        'Produto ' . $movimento->codigo_ncm . ' não encontrado no demonstrativo geral'
                    );
                }

                if (! isset($movimento->concentracao) && in_array($tipo, ['PR', 'RC'])) {
                    throw new \InvalidArgumentException(
                        'Campo "concentracao" é obrigatório para produto/resíduo composto'
                    );
                }

                $txt .= 'MM'
                     .  Common::formatCodigoNcmProduto($movimento->codigo_ncm, $tipo)
                     .  (in_array($tipo, ['PR', 'RC']) ?
                            mb_str_pad($movimento->concentracao, 3, '0', STR_PAD_LEFT) :
                            '   ')
                     .  Common::formatDensidade($movimento->densidade)
                     .  Common::formatQuantidade($movimento->quantidade)
                     .  $movimento->unidade
                     .  PHP_EOL
                ;
            }

            if (isset($movimentacao->transportadora)) {
                $razaoSocial = ucfirst(strtolower($movimentacao->transportadora->razao_social));
                $txt .= 'MT'
                     .  $movimentacao->transportadora->cnpj
                     .  mb_str_pad($razaoSocial, 70, ' ', STR_PAD_RIGHT)
                     .  PHP_EOL
                ;
            } elseif ($movimentacao->transporte === 'T') {
                throw new \InvalidArgumentException(
                    'Campo "transportadora" deve ser preenchido quando transporte terceirizado'
                );
            }

            if (isset($movimentacao->armazenadora)) {
                $txt .= 'MA'
                    .  $movimentacao->armazenadora->cnpj
                    .  mb_str_pad(ucfirst(strtolower($movimentacao->armazenadora->razao_social)), 70, ' ', STR_PAD_RIGHT)
                    .  mb_str_pad(ucfirst(strtolower($movimentacao->armazenadora->endereco)), 70, ' ', STR_PAD_RIGHT)
                    .  vsprintf('%s%s.%s%s%s-%s%s%s', str_split($movimentacao->armazenadora->cep))
                    .  mb_str_pad($movimentacao->armazenadora->numero, 5, ' ', STR_PAD_RIGHT)
                    .  mb_str_pad($movimentacao->armazenadora->complemento, 20, ' ', STR_PAD_RIGHT)
                    .  mb_str_pad($movimentacao->armazenadora->bairro, 30, ' ', STR_PAD_RIGHT)
                    .  $movimentacao->armazenadora->uf
                    .  $movimentacao->armazenadora->municipio
                    .  PHP_EOL
                ;
            } elseif ($movimentacao->armazenagem === 'S' || $movimentacao->armazenagem === 'T') {
                throw new \InvalidArgumentException(
                    'Campo "armazenadora" deve ser preenchido quando há armazenagem em local terceirizado'
                );
            }
        }

        if (isset($this->std->movimentacao_internacional)) {
            foreach ($this->std->movimentacao_internacional as $mvi) {
                if (($mvi->operacao === 'E' && in_array($mvi->responsavel_armazenagem, ['E', 'T']) === false) ||
                    ($mvi->operacao === 'C' && in_array($mvi->responsavel_armazenagem, ['I', 'A', 'T']) === false)
                ) {
                    throw new \InvalidArgumentException(
                        'Campo "responsavel_armazenagem" inválido para o tipo de operação informado'
                    );
                }

                if (($mvi->operacao === 'E' && in_array($mvi->responsavel_transporte, ['E', 'T', 'A']) === false) ||
                    ($mvi->operacao === 'I' && in_array($mvi->responsavel_transporte, ['I', 'F', 'T']) === false) ||
                    ($mvi->operacao === 'C' && in_array($mvi->responsavel_transporte, ['I', 'Q', 'T', 'F']) === false)
                ) {
                    throw new \InvalidArgumentException(
                        'Campo "responsavel_transporte" inválido para o tipo de operação informado'
                    );
                }

                $txt .= 'MVI'
                     . $mvi->operacao
                     . mb_str_pad($mvi->pais, 3, '0', STR_PAD_LEFT)
                     . mb_str_pad($mvi->razao_social, 69, ' ', STR_PAD_RIGHT)
                     . vsprintf('%s%s/%s%s%s%s%s%s%s-%s', str_split($mvi->numero_li))
                     . \DateTime::createFromFormat('Y-m-d', $mvi->data_restricao_embarque)->format('d/m/Y')
                     . \DateTime::createFromFormat('Y-m-d', $mvi->data_conhecimento_embarque)->format('d/m/Y')
                     . mb_str_pad($mvi->numero_due, 15, ' ', STR_PAD_RIGHT)
                     . \DateTime::createFromFormat('Y-m-d', $mvi->data_due)->format('d/m/Y')
                     . vsprintf('%s%s/%s%s%s%s%s%s%s-%s', str_split($mvi->numero_di))
                     . \DateTime::createFromFormat('Y-m-d', $mvi->data_di)->format('d/m/Y')
                     . ($mvi->operacao === 'I' ? ' ' : $mvi->responsavel_armazenagem)
                     . $mvi->responsavel_transporte
                     . ($mvi->operacao !== 'I' ? ' ' : $mvi->local_entrega)
                     . PHP_EOL;

                if (isset($mvi->transporte) && in_array($mvi->responsavel_transporte, ['T', 'F'])) {
                    $txt .= ($mvi->responsavel_transporte === 'T' ? 'TRA' : 'TRI')
                         . ($mvi->responsavel_transporte === 'T' ? $mvi->transporte->cnpj : '')
                         . mb_str_pad($mvi->transporte->razao_social, 70, ' ', STR_PAD_RIGHT)
                         . PHP_EOL;
                }

                if (isset($mvi->armazenagem) && in_array($mvi->responsavel_armazenagem, ['T', 'I'])) {
                    $complemento = isset($mvi->armazenagem->complemento) ? $mvi->armazenagem->complemento : '';

                    $txt .= 'AMZ'
                         . $mvi->armazenagem->cnpj
                         . mb_str_pad($mvi->armazenagem->razao_social, 70, ' ', STR_PAD_RIGHT)
                         . mb_str_pad($mvi->armazenagem->endereco, 70, ' ', STR_PAD_RIGHT)
                         . vsprintf('%s%s.%s%s%s-%s%s%s', str_split($mvi->armazenagem->cep))
                         . mb_str_pad($mvi->armazenagem->numero, 5, ' ', STR_PAD_RIGHT)
                         . mb_str_pad($complemento, 20, ' ', STR_PAD_RIGHT)
                         . mb_str_pad($mvi->armazenagem->bairro, 30, ' ', STR_PAD_RIGHT)
                         . $mvi->armazenagem->uf
                         . mb_str_pad($mvi->armazenagem->municipio, 7, ' ', STR_PAD_RIGHT)
                         . PHP_EOL;
                }

                if (!isset($mvi->entrega) && $mvi->operacao === 'I') {
                    throw new \InvalidArgumentException('Campo "adquirente" obrigatório para operação de Importação');
                }
                if (isset($mvi->entrega) && $mvi->operacao === 'I') {
                    $complemento = isset($mvi->entrega->complemento) ? $mvi->entrega->complemento : '';

                    $txt .= 'TER'
                         . $mvi->entrega->cnpj
                         . mb_str_pad($mvi->entrega->razao_social, 70, ' ', STR_PAD_RIGHT)
                         . mb_str_pad($mvi->entrega->endereco, 70, ' ', STR_PAD_RIGHT)
                         . vsprintf('%s%s.%s%s%s-%s%s%s', str_split($mvi->entrega->cep))
                         . mb_str_pad($mvi->entrega->numero, 5, ' ', STR_PAD_RIGHT)
                         . mb_str_pad($complemento, 20, ' ', STR_PAD_RIGHT)
                         . mb_str_pad($mvi->entrega->bairro, 30, ' ', STR_PAD_RIGHT)
                         . $mvi->entrega->uf
                         . mb_str_pad($mvi->entrega->municipio, 7, ' ', STR_PAD_RIGHT)
                         . PHP_EOL;
                }

                if (!isset($mvi->adquirente) && $mvi->operacao === 'C') {
                    throw new \InvalidArgumentException(
                        'Campo "adquirente" obrigatório para operação de Importação por Conta e Ordem'
                    );
                }
                if (isset($mvi->adquirente) && $mvi->operacao === 'C') {
                    $complemento = isset($mvi->adquirente->complemento) ? $mvi->adquirente->complemento : '';

                    $txt .= 'ADQ'
                         . $mvi->adquirente->cnpj
                         . mb_str_pad($mvi->adquirente->razao_social, 70, ' ', STR_PAD_RIGHT)
                         . mb_str_pad($mvi->adquirente->endereco, 70, ' ', STR_PAD_RIGHT)
                         . vsprintf('%s%s.%s%s%s-%s%s%s', str_split($mvi->entrega->cep))
                         . mb_str_pad($mvi->adquirente->numero, 5, ' ', STR_PAD_RIGHT)
                         . mb_str_pad($complemento, 20, ' ', STR_PAD_RIGHT)
                         . mb_str_pad($mvi->adquirente->bairro, 30, ' ', STR_PAD_RIGHT)
                         . $mvi->adquirente->uf
                         . mb_str_pad($mvi->adquirente->municipio, 7, ' ', STR_PAD_RIGHT)
                         . PHP_EOL;
                }

                foreach ($mvi->nota_fiscal as $nf) {
                    $txt .= 'NF'
                         . mb_str_pad($nf->numero_nf, 10, '0', STR_PAD_LEFT)
                         . \DateTime::createFromFormat('Y-m-d', $nf->data_emissao)->format('d/m/Y')
                         . $nf->tipo_operacao
                         . PHP_EOL;
                }

                foreach ($mvi->produto as $produto) {
                    $tipo = $this->searchTipoProduto($this->std->demonstrativo_geral, $produto);
                    $txt .= Common::formatCodigoNcmProduto($produto->codigo_ncm, $tipo)
                        . (in_array($tipo, ['PR', 'RC']) ?
                            mb_str_pad($produto->concentracao, 3, '0', STR_PAD_LEFT) :
                            '   ')
                        . Common::formatDensidade($produto->densidade)
                        . Common::formatQuantidade($produto->quantidade)
                        . $produto->unidade
                        . PHP_EOL;
                }
            }
        }

        //TODO 3.1.5. Seção Utilização para Produção (UP)

        //TODO 3.1.6. Seção Utilização para Transformação (UT)

        if (isset($this->std->consumo)) {
            foreach ($this->std->consumo as $consumo) {
                $tipo = $this->searchTipoProduto($this->std->demonstrativo_geral, $consumo);

                if ($tipo === null) {
                    throw new \InvalidArgumentException(
                        'Produto ' . $consumo->codigo_ncm . ' não encontrado no demonstrativo geral'
                    );
                }

                if (! isset($consumo->concentracao) && in_array($tipo, ['PR', 'RC'])) {
                    throw new \InvalidArgumentException(
                        'Campo "concentracao" é obrigatório para produto/resíduo composto'
                    );
                }

                $txt .= 'UC'
                     .  Common::formatCodigoNcmProduto($consumo->codigo_ncm, $tipo)
                     .  (in_array($tipo, ['PR', 'RC']) ?
                        mb_str_pad($consumo->concentracao, 3, '0', STR_PAD_LEFT) :
                        '   ')
                     .  Common::formatDensidade($consumo->densidade)
                     .  Common::formatQuantidade($consumo->quantidade)
                     .  $consumo->unidade
                     .  $consumo->codigo_consumo
                     .  mb_str_pad(
                         isset($consumo->observacao_consumo) ? $consumo->observacao_consumo : '',
                         62,
                         ' ',
                         STR_PAD_RIGHT
                     )
                     . \DateTime::createFromFormat('Y-m-d', $consumo->data_consumo)->format('d/m/Y')
                     .  PHP_EOL
                ;
            }
        }

        //TODO 3.1.8. Seção Fabricação (FB)

        //TODO 3.1.9. Seção Transporte Nacional (TN)

        //TODO 3.1.10. Seção Transporte Internacional (TI)

        //TODO 3.1.11. Seção Armazenamento (AR)

        return trim($txt);
    }

    protected function searchTipoProduto(stdClass $demonstrativoGeral, stdClass $produto) : ?string
    {
        $tipo = null;
        if (isset($demonstrativoGeral->produto_controlado)) {
            foreach ($demonstrativoGeral->produto_controlado as $produtoDm) {
                if ($produto->codigo_ncm === $produtoDm->codigo_ncm &&
                    $produto->concentracao === $produtoDm->concentracao &&
                    $produto->densidade === $produtoDm->densidade
                ) {
                    $tipo = 'PR';
                    break;
                }
            }
        }

        if (isset($demonstrativoGeral->residuo_controlado) && $tipo === null) {
            foreach ($demonstrativoGeral->residuo_controlado as $residuo) {
                if ($produto->codigo_ncm === $residuo->codigo_ncm &&
                    $produto->concentracao === $residuo->concentracao &&
                    $produto->densidade === $residuo->densidade
                ) {
                    $tipo = 'RC';
                    break;
                }
            }
        }

        if (isset($demonstrativoGeral->produto_composto) && $tipo === null) {
            foreach ($demonstrativoGeral->produto_composto as $produtoDm) {
                if ($produto->codigo_ncm === $produtoDm->ncm && $produto->densidade === $produtoDm->densidade) {
                    $tipo = 'PC';
                    break;
                }
            }
        }

        if (isset($demonstrativoGeral->residuo_composto) && $tipo === null) {
            foreach ($demonstrativoGeral->residuo_composto as $residuo) {
                if ($produto->codigo_ncm === $residuo->ncm && $produto->densidade === $residuo->densidade) {
                    $tipo = 'RS';
                    break;
                }
            }
        }

        return $tipo;
    }

    /**
     * Converts $std to string
     *
     * @param stdClass $std
     *
     * @return string
     * @throws \Exception
     */
    public static function toString(StdClass $std) : string
    {
        $mapas = new Mapas($std);

        return $mapas->__toString();
    }
}
