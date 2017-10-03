<?php
/**
 * Created by PhpStorm.
 * User: davidch
 * Date: 02/10/17
 * Time: 08:52
 */
require_once "eway/eway.class.php";

$NL = "\r\n";

// Validation
$arguments = getopt("d::", array("data::"));
//print_r($arguments);
if (!isset($arguments["data"])) {
    print "Data folder not set.";
    exit(1);
}

$fhIn = fopen('/data/in/tables/source.csv', 'r');
$fhOut = fopen('/data/out/tables/destination.csv', 'w');
$header = fgetcsv($fhIn);

try {
    $dataDir = $arguments["data"] . DIRECTORY_SEPARATOR;
    $configFile = $dataDir . 'config.json';

    $config = json_decode(file_get_contents($configFile), FILE_USE_INCLUDE_PATH);

    $webServiceAddress = $config['parameters']['webServiceAddress'];
    $username = $config['parameters']['username'];
    $password = $config['parameters']['#password'];
    $passwordAlreadyEncrypted = false; //$config['parameters']['passwordAlreadyEncrypted'];
    $dieOnItemConflict = $config['parameters']['dieOnItemConflict'];
    $apiFunction = $config['parameters']['apiFunction'];

    print "host: " . $webServiceAddress . $NL;
//    print "user: " . $username . $NL;

    // Create eWay API connector
    $connector = new eWayConnector($webServiceAddress, $username, $password, $passwordAlreadyEncrypted, $dieOnItemConflict);

    switch ($apiFunction) {
        case "saveCompany":
            print "Reading data of companies ..." . $NL;

            while ($row = fgetcsv($fhIn)) {
                $row = array_map('trim', $row);

                $company = array(
                    'CompanyName' => $row[array_search('CompanyName', $header)],
                    'IdentificationNumber' => $row[array_search('IdentificationNumber', $header)],
                    'Address1Street' => $row[array_search('Street', $header)],
                    'Address1City' => $row[array_search('City', $header)],
                    'Address1Country' => $row[array_search('Country', $header)],
                    'Address1PostalCode' => $row[array_search('PostalCode', $header)],
                    'VATNumber' => $row[array_search('VATNumer', $header)],
                    'Phone' => $row[array_search('Phone', $header)],
                    'Mobile' => $row[array_search('Mobile', $header)],
                    'Fax' => $row[array_search('Fax', $header)],
                    'Email' => $row[array_search('Email', $header)],
                    'Note' => $row[array_search('Note', $header)],
                    'Department' => $row[array_search('Department', $header)],
                    'AdditionalFields' => array (
                        'af_18' => $row[array_search('MRPID', $header)], // MRPID
                        'af_19' => $row[array_search('CompanyName2', $header)], // Nazev 2
                        'af_20' => $row[array_search('OtherContact', $header)], // Jiny kontakt
                        'af_21' => $row[array_search('Mobile2', $header)] // Telefon dalsi
                    )
                );

                if($row[0] != "NULL") {
                    $company['ItemGUID'] = $row[array_search('ItemGUID', $header)];
                    $company['ItemVersion'] = $row[array_search('ItemVersion', $header)]++;
                }

//                print_r($company);
                $result = $connector->saveCompany($company);

//                print_r($result);
                if ($result->ReturnCode == 'rcSuccess') {
                    echo "New company created with Guid {$result->Guid} \n";
//                fputcsv($fhOut, $row); TODO write log of stored companies
                } else {
                    echo "Unable to create new company: {$result->Description} \n";
                }

            }

            break;
        case "saveProject":
            print "Reading data of projects ..." . $NL;

            while ($row = fgetcsv($fhIn)) {
                $row = array_map('trim', $row);

                $project = array(
                    'Companies_CustomerGuid' => $row[array_search('CompanyGUID', $header)],
                    'ProjectName' => $row[array_search('ProjectName', $header)],
                    'TypeEn' => 'bd1fe684-8bea-43e0-8bb5-1666992d8530', // typ: zakazka
                    'StateEn' => 'e78d1b07-fe63-4d38-8bf5-5e1331103a40', // stav: priprava
                    'AdditionalFields' => array (
                        'af_24' => $row[array_search('MRPID', $header)], // MRPID
                        'af_26' => $row[array_search('OrderNumber', $header)]
                    )
                );

                $projectStart = $row[array_search('ProjectStart', $header)];
                $projectEnd = $row[array_search('ProjectEnd', $header)];
                $estimatedPrice = $row[array_search('EstimatedPrice', $header)];
                $note = trim($row[array_search('Note', $header)] . " " . $row[array_search('Note2', $header)]);

                if(! empty($projectStart)) $project['ProjectStart'] = $projectStart;
                if(! empty($projectEnd)) $project['ProjectEnd'] = $projectEnd;
                if(! empty($estimatedPrice)) $project['EstimatedPrice'] = $estimatedPrice;
                if(! empty($note)) $project['Note'] = $note;

                if($row[0] != "NULL") {
                    $company['ItemGUID'] = $row[array_search('ItemGUID', $header)];
                    $company['ItemVersion'] = $row[array_search('ItemVersion', $header)]++;
                }

//                print_r($project);
                $result = $connector->saveProject($project);

//                print_r($result);
                if ($result->ReturnCode == 'rcSuccess') {
                    echo "New project created with Guid {$result->Guid} \n";
//                fputcsv($fhOut, $row); TODO write log of stored projects
                } else {
                    echo "Unable to create new project: {$result->Description} \n";
                }
            }

            break;
        default:
            print "Unknown eWay API call! try: saveCompany, saveProject." . $NL;
            exit(1);
    }
} catch (InvalidArgumentException $e) {
    print $e->getMessage();
    exit(1);
} catch (\Throwable $e) { // + $e
    print $e->getMessage();
    exit(2);
}

fclose($fhIn);
fclose($fhOut);

print "Processed " . count($result->Data) . " rows." . $NL;
exit(0);