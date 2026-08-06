<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper de traduction monolingue (français).
 * Le site étant en français uniquement, t() renvoie le texte français
 * correspondant à la clé, ou la clé elle-même si elle est inconnue.
 */

if (!function_exists('t')) {
    function t($key)
    {
        static $translations = array(
            // Page Médecins
            'service_available_24h' => 'Service disponible 24h/24 et 7j/7',
            'our_doctors'           => 'Nos Médecins',
            'doctors_subtitle'      => 'Trouvez un médecin et prenez rendez-vous en ligne',
            'all_specialties'       => 'Toutes les spécialités',
            'general_practitioner'  => 'Médecin généraliste',
            'doctors_count'         => 'médecins disponibles',
            'available'             => 'Disponible',
            'unavailable'           => 'Indisponible',
            'years'                 => 'ans',
            'french'                => 'Français',
            'monday'                => 'Lundi',
            'tuesday'               => 'Mardi',
            'wednesday'             => 'Mercredi',
            'thursday'              => 'Jeudi',
            'friday'                => 'Vendredi',
            'saturday'              => 'Samedi',
            'sunday'                => 'Dimanche',
            'by_appointment'        => 'Sur rendez-vous',
            'today_at'              => "Aujourd'hui à",
            'reviews'               => 'avis',
            'experience'            => 'Expérience',
            'languages'             => 'Langues',
            'schedule'              => 'Horaires',
            'consultation_fee'      => 'Honoraires de consultation',
            'burundi_price'         => 'Tarif au Burundi',
            'details'               => 'Détails',
            'appointment'           => 'Rendez-vous',
            'doctor_profile'        => 'Profil du médecin',
            'verified'              => 'Vérifié',
            'information'           => 'Informations',
            'license'               => 'Licence',
            'day'                   => 'Jour',
            'start'                 => 'Début',
            'end'                   => 'Fin',
            'no_schedule'           => 'Aucun horaire disponible',
            'prices'                => 'Tarifs',
            'take_appointment'      => 'Prendre rendez-vous',
            'doctor_unavailable'    => 'Médecin indisponible pour le moment',
            'no_doctors'            => 'Aucun médecin disponible pour le moment. Revenez plus tard.',
            'why_24h'               => 'Consultations disponibles 24h/24, 7 jours sur 7.',
            'why_certified'         => 'Médecins certifiés',
            'why_certified_text'    => 'Des professionnels de santé diplômés et certifiés.',
            'why_affordable'        => 'Tarifs abordables',
            'why_affordable_text'   => 'Des consultations à des prix accessibles.',
            'why_confidential'      => 'Confidentialité totale',
            'why_confidential_text' => 'Vos informations médicales restent strictement privées.',

            // Divers
            'media_detail_title'    => 'Détail du média',
        );

        return isset($translations[$key]) ? $translations[$key] : $key;
    }
}