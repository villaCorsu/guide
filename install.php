<?php
/* ════════════════════════════════════════════════════════
   install.php — Cree la table MySQL
   A lancer UNE SEULE FOIS puis a supprimer du serveur !
   Compatible PHP 5.2+
   ════════════════════════════════════════════════════════ */

$db_host = 'sql.free.fr';
$db_name = 'villa.corsu';
$db_user = 'villa.corsu';
$db_pass = 'Peugeot06!ftp';

$link = mysql_connect($db_host, $db_user, $db_pass);
if (!$link) {
    die('<h2>Connexion impossible : ' . mysql_error() . '</h2><p>Verifiez les identifiants.</p>');
}

mysql_select_db($db_name, $link) or die('<h2>Base introuvable : ' . mysql_error() . '</h2>');
mysql_query('SET NAMES utf8', $link);

$sql_create = "CREATE TABLE IF NOT EXISTS poi (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(200) NOT NULL,
    category    VARCHAR(50)  NOT NULL DEFAULT 'plage',
    lat         DECIMAL(10,6) NOT NULL,
    lng         DECIMAL(10,6) NOT NULL,
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

if (!mysql_query($sql_create, $link)) {
    die('<h2>Erreur creation table : ' . mysql_error() . '</h2>');
}

/* Points par defaut */
$default = array(
    array('Villa Corsu',                         'hebergement', 41.523500,  9.010800,  "Airbnb - 8 Lotissement Susini, Monacia d'Aullene - Grand Sud Corse, 20171."),
    array('Sentier du Patrimoine - I Stritonni', 'randonnee',   41.510900,  9.006900,  "Boucle 4 km - 2h - Facile. Orii mesolithiques, moulins en pierre seche, terrasses agricoles."),
    array('Plage de Mucchiu Biancu',             'plage',       41.489290,  8.966620,  "Plage sauvage et preservee. Dunes, maquis blanc, cadre naturel intact. Acces depuis Furnellu."),
    array('Plage de San Giovanni',               'plage',       41.461340,  9.050040,  "Plage sauvage de Pianottoli-Caldarello. Sable dore, eaux cristallines, snorkeling."),
    array('Domaine de Saparale',                 'domaine',     41.641800,  8.927500,  "Domaine viticole du XIXe siecle, vallee de l'Ortolo. Vins AOC Sartene, caveau de degustation, hotel 4 etoiles."),
    array('Le Cabanon Bleu',                     'restaurant',  41.643500,  9.319800,  "Restaurant de plage sur la baie de Saint-Cyprien. Cuisine corse. Ouvert fin mai - fin septembre."),
    array('Plage de Pinarellu',                  'plage',       41.683270,  9.376560,  "Vaste plage de sable blanc, 2 km, bordeee d'une pinede. Tour genoise du XVIe siecle."),
    array('Plage de Saint-Cyprien',              'plage',       41.633020,  9.346150,  "Grande plage de sable fin. Zone animee au sud, zone sauvage au nord. Baignade surveillee."),
    array("Plage d'Arbitru",                     'plage',       41.478640,  9.017230,  "Plage sauvage de Pianottoli-Caldarello. Sable blanc, eau limpide. Acces par sentier des Bruzzi."),
    array('Auberge La Pergola',                  'restaurant',  41.519700,  9.011800,  "Restaurant de specialites corses a Monacia d'Aullene. Charcuterie, sanglier. Note 4.4/5."),
    array("Chemin de la Tour - Torra d'Ulmetu",  'randonnee',   41.475200,  8.984300,  "Sentier cotier depuis Furnellu jusqu'a la tour genoise d'Olmeto. Vue sur l'Omu di Cagna."),
    array('Plage de Carataggio',                 'plage',       41.574700,  9.346390,  "Plage sauvage accessible a pied depuis Sainte-Lucie de Porto-Vecchio. Sable fin, eaux cristallines."),
);

/* Verifier si la table est deja peuplee */
$res = mysql_query('SELECT COUNT(*) AS n FROM poi', $link);
$row = mysql_fetch_assoc($res);
$inserted = 0;

if ($row['n'] == 0) {
    foreach ($default as $p) {
        $name     = mysql_real_escape_string($p[0], $link);
        $cat      = mysql_real_escape_string($p[1], $link);
        $lat      = floatval($p[2]);
        $lng      = floatval($p[3]);
        $desc     = mysql_real_escape_string($p[4], $link);
        $q = "INSERT INTO poi (name, category, lat, lng, description) VALUES ('$name','$cat',$lat,$lng,'$desc')";
        if (mysql_query($q, $link)) $inserted++;
    }
}

mysql_close($link);

echo '<!DOCTYPE html><html><head><meta charset="utf-8"><style>
body{font-family:sans-serif;max-width:600px;margin:60px auto;padding:20px;background:#0f1923;color:#f5efe6;}
code{background:rgba(255,255,255,.1);padding:2px 6px;border-radius:4px;}
.warn{color:#e8926a;}
</style></head><body>';

if ($inserted > 0) {
    echo '<h2>Installation reussie !</h2>';
    echo '<p>Table <code>poi</code> creee avec <strong>' . $inserted . ' points</strong> par defaut.</p>';
} else {
    echo '<h2>Table deja existante</h2>';
    echo '<p>La table <code>poi</code> contenait deja des donnees — aucun point insere.</p>';
}

echo '<p class="warn" style="margin-top:24px;"><strong>Supprimez ce fichier du serveur maintenant !</strong><br>
Il ne doit pas rester accessible publiquement.</p>';
echo '</body></html>';
