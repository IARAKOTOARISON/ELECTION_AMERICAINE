# Guide de Test Rapide

## 🧪 Comment tester que les modifications fonctionnent

### 1. Démarrer le serveur LAMPP
```bash
sudo /opt/lampp/lampp start
```

### 2. Accéder à l'application
Ouvrir dans le navigateur :
```
http://localhost/COLLECTE_BNGRC/
```

### 3. Tests à effectuer

#### ✅ Test 1 : Page d'accueil
- [ ] L'image de fond s'affiche correctement
- [ ] Le bouton "Commencer" est visible
- [ ] Cliquer sur "Commencer" → redirige vers le tableau de bord

#### ✅ Test 2 : Navigation
- [ ] Cliquer sur chaque élément du menu :
  - Accueil
  - Saisir Besoins
  - Liste Besoins
  - Saisir Dons
  - Liste Dons
  - Liste Villes
  - Simulation Dispatch
- [ ] Aucun lien ne doit renvoyer une erreur 404

#### ✅ Test 3 : CSS et images
- [ ] Le logo BNGRC s'affiche dans le header
- [ ] Les icônes du menu s'affichent
- [ ] Les styles Bootstrap sont appliqués (boutons, tableaux, cartes)
- [ ] Les couleurs et la mise en page sont correctes

#### ✅ Test 4 : Formulaires
- [ ] Aller sur "Saisir Besoins"
- [ ] Remplir le formulaire
- [ ] Soumettre → doit rediriger vers le formulaire avec un message de succès
- [ ] Répéter avec "Saisir Dons"

#### ✅ Test 5 : JavaScript
- [ ] Ouvrir la console du navigateur (F12)
- [ ] Vérifier qu'il n'y a pas d'erreurs de chargement de scripts
- [ ] Les scripts Bootstrap doivent fonctionner (menus déroulants, etc.)

### 4. Tests de différents chemins

#### Test avec sous-dossier (actuel)
```
http://localhost/COLLECTE_BNGRC/
```

#### Test à la racine (si vous déplacez le projet)
```bash
sudo mv /opt/lampp/htdocs/COLLECTE_BNGRC/* /opt/lampp/htdocs/
```
Puis accéder à :
```
http://localhost/
```
→ Tout doit fonctionner de la même manière !

### 5. Vérifier les logs

Si quelque chose ne fonctionne pas :

```bash
# Logs Apache
sudo tail -f /opt/lampp/logs/error_log

# Logs PHP
# Vérifier dans app/log/ si Tracy Debugger est activé
```

## 🐛 Problèmes courants et solutions

### Problème 1 : Images ne se chargent pas
**Cause** : Le dossier `public/assets` n'est peut-être pas accessible
**Solution** :
```bash
sudo chmod -R 755 /opt/lampp/htdocs/COLLECTE_BNGRC/public/assets
```

### Problème 2 : Erreur de base de données
**Cause** : La base de données n'est pas configurée
**Solution** : Vérifier `app/config/config.php` et importer `base.sql` et `data.sql`

### Problème 3 : Erreur 500
**Cause** : Erreur PHP
**Solution** : 
1. Activer l'affichage des erreurs dans `php.ini`
2. Consulter les logs Apache
3. Vérifier que Tracy Debugger est activé dans `services.php`

## ✨ Résultat attendu

Si tout fonctionne correctement :
- ✅ Aucune erreur 404
- ✅ Toutes les images s'affichent
- ✅ Tous les styles CSS sont appliqués
- ✅ Tous les liens de navigation fonctionnent
- ✅ Les formulaires se soumettent correctement
- ✅ Les redirections fonctionnent

## 📞 En cas de problème persistant

1. Vérifier le fichier `ADAPTATION_URLS.md` pour la documentation complète
2. Vérifier que tous les contrôleurs étendent `BaseController`
3. Vérifier que toutes les vues reçoivent le paramètre `baseUrl`
4. Consulter les logs d'erreur
