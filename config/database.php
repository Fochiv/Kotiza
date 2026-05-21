<?php
require_once __DIR__ . '/config.php';

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO('sqlite:' . DB_PATH);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->exec('PRAGMA journal_mode=WAL');
            $pdo->exec('PRAGMA foreign_keys=ON');
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed']));
        }
    }
    return $pdo;
}

function initDatabase(): void {
    $pdo = getDB();

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            full_name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            phone TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'user',
            status TEXT NOT NULL DEFAULT 'active',
            balance_pending REAL NOT NULL DEFAULT 0,
            balance_available REAL NOT NULL DEFAULT 0,
            total_collected REAL NOT NULL DEFAULT 0,
            login_attempts INTEGER NOT NULL DEFAULT 0,
            locked_until INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS kyc (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL UNIQUE,
            id_card_front TEXT,
            id_card_back TEXT,
            selfie TEXT,
            status TEXT NOT NULL DEFAULT 'pending',
            rejection_reason TEXT,
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            reviewed_at DATETIME,
            reviewed_by INTEGER,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS campaigns (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            description TEXT NOT NULL,
            cover_image TEXT,
            goal_amount REAL NOT NULL,
            collected_amount REAL NOT NULL DEFAULT 0,
            category TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'active',
            donors_count INTEGER NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS donations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            campaign_id INTEGER NOT NULL,
            donor_id INTEGER,
            donor_name TEXT NOT NULL DEFAULT 'Anonyme',
            donor_email TEXT,
            amount REAL NOT NULL,
            currency TEXT NOT NULL DEFAULT 'XAF',
            status TEXT NOT NULL DEFAULT 'pending',
            transaction_id TEXT,
            reference TEXT UNIQUE NOT NULL,
            operator TEXT,
            country_code TEXT,
            phone TEXT,
            payment_flow TEXT,
            wave_url TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            confirmed_at DATETIME,
            FOREIGN KEY (campaign_id) REFERENCES campaigns(id),
            FOREIGN KEY (donor_id) REFERENCES users(id)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS withdrawals (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            amount REAL NOT NULL,
            commission REAL NOT NULL,
            net_amount REAL NOT NULL,
            mobile_number TEXT NOT NULL,
            operator TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'pending',
            rejection_reason TEXT,
            requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            processed_at DATETIME,
            processed_by INTEGER,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS wallet_transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            type TEXT NOT NULL,
            amount REAL NOT NULL,
            balance_before REAL NOT NULL,
            balance_after REAL NOT NULL,
            description TEXT,
            reference_id INTEGER,
            reference_type TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");

    $admin = $pdo->prepare("SELECT id FROM users WHERE role='admin' LIMIT 1");
    $admin->execute();
    if (!$admin->fetch()) {
        $hash = password_hash(ADMIN_DEFAULT_PASSWORD, PASSWORD_BCRYPT);
        $pdo->prepare("
            INSERT INTO users (full_name, email, phone, password, role)
            VALUES ('Administrateur', 'aldofoch@gmail.com', '+237000000000', ?, 'admin')
        ")->execute([$hash]);
    }

    $pdo->exec("
        INSERT OR IGNORE INTO campaigns (user_id, title, slug, description, cover_image, goal_amount, collected_amount, category, status, donors_count, created_at)
        VALUES
        (1,'Aide médicale pour enfants','aide-medicale-enfants','Collecte pour financer les soins médicaux de jeunes enfants défavorisés dans les zones rurales.','don_medecine.jpg',500000,487000,'Santé','completed',42,'2025-12-01'),
        (1,'Construction école primaire','construction-ecole-primaire','Projet de construction d une école primaire dans le village de Ndikinimeki.','don_ecole.jpg',2000000,1985000,'Éducation','completed',156,'2025-10-15'),
        (1,'Soutien aux victimes inondations','soutien-victimes-inondations','Aider les familles victimes des inondations de la saison des pluies.','don_innondation.jpg',300000,315000,'Urgence','completed',89,'2025-11-10'),
        (1,'Equipement salle informatique','equipement-salle-info','Doter un lycée public d ordinateurs et de mobilier de bureau.','don_informatique.jpg',800000,762000,'Éducation','completed',67,'2025-09-20'),
        (1,'Alimentation enfants rue','alimentation-enfants-rue','Nourrir quotidiennement les enfants des rues de Yaoundé.','don_nourriture.jpg',250000,241000,'Humanitaire','completed',193,'2025-08-05'),
        (1,'Micro-crédit femmes rurales','microcredit-femmes-rurales','Permettre à des femmes rurales de lancer leur activité commerciale.','don_femmesrurales.jpg',600000,598000,'Projets','completed',45,'2025-07-30'),
        (1,'Forage eau potable village','forage-eau-village','Accès à l eau potable pour 500 habitants d un village du nord.','don_forage.jpg',1500000,1500000,'Humanitaire','completed',201,'2025-06-15'),
        (1,'Bourses scolaires orphelins','bourses-orphelins','Financer les frais de scolarité d enfants orphelins pour l année académique.','don_scolaire_orphelin.jpg',400000,387000,'Éducation','completed',78,'2025-05-01'),
        (1,'Soutien mamans solo','soutien-mamans-solo','Appui financier aux mères célibataires en situation de précarité.','don_merecelibataire.jpg',350000,342000,'Santé','completed',62,'2025-04-10'),
        (1,'Jardin communautaire Douala','jardin-communautaire','Créer un espace vert et nourricier dans un quartier défavorisé.','don_jardincommunautaire.jpg',180000,175000,'Projets','completed',34,'2025-03-22'),
        (1,'Matériel sportif jeunes','materiel-sportif','Offrir équipements sportifs à des jeunes d un quartier populaire.','don_materielsportif.jpg',220000,218000,'Projets','completed',57,'2025-02-14'),
        (1,'Aide funéraire famille démunie','aide-funeraire','Soutien pour les frais funéraires d une famille dans le besoin.','don_funeraille.jpg',150000,150000,'Urgence','completed',23,'2025-01-28')
    ");
}

initDatabase();
