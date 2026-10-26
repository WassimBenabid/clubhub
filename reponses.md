# reponses.md : défis de l'atelier S04

## Défi 1 : contourner le JavaScript

**Test 1 : JavaScript désactivé (F12, Ctrl+Shift+P, « Disable JavaScript »), formulaire vide envoyé.**
Le navigateur envoie quand même le formulaire (il a `novalidate`, et sans JS plus rien ne le bloque).
Le serveur PHP revalide : la page est réaffichée avec les 4 messages d'erreur (nom, email, niveau,
règlement) et aucune confirmation « Merci ». Le serveur a donc bien refusé.

**Test 2 : `curl -X POST -d "name=A&email=faux" "http://localhost:8000/evenement.php?id=1"`**
Il n'y a ni navigateur ni JavaScript dans cette requête. PHP reçoit `name=A` et `email=faux` et
renvoie du HTML où l'on trouve les erreurs : nom trop court, email au mauvais format, niveau absent
(le champ n'a pas été envoyé, `?? ''` évite une erreur) et règlement non accepté. Pas de message
« Merci » : la requête est refusée.

**Conclusion :** la validation JavaScript sert au confort de l'utilisateur (réponse immédiate),
mais elle ne protège rien : n'importe qui peut la contourner. La validation du serveur est
obligatoire.

## Défi 2 : recharger la page après l'envoi (F5)

Après une inscription réussie, la page affichée est le résultat d'une requête POST. En appuyant sur
F5, le navigateur demande de confirmer le renvoi du formulaire (« Confirmer le renvoi du formulaire »).

C'est un problème pour un vrai site : si l'utilisateur confirme, la même inscription est envoyée une
deuxième fois. Avec une base de données, on obtiendrait des doublons (deux inscriptions, deux emails
de confirmation, deux commandes payées...).

La solution classique s'appelle POST/Redirect/GET : après un POST réussi, le serveur ne renvoie pas
la page, il redirige (`header('Location: ...')`) vers une page de confirmation en GET. F5 recharge
alors seulement cette page de confirmation, sans renvoyer le formulaire.

## Défi 3 : recherche côté serveur

`evenements.php?q=arduino` fonctionne sans JavaScript : le formulaire de filtre envoie un GET et PHP
filtre le tableau avec `filterEvents()` (recherche insensible aux majuscules et aux accents, si
l'extension PHP « intl » est installée). Les cartes qui ne correspondent pas reçoivent l'attribut
`hidden`, et le compteur est calculé par PHP.

**Configuration nécessaire :** cette recherche utilise des fonctions des extensions PHP `mbstring`
(`mb_strtolower()`, `mb_strlen()`) et `intl` (`Normalizer`, qui retire les accents). Elles doivent être
activées dans `php.ini` (`extension=mbstring` et `extension=intl`, sans le `;` devant). Sans `mbstring`,
la page plante avec « Call to undefined function mb_strtolower() » (erreur rencontrée pendant le test).
Sans `intl`, la page marche mais la recherche ne gère plus les accents : « echecs » ne trouve pas
« Échecs ». Après toute modification de `php.ini`, il faut redémarrer le serveur `php -S`.
