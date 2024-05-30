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

$errors = $formData = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Валидация и сбор данных из формы
        $formData = [
            'fio' => filter_var($_POST['fio'], FILTER_SANITIZE_STRING),
            'phone' => filter_var($_POST['phone'], FILTER_SANITIZE_STRING),
            'email' => filter_var($_POST['email'], FILTER_VALIDATE_EMAIL),
            'dob' => $_POST['dob'],
            'gender' => $_POST['gender'],
            'languages' => $_POST['language'],
            'bio' => filter_var($_POST['bio'], FILTER_SANITIZE_STRING),
            'agreement' => isset($_POST['agreement']) ? 1 : 0
        ];

        // Проверка на ошибки
        if (!preg_match("/^[a-zA-Zа-яА-Я\s]+$/", $formData['fio']) || strlen($formData['fio']) > 150 || !$formData['email']) {
            $errors['general'] = "Некорректные данные.";
        }

        if (empty($errors)) {
            $conn->beginTransaction();
            try {
                // Вставка данных пользователя
                $stmt = $conn->prepare("INSERT INTO osnova (fio, phone, email, dob, gender, bio, agreement) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$formData['fio'], $formData['phone'], $formData['email'], $formData['dob'], $formData['gender'], $formData['bio'], $formData['agreement']]);
                $userId = $conn->lastInsertId();

                // Вставка языков программирования
                foreach ($formData['languages'] as $language) {
                    $stmt = $conn->prepare("INSERT INTO osnova_languages (user_id, language_id) VALUES (?, (SELECT id FROM languages WHERE name = ?))");
                    $stmt->execute([$userId, $language]);
                }

                // Генерация учетных данных
                $password = bin2hex(random_bytes(8));
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (user_id, password_hash) VALUES (?, ?)");
                $stmt->execute([$userId, $hashedPassword]);

                $conn->commit();

                $_SESSION['loginData'] = [
                    'userId' => $userId,
                    'password' => $password
                ];
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } catch (PDOException $e) {
                $conn->rollBack();
                $errors['db'] = "Ошибка базы данных: " . $e->getMessage();
            }
        } else {
            $_SESSION['errors'] = $errors;
            $_SESSION['formData'] = $formData;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    } catch (Exception $e) {
        $errors['general'] = $e->getMessage();
    }
} else {
    $formData = $_SESSION['formData'] ?? [];
    $errors = $_SESSION['errors'] ?? [];

    unset($_SESSION['errors'], $_SESSION['formData']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <!-- Форма остается без изменений -->

        <!-- Отображение ошибок -->
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Отображение данных из cookies -->
        <?php foreach ($formData as $key => $value): ?>
            <input type="hidden" name="<?php echo $key; ?>" value="<?php echo htmlspecialchars($value); ?>">
        <?php endforeach; ?>

        <input type="submit" value="Сохранить">
    </form>

    <!-- Отображение логин-данных -->
    <?php if (!empty($_SESSION['loginData'])): ?>
        <div class="login-info">
            <p>Ваш логин: <?php echo $_SESSION['loginData']['userId']; ?></p>
            <p>Ваш пароль: <?php echo $_SESSION['loginData']['password']; ?></p>
        </div>
    <?php endif; ?>
</body>
</html>
