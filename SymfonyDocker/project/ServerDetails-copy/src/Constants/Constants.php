<?php

namespace App\Constants;

class Constants
{
    const CSV_HEADER_FORMAT = [
        'Model','RAM','HDD','Location','Price'
    ];
    const FILE_PATH = "/var/www/html/ServerDetails/Uploads/";
    const FILE_NAME = "ServerDetails";

    const FILTER_RAM = [
        '0GB', '250GB', '500GB', '1TB', '2TB', '3TB', '4TB', '8TB', '12TB', '24TB', '48TB', '72TB'
        ];
    const HDD_FORM = ['SAS', 'SATA', 'SSD'];
    const Location = ['AmsterdamAMS-01','Washington D.C.WDC-01','San FranciscoSFO-12','SingaporeSIN-11','DallasDAL-10','SingaporeSIN-11','FrankfurtFRA-10'
    ];
}