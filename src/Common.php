<?php
/**
 * @author    Anderson Luiz Silvério <andersonlsilverio@gmail.com>
 * @author    Emerson Casas Salvador <salvaemerson@gmail.com>
 *
 * @license   http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3
 * @copyright 2022 Digicart Indústria e Com de Circuitos Impressos LTDA
 */

namespace Siproquim;

class Common
{
    /**
     * Formats a float to XX,XX
     *
     * @param float $densidade
     *
     * @return string
     */
    public static function formatDensidade(float $densidade) : string
    {
        $retorno = str_pad(number_format($densidade, 2, ',', '.'), 5, '0', STR_PAD_LEFT);

        return $retorno;
    }

    public static function getMonthAbbreviation(int $month) : string
    {
        switch ($month) {
            case 1:
                $monthAbbrev = 'JAN';
                break;
            case 2:
                $monthAbbrev = 'FEV';
                break;
            case 3:
                $monthAbbrev = 'MAR';
                break;
            case 4:
                $monthAbbrev = 'ABR';
                break;
            case 5:
                $monthAbbrev = 'MAI';
                break;
            case 6:
                $monthAbbrev = 'JUN';
                break;
            case 7:
                $monthAbbrev = 'JUL';
                break;
            case 8:
                $monthAbbrev = 'AGO';
                break;
            case 9:
                $monthAbbrev = 'SET';
                break;
            case 10:
                $monthAbbrev = 'OUT';
                break;
            case 11:
                $monthAbbrev = 'NOV';
                break;
            case 12:
                $monthAbbrev = 'DEZ';
                break;
            default:
                throw new \InvalidArgumentException('Month must be an integer between 1 and 12');
        }

        return $monthAbbrev;
    }

    public static function formatCodigoNcmProduto(string $codigo, string $tipo) : string
    {
        $retorno = $tipo;
        $retorno .= (substr($codigo, 0, 3) === 'TPN' ?  $codigo : vsprintf('%s%s%s%s.%s%s.%s%s', str_split($codigo)));

        return str_pad($retorno, 13, ' ', STR_PAD_RIGHT);
    }

    public static function formatQuantidade(float $quantidade) : string
    {
        $retorno = number_format($quantidade, 3, ',', '.');

        return str_pad($retorno, 15, ' ', STR_PAD_LEFT);
    }
}
