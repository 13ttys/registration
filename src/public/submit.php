<?php
require_once __DIR__."/../vendor/autoload.php";
require_once __DIR__."/../bootstrap.php";

use App\Settings;
use UCRM\REST\Endpoints\Country;
use UCRM\REST\Endpoints\State;
use UCRM\REST\Endpoints\Client;
use UCRM\REST\Endpoints\ClientContact;
use UCRM\REST\Endpoints\Organization;

use UCRM\Common\Log;

// Fix-Up the query string to remove the '&status=...' part, in the case of re-submission!
$previousPage = preg_replace("/\&status=\w+/", "", $_SERVER["HTTP_REFERER"]);

try
{
    /** @var Country $country */
    $country = null;

    if($_POST["country"] !== "")
        $country = Country::get()->whereAny([ "name" => $_POST["country"], "code" => $_POST["country"] ])->first();

    /** @var State $state */
    $state = null;

    if($_POST["state"] !== null && $country !== null)
        $state = $country->getStates()->whereAny([ "name" => $_POST["state"], "code" => $_POST["state"] ])->first();

    $organizationId = Settings::getOrganizationId() ?? Organization::getByDefault()->getId();

    // Create the Client data...
    $client = (new Client())
        ->setOrganizationId($organizationId)
        ->setIsLead(true)
        ->setClientType((int)$_POST["clientType"])
        ->setCompanyName($_POST["companyName"])
        ->setCompanyContactFirstName($_POST["clientType"] === "2" ? $_POST["firstName"] : "")
        ->setCompanyContactLastName($_POST["clientType"] === "2" ? $_POST["lastName"] : "")
        ->setFirstName($_POST["firstName"])
        ->setLastName($_POST["lastName"])
        ->setStreet1($_POST["street1"])
        ->setStreet2($_POST["street2"])
        ->setCity($_POST["city"])
        ->setStateId($state !== null ? $state->getId() : null)
        ->setZipCode($_POST["zipCode"])
        ->setCountryId($country !== null ? $country->getId() : null)
        ->setAddressGpsLat((float)$_POST["latitude"])
        ->setAddressGpsLon((float)$_POST["longitude"])
        ->setInvoiceAddressSameAsContact(true)
        ->setRegistrationDate(new DateTime());

    // Insert the Client.
    $insertedClient = $client->insert();

    Log::info("Client Lead Created: $insertedClient");

    $contact = (new ClientContact())
        ->setClientId($insertedClient->getId())
        ->setEmail($_POST["email"])
        ->setPhone($_POST["phone"] ?: "")
        ->setName($_POST["firstName"]." ".$_POST["lastName"])
        ->setIsBilling(true)
        ->setIsContact(true);

    // Attempt to insert the Contact in the UCRM system.
    $insertedContact = $contact->insert();

    Log::info("Client Lead's Contact Created: $insertedContact");

    header("Location: $previousPage&status=success");
}
catch (Exception $e)
{
    Log::warning("Client Lead and/or Contact could not be created!\n{$e->getMessage()}");
    //var_dump($e);

    header("Location: $previousPage&status=failure");
}

