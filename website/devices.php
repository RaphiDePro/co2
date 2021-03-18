<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
    <link rel="icon" type="image/png" href="/favicon.png" sizes="32x32">
    <title>CO2</title>
    <link href="css/co2.css" rel="stylesheet">
</head>

<body class="text-center">

<div class="cover-container d-flex h-100 p-3 mx-auto flex-column">
    <header class="masthead mb-auto">
        <div class="inner">
            <h3 class="masthead-brand">CO2</h3>
            <nav class="nav nav-masthead justify-content-center">
                <a class="nav-link" href="/">Home</a>
                <a class="nav-link active" href="/devices.php">Devices</a>
            </nav>
        </div>
    </header>

    <main class="inner cover">
        <h1 class="cover-heading">Devices.</h1>
        <?php
        $mysql = new mysqli("localhost", "ch53807_raphi", "Luusbueb#02", "ch53807_CO2");
        $mysql->set_charset("utf8");

        $stmt = $mysql->prepare("SELECT `dev_id`, `name`, `room` FROM `devices`");

        $stmt->bind_result($dev_id, $name, $room);
        $stmt->execute();

        //Get devices and make a button per device
        while ($stmt->fetch()) {
            echo "<p class='lead'>
                    <a href='/device.php?id=$dev_id' class='btn btn-lg btn-secondary'>$dev_id $name $room</a>
                 </p>";
        }

        $stmt->close();

        ?>
    </main>

    <footer class="mastfoot mt-auto">
        <div class="inner">
            <p>Raphael Furrer</p>
        </div>
    </footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW"
        crossorigin="anonymous"></script>
</body>
</html>
