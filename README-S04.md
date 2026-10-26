## Wassim Benabid
## Atelier S04 : ClubHub en PHP
### Lancer le site
Dans le dossier du projet : `php -S localhost:8000`, puis ouvrir http://localhost:8000/index.php
Test d'installation : `bonjour.php`.

### Ce qui a été fait
- **Base** : `includes/functions.php` (`e()`, `getEvents()`, `findEvent()`), `includes/header.php` et `footer.php`, pages `index.php`, `evenements.php`, `evenement.php` générées par des boucles, page 404 pour un id absent/invalide/inconnu, traitement du formulaire en POST avec validation serveur, sortie échappée par `e()`. Les anciens `.html` sont supprimés.
- **Plus** : `aria-current` via `$currentPage`, clubs du filtre sans doublon (`array_unique`), motivation limitée à 300 caractères côté serveur, niveau et réponse oui/non conservés après une erreur.
- **Défis** : voir `reponses.md` (contournement du JS, F5 après l'envoi) et recherche côté serveur (`evenements.php?q=...`).

### Changements dans les fichiers du TP précédent (et pourquoi)
- `js/app.js` : les événements ne sont plus écrits en JavaScript (PHP génère les cartes) ; le JS ne fait que filtrer, trier et gérer les favoris. Le formulaire n'est bloqué que s'il est invalide ; s'il est valide, il part vers le serveur.
- `data/events.php` : les données des 5 événements (descriptions, programmes, « à apporter ») ont été reprises du HTML d'origine et complétées.
- `evenements.php` écrit toutes les cartes mais met `hidden` sur celles qui ne correspondent pas à `?q=`, afin que le JavaScript puisse les réafficher si on efface la recherche.
- Les dates de `<time datetime>` sont maintenant au format valide AAAA-MM-JJ.
