<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once __DIR__ . '/../bootstrap.php';

$std = new \stdClass();
$std->cnpj = '12345678000100';
$std->data = '2022-02';

$std->demonstrativo_geral = new \stdClass();
$std->demonstrativo_geral->produto_controlado[0] = new \stdClass();
$std->demonstrativo_geral->produto_controlado[0]->codigo_ncm = 'TPN12881067';
$std->demonstrativo_geral->produto_controlado[0]->nome_comercial = 'Acido Sulfúrico';
$std->demonstrativo_geral->produto_controlado[0]->concentracao = 1;
$std->demonstrativo_geral->produto_controlado[0]->densidade = 1.1;

$std->demonstrativo_geral->produto_composto[0] = new \stdClass();
$std->demonstrativo_geral->produto_composto[0]->ncm = '12345678';
$std->demonstrativo_geral->produto_composto[0]->nome_comercial = 'Químico Composto';
$std->demonstrativo_geral->produto_composto[0]->densidade = 5.5;
$std->demonstrativo_geral->produto_composto[0]->substancia_controlada[0] = new \stdClass();
$std->demonstrativo_geral->produto_composto[0]->substancia_controlada[0]->codigo_ncm = 'TPN12961074';
$std->demonstrativo_geral->produto_composto[0]->substancia_controlada[0]->concentracao = 55;

$std->demonstrativo_geral->residuo_controlado[0] = new \stdClass();
$std->demonstrativo_geral->residuo_controlado[0]->codigo_ncm = 'TPN12881067';
$std->demonstrativo_geral->residuo_controlado[0]->nome_comercial = 'Residuo de Acido Sulfúrico';
$std->demonstrativo_geral->residuo_controlado[0]->concentracao = 10;
$std->demonstrativo_geral->residuo_controlado[0]->densidade = 66.6;

$std->demonstrativo_geral->residuo_composto[0] = new \stdClass();
$std->demonstrativo_geral->residuo_composto[0]->ncm = '12345678';
$std->demonstrativo_geral->residuo_composto[0]->nome_comercial = 'Resíduo Composto';
$std->demonstrativo_geral->residuo_composto[0]->densidade = 6.66;
$std->demonstrativo_geral->residuo_composto[0]->substancia_controlada[0] = new \stdClass();
$std->demonstrativo_geral->residuo_composto[0]->substancia_controlada[0]->codigo_ncm = 'TPN12961074';
$std->demonstrativo_geral->residuo_composto[0]->substancia_controlada[0]->concentracao = 66;

$std->movimentacao_nacional[0] = new \stdClass();
$std->movimentacao_nacional[0]->entrada_saida = 'E';
$std->movimentacao_nacional[0]->operacao = 'EC';
$std->movimentacao_nacional[0]->cnpj = '99999999999999';
$std->movimentacao_nacional[0]->razao_social = 'Fornecedor de Químicos';
$std->movimentacao_nacional[0]->nota_fiscal = 123;
$std->movimentacao_nacional[0]->data_emissao_nf = '2022-02-01';
$std->movimentacao_nacional[0]->armazenagem = 'N';
$std->movimentacao_nacional[0]->transporte = 'F';

$std->movimentacao_nacional[0]->movimento[0] = new \stdClass();
$std->movimentacao_nacional[0]->movimento[0]->codigo_ncm = 'TPN12881067';
$std->movimentacao_nacional[0]->movimento[0]->concentracao = 1;
$std->movimentacao_nacional[0]->movimento[0]->densidade = 1.1;
$std->movimentacao_nacional[0]->movimento[0]->quantidade = 100;
$std->movimentacao_nacional[0]->movimento[0]->unidade = 'L';

$std->movimentacao_nacional[0]->movimento[1] = new \stdClass();
$std->movimentacao_nacional[0]->movimento[1]->codigo_ncm = '12345678';
$std->movimentacao_nacional[0]->movimento[1]->densidade = 5.5;
$std->movimentacao_nacional[0]->movimento[1]->quantidade = 500;
$std->movimentacao_nacional[0]->movimento[1]->unidade = 'K';

$std->movimentacao_nacional[0]->transportadora = new \stdClass();
$std->movimentacao_nacional[0]->transportadora->cnpj = '98765432109800';
$std->movimentacao_nacional[0]->transportadora->razao_social = 'Transportadora de Químicos';

$std->movimentacao_nacional[0]->armazenadora = new \stdClass();
$std->movimentacao_nacional[0]->armazenadora->cnpj = '43210989876543';
$std->movimentacao_nacional[0]->armazenadora->razao_social = 'Armazém dos Químicos';
$std->movimentacao_nacional[0]->armazenadora->endereco = 'Rua do Armazém';
$std->movimentacao_nacional[0]->armazenadora->cep = '88270000';
$std->movimentacao_nacional[0]->armazenadora->numero = 'S/N';
$std->movimentacao_nacional[0]->armazenadora->complemento = 'Galpão 01';
$std->movimentacao_nacional[0]->armazenadora->bairro = 'Bairro dos Armazéns';
$std->movimentacao_nacional[0]->armazenadora->uf = 'SC';
$std->movimentacao_nacional[0]->armazenadora->municipio = '4211504';

$std->consumo[0] = new \stdClass();
$std->consumo[0]->codigo_ncm = 'TPN12881067';
$std->consumo[0]->concentracao = 1;
$std->consumo[0]->densidade = 1.1;
$std->consumo[0]->quantidade = 100;
$std->consumo[0]->unidade = 'L';
$std->consumo[0]->codigo_consumo = 4;
$std->consumo[0]->observacao_consumo = 'Produto consumido no processo ABCD'; //Opcional
$std->consumo[0]->data_consumo = '2022-02-15';

$std->consumo[1] = new \stdClass();
$std->consumo[1]->codigo_ncm = 'TPN12881067';
$std->consumo[1]->concentracao = 1;
$std->consumo[1]->densidade = 1.1;
$std->consumo[1]->quantidade = 100;
$std->consumo[1]->unidade = 'L';
$std->consumo[1]->codigo_consumo = 4;
$std->consumo[1]->data_consumo = '2022-02-20';

try {
    $mapas = new \Siproquim\Mapas($std);

    echo $mapas;
} catch (Exception $e) {
    echo $e->getMessage();
}
