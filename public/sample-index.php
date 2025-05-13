<?php
require_once dirname(__FILE__, 4) . '/globals.php';
$externalUrl = "https://devcopilot.wpecker.ai/";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Redirect to WoodPecker Platform</title>
</head>
<body>
    <h2>Redirecting to WoodPecker Platform</h2>
    <p>You will be redirected to <a href="<?php echo $externalUrl; ?>"><?php echo $externalUrl; ?></a> in 3 seconds...</p>
    <script>
        setTimeout(function() {
            window.location.href = '<?php echo $externalUrl; ?>';
        }, 3000);
    </script>
</body>
</html>