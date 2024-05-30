<?php
session_start();

$servername = "localhost";
$username = "u67296";
$password = "5247723";
$dbname = "u67296";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Создаем таблицу для пользователей, если она не существует
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        login VARCHAR(50) NOT NULL,
        password_hash VARCHAR(255) NOT NULL
    )";
    $conn->exec($sql);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Проверяем, вошел ли пользователь
if (isset($_SESSION['user_id'])) {
    // Пользователь вошел, можно изменять данные
    // Реализация изменения данных
} else {
    // Проверяем, была ли отправлена форма входа
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login']) && isset($_POST['password'])) {
        // Попытка входа с использованием логина и пароля

        // Получаем введенные логин и пароль
        $login = $_POST['login'];
        $password = $_POST['password'];

        // Ищем пользователя в базе данных по логину
        $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Пароль верный, устанавливаем сессию для пользователя
            $_SESSION['user_id'] = $user['id'];

            // Пользователь вошел, можно изменять данные
            // Реализация изменения данных
        } else {
            // Неправильный логин или пароль, отобразить сообщение об ошибке
            echo "Неправильный логин или пароль.";
        }
    } else {
        // Форма входа
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login</title>
        </head>
        <body>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <label for="login">Логин:</label>
                <input type="text" id="login" name="login" required><br>
                <label for="password">Пароль:</label>
                <input type="password" id="password" name="password" required><br>
                <input type="submit" value="Войти">
            </form>
        </body>
        </html>
        <?php
    }
}
?>
