## TP3 : ClubHub interactif

### Ce qui a été fait
- **Base** : recherche, filtre par club, compteur (singulier/pluriel) et message « aucun résultat » sur `evenements.html` ; validation du formulaire (nom, email, niveau, règlement), messages d'erreur, correction en direct et message de succès sur `evenement.html`.
- **Plus** : compteur de caractères (classe `is-near-limit` à 90 %), mode sombre mémorisé dans `localStorage`, tri par date ou par places, accessibilité (focus sur le premier champ invalide, `aria-invalid`).
- **Défis choisis** : Favoris, Cartes générées, Accents, Pas de flash.

### Modifications du HTML et du CSS (et pourquoi)
- Le starter du TP3 n'était pas fourni : j'ai donc ajouté moi-même les éléments décrits dans l'énoncé (`.theme-toggle`, `.filters`, `.results-count`, `.empty-message`, les `<small class="field-error">`, `#motivation-count`, `.form-success`) et les classes CSS associées.
- `evenement.html` : `novalidate` sur le formulaire, pour que le navigateur n'affiche pas ses propres bulles et que mes messages s'affichent à la place. `maxlength="300"` sur la motivation.
- `evenements.html` : les 5 cartes ont été supprimées du HTML (défi « Cartes générées »), elles sont créées par `js/app.js`. Le tableau récapitulatif reste écrit à la main.
- `style.css` : nouvelles variables (`--entete`, `--texte-clair`, `--pied`...) car certaines couleurs étaient écrites en dur ou réutilisaient `--blanc` pour du texte, ce qui empêchait le mode sombre. Règle `.event-card[hidden] { display: none; }` : sans elle, le `display: flex` des cartes annule l'attribut `hidden`.

### Défis
- **Favoris** : étoile sur chaque carte, identifiants des favoris stockés en JSON dans `localStorage`, case « Seulement mes favoris ».
- **Cartes générées** : tableau `EVENTS` + `createElement`. C'est une étape vers le serveur : plus tard, la liste viendra du serveur (base de données) et le code qui construit les cartes restera le même.
- **Accents** : fonction `normalize()` avec `normalize('NFD')` qui retire les accents ; « echecs » trouve « Échecs ».
- **Pas de flash** : `app.js` est chargé en fin de `<body>`, donc exécuté *après* que le navigateur a commencé à afficher la page : elle s'affiche d'abord en clair, puis passe en sombre. Correction : un petit script dans le `<head>` de chaque page, qui lit `localStorage` et applique `data-theme` *avant* le premier affichage (le `<head>` est lu avant le `<body>`).

### Une erreur de console rencontrée
`Uncaught TypeError: Cannot read properties of null (reading 'addEventListener')`.
Cause : `app.js` est chargé par toutes les pages, mais sur `index.html` les éléments `#search` ou `#registration-form` n'existent pas : `querySelector` renvoyait `null`.
Correction : une « garde » au début de chaque fonction (`if (element === null) { return; }`).
