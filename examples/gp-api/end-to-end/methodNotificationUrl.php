<?php
/*
 * this sample code is not specific to the Global Payments SDK and is intended as a simple example and
 * should not be treated as Production-ready code. You'll need to add your own message parsing and 
 * security in line with your application or website
 */
$threeDSMethodData = $_REQUEST["threeDSMethodData"];

// sample ACS response for Method URL Response Notification
// $threeDSMethodData = "eyJ0aHJlZURTU2VydmVyVHJhbnNJRCI6ImFmNjVjMzY5LTU5YjktNGY4ZC1iMmY2LTdkN2Q1ZjVjNjlkNSJ9";

try {
   $decodedThreeDSMethodData = base64_decode($threeDSMethodData);
   if (!empty($decodedThreeDSMethodData)) {
      $convertedThreeDSMethodData = json_decode(htmlspecialchars($decodedThreeDSMethodData, ENT_NOQUOTES), true);
      $serverTransID = htmlspecialchars($convertedThreeDSMethodData['threeDSServerTransID'], ENT_NOQUOTES);
   }

   // TODO: notify client-side that the Method URL step is complete
   // optional to return decoded JSON string, see below
} catch (Exception $exce) {
   // TODO: Add your exception handling here
}
?>
<script src="globalpayments-3ds.js"></script>
<script>
   <?php if (isset($serverTransID)) { ?>
      GlobalPayments.ThreeDSecure.handleMethodNotification(<?php echo '"' . $serverTransID . '"'; ?>);
   <?php  } ?>
</script>