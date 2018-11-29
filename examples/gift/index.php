<?php
$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
?>

<html>
    <head>
        <title>GiftCard Example</title>
    </head>
    <body>
        <form action="charge.php">
            Card Number:<br />
            <input type="text" value="5022440000000000098" id="card-number" name="card-number" />
            <br /><br />
            <input type="submit" value="Charge!" />
        </form>
    </body>
</html>