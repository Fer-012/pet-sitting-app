<?php

namespace App\Entity\Enum;

enum Statut: string
{
    case EN_ATTENTE = 'En attente';
    case CONFIRME = 'Confirmé';
    case ENREGISTRE = 'Enregistré';

    case ANNULE = 'Annulé';
}
