<?php
/* =====================================================================
   includes/functions.php : fonctions communes à toutes les pages
   Chaque page commence par : require __DIR__ . '/includes/functions.php';
   (__DIR__ = le dossier du fichier courant : le chemin marche toujours)
   ===================================================================== */

/**
 * e() = "escape" : protège une valeur avant de l'afficher dans du HTML.
 * htmlspecialchars transforme < > & " ' en codes inoffensifs (&lt; &gt; ...).
 * Ainsi un nom saisi comme <script>alert(1)</script> s'affiche comme TEXTE
 * au lieu de s'exécuter (c'est la faille XSS). RÈGLE : tout ce qui vient
 * d'un utilisateur ou de données passe par e() avant d'être affiché.
 */
function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Renvoie le tableau des 5 événements (le fichier data/events.php fait un "return [...]"). */
function getEvents(): array
{
    return require __DIR__ . '/../data/events.php';
}

/** Cherche un événement par son id. Renvoie le tableau de l'événement, ou null s'il n'existe pas. */
function findEvent(int $id): ?array
{
    foreach (getEvents() as $event) {
        if ($event['id'] === $id) {
            return $event;
        }
    }
    return null;
}

/**
 * Lit un champ de $_POST ou de $_GET en toute sécurité et renvoie du texte sans espaces autour.
 * Pourquoi ne pas écrire simplement $_POST['name'] ?
 *  - le champ peut ne pas exister (requête fabriquée avec curl) -> on utilise ?? ''
 *  - le champ peut être un tableau (name[]=x) -> on ne garde que les textes
 */
function inputString(array $source, string $key): string
{
    $value = $source[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

/**
 * Prépare un texte pour une comparaison (équivalent PHP de normalize() du JavaScript) :
 * minuscules + accents retirés, pour que « echecs » trouve « Échecs ».
 */
function normalizeText(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');
    // Normalizer vient de l'extension "intl" : on l'utilise seulement si elle est installée
    if (class_exists('Normalizer')) {
        $decomposed = Normalizer::normalize($text, Normalizer::FORM_D);
        if ($decomposed !== false) {
            // \p{Mn} = les accents isolés, qu'on supprime
            $text = preg_replace('/\p{Mn}/u', '', $decomposed) ?? $text;
        }
    }
    return $text;
}

/** Liste des clubs SANS doublon (Plus : filtre généré à partir des événements). */
function getClubs(array $events): array
{
    // array_column extrait la colonne 'club', array_unique supprime les doublons,
    // array_values renumérote 0,1,2... proprement
    return array_values(array_unique(array_column($events, 'club')));
}

/**
 * Filtre le tableau d'événements (Défi "Recherche côté serveur").
 *  $query : texte cherché (vide = tout)
 *  $club  : nom du club exact (vide = tous)
 */
function filterEvents(array $events, string $query, string $club): array
{
    $needle = normalizeText($query);

    return array_values(array_filter($events, function (array $event) use ($needle, $club): bool {
        // Test du club
        if ($club !== '' && $event['club'] !== $club) {
            return false;
        }
        // Test du texte : on cherche dans tout ce qu'affiche la carte
        $haystack = normalizeText(
            $event['club'] . ' ' . $event['title'] . ' ' . $event['date_label'] . ' ' . $event['hour'] . ' ' . $event['place']
        );
        return $needle === '' || str_contains($haystack, $needle);
    }));
}

/** Trie les événements : 'places' = du plus grand au plus petit, sinon par date. */
function sortEvents(array $events, string $sort): array
{
    usort($events, function (array $a, array $b) use ($sort): int {
        if ($sort === 'places') {
            return $b['seats'] <=> $a['seats'];   // <=> : "opérateur vaisseau spatial" (-1, 0 ou 1)
        }
        return strcmp($a['date'], $b['date']);
    });
    return $events;
}

/**
 * Renvoie ' aria-current="page"' si la page est la page actuelle, sinon rien.
 * Utilisé dans header.php (Plus : lien de la page actuelle).
 */
function ariaCurrent(string $page, string $currentPage): string
{
    return $page === $currentPage ? ' aria-current="page"' : '';
}
