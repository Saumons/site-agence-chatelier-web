<?php

namespace App\enums;

/**
 * Enumération des différents statuts du
 * devis
 */
enum DevisFormStatutEnum : string
{
    case aTraiter = "À traiter";
    case enCours = "En cours";
    case accepte = "Accepté";
    case annule = "Annulé";

}
