# Configuration Render pour LawApp

## 1. Base de données PostgreSQL

### Détails de la base de données
- **Nom** : lawapp-db
- **Type** : PostgreSQL
- **Version** : 16
- **Région** : Frankfurt (EU Central)
- **Plan** : Free

### URL de connexion
```
postgresql://lawapp_user:JqemXeIWprT3M3l7VxIti0DkS9qbMRFQ@dpg-d0ibffqdbo4c739c6jn0-a/lawapp
```

## 2. Application Web

### Configuration générale
- **Nom** : lawapp
- **Type** : Web Service
- **Runtime** : Docker
- **Region** : Frankfurt (EU Central)
- **Plan** : Free

### Variables d'environnement requises

1. **ENVIRONMENT**
   - Key: `ENVIRONMENT`
   - Value: `production`
   - Description: Définit l'environnement de l'application

2. **APP_URL**
   - Key: `APP_URL`
   - Value: `https://lawapp.onrender.com`
   - Description: URL de l'application en production

3. **DATABASE_URL**
   - Key: `DATABASE_URL`
   - Value: `postgresql://lawapp_user:JqemXeIWprT3M3l7VxIti0DkS9qbMRFQ@dpg-d0ibffqdbo4c739c6jn0-a/lawapp`
   - Description: URL de connexion à la base de données PostgreSQL

## 3. Procédure de déploiement

1. Vérifier que les variables d'environnement sont correctement configurées
2. Dans l'interface Render, aller dans la section "Manual Deploy"
3. Cliquer sur "Deploy Latest Commit"
4. Attendre la fin du déploiement
5. Vérifier l'application sur https://lawapp.onrender.com

## 4. URLs importantes

- **Application** : https://lawapp.onrender.com
- **Test DB** : https://lawapp.onrender.com/test_db.php
- **Init DB** : https://lawapp.onrender.com/init_db.php

## 5. Notes importantes

- Ne jamais partager les identifiants de la base de données
- Toujours vérifier la connexion à la base de données après un déploiement
- En cas de problème de connexion, vérifier les variables d'environnement
- Les logs de l'application sont disponibles dans l'interface Render
