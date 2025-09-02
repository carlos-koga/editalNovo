<?php
require('../vendor/autoload.php');

use App\Database\Connect;


$strSql = "WITH submenu AS ";
$strSql .= " (SELECT submenu_text, submenu_action, menu_id FROM submenu ORDER BY submenu_ordem), ";
$strSql .= " menu AS ";
$strSql .= " (SELECT menu_name::text, menu_text,  json_agg(S) FROM menu AS M";
$strSql .= "  JOIN submenu AS S";
$strSql .= "  USING (menu_id)";
$strSql .= "   GROUP BY M.menu_name, M.menu_text";
$strSql .= "   ORDER BY M.menu_name)";

$strSql .= " SELECT json_agg(menu)";
$strSql .= " FROM menu";

$con =  new Connect();

if ($con) {
    $query = $con->pdo->prepare($strSql);
    $query->execute();
    $json = $query->fetch()[0];
    echo $json;
    unset($con);
    unset($query);
}
