<?php
/* bonjour.php : page de test. Si vous voyez la version de PHP et l'heure,
   le serveur PHP fonctionne. */
require __DIR__ . '/includes/functions.php'; // pour avoir la fonction e()
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Test PHP</title>
</head>
<body>
  <h1>Bonjour !</h1>
  <p>Version de PHP : <?= e(PHP_VERSION) ?></p>
  <p>Il est <?= e(date('H:i:s')) ?> (heure du serveur).</p>
</body>
</html>
