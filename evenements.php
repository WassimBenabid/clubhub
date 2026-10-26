<?php
/* evenements.php : liste de tous les événements.
   Les cartes sont générées par une boucle PHP. Le filtre fonctionne de 2 façons :
    - SANS JavaScript : le formulaire envoie un GET (?q=...&club=...&sort=...) et PHP filtre le tableau ;
    - AVEC JavaScript : js/app.js filtre en direct, sans recharger la page. */
require __DIR__ . '/includes/functions.php';

$pageTitle = 'Événements · ClubHub';
$currentPage = 'evenements';

$allEvents = getEvents();

// 1) Lire les paramètres de l'URL ($_GET). inputString() gère le cas "paramètre absent".
$query = inputString($_GET, 'q');
$club = inputString($_GET, 'club');
$sort = inputString($_GET, 'sort');
if ($sort !== 'places') {
    $sort = 'date';          // valeur inconnue -> tri par défaut
}

// 2) Filtrer le tableau (filterEvents) puis trier tous les événements.
//    On garde la liste des id qui correspondent à la recherche : les autres cartes seront
//    quand même écrites dans la page mais avec l'attribut "hidden" (invisibles). Ainsi,
//    si JavaScript est actif et que l'utilisateur efface la recherche, js/app.js retrouve
//    toutes les cartes et peut les réafficher sans recharger la page.
$matching = filterEvents($allEvents, $query, $club);
$matchingIds = array_column($matching, 'id');
$events = sortEvents($allEvents, $sort);
$count = count($matching);

// 3) Liste des clubs sans doublon, pour le menu déroulant
$clubs = getClubs($allEvents);

require __DIR__ . '/includes/header.php';
?>

  <main>
    <h1>Tous les événements</h1>

    <!-- method="get" : les valeurs partent dans l'URL, ce qui permet de partager/recharger une recherche -->
    <form class="filters" role="search" action="evenements.php" method="get">
      <div class="filter-field">
        <label for="search">Rechercher</label>
        <input type="search" id="search" name="q" value="<?= e($query) ?>" placeholder="Arduino, photo, salle...">
      </div>
      <div class="filter-field">
        <label for="club-filter">Club</label>
        <select id="club-filter" name="club">
          <option value="">Tous les clubs</option>
          <?php foreach ($clubs as $clubName) : ?>
            <option value="<?= e($clubName) ?>"<?= $clubName === $club ? ' selected' : '' ?>><?= e($clubName) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="filter-field">
        <label for="sort">Trier par</label>
        <select id="sort" name="sort">
          <option value="date"<?= $sort === 'date' ? ' selected' : '' ?>>Date (la plus proche d'abord)</option>
          <option value="places"<?= $sort === 'places' ? ' selected' : '' ?>>Nombre de places (du plus grand au plus petit)</option>
        </select>
      </div>
      <div class="filter-field filter-check">
        <input type="checkbox" id="favorites-only" name="favorites-only">
        <label for="favorites-only">Seulement mes favoris</label>
      </div>
      <!-- Bouton utile surtout SANS JavaScript (avec JS, le filtre est instantané) -->
      <button type="submit">Filtrer</button>
    </form>

    <p class="results-count" aria-live="polite"><?= $count === 1 ? '1 événement affiché' : e($count) . ' événements affichés' ?></p>
    <p class="empty-message"<?= $count > 0 ? ' hidden' : '' ?>>Aucun événement ne correspond à votre recherche.</p>

    <div class="event-list" id="event-list">
      <?php foreach ($events as $event) : ?>
        <!-- data-club / data-date / data-places / data-id : relus par js/app.js pour filtrer et trier -->
        <article class="event-card"
                 data-id="<?= e($event['id']) ?>"
                 data-club="<?= e($event['club']) ?>"
                 data-date="<?= e($event['date']) ?>"
                 data-places="<?= e($event['seats']) ?>"<?= in_array($event['id'], $matchingIds, true) ? '' : ' hidden' ?>>
          <p class="club"><?= e($event['club']) ?></p>
          <h2><?= e($event['title']) ?></h2>
          <p class="meta"><time datetime="<?= e($event['date']) ?>"><?= e($event['date_label']) ?></time>, <?= e($event['hour']) ?></p>
          <p class="meta"><?= e($event['place']) ?></p>
          <p class="places"><strong><?= e($event['seats']) ?></strong> places</p>
          <a href="evenement.php?id=<?= e($event['id']) ?>">Voir le détail</a>
          <!-- Étoile favori : son état (★/☆) est géré par js/app.js -->
          <button type="button" class="favorite-btn" data-title="<?= e($event['title']) ?>" aria-pressed="false" aria-label="Ajouter aux favoris : <?= e($event['title']) ?>">☆</button>
        </article>
      <?php endforeach; ?>
    </div>

    <section>
      <h2>Récapitulatif</h2>
      <table>
        <thead>
          <tr><th scope="col">Événement</th><th scope="col">Date</th><th scope="col">Places</th></tr>
        </thead>
        <tbody>
          <?php foreach (sortEvents($matching, $sort) as $event) : ?>
            <tr>
              <td><?= e($event['title']) ?></td>
              <!-- substr : "2026-10-14" -> "10-14" ; on remet jour/mois -->
              <td><?= e(substr($event['date'], 8, 2) . '/' . substr($event['date'], 5, 2)) ?></td>
              <td><?= e($event['seats']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
