<?php
$username = $_POST['uname']; 
$password = $_POST['pass'];

$passValue= md5(null . $password . $username . 'password');

echo $passValue;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

    <form action="create_password.php" method="POST">
        <label for="uname">Username:</label>
            <input type="text" id="uname" name="uname"/>
        <label for="pass">Password:</label>
            <input type="password" id="pass" name="pass"/>
        <button type="submit">Submit</button>
    </form>
</body>
</html>
