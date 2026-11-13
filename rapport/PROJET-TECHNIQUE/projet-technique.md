# Projet technique - Gestion de musique

Ce projet technique correspond à une application web de gestion d'une plateforme musicale. Son objectif est de permettre à un administrateur de gérer les genres musicaux, les artistes et les morceaux, tout en offrant une structure simple et évolutive pour le développement d'un projet plus complet.

La version actuelle sert de prototype CRUD pour valider la logique métier, l'architecture MVC légère et l'intégration front-end / back-end.

## Objectif du projet

Créer une application de gestion musicale permettant de :
- gérer les genres musicaux,
- gérer les artistes,
- gérer les morceaux musicaux,
- organiser les fichiers multimédias associés (images de couverture, fichiers audio),
- suivre le statut de publication d'un morceau (Draft / Published).

## Fonctionnalités clés

- **Gestion des genres :** ajouter, afficher, modifier et supprimer des genres musicaux.
- **Gestion des artistes :** ajouter des artistes avec leur nom et leur image de profil.
- **Gestion des morceaux :** enregistrer un titre, une couverture, un fichier audio, son statut et sa date de publication.
- **Association des données :** chaque morceau est lié à un artiste et à un genre.
- **Organisation du catalogue :** centraliser les musiques par genre et par artiste.

## Modèle de données principal

### Artistes
- `id` : identifiant unique
- `ArtistName` : nom de l'artiste
- `dateOfCreation` : date de création de l'enregistrement
- `ProfilePicture` : image de profil

### Genres
- `id` : identifiant unique
- `genreName` : nom du genre musical

### Songs / morceaux
- `id` : identifiant unique
- `SongTitle` : titre du morceau
- `dateOfCreation` : date d'ajout
- `ProfilePicture` : image de couverture
- `songFilePath` : chemin du fichier audio
- `status` : `Draft` ou `Published`
- `dateOfPublication` : date de publication
- `artist_id` : référence vers l'artiste
- `genre_id` : référence vers le genre

## Relations principales

- **Artist ↔ Songs :** relation One-to-Many (un artiste peut avoir plusieurs morceaux).
- **Genre ↔ Songs :** relation One-to-Many (un genre contient plusieurs morceaux).
- **Song :** appartient à un seul artiste et à un seul genre.

## Structure actuelle du projet

### Frontend
- HTML
- CSS / Tailwind CSS
- JavaScript

### Backend
- PHP
- Contrôleurs
- Services
- DAO / accès aux données
- API REST simple

### Base de données
- Schéma SQL de référence pour une base MySQL / MariaDB
- Implémentation prototype possible via fichier JSON avant migration vers la base de données

## Architecture technique

- **Frontend :** interface de gestion des catégories / genres via JavaScript et HTML.
- **API :** point d'entrée PHP pour les opérations CRUD.
- **Service layer :** logique métier des opérations liées aux genres.
- **DAO / Models :** encapsulation des objets et accès aux données.

## Exemple de logique métier

- Ajouter un nouveau genre musical
- Récupérer tous les genres
- Enregistrer un morceau associé à un artiste et à un genre
- Mettre à jour le statut d'un morceau comme `Published`
- Gérer les fichiers multimédias du catalogue musical

## Conclusion

Ce prototype a permis de valider l'organisation base de données + back-end + front-end pour une application de gestion musicale. Il sert de base solide pour une version plus avancée avec authentification, gestion complète des artistes, upload de fichiers audio et interface admin plus complète.