<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <form action="submit_form.php" method="POST">
        <label for="fio">ФИО:</label>
        <input type="text" id="fio" name="fio" required><br>

        <label for="phone">Телефон:</label>
        <input type="tel" id="phone" name="phone" required><br>

        <label for="email">e-mail:</label>
        <input type="email" id="email" name="email" required><br>

        <label for="dob">Дата рождения:</label>
        <input type="date" id="dob" name="dob" required><br>

        <label for="gender">Пол:</label>
        <input type="radio" id="male" name="gender" value="male" required>Мужчина
        <input type="radio" id="female" name="gender" value="female" required>Женщина<br>

        <label for="language">Любимый язык программирования:</label>
        <select id="language" name="language[]" multiple required>
            <option value="Pascal">Pascal</option>
            <option value="C">C</option>
            <option value="C++">C++</option>
            <option value="JavaScript">JavaScript</option>
            <option value="PHP">PHP</option>
            <option value="Python">Python</option>
            <option value="Java">Java</option>
            <option value="Haskell">Haskell</option>
            <option value="Clojure">Clojure</option>
            <option value="Prolog">Prolog</option>
            <option value="Scala">Scala</option>
        </select><br>

        <label for="bio">Биография:</label>
        <textarea id="bio" name="bio" required></textarea><br>

        <label for="agreement">С контрактом ознакомлен (а):</label>
        <input type="checkbox" id="agreement" name="agreement" required><br>

        <button type="submit">Сохранить</button>
    </form>
</body>
</html>
