<?php
/* ════════════════════════════════════════════════════════
   poi.php — API REST pour les points d'interet Corse
   Compatible PHP 5.2+ (utilise mysql_ et non PDO)
   ════════════════════════════════════════════════════════ */

/* ── CONFIGUREZ CES 4 LIGNES ── */
$db_host = 'sql.free.fr';
$db_name = 'villa.corsu';
$db_user = 'villa.corsu';
$db_pass = 'Peugeot06!ftp';
/* ─────────────────────────────── */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

/* Connexion */
$link = mysql_connect($db_host, $db_user, $db_pass);
if (!$link) {
    http_response_code(500);
    echo json_encode(array('error' => 'Connexion impossible'));
    exit;
}
mysql_select_db($db_name, $link);
mysql_query('SET NAMES utf8', $link);

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

/* ── GET ?action=list ── */
if ($method === 'GET' && $action === 'list') {
    $res  = mysql_query('SELECT id, name, category, lat, lng, description FROM poi ORDER BY id ASC', $link);
    $rows = array();
    while ($r = mysql_fetch_assoc($res)) {
        $rows[] = array(
            'id'          => intval($r['id']),
            'name'        => $r['name'],
            'category'    => $r['category'],
            'lat'         => floatval($r['lat']),
            'lng'         => floatval($r['lng']),
            'description' => $r['description'],
        );
    }
    echo json_encode(array('ok' => true, 'points' => $rows));
    mysql_close($link);
    exit;
}

/* ── POST ?action=add ── */
if ($method === 'POST' && $action === 'add') {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    $name = isset($data['name'])        ? trim($data['name'])        : '';
    $cat  = isset($data['category'])    ? trim($data['category'])    : 'plage';
    $lat  = isset($data['lat'])         ? floatval($data['lat'])     : 0;
    $lng  = isset($data['lng'])         ? floatval($data['lng'])     : 0;
    $desc = isset($data['description']) ? trim($data['description']) : '';

    if (!$name || !$lat || !$lng) {
        http_response_code(400);
        echo json_encode(array('error' => 'Champs manquants (name, lat, lng)'));
        mysql_close($link); exit;
    }
    if ($lat < 40.5 || $lat > 43.5 || $lng < 8.0 || $lng > 10.0) {
        http_response_code(400);
        echo json_encode(array('error' => 'Coordonnees hors de Corse'));
        mysql_close($link); exit;
    }

    $res   = mysql_query('SELECT COUNT(*) AS n FROM poi', $link);
    $row   = mysql_fetch_assoc($res);
    if (intval($row['n']) >= 500) {
        http_response_code(429);
        echo json_encode(array('error' => 'Limite de POI atteinte'));
        mysql_close($link); exit;
    }

    $name = mysql_real_escape_string($name, $link);
    $cat  = mysql_real_escape_string($cat,  $link);
    $desc = mysql_real_escape_string($desc, $link);

    $q  = "INSERT INTO poi (name, category, lat, lng, description) VALUES ('$name','$cat',$lat,$lng,'$desc')";
    $ok = mysql_query($q, $link);
    if (!$ok) {
        http_response_code(500);
        echo json_encode(array('error' => mysql_error($link)));
        mysql_close($link); exit;
    }
    $id = mysql_insert_id($link);
    echo json_encode(array('ok' => true, 'id' => intval($id)));
    mysql_close($link);
    exit;
}

/* ── DELETE ?action=delete&id=X ── */
if ($method === 'DELETE' && $action === 'delete') {
    $id = intval(isset($_GET['id']) ? $_GET['id'] : 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(array('error' => 'id manquant'));
        mysql_close($link); exit;
    }
    mysql_query("DELETE FROM poi WHERE id = $id", $link);
    echo json_encode(array('ok' => true));
    mysql_close($link);
    exit;
}

/* ── GET ?action=export ── */
if ($method === 'GET' && $action === 'export') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="mes_lieux_corse.csv"');
    $res = mysql_query('SELECT id, name, category, lat, lng, description FROM poi ORDER BY id ASC', $link);
    echo "id,name,category,lat,lng,description\n";
    while ($r = mysql_fetch_assoc($res)) {
        $line = array(
            $r['id'],
            '"' . str_replace('"', '""', $r['name'])        . '"',
            $r['category'],
            $r['lat'],
            $r['lng'],
            '"' . str_replace('"', '""', $r['description']) . '"',
        );
        echo implode(',', $line) . "\n";
    }
    mysql_close($link);
    exit;
}

http_response_code(404);
echo json_encode(array('error' => 'Action inconnue'));
mysql_close($link);
