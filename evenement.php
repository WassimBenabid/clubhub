<?php
/* =====================================================================
   evenement.php : détail d'un événement + formulaire d'inscription
   ---------------------------------------------------------------------
   Adresse : evenement.php?id=3
   Cette page fait 3 choses :
     A. lire l'id dans l'URL et trouver l'événement (sinon erreur 404) ;
     B. traiter le formulaire s'il vient d'être envoyé (POST) et le VALIDER côté serveur ;
     C. afficher la page (HTML).
   La validation du JavaScript est pratique, mais elle n'est PAS une protection :
   on peut la désactiver ou envoyer une requête avec curl. Seul le serveur décide.
   ===================================================================== */
require __DIR__ . '/includes/functions.php';

$currentPage = 'evenement';

/* ---------------------------------------------------------------------
   A. Lire l'id et trouver l'événement
   --------------------------------------------------------------------- */
$idText = inputString($_GET, 'id');   // '' si absent
$event = null;

// ctype_digit : vrai seulement si le texte est fait uniquement de chiffres ("abc", "-1", "2.5" refusés)
if ($idText !== '' && ctype_digit($idText)) {
    $event = findEvent((int) $idText);
}

// Id absent, pas un nombre ou inconnu -> code HTTP 404 + message clair + lien vers la liste
if ($event === null) {
    http_response_code(404);   // doit être appelé AVANT d'écrire du HTML
    $pageTitle = 'Événement introuvable · ClubHub';
    require __DIR__ . '/includes/header.php';
    ?>
  <main>
    <section class="event-detail">
      <h1>Événement introuvable</h1>
      <p>Désolé, l'événement demandé n'existe pas ou l'adresse est incorrecte.</p>
      <p><a class="button" href="evenements.php">Voir tous les événements</a></p>
    </section>
  </main>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;   // on s'arrête ici : le reste de la page ne doit pas s'exécuter
}

/* ---------------------------------------------------------------------
   B. Traitement du formulaire
   --------------------------------------------------------------------- */
const MOTIVATION_MAX = 300;

// Valeurs affichées dans le formulaire (vides au premier affichage)
$values = [
    'name' => '',
    'email' => '',
    'level' => '',
    'experience' => 'no',   // "Non" coché par défaut, comme dans le HTML d'origine
    'motivation' => '',
    'rules' => '',
];
$errors = [];        // un message par champ invalide : ['name' => '...', ...]
$success = false;    // passe à true quand l'inscription est valide

// Le formulaire a-t-il été envoyé ? (la page est aussi ouverte en GET, simplement pour la lire)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Récupérer chaque champ : inputString() = $_POST[...] ?? '' + trim()
    $values['name'] = inputString($_POST, 'name');
    $values['email'] = inputString($_POST, 'email');
    $values['level'] = inputString($_POST, 'level');
    $values['experience'] = inputString($_POST, 'experience') === 'yes' ? 'yes' : 'no';
    $values['motivation'] = inputString($_POST, 'motivation');
    $values['rules'] = inputString($_POST, 'rules');

    // 2) Valider : mêmes règles qu'en JavaScript
    // mb_strlen compte les caractères (et pas les octets) : "é" vaut 1 et non 2
    if (mb_strlen($values['name'], 'UTF-8') < 3) {
        $errors['name'] = 'Le nom doit contenir au moins 3 caractères.';
    }

    if ($values['email'] === '') {
        $errors['email'] = "L'email est obligatoire.";
    } elseif (filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = "L'email n'est pas au bon format (exemple : prenom.nom@campus.example).";
    }

    // in_array(..., true) : comparaison stricte, le niveau doit être exactement '1', '2' ou '3'
    if (!in_array($values['level'], ['1', '2', '3'], true)) {
        $errors['level'] = "Choisissez votre niveau d'études.";
    }

    if ($values['rules'] !== '1') {
        $errors['rules'] = 'Vous devez accepter le règlement du club.';
    }

    // Plus : motivation limitée à 300 caractères aussi côté serveur
    if (mb_strlen($values['motivation'], 'UTF-8') > MOTIVATION_MAX) {
        $errors['motivation'] = 'La motivation ne doit pas dépasser ' . MOTIVATION_MAX . ' caractères.';
    }

    // 3) Aucun message d'erreur -> inscription valide
    //    (Il n'y a pas encore de base de données : on n'enregistre rien pour l'instant.)
    $success = count($errors) === 0;
}

/* ---------------------------------------------------------------------
   C. Affichage
   --------------------------------------------------------------------- */
$pageTitle = $event['title'] . ' · ClubHub';
require __DIR__ . '/includes/header.php';

?>

  <main class="event-page">
    <article class="event-detail">
      <p class="club"><?= e($event['club']) ?></p>
      <h1><?= e($event['title']) ?></h1>
      <ul class="facts">
        <li><strong>Date :</strong> <time datetime="<?= e($event['date']) ?>"><?= e($event['date_label']) ?></time>, <?= e($event['hour']) ?></li>
        <li><strong>Lieu :</strong> <?= e($event['place']) ?></li>
        <li><strong>Places :</strong> <?= e($event['seats']) ?></li>
      </ul>

      <p><?= e($event['description']) ?></p>

      <h2>Au programme</h2>
      <ul>
        <?php foreach ($event['program'] as $item) : ?>
          <li><?= e($item) ?></li>
        <?php endforeach; ?>
      </ul>

      <h2>À apporter</h2>
      <ul>
        <?php foreach ($event['bring'] as $item) : ?>
          <li><?= e($item) ?></li>
        <?php endforeach; ?>
      </ul>
    </article>

    <section class="registration">
      <h2>S'inscrire</h2>

      <?php if ($success) : ?>
        <!-- Inscription valide : le message REMPLACE le formulaire -->
        <p class="form-success" role="status">Merci <?= e($values['name']) ?> ! Votre inscription à <?= e($event['title']) ?> est bien reçue.</p>
        <p><a href="evenements.php">Retour à la liste des événements</a></p>
      <?php else : ?>
        <!-- action : le formulaire s'envoie à cette même page (avec le même id), en POST.
             novalidate : ce sont nos messages (JS et PHP) qui s'affichent, pas ceux du navigateur. -->
        <form id="registration-form" action="evenement.php?id=<?= e($event['id']) ?>" method="post" novalidate>
          <p>
            <label for="name">Nom complet</label>
            <input type="text" id="name" name="name" required minlength="3" autocomplete="name" aria-describedby="name-error"
                   value="<?= e($values['name']) ?>"<?= isset($errors['name']) ? ' class="is-invalid" aria-invalid="true"' : '' ?>>
            <small class="field-error" id="name-error"><?= e($errors['name'] ?? '') ?></small>
          </p>
          <p>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required autocomplete="email" placeholder="prenom.nom@campus.example" aria-describedby="email-error"
                   value="<?= e($values['email']) ?>"<?= isset($errors['email']) ? ' class="is-invalid" aria-invalid="true"' : '' ?>>
            <small class="field-error" id="email-error"><?= e($errors['email'] ?? '') ?></small>
          </p>
          <p>
            <label for="level">Niveau d'études</label>
            <select id="level" name="level" required aria-describedby="level-error"<?= isset($errors['level']) ? ' class="is-invalid" aria-invalid="true"' : '' ?>>
              <option value="">Choisir...</option>
              <!-- Plus : le niveau choisi reste sélectionné quand le formulaire est réaffiché -->
              <option value="1"<?= $values['level'] === '1' ? ' selected' : '' ?>>1re année</option>
              <option value="2"<?= $values['level'] === '2' ? ' selected' : '' ?>>2e année</option>
              <option value="3"<?= $values['level'] === '3' ? ' selected' : '' ?>>3e année</option>
            </select>
            <small class="field-error" id="level-error"><?= e($errors['level'] ?? '') ?></small>
          </p>
          <fieldset>
            <legend>Avez vous déjà utilisé un Arduino ?</legend>
            <!-- Plus : la réponse oui / non reste cochée -->
            <label><input type="radio" name="experience" value="yes"<?= $values['experience'] === 'yes' ? ' checked' : '' ?>> Oui</label>
            <label><input type="radio" name="experience" value="no"<?= $values['experience'] === 'no' ? ' checked' : '' ?>> Non</label>
          </fieldset>
          <p>
            <label for="motivation">Motivation (facultatif)</label>
            <!-- Entre les balises <textarea> : pas d'espace ni de retour à la ligne, sinon ils seraient dans la valeur -->
            <textarea id="motivation" name="motivation" rows="4" maxlength="300" aria-describedby="motivation-count motivation-error"<?= isset($errors['motivation']) ? ' class="is-invalid" aria-invalid="true"' : '' ?>><?= e($values['motivation']) ?></textarea>
            <small class="counter" id="motivation-count"><?= e(mb_strlen($values['motivation'], 'UTF-8')) ?> / 300</small>
            <small class="field-error" id="motivation-error"><?= e($errors['motivation'] ?? '') ?></small>
          </p>
          <p>
            <label><input type="checkbox" id="rules" name="rules" value="1" required aria-describedby="rules-error"<?= $values['rules'] === '1' ? ' checked' : '' ?><?= isset($errors['rules']) ? ' class="is-invalid" aria-invalid="true"' : '' ?>> J'accepte le règlement du club</label>
            <small class="field-error" id="rules-error"><?= e($errors['rules'] ?? '') ?></small>
          </p>
          <p>
            <button type="submit">S'inscrire</button>
          </p>
        </form>
      <?php endif; ?>
    </section>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
