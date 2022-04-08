<?php
/**
 * @author    Anderson Luiz Silvério <andersonlsilverio@gmail.com>
 * @author    Emerson Casas Salvador <salvaemerson@gmail.com>
 *
 * @license   http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3
 * @copyright 2022 Digicart Indústria e Com de Circuitos Impressos LTDA
 */

namespace Siproquim\Tests;

use PHPUnit\Framework\TestCase;
use Siproquim\Mapas;
use StdClass;

class MapasTest extends TestCase
{
    /**
     * @var StdClass
     */
    protected $std;

    public function testConstructorWithoutHeaderShouldThrowException()
    {
        $this->std = new StdClass();

        $this->expectException(\InvalidArgumentException::class);
        new Mapas($this->std);
    }

    public function testConstructorWithoutDemonstrativoGeralShouldThrowException()
    {
        $this->generateHeaderStd();

        $this->expectException(\InvalidArgumentException::class);
        new Mapas($this->std);
    }

    public function testConstructorWithoutMovimentacaoShouldThrowException()
    {
        $this->generateHeaderStd();
        $this->generateDemonstrativoGeralStd();

        $this->expectException(\InvalidArgumentException::class);
        new Mapas($this->std);
    }

    public function testConstructor()
    {
        $this->generateHeaderStd();
        $this->generateDemonstrativoGeralStd();
        $this->generateMovimentacaoStd();
        $this->generateConsumoStd();

        $this->assertInstanceOf(Mapas::class, new Mapas($this->std));
    }

    public function testToString()
    {
        $this->generateHeaderStd();
        $this->generateDemonstrativoGeralStd();
        $this->generateMovimentacaoStd();
        $this->generateMovimentacaoInternacionalStd();
        $this->generateConsumoStd();

        $txt = Mapas::toString($this->std);

        $this->assertEquals(trim(file_get_contents(__DIR__ . '/fixtures/mapas.txt')), $txt);
    }

    public function testToStringSemSecaoDemonstrativoGeral()
    {
        $this->generateHeaderStd();
        $this->generateMovimentacaoStd();
        $this->generateMovimentacaoInternacionalStd();
        $this->generateConsumoStd();

        $txt = Mapas::toString($this->std);

        $this->assertEquals(trim(file_get_contents(__DIR__ . '/fixtures/mapas_sem_demonstrativo_geral.txt')), $txt);
    }

    protected function generateHeaderStd() : StdClass
    {
        $this->std = new stdClass();
        $this->std->cnpj = '12345678000100';
        $this->std->data = '2022-02';
        $this->std->atividades = new \StdClass();
        $this->std->atividades->comercializacao_nacional = 'S';
        $this->std->atividades->comercializacao_internacional = 'S';
        $this->std->atividades->producao = 'N';
        $this->std->atividades->consumo = 'S';

        return $this->std;
    }

    protected function generateDemonstrativoGeralStd() : StdClass
    {
        $this->std->demonstrativo_geral = new stdClass();
        $this->std->demonstrativo_geral->produto_controlado[0] = new \stdClass();
        $this->std->demonstrativo_geral->produto_controlado[0]->codigo_ncm = 'TPN12881067';
        $this->std->demonstrativo_geral->produto_controlado[0]->nome_comercial = 'Acido Sulfúrico';
        $this->std->demonstrativo_geral->produto_controlado[0]->concentracao = 1;
        $this->std->demonstrativo_geral->produto_controlado[0]->densidade = 1.1;

        $this->std->demonstrativo_geral->produto_composto[0] = new \stdClass();
        $this->std->demonstrativo_geral->produto_composto[0]->ncm = '12345678';
        $this->std->demonstrativo_geral->produto_composto[0]->nome_comercial = 'Químico Composto';
        $this->std->demonstrativo_geral->produto_composto[0]->densidade = 5.5;
        $this->std->demonstrativo_geral->produto_composto[0]->substancia_controlada[0] = new \stdClass();
        $this->std->demonstrativo_geral->produto_composto[0]->substancia_controlada[0]->codigo_ncm = 'TPN12961074';
        $this->std->demonstrativo_geral->produto_composto[0]->substancia_controlada[0]->concentracao = 55;

        $this->std->demonstrativo_geral->residuo_controlado[0] = new \stdClass();
        $this->std->demonstrativo_geral->residuo_controlado[0]->codigo_ncm = 'TPN12881067';
        $this->std->demonstrativo_geral->residuo_controlado[0]->nome_comercial = 'Residuo de Acido Sulfúrico';
        $this->std->demonstrativo_geral->residuo_controlado[0]->concentracao = 10;
        $this->std->demonstrativo_geral->residuo_controlado[0]->densidade = 66.6;

        $this->std->demonstrativo_geral->residuo_composto[0] = new \stdClass();
        $this->std->demonstrativo_geral->residuo_composto[0]->ncm = '12345678';
        $this->std->demonstrativo_geral->residuo_composto[0]->nome_comercial = 'Resíduo Composto';
        $this->std->demonstrativo_geral->residuo_composto[0]->densidade = 6.66;
        $this->std->demonstrativo_geral->residuo_composto[0]->substancia_controlada[0] = new \stdClass();
        $this->std->demonstrativo_geral->residuo_composto[0]->substancia_controlada[0]->codigo_ncm = 'TPN12961074';
        $this->std->demonstrativo_geral->residuo_composto[0]->substancia_controlada[0]->concentracao = 66;

        return $this->std;
    }

    protected function generateMovimentacaoStd() : StdClass
    {
        $this->std->movimentacao_nacional[0] = new \stdClass();
        $this->std->movimentacao_nacional[0]->entrada_saida = 'E';
        $this->std->movimentacao_nacional[0]->operacao = 'EC';
        $this->std->movimentacao_nacional[0]->cnpj = '99999999999999';
        $this->std->movimentacao_nacional[0]->razao_social = 'Fornecedor de Químicos';
        $this->std->movimentacao_nacional[0]->nota_fiscal = 123;
        $this->std->movimentacao_nacional[0]->data_emissao_nf = '2022-02-01';
        $this->std->movimentacao_nacional[0]->armazenagem = 'N';
        $this->std->movimentacao_nacional[0]->transporte = 'F';

        $this->std->movimentacao_nacional[0]->movimento[0] = new \stdClass();
        $this->std->movimentacao_nacional[0]->movimento[0]->tipo = 'PR';
        $this->std->movimentacao_nacional[0]->movimento[0]->codigo_ncm = 'TPN12881067';
        $this->std->movimentacao_nacional[0]->movimento[0]->concentracao = 1;
        $this->std->movimentacao_nacional[0]->movimento[0]->densidade = 1.1;
        $this->std->movimentacao_nacional[0]->movimento[0]->quantidade = 100;
        $this->std->movimentacao_nacional[0]->movimento[0]->unidade = 'L';

        $this->std->movimentacao_nacional[0]->movimento[1] = new \stdClass();
        $this->std->movimentacao_nacional[0]->movimento[1]->tipo = 'PC';
        $this->std->movimentacao_nacional[0]->movimento[1]->codigo_ncm = '12345678';
        $this->std->movimentacao_nacional[0]->movimento[1]->densidade = 5.5;
        $this->std->movimentacao_nacional[0]->movimento[1]->quantidade = 500;
        $this->std->movimentacao_nacional[0]->movimento[1]->unidade = 'K';

        $this->std->movimentacao_nacional[0]->transportadora = new \stdClass();
        $this->std->movimentacao_nacional[0]->transportadora->cnpj = '98765432109800';
        $this->std->movimentacao_nacional[0]->transportadora->razao_social = 'Transportadora de Químicos';

        $this->std->movimentacao_nacional[0]->armazenadora = new \stdClass();
        $this->std->movimentacao_nacional[0]->armazenadora->cnpj = '43210989876543';
        $this->std->movimentacao_nacional[0]->armazenadora->razao_social = 'Armazém dos Químicos';
        $this->std->movimentacao_nacional[0]->armazenadora->endereco = 'Rua do Armazém';
        $this->std->movimentacao_nacional[0]->armazenadora->cep = '88270000';
        $this->std->movimentacao_nacional[0]->armazenadora->numero = 'S/N';
        $this->std->movimentacao_nacional[0]->armazenadora->complemento = 'Galpão 01';
        $this->std->movimentacao_nacional[0]->armazenadora->bairro = 'Bairro dos Armazéns';
        $this->std->movimentacao_nacional[0]->armazenadora->uf = 'SC';
        $this->std->movimentacao_nacional[0]->armazenadora->municipio = '4211504';

        return $this->std;
    }

    public function generateMovimentacaoInternacionalStd()
    {
        $this->std->movimentacao_internacional[0] = new \stdClass();
        $this->std->movimentacao_internacional[0]->operacao = 'C';
        $this->std->movimentacao_internacional[0]->pais = '24';
        $this->std->movimentacao_internacional[0]->razao_social = 'Razao social internacional';
        $this->std->movimentacao_internacional[0]->numero_li = '6756756756';
        $this->std->movimentacao_internacional[0]->data_restricao_embarque = '2017-01-02';
        $this->std->movimentacao_internacional[0]->data_conhecimento_embarque = '2017-01-02';
        $this->std->movimentacao_internacional[0]->data_conhecimento_embarque = '2017-01-02';
        $this->std->movimentacao_internacional[0]->numero_due = '19BR012345678-9';
        $this->std->movimentacao_internacional[0]->data_due = '2017-01-02';
        $this->std->movimentacao_internacional[0]->numero_di = '9999999999';
        $this->std->movimentacao_internacional[0]->data_di = '2017-01-02';
        $this->std->movimentacao_internacional[0]->responsavel_armazenagem = 'T';
        $this->std->movimentacao_internacional[0]->responsavel_transporte = 'T';
        $this->std->movimentacao_internacional[0]->local_entrega = 'I';

        $this->std->movimentacao_internacional[0]->transporte = new \stdClass();
        $this->std->movimentacao_internacional[0]->transporte->cnpj = '01337593184567';
        $this->std->movimentacao_internacional[0]->transporte->razao_social = 'razão social transporte';

        $this->std->movimentacao_internacional[0]->armazenagem = new \stdClass();
        $this->std->movimentacao_internacional[0]->armazenagem->cnpj = '01337593184567';
        $this->std->movimentacao_internacional[0]->armazenagem->razao_social = 'razão social armazenagem';
        $this->std->movimentacao_internacional[0]->armazenagem->endereco = 'Rua das armazenagens';
        $this->std->movimentacao_internacional[0]->armazenagem->cep = '88050050';
        $this->std->movimentacao_internacional[0]->armazenagem->numero = '1234';
        $this->std->movimentacao_internacional[0]->armazenagem->bairro = 'Bairro dos galpões';
        $this->std->movimentacao_internacional[0]->armazenagem->uf = 'SC';
        $this->std->movimentacao_internacional[0]->armazenagem->municipio = '4211504';

        $this->std->movimentacao_internacional[0]->entrega = new \stdClass();
        $this->std->movimentacao_internacional[0]->entrega->cnpj = '01337593184567';
        $this->std->movimentacao_internacional[0]->entrega->razao_social = 'razão social entregador';
        $this->std->movimentacao_internacional[0]->entrega->endereco = 'Rua dos entregadores';
        $this->std->movimentacao_internacional[0]->entrega->cep = '88050050';
        $this->std->movimentacao_internacional[0]->entrega->numero = '1234';
        $this->std->movimentacao_internacional[0]->entrega->bairro = 'Bairro do correio';
        $this->std->movimentacao_internacional[0]->entrega->uf = 'SC';
        $this->std->movimentacao_internacional[0]->entrega->municipio = '4211504';

        $this->std->movimentacao_internacional[0]->adquirente = new \stdClass();
        $this->std->movimentacao_internacional[0]->adquirente->cnpj = '01337593184567';
        $this->std->movimentacao_internacional[0]->adquirente->razao_social = 'razão social adquirente';
        $this->std->movimentacao_internacional[0]->adquirente->endereco = 'Rua dos compradores';
        $this->std->movimentacao_internacional[0]->adquirente->cep = '88050050';
        $this->std->movimentacao_internacional[0]->adquirente->numero = '1234';
        $this->std->movimentacao_internacional[0]->adquirente->bairro = 'Bairro das compras';
        $this->std->movimentacao_internacional[0]->adquirente->uf = 'SC';
        $this->std->movimentacao_internacional[0]->adquirente->municipio = '4211504';

        $this->std->movimentacao_internacional[0]->nota_fiscal[0] = new \stdClass();
        $this->std->movimentacao_internacional[0]->nota_fiscal[0]->numero_nf = '1010201010';
        $this->std->movimentacao_internacional[0]->nota_fiscal[0]->data_emissao = '2017-01-11';
        $this->std->movimentacao_internacional[0]->nota_fiscal[0]->tipo_operacao = 'E';

        $this->std->movimentacao_internacional[0]->nota_fiscal[1] = new \stdClass();
        $this->std->movimentacao_internacional[0]->nota_fiscal[1]->numero_nf = '1210201010';
        $this->std->movimentacao_internacional[0]->nota_fiscal[1]->data_emissao = '2017-01-11';
        $this->std->movimentacao_internacional[0]->nota_fiscal[1]->tipo_operacao = 'S';

        $this->std->movimentacao_internacional[0]->produto[0] = new \stdClass();
        $this->std->movimentacao_internacional[0]->produto[0]->tipo = 'PR';
        $this->std->movimentacao_internacional[0]->produto[0]->codigo_ncm = 'TPN12881067';
        $this->std->movimentacao_internacional[0]->produto[0]->concentracao = 1;
        $this->std->movimentacao_internacional[0]->produto[0]->densidade = 1.1;
        $this->std->movimentacao_internacional[0]->produto[0]->quantidade = 100;
        $this->std->movimentacao_internacional[0]->produto[0]->unidade = 'L';
    }

    public function generateConsumoStd() : StdClass
    {
        $this->std->consumo[0] = new \stdClass();
        $this->std->consumo[0]->tipo = 'PR';
        $this->std->consumo[0]->codigo_ncm = 'TPN12881067';
        $this->std->consumo[0]->concentracao = 1;
        $this->std->consumo[0]->densidade = 1.1;
        $this->std->consumo[0]->quantidade = 100;
        $this->std->consumo[0]->unidade = 'L';
        $this->std->consumo[0]->codigo_consumo = 4;
        $this->std->consumo[0]->observacao_consumo = 'Produto consumido no processo ABCD'; //Opcional
        $this->std->consumo[0]->data_consumo = '2022-02-15';

        $this->std->consumo[1] = new \stdClass();
        $this->std->consumo[1]->tipo = 'PR';
        $this->std->consumo[1]->codigo_ncm = 'TPN12881067';
        $this->std->consumo[1]->concentracao = 1;
        $this->std->consumo[1]->densidade = 1.1;
        $this->std->consumo[1]->quantidade = 100;
        $this->std->consumo[1]->unidade = 'L';
        $this->std->consumo[1]->codigo_consumo = 4;
        $this->std->consumo[1]->data_consumo = '2022-02-20';

        return $this->std;
    }
}
