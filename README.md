# TP — Déploiement d'une architecture de supervision et de sécurisation réseau

## Description du projet

Ce projet consiste en la mise en place d'une infrastructure complète de surveillance et de sécurisation d'un réseau d'entreprise, en combinant deux technologies complémentaires et incontournables : **Zabbix 7.0 LTS** et **FortiGate**.

L'objectif est de déployer un serveur Ubuntu 24.04 hébergeant Zabbix pour la supervision centralisée des équipements réseau (serveurs, routeurs, postes de travail), ainsi qu'un pare-feu FortiGate pour le contrôle et la segmentation du trafic. Le serveur Zabbix collecte les métriques (CPU, mémoire, disque, services, réseau) via les agents Zabbix et le protocole SNMP. Le pare-feu FortiGate applique des politiques de sécurité précises pour filtrer les flux entre les différentes zones du réseau.

Cette architecture répond à l'exigence fondamentale de la cybersécurité moderne : **voir et protéger**. Zabbix offre la visibilité en temps réel sur l'état du réseau, tandis que FortiGate assure le contrôle des accès et la protection contre les menaces extérieures.

## Prérequis

Avant de déployer ce projet, assurez-vous d'avoir installé les outils suivants sur votre machine :

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/)

Vous pouvez vérifier que Docker Compose est correctement installé avec la commande :

```bash
docker compose version
```

## Automatisation de la Partie I

> **Note :** La partie **Partie I — Préparation du Serveur Linux** du TP original (mise à jour du système, installation d'Apache, PHP 8.3 et MySQL) est entièrement automatisée via Docker. Il n'est pas nécessaire d'exécuter manuellement ces étapes sur l'hôte. Le conteneur Zabbix embarque toutes les dépendances requises.

## Reconnaissance

Ce travail pratique est basé sur l'énoncé publié par **Gwladysgodem** sur Medium.

- **Autrice** : Gwladysgodem
- **Article original** : [TP — Déploiement d'une architecture de supervision et de sécurisation réseau](https://medium.com/@gwladysgodem/tp-d%C3%A9ploiement-dune-architecture-de-supervision-et-de-s%C3%A9curisation-r%C3%A9seau-b96e4e9944c1?postPublishedType=initial)

Je remercie Gwladysgodem pour la rédaction de ce TP pédagogique qui sert de référence pour la réalisation de ce projet.
