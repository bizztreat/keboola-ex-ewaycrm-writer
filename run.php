<?php
/**
 * Created by PhpStorm.
 * User: davidch
 * Date: 02/10/17
 * Time: 08:52
 */
require_once "eway/eway.class.php";

$NL = "\r\n";

$arguments = getopt("d::", array("data::"));

//print_r($arguments);
if (!isset($arguments["data"])) {
    print "Data folder not set.";
    exit(1);
}

try {
    $fhIn = fopen('/data/in/tables/source.csv', 'r');
    $fhOut = fopen('/data/out/tables/destination.csv', 'w');
    $fhError = fopen('/data/out/tables/error.csv', 'w');
    if (!$fhIn) {
        print "Input file open failed.";
        exit(1);
    }
    if (!$fhOut) {
        print "Output file open failed.";
        exit(1);
    }
    if (!$fhError) {
        print "Error file open failed.";
        exit(1);
    }

    $header = fgetcsv($fhIn);
    $outHeader = $header;
    array_push($outHeader, "Timestamp");
    fputcsv($fhOut, $outHeader, ',', '"');
    fputcsv($fhError, $outHeader, ',', '"');
    date_default_timezone_set('UTC');
    $counter = 0;
    $counterErr = 0;

    $dataDir = $arguments["data"] . DIRECTORY_SEPARATOR;
    $configFile = $dataDir . 'config.json';

    $config = json_decode(file_get_contents($configFile), FILE_USE_INCLUDE_PATH);

    $webServiceAddress = $config['parameters']['webServiceAddress'];
    $username = $config['parameters']['username'];
    $password = $config['parameters']['#password'];
    $apiFunction = $config['parameters']['apiFunction'];
    $dieOnItemConflict = $config['parameters']['dieOnItemConflict'];
    $passwordAlreadyEncrypted = false; //$config['parameters']['passwordAlreadyEncrypted'];

    print "version: 1.1.0" . $NL;
    print "host: " . $webServiceAddress . $NL;

    // Create eWay API connector
    $connector = new eWayConnector($webServiceAddress, $username, $password, $passwordAlreadyEncrypted, $dieOnItemConflict);

    switch ($apiFunction) {
        case "saveCompany":
            print "Reading data of companies ..." . $NL;

            while ($row = fgetcsv($fhIn)) {
                $row = array_map('trim', $row);
                $outRow = $row;
                array_push($outRow, date('Y-m-d H:i:s'));
                $isUpdate = false;

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
                    'AdditionalFields' => array(
                        'af_33' => $row[array_search('MRPID', $header)], // MRPID
//                        'af_18' => $row[array_search('MRPID', $header)], // MRPID trial
                        'af_26' => $row[array_search('CompanyName2', $header)], // Nazev 2
//                        'af_19' => $row[array_search('CompanyName2', $header)], // Nazev 2 trial
                        'af_27' => $row[array_search('OtherContact', $header)], // Jiny kontakt
//                        'af_20' => $row[array_search('OtherContact', $header)], // Jiny kontakt trial
                        'af_28' => $row[array_search('Mobile2', $header)] // Telefon dalsi
//                        'af_21' => $row[array_search('Mobile2', $header)] // Telefon dalsi trial
                    )
                );

                $guid = $row[array_search('ItemGUID', $header)];

                if ($guid != "NULL") {
                    $company['ItemGUID'] = $row[array_search('ItemGUID', $header)];
                    $company['ItemVersion'] = $row[array_search('ItemVersion', $header)]++;
                    $isUpdate = true;
                }

//                print_r($company);
                $result = $connector->saveCompany($company);

//                print_r($result);
                if ($result->ReturnCode == 'rcSuccess') {
                    $msg = ($isUpdate) ? "Company updated " : "New company created ";
                    $msg .= "with Guid {$result->Guid} \n";
                    echo $msg;
                    fputcsv($fhOut, $outRow, ',', '"');
                } else {
                    fputcsv($fhError, $outRow, ',', '"');
                    echo "Unable to create/update company: {$result->Description}, index: {$counter}  \n";
                    $counterErr++;
                }
                $counter++;
            }

            break;
        case "saveProject":
            print "Reading data of projects ..." . $NL;

            while ($row = fgetcsv($fhIn)) {
                $row = array_map('trim', $row);
                $outRow = $row;
                array_push($outRow, date('Y-m-d H:i:s'));
                $isUpdate = false;

                $project = array(
                    'Companies_CustomerGuid' => $row[array_search('CompanyGUID', $header)],
                    'ProjectName' => $row[array_search('ProjectName', $header)],
                    'CurrencyEn' => '8d70fea5-8370-4923-97f5-8667707b4802',
                    'AdditionalFields' => array(
                        'af_34' => $row[array_search('MRPID', $header)], // MRPID
//                        'af_24' => $row[array_search('MRPID', $header)], // MRPID trial
                        'af_25' => $row[array_search('OrderNumber', $header)]
//                        'af_26' => $row[array_search('OrderNumber', $header)] trial
                    )
                );

                $guid = $row[array_search('ItemGUID', $header)];
                $projectStart = $row[array_search('ProjectStart', $header)];
                $projectEnd = $row[array_search('ProjectEnd', $header)];
                $estimatedPrice = $row[array_search('EstimatedPrice', $header)];
                $note = trim($row[array_search('Note', $header)] . " " . $row[array_search('Note2', $header)]);

                if (!empty($projectStart)) $project['ProjectStart'] = $projectStart;
                if (!empty($projectEnd)) $project['ProjectRealEnd'] = $projectEnd;
                if (!empty($estimatedPrice)) $project['EstimatedPrice'] = $estimatedPrice;
                if (!empty($note)) $project['Note'] = $note;

                $isFinished = $row[array_search('IsFinished', $header)];
                if ($isFinished) {
                    $project['StateEn'] = 'c6eb9e3c-8560-43be-90e1-97b729095979'; // stav: vyfakturovano
                } else {
                    $project['StateEn'] = '50670915-6abd-4047-9b08-1a991c45d3ba'; // stav: prijato
                }

                if ($guid != "NULL") {
                    $project['ItemGUID'] = $guid;
                    $project['ItemVersion'] = $row[array_search('ItemVersion', $header)]++;
                    $isUpdate = true;
                } else {
                    $project['TypeEn'] = '249d394a-4598-4f72-b559-8f0c4b97c02e'; // typ: servis
                }

//                print_r($project);
                $result = $connector->saveProject($project);

//                print_r($result);
                if ($result->ReturnCode == 'rcSuccess') {
                    $msg = ($isUpdate) ? "Project updated " : "New project created ";
                    $msg .= "with Guid {$result->Guid} \n";
                    echo $msg;
                    fputcsv($fhOut, $outRow, ',', '"');
                } else {
                    fputcsv($fhError, $outRow, ',', '"');
                    echo "Unable to create/update project: {$result->Description}, index: {$counter} \n";
                    $counterErr++;
                }
                $counter++;
            }

            break;
        case "saveInvoice":
            print "Reading data of invoices ..." . $NL;

            while ($row = fgetcsv($fhIn)) {
                $row = array_map('trim', $row);
                $outRow = $row;
                array_push($outRow, date('Y-m-d H:i:s'));
                $isUpdate = false;

                $invoice = array(
                    'FileAs' => $row[array_search('InvoiceNumber', $header)],
                    'Companies_CustomerGuid' => $row[array_search('CompanyGUID', $header)],
                    'CurrencyEn' => '8d70fea5-8370-4923-97f5-8667707b4802', // CZK
                    'TypeEn' => 'c06d165d-765b-4a93-a8b3-caf494dbbb34', // typ: Faktura vydaná
                    'Note' => $row[array_search('Note', $header)],
                    'Vat' => $row[array_search('Vat', $header)],
                    'PriceTotal' => $row[array_search('PriceTotal', $header)],
                    'PriceTotalExcludingVat' => $row[array_search('PriceTotalExcludingVat', $header)],
                    'EffectiveFrom' => $row[array_search('EffectiveFrom', $header)],
                    'ValidUntil' => $row[array_search('ValidUntil', $header)],
                    'AdditionalFields' => array(
                        'af_35' => $row[array_search('MRPID', $header)], // MRPID
                        'af_36' => $row[array_search('Currency', $header)] // Originalni mena
                    )
                );

                $isFullyPaid = $row[array_search('IsFullyPaid', $header)];
                if ($isFullyPaid) {
                    $invoice['StateEn'] = '92b34166-dff0-4c26-9f24-d96f086124d9'; // stav: zaplaceno
                } else {
                    $invoice['StateEn'] = '45448791-e10f-420e-a261-2c7db752d954'; // stav: vyfakturováno
                }

                $guid = $row[array_search('ItemGUID', $header)];
                $paid = $row[array_search('Paid', $header)];
                $paidChanged = $row[array_search('PaidChanged', $header)];

                if (!empty($paid)) $project['Paid'] = $paid;
                if (!empty($paidChanged)) $project['PaymentDate'] = $paidChanged;

                if ($guid != "NULL") {
                    $invoice['ItemGUID'] = $row[array_search('ItemGUID', $header)];
                    $invoice['ItemVersion'] = $row[array_search('ItemVersion', $header)]++;
                    $isUpdate = true;
                }

//                print_r($invoice);
                $result = $connector->saveCart($invoice);

//                print_r($result);
                if ($result->ReturnCode == 'rcSuccess') {
                    $msg = ($isUpdate) ? "Invoice updated " : "New invoice created ";
                    $msg .= "with Guid {$result->Guid} \n";
                    echo $msg;
                    fputcsv($fhOut, $outRow, ',', '"');
                } else {
                    fputcsv($fhError, $outRow, ',', '"');
                    echo "Unable to create/update invoice: {$result->Description}, index: {$counter} \n";
                    $counterErr++;
                }
                $counter++;
            }

            break;
        default:
            print "Unknown eWay API call! try: saveCompany, saveProject, saveInvoice." . $NL;
            exit(1);
    }
} catch (InvalidArgumentException $e) {
    print $e->getMessage();
    exit(1);
} catch (\Throwable $e) { // + $e
    print $e->getMessage();
    exit(2);
} finally {
    fclose($fhIn);
    fclose($fhOut);
    fclose($fhError);
}

print "Processed " . $counter . " rows." . $NL;
print "* with error " . $counterErr . " rows." . $NL;
exit(0);