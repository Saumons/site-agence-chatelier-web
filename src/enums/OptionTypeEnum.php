<?php
namespace App\enums;

/**
 * Enumération des différents types de produit
 * sur lequel l'option' se porte
 */
enum OptionTypeEnum : string
{
    case webApp = 'webApp';
    case commerce = 'commerce';
    case vitrine = 'vitrine';
    case trans = 'transversal';

}
