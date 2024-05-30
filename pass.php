<?php
// Подключение к базе данных
$servername = "localhost";
$username = "u67296";
$password = "5247723";
$dbname = "u67296";

$conn = new mysqli($servername, $username, $password, $dbname);

// Обработка отправки формы
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Получение хешированного пароля из базы данных
    $sql = "SELECT password_hash FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stored_password_hash = $row["password_hash"];

        // Проверка введенного пароля
        if (password_verify($password, $stored_password_hash)) {
            // Пароль верный, выполняем необходимые действия
            echo "Вход выполнен успешно!";
        } else {
            // Неверный пароль
            echo "Неверный логин или пароль";
        }
    } else {
        // Пользователь не найден
        echo "Неверный логин или пароль";
    }
}
?>
