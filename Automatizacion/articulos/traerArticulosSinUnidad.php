<?php
require_once '/var/www/vhosts/' . $_SERVER['HTTP_HOST'] . '/httpdocs/Automatizacion/database/dbSelectors.php';
$selectBDD = selectBDD();
$dbname    = $selectBDD[dbname];
$username  = $selectBDD[username];
$password  = $selectBDD[password];

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$contador  = 0;
$productos = array();

$sql    = "SELECT id_product, 0 as id_product_attribute, reference FROM prstshp_product WHERE reference != '' AND reference LIKE '%-%' AND reference NOT LIKE '%0000-0000%' AND reference NOT like '%al%' AND unitId IS NULL;";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id_order             = $row[id_order];
        $id_customer          = $row[id_customer];
        $id_cart              = $row[id_cart];
        $productos[$contador] = array("id_product" => (int) $row[id_product],
            "id_product_attribute"                     => (int) $row[id_product_attribute],
            "reference"                                => "{$row[reference]}",
        );
        $contador++;
    }
}
$sql    = "SELECT id_product, id_product_attribute, reference  FROM prstshp_product_attribute WHERE reference != '' AND reference LIKE '%-%' AND reference NOT LIKE '%0000-0000%' AND reference NOT like '%al%' AND unitId IS NULL;";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id_order             = $row[id_order];
        $id_customer          = $row[id_customer];
        $id_cart              = $row[id_cart];
        $productos[$contador] = array("id_product" => (int) $row[id_product],
            "id_product_attribute"                     => (int) $row[id_product_attribute],
            "reference"                                => "{$row[reference]}",
        );
        $contador++;
    }
}
echo json_encode($productos);
/*

SELECT pp.id_product, psa.id_product_attribute, pp.reference, pp.act_existencia, psa.quantity, ppl.name
FROM prstshp_product pp
INNER JOIN prstshp_stock_available psa ON psa.id_product = pp.id_product AND psa.id_product_attribute = 0
INNER JOIN prstshp_product_lang ppl ON ppl.id_product = pp.id_product AND ppl.id_lang = 2
WHERE pp.act_existencia ='2021-05-06'
ORDER BY `psa`.`quantity` ASC

(InventoryWarehouseId eq 'CHIHCONS') and

 */
