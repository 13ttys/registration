<?php
declare(strict_types=1);

namespace UCRM\Slim\Controllers\Common;

use MVQN\Localization\Translator;
use UCRM\Slim\Middleware\PluginAuthentication;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class TemplateController
 *
 * Handles routing and subsequent rendering of Twig templates.
 *
 * @package UCRM\Slim\Controllers\Common
 * @author Ryan Spaeth <rspaeth@mvqn.net>
 * @final
 */
final class TemplateController
{
    /**
     * TemplateController constructor.
     *
     * @param App $app The Slim Application for which to configure routing.
     * @param array $data Any additional data that should be passed along to the Twig template.
     */
    public function __construct(App $app, array $data = [])
    {
        // Get a local reference to the Slim Application's DI Container.
        $container = $app->getContainer();

        $app->get("/{file:.+}.{ext:twig}",
            function (Request $request, Response $response, array $args) use ($container, $data)
            {
                // Get the file and extension from the matched route.
                $file = $args["file"] ?? "index";
                $ext = $args["ext"] ?? "html";

                // Interpolate the absolute path to the static HTML file or Twig template.
                $templates = VIEWS_PATH."/$file.$ext";

                // Get a local reference to the Twig template renderer.
                $twig = $container->get("twig");

                // Append any additional data to send along to the Twig template!
                $data["route"] = $request->getAttribute("vRoute");
                $data["query"] = $request->getAttribute("vQuery");
                $data["user"]  = $request->getAttribute("user");

                // IF the file exists exactly as specified...
                if (file_exists($templates) && !is_dir($templates))
                    // THEN render the file.
                    return $twig->render($response, "$file.$ext", $data);
                else
                    // OTHERWISE, return the default 404 page!
                    return $container->get("notFoundHandler")($request, $response, $data);
            }
        )->add(new PluginAuthentication($container))->setName("template");
    }


}