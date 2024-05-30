<?php
session_start();

$servername = "localhost";
$username = "u67296";
$password = "5237724";
$dbname = "u67296";

function setCookieValue($name, $value) {
    if (is_array($value)) {
        $value = implode(',', $value);
    }
    setcookie($name, (string)$value, time() + (86400 * 365), "/"); // 1 year
}

function getCookieValue($name) {
    return isset($_COOKIE[$name]) ? $_COOKIE[$name] : '';
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Ошибка подключения к базе данных: " . $e->getMessage();
    die();
}

$errors = [];
$formData = [];
$loginData = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $fio = filter_var($_POST['fio'], FILTER_SANITIZE_STRING);
        $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $dob = $_POST['dob'];
        $gender = $_POST['gender'];
        $languages = $_POST['language'];
        $bio = filter_var($_POST['bio'], FILTER_SANITIZE_STRING);
        $agreement = isset($_POST['agreement']) ? 1 : 0;

        // Validation
        if (!preg_match("/^[a-zA-Zа-яА-Я\s]+$/", $fio) || strlen($fio) > 150) {
            $errors['fio'] = "Некорректное ФИО.";
        }
        if (!$email) {
            $errors['email'] = "Некорректный email.";
        }

        $formData = [
            'fio' => $fio,
            'phone' => $phone,
            'email' => $email,
            'dob' => $dob,
            'gender' => $gender,
            'languages' => $languages,
            'bio' => $bio,
            'agreement' => $agreement
        ];

        if (empty($errors)) {
            $conn->beginTransaction();
            try {
                // Insert user data
                $stmt = $conn->prepare("INSERT INTO osnova (fio, phone, email, dob, gender, bio, agreement) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$fio, $phone, $email, $dob, $gender, $bio, $agreement]);
                $userId = $conn->lastInsertId();

                // Insert user languages
                foreach ($languages as $language) {
                    try {
                        $stmt = $conn->prepare("INSERT INTO osnova_languages (user_id, language_id) VALUES (?, (SELECT id FROM languages WHERE name = ?))");
                        $stmt->execute([$userId, $language]);
                    } catch (PDOException $e) {
                        throw new Exception("Ошибка добавления языка программирования: " . $e->getMessage());
                    }
                }

                // Generate login credentials
                try {
                    $password = bin2hex(random_bytes(8));
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (user_id, password_hash) VALUES (?, ?)");
                    $stmt->execute([$userId, $hashedPassword]);
                } catch (Exception $e) {
                    throw new Exception("Ошибка создания учетных данных пользователя: " . $e->getMessage());
                }

                $loginData = [
                    'userId' => $userId,
                    'password' => $password
                ];

                // Save form data to cookies
                foreach ($formData as $key => $value) {
                    setCookieValue($key, $value);
                }

                $conn->commit();

                $_SESSION['loginData'] = $loginData;
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } catch (Exception $e) {
                $conn->rollBack();
                $errors['db'] = $e->getMessage();
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
    $loginData = $_SESSION['loginData'] ?? [];

    foreach ($formData as $key => $value) {
        setCookieValue($key, $value);
    }

    unset($_SESSION['errors'], $_SESSION['formData'], $_SESSION['loginData']);
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
        <label for="fio">ФИО:</label>
        <input type="text" id="fio" name="fio" value="<?php echo getCookieValue('fio'); ?>" required>
        <?php if (isset($errors['fio'])) echo '<span class="error">'.$errors['fio'].'</span>'; ?><br>

        <label for="phone">Телефон:</label>
        <input type="tel" id="phone" name="phone" value="<?php echo getCookieValue('phone'); ?>" required><br>

        <label for="email">e-mail:</label>
        <input type="email" id="email" name="email" value="<?php echo getCookieValue('email'); ?>" required>
        <?php if (isset($errors['email'])) echo '<span class="error">'.$errors['email'].'</span>'; ?><br>

        <label for="dob">Дата рождения:</label>
        <input type="date" id="dob" name="dob" value="<?php echo getCookieValue('dob'); ?>" required><br>

        <label for="gender">Пол:</label>
        <input type="radio" id="male" name="gender" value="male" <?php echo getCookieValue('gender') == 'male' ? 'checked' : ''; ?> required>Мужчина
        <input type="radio" id="female" name="gender" value="female" <?php echo getCookieValue('gender') == 'female' ? 'checked' : ''; ?> required>Женщина<br>

        <label for="language">Любимый язык программирования:</label>
        <select id="language" name="language[]" multiple required>
            <?php
            try {
                $stmt = $conn->query("SELECT name FROM languages");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="'.$row['name'].'"'.(in_array($row['name'], explode(',', getCookieValue('languages'))) ? ' selected' : '').'>'.$row['name'].'</option>';
                }
            } catch (PDOException $e) {
                echo "Ошибка загрузки языков программирования: " . $e->getMessage();
            }
            ?>
        </select><br>

        <label for="bio">Биография:</label>
        <textarea id="bio" name="bio" required><?php echo getCookieValue('bio'); ?></textarea><br>

        <label for="agreement">С контрактом ознакомлен:</label>
        <input type="checkbox" id="agreement" name="agreement" <?php echo getCookieValue('agreement') ? 'checked' : ''; ?>><br>

        <input type="submit" value="Сохранить">
    </form>

    <?php if (!empty($loginData)): ?>
        <div class="login-info">
            <p>Ваш логин: <?php echo $loginData['userId']; ?></p>
            <p>Ваш пароль: <?php echo $loginData['password']; ?></p>
        </div>
    <?php endif; ?>
</body>
</html>
