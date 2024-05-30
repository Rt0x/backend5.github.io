<?php
session_start();

$servername = "localhost";
$username = "u67296";
$password = "5237724";
$dbname = "u67296";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

$errors = [];

// Проверка наличия данных
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Валидация данных
    $fio = validateInput($_POST['fio'], "/^[a-zA-Zа-яА-Я\s]+$/u", "ФИО");
    $phone = validateInput($_POST['phone'], "/^\d{3}-\d{3}-\d{4}$/", "Телефон");
    $email = validateInput($_POST['email'], "/^\S+@\S+\.\S+$/", "E-mail");
    $dob = $_POST['dob']; // Дата рождения не проверяется здесь, так как это требует другого подхода
    $gender = $_POST['gender'];
    $languages = isset($_POST['languages']) ? $_POST['languages'] : [];
    $bio = validateInput($_POST['bio'], "/^[\w\s.,!?-]+$/u", "Биография");
    $agreement = isset($_POST['agreement']) ? 1 : 0;

    // Если есть ошибки, сохраняем их в Cookies
    if (!empty($errors)) {
        setcookie("form_errors", serialize($errors), 0, "/");
        setcookie("form_data", serialize($_POST), 0, "/");
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Вставка данных в базу данных
    try {
        $stmt = $conn->prepare("INSERT INTO osnova (fio, phone, email, dob, gender, bio, agreement) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fio, $phone, $email, $dob, $gender, $bio, $agreement]);
        echo "Данные успешно сохранены.";

        // Если данные успешно сохранены, сохраняем значения в Cookies на один год
        setcookie("form_data", serialize($_POST), time() + (86400 * 365), "/");
    } catch (PDOException $e) {
        echo "Ошибка базы данных: " . $e->getMessage();
    }
} else {
    // Если запрос не POST, проверяем наличие Cookies с ошибками и ранее введенными значениями
    $errors = isset($_COOKIE['form_errors']) ? unserialize($_COOKIE['form_errors']) : [];
    $formData = isset($_COOKIE['form_data']) ? unserialize($_COOKIE['form_data']) : [];

    // Удаляем Cookies после использования
    setcookie("form_errors", "", time() - 3600, "/");
    setcookie("form_data", "", time() - 3600, "/");
}

// Функция для валидации входных данных с использованием регулярного выражения
function validateInput($data, $pattern, $fieldName) {
    if (preg_match($pattern, $data)) {
        return $data;
    } else {
        global $errors;
        $errors[$fieldName] = "Некорректное значение поля '$fieldName'. Разрешены только буквы, цифры, пробелы и знаки препинания.";
        return "";
    }
}
?>
