<?php

function selectBDD()
{
    $activeStoreSelector = $_SERVER['HTTP_HOST'];
    switch ($activeStoreSelector) {
        case "avanceytec.info":
            $dbname   = "prestashop_5";
            $username = "prestashop_7";
            $password = "0rtHb?05";
            break;

        case "avanceytec.com.mx":
            $dbname   = "prestashop_8";
            $username = "admin_atp";
            $password = "1T7#ev0b";
            break;

        case "avanceytec.mx":
            $dbname   = "prestashop_5";
            $username = "prestashop_e";
            $password = "Ly0%9i0c";
            break;

        case "lideart.net":
            $dbname   = "prestashop_3";
            $username = "admin_lideart";
            $password = "Avanceytec_2022";
            break;

        case "mundocricut.com.mx":
            $dbname   = "prestashop_8";
            $username = "prestashop_1";
            $password = "L7&yoy33";
            break;

        case "silhouettemexico.com.mx":
            $dbname   = "prestashop_b";
            $username = "prestashop_c";
            $password = "5!nm4B21";
            break;
    }

    $arrayDatabase = array(
        'dbname'   => $dbname,
        'username' => $username,
        'password' => $password,
    );
    return $arrayDatabase;
}
