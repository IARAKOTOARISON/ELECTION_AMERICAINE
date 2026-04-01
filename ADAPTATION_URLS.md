# Documentation - Adaptation des URLs pour le déploiement

## Problème résolu

Le projet utilisait des URLs absolues (commençant par `/`) qui ne fonctionnaient pas correctement lorsque l'application était déployée dans un sous-dossier comme `/opt/lampp/htdocs/COLLECTE_BNGRC`.

## Solution implémentée

### 1. Service BaseURL Global

**Fichier : `app/config/services.php`**

Ajout d'un service global `baseUrl` qui calcule automatiquement le chemin de base de l'application :

```php
// Calculate base URL for the application
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$basePath = dirname($scriptName);
if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
    $basePath = '';
}
$app->set('baseUrl', $basePath);
```

**Résultat :**
- Si le projet est à la racine : `$baseUrl = ''`
- Si le projet est dans un sous-dossier : `$baseUrl = '/COLLECTE_BNGRC'`

### 2. Contrôleur de Base

**Fichier : `app/controllers/BaseController.php`** (nouveau fichier)

Création d'un contrôleur de base dont tous les contrôleurs héritent :

```php
class BaseController {
    protected Engine $app;
    protected \PDO $db;

    protected function getBaseUrl(): string {
        return $this->app->get('baseUrl') ?? '';
    }
}
```

### 3. Mise à jour des Contrôleurs

Tous les contrôleurs étendent maintenant `BaseController` :
- `BesoinController`
- `DonController`
- `VilleController`
- `SimulationController`
- `TableauBordController`

Chaque méthode qui rend une vue passe maintenant `'baseUrl' => $this->getBaseUrl()` dans les paramètres.

### 4. Mise à jour des Vues

#### En-tête de chaque vue :
```php
<?php $base = isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>
```

#### Liens CSS et JavaScript :
```php
<link href="<?= htmlspecialchars($base) ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<script src="<?= htmlspecialchars($base) ?>/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
```

#### Liens de navigation :
```php
<a href="<?= htmlspecialchars($base) ?>/besoins/formulaire" class="btn btn-primary">
```

#### Formulaires :
```php
<form method="POST" action="<?= htmlspecialchars($base) ?>/besoins/ajouter">
```

#### Includes :
```php
<?php include $_SERVER['DOCUMENT_ROOT'] . $base . '/includes/header.php'; ?>
```

### 5. Fichiers Includes

**Fichiers : `public/includes/header.php` et `public/includes/menu.php`**

Tous les liens utilisent maintenant la variable `$base` :

```php
<?php $base = isset($baseUrl) ? rtrim($baseUrl, '/') : ''; ?>
<a href="<?= htmlspecialchars($base) ?>/accueil">Accueil</a>
<img src="<?= htmlspecialchars($base) ?>/assets/icons/home.png" alt="home">
```

### 6. Redirections dans les Contrôleurs

Les redirections utilisent maintenant le baseUrl :

```php
$baseUrl = $this->getBaseUrl();
$this->app->redirect($baseUrl . '/besoins/formulaire');
```

## Fichiers modifiés

### Configuration
- `app/config/services.php` - Ajout du service baseUrl
- `app/config/routes.php` - Passage du baseUrl aux vues

### Contrôleurs
- `app/controllers/BaseController.php` - **NOUVEAU**
- `app/controllers/BesoinController.php`
- `app/controllers/DonController.php`
- `app/controllers/VilleController.php`
- `app/controllers/SimulationController.php`
- `app/controllers/TableauBordController.php`

### Vues
- `app/views/accueil.php`
- `app/views/besoinFormulaire.php`
- `app/views/besoinListe.php`
- `app/views/donFormulaire.php`
- `app/views/donListe.php`
- `app/views/listeVille.php`
- `app/views/simulation.php`
- `app/views/tableauBord.php`

### Includes
- `public/includes/header.php`
- `public/includes/menu.php`

## Avantages de cette solution

1. **Portabilité** : Le projet fonctionne maintenant dans n'importe quel dossier
2. **Aucune configuration manuelle** : Le baseUrl est calculé automatiquement
3. **Compatibilité** : Fonctionne aussi bien à la racine que dans un sous-dossier
4. **Maintenance facile** : Tous les contrôleurs héritent de BaseController
5. **Cohérence** : Toutes les vues utilisent le même pattern

## Test de fonctionnement

Pour tester que tout fonctionne :

1. Accédez à : `http://localhost/COLLECTE_BNGRC/`
2. Vérifiez que :
   - Les images et CSS se chargent correctement
   - Les liens du menu fonctionnent
   - Les formulaires se soumettent aux bonnes URLs
   - Les redirections fonctionnent

## En cas de problème

Si certains liens ne fonctionnent pas :

1. Vérifiez que la vue reçoit bien le paramètre `baseUrl`
2. Vérifiez que la vue définit `$base` en en-tête
3. Vérifiez que tous les liens utilisent `<?= htmlspecialchars($base) ?>`
4. Pour déboguer, ajoutez temporairement : `<?php var_dump($baseUrl); ?>`
