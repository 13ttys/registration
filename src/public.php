<?php
declare(strict_types=1);
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/bootstrap.php";

use App\Settings;
use App\Controllers;
use UCRM\Slim\Controllers\Common;
use MVQN\Localization\Translator;
use UCRM\Common\Config;

use UCRM\REST\Endpoints\Organization;

/**
 * Use an immediately invoked function here, to avoid global namespace pollution...
 *
 * @author Ryan Spaeth <rspaeth@mvqn.net>
 *
 */
(function() use ($app)
{
    // -----------------------------------------------------------------------------------------------------------------
    // CUSTOM ROUTES
    // -----------------------------------------------------------------------------------------------------------------

    new Controllers\ExampleController($app);

    // TODO: Add additional custom routes here!
    // ...



    // -----------------------------------------------------------------------------------------------------------------
    // BUILD-IN ROUTES
    // Note: These controllers should be added last, so the above controllers can override routes as needed.
    // -----------------------------------------------------------------------------------------------------------------

    // Append a route handler for static assets.
    new Common\AssetController($app);

    // Append a route handler for Twig templates.
    new Common\TemplateController($app,
    [
        "translations" => Translator::loadDictionary(),
        "config" => [
            "maps" => [
                "google" => [
                    "api" =>
                        [
                            // You should be able use the same key that you are currently using in UCRM or UNMS.
                            "key"       => Config::getGoogleApiKey() ?? "",
                        ],

                    "defaults" =>
                        [
                            // Set a default Latitude to center on a specific area, if desired.
                            "latitude"  => (float)(Settings::getMapDefaultLat() ?? 37.09024),

                            // Set a default Longitude to center on a specific area, if desired.
                            "longitude" => (float)(Settings::getMapDefaultLng() ?? -95.712891),

                            // Set a default Zoom level, if desired.  The higher the number, the closer the view.
                            "zoom"      => (int)(Settings::getMapDefaultZoom() ?? 3),
                        ],

                    "layers" =>
                        [
                            // A list of KML/KMZ files that should be drawn on the map as overlays...

                            //"http://ucrm.dev.mvqn.net/Meteorite_Impacts_from_around_the_World.kmz",
                            //"http://ucrm.dev.mvqn.net/Wal-Mart_sites.kml"
                            //"http://ucrm.dev.mvqn.net/McDonald's_Europe.kml",
                            //"http://ucrm.dev.mvqn.net/costco-gas-stations.kml",
                            //"http://api.flickr.com/services/feeds/geo/?g=322338@N20&lang=en-us&format=feed-georss",
                            Settings::getMapLayer(),
                        ],

                    "heatmaps" =>
                        [
                            // Not yet implemented!
                        ]
                ]
            ]
        ]
    ]);

    // Append a route handler for PHP scripts.
    new Common\ScriptController($app);

    // Run the Slim Framework Application!
    $app->run();

})();

