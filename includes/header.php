<?php
/* =====================================================================
   includes/header.php : du <!DOCTYPE html> jusqu'à la fin du <header>
   ---------------------------------------------------------------------
   Chaque page fait, AVANT de l'inclure :
       $pageTitle   = 'Événements · ClubHub';   // titre de l'onglet
       $currentPage = 'evenements';              // 'index', 'evenements' ou 'evenement'
       require __DIR__ . '/includes/header.php';
   Les variables de la page sont visibles ici car require "copie-colle" le fichier.
   ===================================================================== */
$pageTitle = $pageTitle ?? 'ClubHub';       // valeur par défaut si la page l'oublie
$currentPage = $currentPage ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <link rel="stylesheet" href="css/style.css">
  <!-- Défi "Pas de flash" : appliqué AVANT l'affichage de la page (voir README) -->
  <script>
    try {
      const themeMemorise = localStorage.getItem('clubhub-theme');
      if (themeMemorise === 'dark') {
        document.documentElement.dataset.theme = 'dark';
      }
    } catch (erreur) {
      /* localStorage bloqué : on ignore */
    }
  </script>
</head>
<body>
  <header class="site-header">
    <a class="logo" href="index.php">ClubHub</a>
    <nav>
      <ul>
        <!-- ariaCurrent() ajoute aria-current="page" sur le lien de la page actuelle -->
        <li><a href="index.php"<?= ariaCurrent('index', $currentPage) ?>>Accueil</a></li>
        <li><a href="evenements.php"<?= ariaCurrent('evenements', $currentPage) ?>>Événements</a></li>
        <li><a href="evenement.php?id=1"<?= ariaCurrent('evenement', $currentPage) ?>>S'inscrire</a></li>
      </ul>
    </nav>
    <button type="button" class="theme-toggle">Mode sombre</button>
  </header>
