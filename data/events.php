<?php
/* =====================================================================
   data/events.php : les 5 événements de ClubHub
   ---------------------------------------------------------------------
   Ce fichier ne contient QUE des données : il "renvoie" (return) un
   tableau PHP. Chaque événement est lui-même un tableau associatif
   (clé => valeur). C'est le même contenu que l'ancien tableau EVENTS
   du JavaScript, mais il vit maintenant côté SERVEUR.
   Plus tard, ce fichier sera remplacé par une base de données :
   le reste du site n'aura presque rien à changer.

   Clés d'un événement :
     id          numéro unique (utilisé dans l'URL : evenement.php?id=1)
     club        nom du club
     title       titre
     date        date au format AAAA-MM-JJ (se trie facilement)
     date_label  date lisible à afficher
     hour        heure
     place       lieu
     seats       nombre de places
     description texte de présentation
     program     liste "Au programme"
     bring       liste "À apporter"
   ===================================================================== */

return [
    [
        'id' => 1,
        'club' => 'Club Robotique',
        'title' => 'Atelier Arduino pour débutants',
        'date' => '2026-10-14',
        'date_label' => 'mercredi 14 octobre',
        'hour' => '14 h',
        'place' => 'Salle B204',
        'seats' => 20,
        'description' => "Vous n'avez jamais touché un Arduino ? Cet atelier est fait pour vous. En trois heures, vous allez câbler vos premiers composants, écrire vos premiers programmes et repartir avec un petit projet qui fonctionne. Les cartes et les composants sont fournis par le club.",
        'program' => [
            'Découvrir la carte Arduino Uno',
            'Faire clignoter une LED',
            'Lire un capteur de température',
            'Mini projet : un thermomètre à LED',
        ],
        'bring' => [
            'Un ordinateur portable',
            'Un câble USB (si vous en avez un)',
        ],
    ],
    [
        'id' => 2,
        'club' => 'Club Photo',
        'title' => 'Sortie photo en ville',
        'date' => '2026-10-17',
        'date_label' => 'samedi 17 octobre',
        'hour' => '9 h',
        'place' => 'Entrée principale',
        'seats' => 15,
        'description' => "Une matinée pour photographier la ville avec le regard du club : lumière du matin, architecture et scènes de rue. Tous les niveaux sont les bienvenus, les plus expérimentés aident les débutants.",
        'program' => [
            'Rendez-vous et conseils de cadrage',
            'Parcours photo en petits groupes',
            'Pause café et partage des premières images',
        ],
        'bring' => [
            'Un appareil photo ou un smartphone',
            'Des chaussures confortables',
        ],
    ],
    [
        'id' => 3,
        'club' => 'Club IA',
        'title' => "Conférence : l'IA dans l'industrie",
        'date' => '2026-10-21',
        'date_label' => 'mercredi 21 octobre',
        'hour' => '10 h',
        'place' => 'Amphithéâtre A',
        'seats' => 120,
        'description' => "Des ingénieurs viennent présenter comment l'intelligence artificielle est utilisée dans l'industrie : maintenance prédictive, contrôle qualité, optimisation de la production. La conférence est suivie d'un temps de questions.",
        'program' => [
            'Introduction par le Club IA',
            "Trois retours d'expérience d'entreprises",
            'Table ronde et questions de la salle',
        ],
        'bring' => [
            'De quoi prendre des notes',
            'Votre curiosité',
        ],
    ],
    [
        'id' => 4,
        'club' => 'Club Échecs',
        'title' => 'Tournoi blitz inter promo',
        'date' => '2026-10-24',
        'date_label' => 'samedi 24 octobre',
        'hour' => '15 h',
        'place' => 'Foyer des étudiants',
        'seats' => 32,
        'description' => "Un tournoi de parties rapides (blitz) entre les promotions. Chaque joueur dispose de cinq minutes par partie. Les pendules et les échiquiers sont fournis, l'ambiance est conviviale.",
        'program' => [
            'Accueil et tirage au sort',
            'Rondes qualificatives',
            'Phase finale et remise des prix',
        ],
        'bring' => [
            'Votre carte étudiante',
            'Votre bonne humeur',
        ],
    ],
    [
        'id' => 5,
        'club' => 'Club Entrepreneuriat',
        'title' => 'Hackathon impact social',
        'date' => '2026-10-31',
        'date_label' => 'samedi 31 octobre',
        'hour' => '9 h',
        'place' => 'Salle C101',
        'seats' => 40,
        'description' => "Une journée pour imaginer, en équipe, un projet qui répond à un problème social concret. Des mentors du club vous accompagnent, et chaque équipe présente son idée en fin de journée.",
        'program' => [
            'Formation des équipes',
            'Travail sur le projet avec les mentors',
            'Présentations devant le jury',
        ],
        'bring' => [
            'Un ordinateur portable',
            'Des idées',
        ],
    ],
];
