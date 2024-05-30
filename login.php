<?php
session_start();

$servername = "localhost";
$username = "u67296";
$password = "5237724";
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
    // Проверяем, была ли отправлена форма генерации логина и пароля
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate'])) {
        // Генерируем логин и пароль

        // Случайный логин и пароль
        $login = 'user_' . uniqid();
        $password = bin2hex(random_bytes(8));

        // Хешируем пароль
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Сохраняем логин и хешированный пароль в базе данных
        $stmt = $conn->prepare("INSERT INTO users (login, password_hash) VALUES (?, ?)");
        $stmt->execute([$login, $passwordHash]);

        // Выводим логин и пароль пользователю
        echo "Ваш логин: $login<br>";
        echo "Ваш пароль: $password<br>";
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
                <input type="hidden" name="login" value="<?php echo $login; ?>">
                <input type="hidden" name="password" value="<?php echo $password; ?>">
                <input type="submit" name="login_with_generated" value="Войти">
            </form>
        </body>
        </html>
        <?php
    } elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_with_generated'])) {
        // Пользователь входит с использованием сгенерированного логина и пароля
        $login = $_POST['login'];
        $password = $_POST['password'];

        // Ищем пользователя в базе данных по логину
        $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Пароль верный, устанавливаем сессию для пользователя
            $_SESSION['user_id'] = $user['id'];

            // Выводим сообщение о успешном входе
            echo "Успешный вход! Вы вошли как: $login";
        } else {
            // Неправильный логин или пароль, отобразить сообщение об ошибке
            echo "Неправильный логин или пароль.";
        }
    } else {
        // Форма генерации логина и пароля
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
                <input type="submit" name="generate" value="Сгенерировать логин и пароль">
            </form>
        </body>
        </html>
        <?php
    }
}
?>
