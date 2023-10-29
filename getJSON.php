<?php

function get_json($org, $id, $month, $url) {
    if ($org == 'xxx') {
        $json = <<<EOT
            {
                "id": "xxx$month$id",
                "contractId": "12345",
                "description": "Центральный округ",
                "type": "other",
                "form": "banner",
                "urls": [
                "https://www.xxx.ru",
                "https://www.xxx.ru/"
                ],
                "okveds": [
                "12.34"
                ],
                "mediaData": [
                {
                    "mediaUrl": "$url",
                    "description": "Акция на текущий месяц"
                }
                ],
                "fiasRegionList": [
                ],
                "isSocial": false,
                "isNative": false
            }
        EOT;
    } elseif ($org == 'yyy') {
        $json = <<<EOT
            {
                "id": "yyy$month$id",
                "contractId": "67890",
                "description": "Центральный округ",
                "type": "other",
                "form": "banner",
                "urls": [
                    "https://www.yyy.ru",
                    "https://www.yyy.ru/"
                ],
                "okveds": [
                    "56.78"
                ],
                "mediaData": [
                    {
                    "mediaUrl": "$url",
                    "description": "Акция на текущий месяц"
                    }
                ],
                "fiasRegionList": [
                ],
                "isSocial": false,
                "isNative": false
            }
        EOT;
    } else {
        $json = false;
    }

    return $json;
}