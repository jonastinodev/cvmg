<?php
// cv-template.php — Point d'entrée unique du rendu du CV. Choisit le gabarit
// (personnel.modele en base) et lui délègue le rendu complet. Utilisé à la
// fois pour l'aperçu dans le navigateur et pour la génération PDF (même
// gabarit, même rendu garanti dans les deux cas).
//
// Structure attendue de $cv :
// [
//   'modele' => 'classique'|'bleu-marine'|'olive'|'ocre',  // facultatif, 'classique' par défaut
//   'personnel' => ['nom', 'prenom', 'titre_professionnel', 'profil_court',
//                    'telephone', 'email', 'ville', 'adresse', 'date_naissance',
//                    'permis_conduire', 'photo_url'],
//   'experiences'    => [['poste','employeur','lieu','date_debut','date_fin','poste_actuel','description'], ...],
//   'formations'     => [['diplome','etablissement','ville','annee_debut','annee_fin','description'], ...],
//   'competences'    => [['categorie' => 'technique'|'qualite', 'libelle'], ...],
//   'langues'        => [['libelle','niveau'], ...],
//   'complementaire' => [['type','libelle'], ...],
// ]
//
// Ajouter un gabarit : créer templates/<id>.php avec une fonction
// genererCv<Nom>(array $cv): string, puis l'ajouter à MODELES_CV ci-dessous.

const MODELES_CV = [
    'classique'   => ['libelle' => 'Classique',    'fonction' => 'genererCvClassique'],
    'bleu-marine' => ['libelle' => 'Bleu marine',  'fonction' => 'genererCvBleuMarine'],
    'olive'       => ['libelle' => 'Olive',        'fonction' => 'genererCvOlive'],
    'ocre'        => ['libelle' => 'Ocre',         'fonction' => 'genererCvOcre'],
];

foreach (array_keys(MODELES_CV) as $id) {
    require_once __DIR__ . '/templates/' . $id . '.php';
}

function e(?string $valeur): string {
    return htmlspecialchars($valeur ?? '', ENT_QUOTES, 'UTF-8');
}

function initiales(string $nom, string $prenom): string {
    $i1 = mb_substr(trim($prenom), 0, 1);
    $i2 = mb_substr(trim($nom), 0, 1);
    return mb_strtoupper($i1 . $i2);
}

// Convertit un niveau de langue (liste fermée NIVEAUX_LANGUE côté formulaire)
// en largeur de barre. Basé sur un choix déjà fait par l'utilisateur — pas de
// pourcentage inventé pour des compétences qui n'ont pas de niveau chiffré.
function niveauVersPourcentage(string $niveau): int {
    return match ($niveau) {
        'Débutant' => 20,
        'Intermédiaire' => 40,
        'Avancé' => 65,
        'Courant' => 85,
        'Langue maternelle' => 100,
        default => 50,
    };
}

// Étiquette d'affichage pour chaque type de complementaire[] (miroir exact des
// types produits par construireDonneesCV() dans creer-cv.php : disponibilite,
// mobilite, benevolat, projet, certification, reference, interet).
function libelleTypeComplementaire(string $type): string {
    return match ($type) {
        'disponibilite'  => 'Disponibilité',
        'mobilite'       => 'Mobilité',
        'benevolat'      => 'Bénévolat',
        'projet'         => 'Projet',
        'certification'  => 'Certification',
        'reference'      => 'Référence',
        'interet'        => 'Centre d\'intérêt',
        default          => '',
    };
}

// Sépare complementaire[] en centres d'intérêt (type=interet) et le reste
// (disponibilité, mobilité, bénévolat, projets, certifications, références) :
// ce sont des informations de nature différente, elles ne doivent jamais être
// affichées mélangées sous « Centres d'intérêt ».
function separerComplementaire(array $complementaire): array {
    $interets = array_values(array_filter($complementaire, fn($c) => ($c['type'] ?? '') === 'interet'));
    $autres = array_values(array_filter($complementaire, fn($c) => ($c['type'] ?? '') !== 'interet'));
    return [$interets, $autres];
}

function genererCvHtml(array $cv): string {
    $id = $cv['modele'] ?? 'classique';
    $fonction = MODELES_CV[$id]['fonction'] ?? MODELES_CV['classique']['fonction'];
    return $fonction($cv);
}
