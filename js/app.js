/* =====================================================================
   ClubHub · TP3 : js/app.js
   ---------------------------------------------------------------------
   Ce fichier est chargé par TOUTES les pages (index.php, evenements.php,
   evenement.php).

   CHANGEMENT PAR RAPPORT À LA VERSION SANS PHP : les événements ne sont
   plus écrits dans ce fichier. PHP (data/events.php) génère déjà les
   cartes dans la page ; le JavaScript ne fait que les filtrer, les trier
   et gérer les favoris (amélioration progressive : sans JS, le site
   fonctionne quand même grâce à PHP). Chaque page n'a pas les mêmes éléments : on utilise donc
   des "gardes" ("if (!element) return;") pour que le code d'une page
   ne plante pas sur une autre page (sinon : erreur dans la console).

   Plan du fichier :
     0. Constantes et petits outils
     1. Mode sombre (toutes les pages)
     2. Page evenements.php  : filtres, tri, favoris (les cartes viennent de PHP)
     3. Page evenement.php   : validation du formulaire, compteur
     4. Démarrage
   ===================================================================== */

'use strict'; // Mode strict : JavaScript signale plus d'erreurs (variable non déclarée, etc.)


/* =====================================================================
   0. CONSTANTES ET PETITS OUTILS
   ===================================================================== */

// Noms des "clés" utilisées dans localStorage (la mémoire du navigateur).
// On les met dans des constantes pour ne pas faire de faute de frappe.
// Attention : le script du <head> de chaque page utilise la même clé de thème.
const THEME_KEY = 'clubhub-theme';
const FAVORITES_KEY = 'clubhub-favorites';

/**
 * Prépare un texte pour une comparaison :
 *  - normalize('NFD') sépare chaque lettre accentuée en "lettre + accent" (é -> e + ´)
 *  - le replace supprime ensuite les accents (plage Unicode \u0300 à \u036f)
 *  - toLowerCase() ignore les majuscules
 *  - trim() enlève les espaces au début et à la fin
 * Résultat : « ÉCHECS » et « echecs » deviennent identiques ("echecs").
 * (Défi "Accents")
 */
function normalize(text) {
  return text
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim();
}

/**
 * Lit une valeur dans localStorage sans jamais faire planter la page.
 * localStorage peut être indisponible (navigation privée, réglages du
 * navigateur) : dans ce cas il lance une erreur, que l'on attrape avec try/catch.
 */
function readStorage(key) {
  try {
    return localStorage.getItem(key);
  } catch (error) {
    return null;
  }
}

/** Écrit une valeur dans localStorage (même protection que ci-dessus). */
function writeStorage(key, value) {
  try {
    localStorage.setItem(key, value);
  } catch (error) {
    // On ignore : le site marche, mais le choix ne sera pas mémorisé.
  }
}


/* =====================================================================
   1. MODE SOMBRE (Plus, question 8)
   ===================================================================== */

/**
 * Applique un thème ('light' ou 'dark') :
 *  - change data-theme sur <html> : le CSS [data-theme="dark"] redéfinit les couleurs ;
 *  - met à jour le texte du bouton : il indique l'action possible, pas l'état actuel.
 */
function applyTheme(theme, button) {
  document.documentElement.dataset.theme = theme;
  button.textContent = theme === 'dark' ? 'Mode clair' : 'Mode sombre';
}

function initTheme() {
  const button = document.querySelector('.theme-toggle');
  if (button === null) {
    return; // Garde : pas de bouton sur cette page, rien à faire.
  }

  // Thème de départ : celui mémorisé (par défaut : clair).
  // Le script du <head> a déjà posé data-theme AVANT l'affichage ;
  // ici on remet simplement le texte du bouton en accord avec lui.
  const savedTheme = readStorage(THEME_KEY) === 'dark' ? 'dark' : 'light';
  applyTheme(savedTheme, button);

  // Au clic : on inverse le thème, on l'applique et on le mémorise.
  button.addEventListener('click', () => {
    const currentTheme = document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    applyTheme(newTheme, button);
    writeStorage(THEME_KEY, newTheme);
  });
}


/* =====================================================================
   2. PAGE evenements.php
   ===================================================================== */

/* ---------------------------------------------------------------------
   2.1 Les favoris (Défi "Favoris")
   --------------------------------------------------------------------- */

/**
 * Charge les favoris depuis localStorage.
 * localStorage ne stocke que du TEXTE : on y range la liste d'identifiants
 * sous forme de JSON (ex. '["photo","ia"]') et on la reconvertit avec JSON.parse.
 * On renvoie un Set (ensemble sans doublon, pratique avec .has / .add / .delete).
 */
function loadFavorites() {
  const raw = readStorage(FAVORITES_KEY);
  if (raw === null) {
    return new Set();
  }
  try {
    const list = JSON.parse(raw);
    return new Set(Array.isArray(list) ? list : []);
  } catch (error) {
    return new Set(); // contenu abîmé : on repart de zéro
  }
}

/** Enregistre les favoris : Set -> tableau -> texte JSON -> localStorage. */
function saveFavorites(favorites) {
  writeStorage(FAVORITES_KEY, JSON.stringify(Array.from(favorites)));
}

/* ---------------------------------------------------------------------
   2.2 L'étoile des favoris (le bouton est écrit par PHP, on gère seulement son état)
   --------------------------------------------------------------------- */

/**
 * Met à jour l'apparence ET l'accessibilité de l'étoile :
 *  ★ pleine = favori ; ☆ vide = pas favori.
 *  aria-pressed indique aux lecteurs d'écran s'il s'agit d'un bouton "enfoncé".
 */
function setStarState(star, isFavorite, title) {
  star.textContent = isFavorite ? '★' : '☆';
  star.setAttribute('aria-pressed', String(isFavorite));
  star.setAttribute(
    'aria-label',
    (isFavorite ? 'Retirer des favoris : ' : 'Ajouter aux favoris : ') + title
  );
}

/* ---------------------------------------------------------------------
   2.3 Initialisation de la page : filtres, tri, compteur
   --------------------------------------------------------------------- */
function initEventsPage() {
  // Garde : si #search n'existe pas, on n'est pas sur evenements.php.
  const searchInput = document.querySelector('#search');
  if (searchInput === null) {
    return;
  }

  const clubSelect = document.querySelector('#club-filter');
  const sortSelect = document.querySelector('#sort');
  const favoritesOnly = document.querySelector('#favorites-only');
  const filtersForm = document.querySelector('.filters');
  const resultsCount = document.querySelector('.results-count');
  const emptyMessage = document.querySelector('.empty-message');
  const list = document.querySelector('#event-list');

  const favorites = loadFavorites();

  // La liste des clubs et les cartes sont déjà dans la page : PHP les a générées.
  // On met seulement chaque étoile dans le bon état (★ si l'événement est en favori).
  list.querySelectorAll('.event-card').forEach((card) => {
    const star = card.querySelector('.favorite-btn');
    setStarState(star, favorites.has(card.dataset.id), star.dataset.title);
  });

  /**
   * CŒUR DE LA PAGE : (re)calcule ce qui doit s'afficher.
   * On l'appelle à chaque changement de recherche, de club, de tri ou de favoris.
   * Comme elle relit TOUS les filtres à chaque fois, la recherche, le club,
   * les favoris et le tri fonctionnent ensemble sans se contredire.
   */
  function updateList() {
    const searchText = normalize(searchInput.value);
    const selectedClub = clubSelect.value;           // '' = « Tous les clubs »
    const onlyFavorites = favoritesOnly.checked;

    // Array.from transforme la liste de cartes en vrai tableau (pour pouvoir le trier)
    const cards = Array.from(list.querySelectorAll('.event-card'));

    // 1) TRI (Plus, question 9). On trie toutes les cartes, visibles ou non,
    //    pour que l'ordre reste cohérent quand un filtre change.
    if (sortSelect.value === 'places') {
      // Nombre de places : du plus grand au plus petit (b - a = ordre décroissant)
      cards.sort((a, b) => Number(b.dataset.places) - Number(a.dataset.places));
    } else {
      // Date : le format AAAA-MM-JJ se compare correctement comme du texte
      cards.sort((a, b) => a.dataset.date.localeCompare(b.dataset.date));
    }
    // appendChild sur une carte déjà présente la DÉPLACE : cela réordonne la liste.
    cards.forEach((card) => list.appendChild(card));

    // 2) FILTRES : chaque carte est visible seulement si elle passe les 3 tests.
    let visibleCount = 0;
    cards.forEach((card) => {
      // Test 1 : le texte de la carte contient la recherche (sans accents ni majuscules)
      const matchesSearch = normalize(card.textContent).includes(searchText);
      // Test 2 : bon club (ou « Tous les clubs »)
      const matchesClub = selectedClub === '' || card.dataset.club === selectedClub;
      // Test 3 : favori, seulement si la case est cochée
      const matchesFavorite = !onlyFavorites || favorites.has(card.dataset.id);

      const isVisible = matchesSearch && matchesClub && matchesFavorite;
      card.hidden = !isVisible; // l'attribut hidden masque la carte (voir le CSS)
      if (isVisible) {
        visibleCount += 1;
      }
    });

    // 3) COMPTEUR : singulier ou pluriel
    resultsCount.textContent = visibleCount === 1
      ? '1 événement affiché'
      : visibleCount + ' événements affichés';

    // 4) MESSAGE « aucun résultat » : visible seulement s'il n'y a aucune carte
    emptyMessage.hidden = visibleCount > 0;
  }

  // --- Écouteurs d'événements --------------------------------------
  // 'input' : se déclenche à chaque frappe (recherche en direct)
  searchInput.addEventListener('input', updateList);
  // 'change' : se déclenche quand on choisit une autre option / coche la case
  clubSelect.addEventListener('change', updateList);
  sortSelect.addEventListener('change', updateList);
  favoritesOnly.addEventListener('change', updateList);

  // AVEC JavaScript, le filtre est instantané : on empêche l'envoi du formulaire GET
  // (sinon la page se rechargerait). SANS JavaScript, ce formulaire marche normalement
  // et c'est PHP qui filtre (evenements.php?q=arduino).
  filtersForm.addEventListener('submit', (event) => {
    event.preventDefault();
  });

  // Clic sur une étoile. "Délégation d'événement" : au lieu de mettre un écouteur
  // sur chaque étoile, on en met UN SEUL sur la liste. event.target indique
  // l'élément cliqué ; closest() remonte jusqu'au bouton étoile s'il y en a un.
  list.addEventListener('click', (event) => {
    const star = event.target.closest('.favorite-btn');
    if (star === null) {
      return; // on a cliqué ailleurs (lien, texte...)
    }
    const card = star.closest('.event-card');
    const id = card.dataset.id;

    if (favorites.has(id)) {
      favorites.delete(id);
    } else {
      favorites.add(id);
    }
    saveFavorites(favorites); // mémorisé dans localStorage

    // star.dataset.title = attribut data-title écrit par PHP (le titre de l'événement)
    setStarState(star, favorites.has(id), star.dataset.title);

    // Si « Seulement mes favoris » est coché, la carte doit disparaître tout de suite
    updateList();
  });

  // Premier affichage : applique les filtres/tri/compteur de départ
  updateList();
}


/* =====================================================================
   3. PAGE evenement.php : FORMULAIRE D'INSCRIPTION
   ===================================================================== */

// Même format d'email que celui utilisé couramment : quelquechose@quelquechose.extension
// ^[^\s@]+  : au moins un caractère sans espace ni @
// @         : un arobase
// [^\s@]+   : le domaine
// \.        : un point
// [^\s@]+$  : l'extension (fr, com...)
const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

function initRegistrationForm() {
  // Garde : on n'est pas sur la page d'inscription
  const form = document.querySelector('#registration-form');
  if (form === null) {
    return;
  }

  /* Pour chaque champ à valider on regroupe 3 choses :
       - input    : l'élément du formulaire
       - error    : le <small> où afficher le message
       - validate : une fonction qui renvoie un message d'erreur,
                    ou '' (texte vide) si le champ est valide.            */
  const fields = [
    {
      input: document.querySelector('#name'),
      error: document.querySelector('#name-error'),
      validate: () => {
        // trim() retire les espaces au début/à la fin : "  ab " ne compte que 2 caractères
        if (document.querySelector('#name').value.trim().length < 3) {
          return 'Le nom doit contenir au moins 3 caractères.';
        }
        return '';
      }
    },
    {
      input: document.querySelector('#email'),
      error: document.querySelector('#email-error'),
      validate: () => {
        const email = document.querySelector('#email').value.trim();
        if (email === '') {
          return 'L\'email est obligatoire.';
        }
        if (!EMAIL_REGEX.test(email)) {
          return 'L\'email n\'est pas au bon format (exemple : prenom.nom@campus.example).';
        }
        return '';
      }
    },
    {
      input: document.querySelector('#level'),
      error: document.querySelector('#level-error'),
      validate: () => {
        // L'option "Choisir..." a la valeur '' : tant qu'elle est choisie, c'est invalide
        if (document.querySelector('#level').value === '') {
          return 'Choisissez votre niveau d\'études.';
        }
        return '';
      }
    },
    {
      input: document.querySelector('#rules'),
      error: document.querySelector('#rules-error'),
      validate: () => {
        if (!document.querySelector('#rules').checked) {
          return 'Vous devez accepter le règlement du club.';
        }
        return '';
      }
    }
  ];

  /**
   * Valide UN champ et met l'affichage à jour. Renvoie true si le champ est valide.
   *  - invalide : message dans le <small>, classe is-invalid, aria-invalid="true"
   *  - valide   : on efface tout
   */
  function validateField(field) {
    const message = field.validate();

    if (message !== '') {
      field.error.textContent = message;               // textContent : jamais innerHTML
      field.input.classList.add('is-invalid');         // bordure rouge (CSS)
      field.input.setAttribute('aria-invalid', 'true'); // information pour les lecteurs d'écran
      return false;
    }

    field.error.textContent = '';
    field.input.classList.remove('is-invalid');
    field.input.removeAttribute('aria-invalid');
    return true;
  }

  // --- Validation à l'envoi (questions 4, 6 et 10) -------------------
  form.addEventListener('submit', (event) => {
    // Depuis que PHP traite le formulaire, le JavaScript ne bloque l'envoi QUE si
    // le formulaire est invalide (pour afficher les messages sans recharger la page).
    // S'il est valide, on laisse le navigateur l'envoyer au serveur.

    let firstInvalid = null;

    // On valide TOUS les champs (pas seulement le premier) pour afficher
    // toutes les erreurs d'un coup. On retient le premier champ invalide.
    fields.forEach((field) => {
      const isValid = validateField(field);
      if (!isValid && firstInvalid === null) {
        firstInvalid = field.input;
      }
    });

    if (firstInvalid !== null) {
      firstInvalid.focus(); // Accessibilité : le curseur va sur le premier champ à corriger
      event.preventDefault(); // formulaire invalide : on bloque l'envoi
      return;
    }

    // Formulaire valide : on ne fait PAS preventDefault() -> le navigateur envoie le formulaire en POST.
    // PHP le revalidera (on ne fait jamais confiance au navigateur) et affichera la confirmation.
  });

  // --- Correction en direct (question 5) -----------------------------
  // Dès que l'utilisateur modifie un champ DÉJÀ signalé invalide, on le revalide :
  // le message disparaît dès que la valeur est correcte, sans attendre un nouvel envoi.
  // 'input' couvre la saisie ; 'change' couvre la liste déroulante et la case à cocher.
  fields.forEach((field) => {
    ['input', 'change'].forEach((eventName) => {
      field.input.addEventListener(eventName, () => {
        if (field.input.classList.contains('is-invalid')) {
          validateField(field);
        }
      });
    });
  });

  // --- Compteur de caractères (Plus, question 7) ---------------------
  const motivation = document.querySelector('#motivation');
  const counter = document.querySelector('#motivation-count');

  function updateCounter() {
    const max = motivation.maxLength; // lit maxlength="300" dans le HTML : une seule source de vérité
    const length = motivation.value.length;

    counter.textContent = length + ' / ' + max;

    // À partir de 90 % de la limite (270 sur 300), on ajoute la classe d'alerte.
    // toggle(classe, condition) : ajoute si true, retire si false.
    counter.classList.toggle('is-near-limit', length >= max * 0.9);
  }

  motivation.addEventListener('input', updateCounter);
  updateCounter(); // affichage initial
}


/* =====================================================================
   4. DÉMARRAGE
   ---------------------------------------------------------------------
   Le <script> est placé à la fin du <body> : au moment où il s'exécute,
   tous les éléments HTML existent déjà. On peut donc lancer directement
   les initialisations. Chacune ignore la page si ses éléments n'y sont pas.
   ===================================================================== */
initTheme();
initEventsPage();
initRegistrationForm();
