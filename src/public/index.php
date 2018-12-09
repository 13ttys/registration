<?php
declare(strict_types=1);
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../bootstrap.php";

$baseUrl = BASE_URL;

header("Location: 'public.php?/index.html.twig'");
//echo "<a href='public.php?/index.html.twig'>Index</a>";