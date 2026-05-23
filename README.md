# TP — Architecture de supervision et sécurisation réseau (Zabbix + Docker)

> Laboratoire conteneurisé de supervision réseau et de sécurisation périphérique. Zabbix 7.0 LTS, MariaDB 11.4 et simulation réseau Cisco/FortiGate, déployables en une seule commande.

## Table des matières

- [Description du projet](#description-du-projet)
- [Architecture](#architecture)
- [Prérequis](#prérequis)
- [Quick Start](#quick-start)
- [Stack technique et choix architecturaux](#stack-technique-et-choix-architecturaux)
- [Services et accès](#services-et-accès)
- [Sécurité — Détection des fuites de secrets (Gitleaks)](#sécurité--détection-des-fuites-de-secrets-gitleaks)
- [Ce que tu apprends](#ce-que-tu-apprends)
- [Dépannage](#dépannage)
- [Reconnaissance](#reconnaissance)

---

## Description du projet

Ce projet consiste en la mise en place d'une infrastructure complète de surveillance et de sécurisation d'un réseau d'entreprise, en combinant deux technologies complémentaires et incontournables : **Zabbix 7.0 LTS** et **FortiGate**.

L'objectif est de déployer un serveur Ubuntu 24.04 hébergeant Zabbix pour la supervision centralisée des équipements réseau (serveurs, routeurs, postes de travail), ainsi qu'un pare-feu FortiGate pour le contrôle et la segmentation du trafic. Le serveur Zabbix collecte les métriques (CPU, mémoire, disque, services, réseau) via les agents Zabbix et le protocole SNMP. Le pare-feu FortiGate applique des politiques de sécurité précises pour filtrer les flux entre les différentes zones du réseau.

Cette architecture répond à l'exigence fondamentale de la cybersécurité moderne : **voir et protéger**. Zabbix offre la visibilité en temps réel sur l'état du réseau, tandis que FortiGate assure le contrôle des accès et la protection contre les menaces extérieures.

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Réseau Docker interne                    │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────────┐  │
│  │  web_server │◄──►│  database   │◄──►│  zabbix_server  │  │
│  │(Apache/PHP8)│    │ (MariaDB)   │    │  (Supervision)  │  │
│  └─────────────┘    └─────────────┘    └────────┬────────┘  │
│         ▲                                        │          │
│         │                                        ▼          │
│  ┌─────────────┐                         ┌─────────────────┐│
│  │target_linux │◄───────────────────────►│ zabbix_frontend ││
│  │   agent     │      Agent Zabbix       │  (Dashboard)    ││
│  └─────────────┘                         └─────────────────┘│
│         ▲                                                   │
│         │ SNMP                                              │
│  ┌─────────────┐                                            │
│  │cisco_router │                                            │
│  │   _sim      │                                            │
│  └─────────────┘                                            │
└─────────────────────────────────────────────────────────────┘
```

## Prérequis

Avant de déployer ce projet, assurez-vous d'avoir installé les outils suivants sur votre machine :

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/)

Vous pouvez vérifier que Docker Compose est correctement installé avec la commande :

```bash
docker compose version
```

## Quick Start

> **Note :** La partie **Partie I — Préparation du Serveur Linux** du TP original (mise à jour du système, installation d'Apache, PHP 8.3 et MariaDB) est entièrement automatisée via Docker. Il n'est pas nécessaire d'exécuter manuellement ces étapes sur l'hôte. Le conteneur Zabbix embarque toutes les dépendances requises.

```bash
# 1. Cloner le dépôt
git clone <repo-url>
cd network_security_lab

# 2. Copier et personnaliser le fichier de configuration
cp .env.example .env
# Éditez .env avec vos propres mots de passe

# 3. Lancer l'infrastructure complète
docker compose up -d
```

Accès disponibles après le démarrage :

| Service | URL | Identifiants |
|---------|-----|--------------|
| Serveur web de test | http://localhost | — |
| Interface Zabbix | http://localhost:8080 | Admin / zabbix |

## Stack technique et choix architecturaux

L'infrastructure est déployée via Docker Compose avec la stack suivante :

| Service | Image | Rôle |
|---------|-------|------|
| `web_server` | `ubuntu:24.04` + Apache + PHP 8.3 | Serveur web de test |
| `database_server` | `mariadb:11.4` | Base de données relationnelle |
| `zabbix_server` | `zabbix/zabbix-server-mysql:alpine-7.0-latest` | Moteur de supervision |
| `zabbix_frontend` | `zabbix/zabbix-web-apache-mysql:alpine-7.0-latest` | Interface web Zabbix |
| `target_linux_agent` | `zabbix/zabbix-agent:alpine-7.0-latest` | Agent de surveillance Linux |
| `cisco_router_sim` | `alpine:latest` + `net-snmp` | Simulation routeur Cisco (SNMP) |

### Pourquoi MariaDB plutôt que MySQL 8.0 ?

**MySQL 8.0** causait une erreur d'import du schéma SQL (`ERROR at line 173558: Unknown command '\'`) due à un changement de comportement du client MySQL 8.4. Il a été remplacé par **MariaDB 11.4**, qui reste pleinement compatible avec l'écosystème Zabbix et résout ce problème sans perte de performance.

### Pourquoi les images Alpine pour Zabbix ?

Les images officielles Zabbix existent en deux variantes : **Ubuntu** et **Alpine**. La variante Ubuntu a été remplacée par **Alpine** car le client MySQL 8.4.8 d'Ubuntu est à l'origine du bug d'import SQL. L'image Alpine embarque le client **MariaDB 11.4.10**, qui parse correctement le schéma Zabbix et s'aligne nativement avec le serveur MariaDB 11.4. En bonus, les images Alpine sont plus légères (~80 MB contre ~300 MB) et démarrent plus rapidement.

### Partie III — Agent Zabbix

L'agent Windows Server de l'énoncé est remplacé par un agent Linux (`zabbix/zabbix-agent:alpine-7.0-latest`) car Docker sur Linux ne peut exécuter que des binaires Linux. Les métriques supervisées restent identiques.

## Services et accès

| Service | Port hôte | Port conteneur | Description |
|---------|-----------|----------------|-------------|
| Serveur web (Apache/PHP) | `80` | `80` | Page de test PHP + connexion MariaDB |
| MariaDB | `3306` | `3306` | Base de données Zabbix (accès externe optionnel) |
| Zabbix Server | `10051` | `10051` | API et collecte des métriques |
| Zabbix Frontend | `8080` | `8080` | Interface web de supervision |

> **Note :** L'agent Zabbix et le simulateur SNMP n'exposent pas de ports sur l'hôte. Ils communiquent via le réseau interne Docker.

## Sécurité — Détection des fuites de secrets (Gitleaks)

Ce projet intègre [Gitleaks](https://github.com/gitleaks/gitleaks) pour empêcher les secrets (mots de passe, tokens, clés API) d'être poussés accidentellement sur le dépôt.

### Gestion des secrets

Les mots de passe et identifiants sont externalisés dans un fichier `.env` (non versionné) :

```bash
# Copier le modèle
cp .env.example .env
# Éditer avec vos propres valeurs
```

Le fichier `.env` est ignoré par Git (voir `.gitignore`). Le fichier `.env.example` sert de modèle public sans secrets.

### Installation du hook local (pre-commit)

Le scan s'exécute automatiquement à chaque commit via le framework **pre-commit**.

```bash
# 1. Installer pre-commit (si ce n'est pas déjà fait)
sudo apt install pre-commit

# 2. Installer le hook dans le dépôt local
pre-commit install
```

> **Note :** Le fichier `.env.example` est ignoré par Gitleaks (voir `.gitleaks.toml`).

### Vérification en continu (CI/CD)

Un workflow GitHub Actions (`.github/workflows/gitleaks.yml`) scanne automatiquement chaque **Pull Request** et chaque **push sur `main`** pour bloquer tout secret qui aurait contourné le hook local.

## Ce que tu apprends

Ce laboratoire démontre les compétences suivantes, directement applicables en entreprise :

- **Supervision réseau** : Déploiement de Zabbix 7.0 LTS, configuration d'agents, supervision via SNMP, création de triggers d'alerte.
- **Conteneurisation** : Architecture multi-services avec Docker Compose, orchestration de dépendances (healthchecks), gestion de réseaux internes.
- **Sécurisation des secrets** : Externalisation des credentials dans `.env`, scan automatique des fuites avec Gitleaks, intégration CI/CD.
- **Résolution de conflits d'écosystème** : Diagnostic et contournement d'incompatibilités (MySQL 8.4 vs schéma Zabbix, Ubuntu vs Alpine).
- **Simulation réseau** : Émulation d'équipements réseau (routeur Cisco via SNMP, pare-feu via iptables) dans un environnement 100 % logiciel.
- **Filtrage et segmentation** : Application de règles de pare-feu avec gestion de la priorité (ordre des règles DENY/ALLOW).

## Dépannage

| Symptôme | Cause probable | Solution |
|----------|--------------|----------|
| Zabbix ne démarre pas (logs stoppés sur "Creating schema") | Schéma SQL non importé ou import interrompu | Vérifier les logs : `docker logs projet_network_security_zabbix_server`. Si l'erreur `Unknown command '\'` apparaît, s'assurer que les images Alpine sont utilisées. |
| Port 80 déjà utilisé | Apache/Nginx local ou autre conteneur | Modifier `WEB_PORT` dans `.env` (ex: `8081`) |
| Port 3306 déjà utilisé | MySQL/MariaDB local | Modifier `DB_PORT` dans `.env` (ex: `3307`) |
| Agent injoignable (icône ZBX rouge) | Nom d'hôte incorrect ou DNS non résolu | Vérifier que `ZBX_HOSTNAME` dans le docker-compose correspond exactement au nom d'hôte configuré dans Zabbix. Vérifier que l'option "Connect to" est sur DNS. |
| SNMP injoignable (icône SNMP rouge) | Conteneur `cisco_router_sim` hors du réseau Docker | Vérifier que le conteneur est bien dans le réseau `network_security_lab_default` : `docker network connect network_security_lab_default cisco_router_sim` puis `docker compose restart cisco_router_sim`. |
| Interface Zabbix inaccessible | Conteneur frontend non démarré | Vérifier le statut : `docker compose ps`. Attendre 1-2 minutes après le premier démarrage (initialisation de la base). |

### Commandes utiles

```bash
# Voir les logs en temps réel
docker logs -f projet_network_security_zabbix_server

# Vérifier l'état de tous les conteneurs
docker compose ps

# Redémarrer un service spécifique
docker compose restart zabbix_server

# Entrer dans un conteneur
docker exec -it projet_network_security_mariadb bash

# Réinitialiser complètement (supprime les données)
docker compose down -v
```

## Reconnaissance

Ce travail pratique est basé sur l'énoncé publié par **Gwladysgodem** sur Medium.

- **Autrice** : Gwladysgodem
- **Article original** : [TP — Déploiement d'une architecture de supervision et de sécurisation réseau](https://medium.com/@gwladysgodem/tp-d%C3%A9ploiement-dune-architecture-de-supervision-et-de-s%C3%A9curisation-r%C3%A9seau-b96e4e9944c1?postPublishedType=initial)

Je remercie Gwladysgodem pour la rédaction de ce TP pédagogique qui sert de référence pour la réalisation de ce projet.
