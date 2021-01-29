<?php

function isWorkingDay($timestamp) {

    // Dimanche(0) ou Samedi(6)
    $day = date('w', $timestamp);
    if ($day == 0 || $day == 6) {
        return false;
    }

    $jour = date('d', $timestamp);
    $mois = date('m', $timestamp);
    $annee = date('Y', $timestamp);

    if ($jour == 1 && $mois == 1) {
        return false;
    } // 1er janvier

    if ($jour == 1 && $mois == 5) {
        return false;
    } // 1er mai

    if ($jour == 8 && $mois == 5) {
        return false;
    } // 8 mai

    if ($jour == 14 && $mois == 7) {
        return false;
    } // 14 juillet

    if ($jour == 15 && $mois == 8) {
        return false;
    } // 15 aout

    if ($jour == 1 && $mois == 11) {
        return false;
    } // 1er novembre

    if ($jour == 11 && $mois == 11) {
        return false;
    } // 11 novembre

    if ($jour == 25 && $mois == 12) {
        return false;
    } // 25 décembre


    // Pâques
    $date_paques = easter_date($annee);
    $jour_paques = date('d', $date_paques);
    $mois_paques = date('m', $date_paques);
    if ($jour_paques == $jour && $mois_paques == $mois) {
        return false;
    }

    // Ascension
    $date_ascension = mktime(date("H", $date_paques), date("i", $date_paques), date("s", $date_paques), date("m", $date_paques), date("d", $date_paques) + 39, date("Y", $date_paques));
    if (date('d', $date_ascension) == $jour && date('m', $date_ascension) == $mois) {
        return false;
    }

    // Pentecote
    $date_pentecote = mktime(date("H", $date_paques), date("i", $date_paques), date("s", $date_paques), date("m", $date_paques), date("d", $date_paques) + 50, date("Y", $date_paques));
    if (date('d', $date_pentecote) == $jour && date('m', $date_pentecote) == $mois) {
        return false;
    }

    return true;
}
