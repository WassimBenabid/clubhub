<?php
/* index.php : page d'accueil. Même contenu que l'ancien index.html,
   mais les 2 événements viennent des données (boucle foreach). */
require __DIR__ . '/includes/functions.php';

$pageTitle = 'ClubHub : les événements des clubs étudiants';
$currentPage = 'index';
$footerImage = 'evenement.jpg';   // image du pied de page (comme au TP1)

// array_slice($tableau, 0, 2) : garde seulement les 2 premiers éléments
$upcoming = array_slice(getEvents(), 0, 2);

require __DIR__ . '/includes/header.php';
?>

  <main>
    <section class="hero">
      <h1>Tous les événements des clubs, au même endroit</h1>
      <p>Ateliers, conférences, sorties, tournois : découvrez ce que font les clubs étudiants et réservez votre place en quelques secondes.</p>
      <a class="button" href="evenements.php">Voir les événements</a>
    </section>

    <section>
      <h2>Prochains événements</h2>
      <div class="event-list">
        <?php foreach ($upcoming as $event) : ?>
          <article class="event-card">
            <p class="club"><?= e($event['club']) ?></p>
            <h3><?= e($event['title']) ?></h3>
            <p class="meta"><time datetime="<?= e($event['date']) ?>"><?= e($event['date_label']) ?></time>, <?= e($event['hour']) ?> · <?= e($event['place']) ?></p>
            <a href="evenement.php?id=<?= e($event['id']) ?>">Voir le détail</a>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
