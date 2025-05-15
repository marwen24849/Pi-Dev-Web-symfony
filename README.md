# HR Management System (Symfony)

## Overview
Système complet de gestion des ressources humaines avec des fonctionnalités avancées incluant :
- Gestion traditionnelle des RH (employés, congés, formations)
- Chat intelligent avec assistant IA
- Agent RAG (Recherche Augmentée par Génération) pour l'aide administrative
- Tableau de bord interactif

## Features
### Gestion RH de base
- **Employés** : Profils, équipes, départements
- **Congés** : Demandes, approbations, suivi du solde
- **Formations** : Planning, sessions, suivi
- **Quiz & Évaluations** : Création de quiz, passation, résultats
- **Projets** : Gestion par équipe
- **Réclamations** : Suivi des issues

### Fonctionnalités Intelligentes
- **Chat IA** : 
  - Assistant virtuel pour les employés
  - Réponses contextuelles sur les politiques RH
  - Rappels intelligents (congés, formations)
  
- **Agent RAG** :
  - Système de recommandation pour les administrateurs
  - Analyse des tendances (congés, productivité)
  - Suggestions automatisées basées sur les données historiques

### Autres Modules
- Quiz et évaluations
- Gestion des mobilités internes
- Tableau de bord analytique

## Tech Stack
### Frontend
- Twig + Bootstrap 5
- JavaScript/ES6
- Chart.js (pour les graphiques)
- StimulusJS (pour les interactions)

### Backend
- Symfony 6.x
- Doctrine ORM + MySQL
- API Platform (pour les endpoints IA)

### Intelligence Artificielle
- **Chat** : 
  - Intégration OpenAI/Llama
  - Fine-tuning avec les politiques RH
- **Agent RAG** :
  - Vector DB (Weaviate/Chroma)
  - Embeddings (Sentence-Transformers)
  - Pipeline LangChain

### Infrastructure
- Docker (pour le développement)
- Redis (cache et sessions)

## Architecture
src/
├── AI/
│   ├── ChatService/ # Service de chat intelligent
│   │   ├── MessageProcessor.php
│   │   └── PolicyRetriever.php
│   └── RAGAgent/ # Agent de recommandation
│       ├── DataAnalyzer.php
│       └── SuggestionEngine.php
├── Controller/
│   ├── HR/ # Contrôleurs RH standards
│   └── AIController.php # Endpoints IA
├── Entity/
├── Repository/
├── Resources/
│   └── policies/ # Documents pour le RAG
config/
├── packages/
│   └── ai.yaml # Configuration IA
public/
├── js/
│   └── chat.js # Gestion du chat frontend

## Getting Started

### Prérequis
- PHP 8.2+
- MySQL 8+
- Composer 2+
- Node.js 18+
- Python 3.10+ (pour les composants IA)

### Installation
```bash
git clone https://github.com/marwen24849/Pi-Dev-Web-symfony
cd Pi-Dev-Web-symfony

# Backend
composer install
cp .env.example .env

# Frontend
npm install
npm run build

# Base de données
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### Configuration IA
Créer un fichier `.env.local` :

```ini
# Llama
AI_API_KEY=votre_cle
AI_MODEL=llama-3.1

# Vector DB
VECTOR_DB_URL=http://localhost:6333
```

Initialiser l'agent RAG :

```bash
php bin/console ai:rag:init
```

### Lancer le système
```bash
# Développement
symfony serve --port=8000

```

## Utilisation des Fonctionnalités IA

### Chat Intelligent
Accédez à `/chat` dans l'interface

Posez des questions comme :
- "Quelle est la politique de congés maladie ?"
- "Comment demander une formation ?"

### Agent RAG (Admin)
Accédez au tableau de bord admin

L'agent fournit :
- Alertes automatiques (ex: pic de demandes de congés)
- Recommandations (ex: besoin de formations)
- Insights basés sur les données

