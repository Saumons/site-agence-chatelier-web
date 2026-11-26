<?php
namespace App\enums;

/**
 * Enumération des différents types de produit
 * sur lequel le devis se porte
 */
enum DevisFormTypeEnum : string
{
    case webApp = 'webApp';
    case commerce = 'commerce';
    case vitrine = 'vitrine';

}
