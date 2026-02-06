# MonShop - Site E-Commerce PHP

Site e-commerce développé en PHP avec MySQL.

## Fonctionnalités

- Catalogue de produits avec catégories
- Système de recherche
- Panier d'achat
- Gestion des utilisateurs (inscription/connexion)
- Passage de commande
- Historique des commandes
- Panel d'administration complet

## Installation

1. Importer la base de données :
```sql
mysql -u root -p < sql/database.sql
```

2. Configurer la connexion dans `config/database.php`

3. Lancer un serveur PHP :
```bash
php -S localhost:8000
```

4. Accéder au site : http://localhost:8000

## Compte admin par défaut

- Email : admin@monshop.com
- Mot de passe : password

## Structure du projet

```
├── admin/              # Panel d'administration
├── ajax/               # Requêtes AJAX (panier)
├── assets/
│   ├── css/           # Styles CSS
│   └── js/            # Scripts JavaScript
├── config/            # Configuration
├── includes/          # Header/Footer
├── sql/               # Script SQL
├── uploads/           # Images uploadées
│   ├── produits/
│   └── categories/
└── *.php              # Pages du site
```

## Technologies

- PHP 7.4+
- MySQL
- Bootstrap 5
- Font Awesome
