<?php
/*
 * this sample code is not specific to the Global Payments SDK and is intended as a simple example and
 * should not be treated as Production-ready code. You'll need to add your own message parsing and 
 * security in line with your application or website
 */
$cres = $_REQUEST['cres'];

/* $cres = "eyJ0aHJlZURTU2VydmVyVHJhbnNJRCI6ImFmNjVjMzY5LTU5YjktNGY4ZC1iMmY2LTdkN2Q1ZjVjNjlkNSIsImF"
 * . "jc1RyYW5zSUQiOiIxM2M3MDFhMy01YTg4LTRjNDUtODllOS1lZjY1ZTUwYThiZjkiLCJjaGFsbGVuZ2VDb21wbGV0a"
 * . "W9uSW5kIjoiWSIsIm1lc3NhZ2VUeXBlIjoiQ3JlcyIsIm1lc3NhZ2VWZXJzaW9uIjoiMi4xLjAiLCJ0cmFuc"
 * . "1N0YXR1cyI6IlkifQ==";
 */


try {
   $decodedString = base64_decode($cres);
   if (!empty($decodedString)) {
      $convertedObject = json_decode(htmlspecialchars($decodedString, ENT_NOQUOTES), true);
   }

   date_default_timezone_set('Europe/Dublin');
   $file = fopen("challengeNotificationUrl.log", "a") or die("Unable to open file!");
   fwrite($file, "\n\n**************************\n");
   fwrite($file, date('Y-m-d H:i:s') . " Request Log: \n");
   fwrite($file, print_r($decodedString, true));
   fwrite($file, "\n**************************\n\n");
   fclose($file);

   $serverTransID = htmlspecialchars($convertedObject['threeDSServerTransID'], ENT_NOQUOTES);
   $acsTransID = $convertedObject['acsTransID'] ?? null;
   $messageType = $convertedObject['messageType'] ?? null;
   $messageVersion = $convertedObject['messageVersion'] ?? null;
   $transStatus = $convertedObject['transStatus'];

   // TODO: notify client-side that the Challenge step is complete, see below
} catch (Exception $exce) {
   // TODO: Add your exception handling here
}
?>
<script src="globalpayments-3ds.js"></script>
<script>
   GlobalPayments.ThreeDSecure.handleChallengeNotification({
            "threeDSServerTransID": <?php echo '"' . (isset($serverTransID) ? htmlspecialchars($serverTransID, ENT_NOQUOTES) : "") . '"'; ?>,
            "transStatus": <?php echo '"' . ($transStatus ? htmlspecialchars($transStatus, ENT_NOQUOTES) : "") . '"}'; ?>);
</script>