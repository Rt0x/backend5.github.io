<?php
session_start();
require_once "db_config.php";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate'])) {
    $login = 'user_' . uniqid();
    $password = bin2hex(random_bytes(8));
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (login, password_hash) VALUES (?, ?)");
    $stmt->execute([$login, $passwordHash]);

    echo "Ваш логин: $login<br>";
    echo "Ваш пароль: $password<br>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Generation</title>
</head>
<body>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <input type="submit" name="generate" value="Generate Login and Password">
    </form>
</body>
</html>
