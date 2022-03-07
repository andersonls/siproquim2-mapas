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
                     .  str_pad($produto->codigo_ncm, 13, ' ', STR_PAD_RIGHT)
                     .  str_pad($produto->nome_comercial, 70, ' ', STR_PAD_RIGHT)
                     .  str_pad($produto->concentracao, 3, '0', STR_PAD_LEFT)
                     .  Common::formatDensidade($produto->densidade)
                     .  PHP_EOL
                ;
            }
        }

        if (isset($this->std->demonstrativo_geral->produto_composto)) {
            foreach ($this->std->demonstrativo_geral->produto_composto as $produto) {
                $txt .= 'PC'
                    .  vsprintf('%s%s%s%s.%s%s.%s%s', str_split($produto->ncm))
                    .  str_pad(ucfirst(strtolower($produto->nome_comercial)), 70, ' ', STR_PAD_RIGHT)
                    .  Common::formatDensidade($produto->densidade)
                    .  PHP_EOL
                ;

                foreach ($produto->substancia_controlada as $substancia) {
                    $txt .= 'SC'
                         .  str_pad($produto->codigo_ncm, 13, ' ', STR_PAD_RIGHT)
                         .  str_pad($substancia->concentracao, 2, '0', STR_PAD_LEFT)
                         .  PHP_EOL
                    ;
                }
            }
        }

        if (isset($this->std->demonstrativo_geral->residuo_controlado)) {
            foreach ($this->std->demonstrativo_geral->residuo_controlado as $residuo) {
                $txt .= 'RC'
                     .  str_pad($produto->codigo_ncm, 13, ' ', STR_PAD_RIGHT)
                     .  str_pad($residuo->nome_comercial, 70, ' ', STR_PAD_RIGHT)
                     .  str_pad($residuo->concentracao, 3, '0', STR_PAD_LEFT)
                     .  Common::formatDensidade($residuo->densidade)
                     .  PHP_EOL
                ;
            }
        }

        if (isset($this->std->demonstrativo_geral->residuo_composto)) {
            foreach ($this->std->demonstrativo_geral->residuo_composto as $residuo) {
                $txt .= 'RS'
                    .  vsprintf('%s%s%s%s.%s%s.%s%s', str_split($residuo->ncm))
                    .  str_pad(ucfirst(strtolower($residuo->nome_comercial)), 70, ' ', STR_PAD_RIGHT)
                    .  Common::formatDensidade($residuo->densidade)
                    .  PHP_EOL
                ;

                foreach ($residuo->substancia_controlada as $substancia) {
                    $txt .= 'RB'
                         .  str_pad($produto->codigo_ncm, 13, ' ', STR_PAD_RIGHT)
                         .  str_pad($substancia->concentracao, 2, '0', STR_PAD_LEFT)
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
                 .  str_pad($movimentacao->razao_social, 69, ' ', STR_PAD_RIGHT)
                 .  str_pad($movimentacao->nota_fiscal, 10, '0', STR_PAD_LEFT)
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
                            str_pad($movimento->concentracao, 3, '0', STR_PAD_LEFT) :
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
                     .  str_pad($razaoSocial, 70, ' ', STR_PAD_RIGHT)
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
                    .  str_pad(ucfirst(strtolower($movimentacao->armazenadora->razao_social)), 70, ' ', STR_PAD_RIGHT)
                    .  str_pad(ucfirst(strtolower($movimentacao->armazenadora->endereco)), 70, ' ', STR_PAD_RIGHT)
                    .  vsprintf('%s%s.%s%s%s-%s%s%s', str_split($movimentacao->armazenadora->cep))
                    .  str_pad($movimentacao->armazenadora->cep, 5, ' ', STR_PAD_RIGHT)
                    .  str_pad($movimentacao->armazenadora->complemento, 20, ' ', STR_PAD_RIGHT)
                    .  str_pad($movimentacao->armazenadora->bairro, 30, ' ', STR_PAD_RIGHT)
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

        //TODO 3.1.4. Seção Movimentação Internacional de Produtos Químicos (MVI)

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
                        str_pad($consumo->concentracao, 3, '0', STR_PAD_LEFT) :
                        '   ')
                     .  Common::formatDensidade($consumo->densidade)
                     .  Common::formatQuantidade($consumo->quantidade)
                     .  $consumo->unidade
                     .  $consumo->codigo_consumo
                     .  str_pad(
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

        return $txt;
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
