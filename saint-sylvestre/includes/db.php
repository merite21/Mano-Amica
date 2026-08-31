<?php
/**
 * Connexion PDO SQLite + création automatique du schéma.
 * Aucune base de données externe à configurer : le fichier est créé
 * automatiquement dans data/billetterie.sqlite au premier appel.
 */
require_once __DIR__ . '/config.php';

function get_db(): PDO {
    static $db = null;
    if ($db !== null) {
        return $db;
    }

    $dataDir = __DIR__ . '/../data';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0775, true);
    }

    $db = new PDO('sqlite:' . $dataDir . '/billetterie.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA foreign_keys = ON');

    $db->exec('
        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            buyer_name TEXT NOT NULL,
            buyer_phone TEXT NOT NULL,
            buyer_email TEXT,
            qty_solo INTEGER NOT NULL DEFAULT 0,
            qty_couple INTEGER NOT NULL DEFAULT 0,
            total_amount INTEGER NOT NULL,
            payment_method TEXT,
            payment_status TEXT NOT NULL DEFAULT \'pending\',
            kkiapay_transaction_id TEXT,
            created_at TEXT NOT NULL DEFAULT (datetime(\'now\')),
            paid_at TEXT
        )
    ');

    $db->exec('
        CREATE TABLE IF NOT EXISTS tickets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id INTEGER NOT NULL,
            token TEXT NOT NULL UNIQUE,
            pass_type TEXT NOT NULL,
            buyer_name TEXT NOT NULL,
            price INTEGER NOT NULL,
            created_at TEXT NOT NULL DEFAULT (datetime(\'now\')),
            checked_in_at TEXT,
            FOREIGN KEY (order_id) REFERENCES orders(id)
        )
    ');

    return $db;
}
