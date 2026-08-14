<?php
// test-pdf.php — Ouvre ce fichier directement dans le navigateur pour vérifier
// que la chaîne HTML → PDF fonctionne, sans avoir besoin du formulaire.
// (http://localhost/ocr-cin/test-pdf.php)

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/cv-template.php';

use Dompdf\Dompdf;

$cv = [
    'personnel' => [
        'nom' => 'RAKOTOMALALA',
        'prenom' => 'Voahangy',
        'titre_professionnel' => 'Vendeuse en boutique',
        'profil_court' => "Personne sérieuse, motivée et ponctuelle, à l'aise avec la clientèle et la tenue de caisse. À la recherche d'un poste de vente ou d'accueil client à Antananarivo.",
        'telephone' => '034 12 345 67',
        'email' => '',
        'ville' => 'Antananarivo',
        'permis_conduire' => false,
        'photo_url' => null,
    ],
    'experiences' => [
        [
            'poste' => 'Vendeuse',
            'employeur' => 'Boutique Miora',
            'lieu' => 'Antananarivo',
            'date_debut' => 'Mars 2024',
            'date_fin' => '',
            'poste_actuel' => true,
            'description' => "Accueil des clients, conseil et vente, tenue de la caisse, mise en rayon et gestion du stock.",
        ],
        [
            'poste' => 'Aide-vendeuse',
            'employeur' => 'Marché Analakely',
            'lieu' => 'Antananarivo',
            'date_debut' => 'Jan. 2022',
            'date_fin' => 'Fév. 2024',
            'poste_actuel' => false,
            'description' => "Vente de produits textiles, gestion de la caisse, fidélisation de la clientèle du quartier.",
        ],
    ],
    'formations' => [
        [
            'diplome' => 'BEPC',
            'etablissement' => 'Collège Andohalo',
            'ville' => 'Antananarivo',
            'annee_debut' => '',
            'annee_fin' => '2021',
            'description' => '',
        ],
    ],
    'competences' => [
        ['categorie' => 'technique', 'libelle' => 'Vente'],
        ['categorie' => 'technique', 'libelle' => 'Accueil client'],
        ['categorie' => 'technique', 'libelle' => 'Tenue de caisse'],
        ['categorie' => 'technique', 'libelle' => 'Gestion de stock'],
    ],
    'langues' => [
        ['libelle' => 'Malagasy', 'niveau' => 'Langue maternelle'],
        ['libelle' => 'Français', 'niveau' => 'Courant'],
    ],
    'complementaire' => [
        ['type' => 'disponibilite', 'libelle' => 'Disponible immédiatement'],
        ['type' => 'mobilite', 'libelle' => 'Mobile sur tout Antananarivo'],
        ['type' => 'interet', 'libelle' => 'Couture (loisir)'],
    ],
];

$html = genererCvHtml($cv);

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('CV_Test.pdf', ['Attachment' => false]); // false = s'ouvre dans le navigateur au lieu de forcer le téléchargement
