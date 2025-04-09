# Stage'athon - Plateforme de Signature de Conventions de Stage

## Description

**Stage'athon** est une plateforme en ligne conçue pour faciliter la circulation, la signature et la gestion des conventions de stage. Ce projet est développé dans le cadre du BTS SIO 2024/2025.

## Objectifs

- Simplifier le processus de signature des conventions de stage.
- Garantir la sécurité et la confidentialité des documents.
- Notifier les parties prenantes lorsque les documents sont prêts à être téléchargés.

## Parties Prenantes

### 1. Administrateur du lycée
- Créer une convention.
- Partager la convention avec les parties concernées.
- Archiver la convention après signature de toutes les parties.

### 2. Étudiant
- Signer la convention.
- Télécharger la convention après signature de toutes les parties.

### 3. Tuteur en entreprise
- Signer la convention.
- Télécharger la convention après signature de toutes les parties.

### 4. Direction de l’entreprise
- Signer la convention.
- Télécharger la convention après signature de toutes les parties.

## Fonctionnalités Clés

- **Gestion des utilisateurs** : Accès sécurisé pour chaque partie prenante.
- **Signature électronique** : Permet aux utilisateurs de signer les conventions en ligne.
- **Notifications** : Alertes automatiques pour informer les parties prenantes de l'état des documents.
- **Téléchargement sécurisé** : Les documents signés peuvent être téléchargés uniquement par les parties autorisées.
- **Archivage** : Les conventions signées sont archivées pour une gestion simplifiée.

## Besoins Techniques

- **Sécurité** : 
  - Accès limité à la plateforme via authentification.
  - Protection des données sensibles.
- **Confidentialité** : 
  - Les documents sont accessibles uniquement aux parties concernées.
- **Notifications** : 
  - Envoi d'emails pour informer les utilisateurs de l'état des conventions.

## Technologies Utilisées

- **Backend** : PHP (avec PDO pour la gestion de la base de données).
- **Frontend** : HTML, CSS, JavaScript.
- **Base de données** : MySQL.
- **Bibliothèques** : PHPMailer pour l'envoi d'emails.

## Installation

1. Clonez le dépôt :
   ```bash
   git clone https://github.com/votre-repo/stageathon.git
