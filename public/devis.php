<?php
 // require et paramétrages
use App\BDD\DbConnection;
use App\BDD\DbManager;

require __DIR__ . '/../config.php';
require __DIR__ . '/../src/BDD/dbManager.php';
$pageTitle = "Formulaire de devis";
require __DIR__ . '/../templates/header.php';

// Test DB
//$dbManager = new DbManager(DbConnection::create());
//dd($dbManager->getAllPersonne());
//dd($dbManager->getAllContactForm());
//dd($dbManager->getAllDevisForm());
//dd($dbManager->getAllOption());
//dd($dbManager->getPersonneByEmail("camille.simon50@hotmail.fr"));
//dd($dbManager->getContactFormById(5));
//dd($dbManager->getDevisFormById(27));
//dd($dbManager->getOptionById(7));
?>


<?php require __DIR__ . '/../templates/footer.php'; ?>
