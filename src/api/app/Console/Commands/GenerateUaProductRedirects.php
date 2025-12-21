<?php

namespace App\Console\Commands;

class GenerateUaProductRedirects extends GenerateProductRedirects
{
    protected const COMMAND_NAME = 'redirects:generate-ua-products';
    protected const COMMAND_DESCRIPTION = 'Generate redirects-ua.csv for UA product URLs';
    protected const DEFAULT_SOURCE = 'public/ua-product.php';
    protected const DEFAULT_OUTPUT = 'storage/app/redirects-ua.csv';
    protected const DESTINATION_PREFIX = 'https://vivadzen.com/ua';
}
