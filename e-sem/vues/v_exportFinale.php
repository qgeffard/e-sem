<?php
require 'checkUserIntoSession.php';
if (empty($user->role)) :
  header('Location: index.php?action=login');
  exit(1);
endif;
//envoi des headers csv
header('Content-Type: application/csv-tab-delimited-table');
//nommage du fichier avec la date du jour
header('Content-disposition: filename=seminaireExport_'.date('Ymd').'.csv');

echo $lesInscriptions;
