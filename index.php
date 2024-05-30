<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрационная форма</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Регистрационная форма</h2>
        <form action="process_form.php" method="POST">
            <label for="fio">ФИО:</label>
            <input type="text" id="fio" name="fio" required>

            <label for="phone">Телефон:</label>
            <input type="tel" id="phone" name="phone" required>

            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" required>

            <label for="dob">Дата рождения:</label>
            <input type="date" id="dob" name="dob" required>

            <label>Пол:</label>
            <input type="radio" id="male" name="gender" value="male" required>
            <label for="male">Мужской</label>
            <input type="radio" id="female" name="gender" value="female" required>
            <label for="female">Женский</label>

            <label for="languages">Любимый язык программирования:</label>
            <select id="languages" name="languages[]" multiple required>
                <option value="Pascal">Pascal</option>
                <option value="C">C</option>
                <option value="C++">C++</option>
                <option value="JavaScript">JavaScript</option>
                <option value="PHP">PHP</option>
                <option value="Python">Python</option>
                <option value="Java">Java</option>
                <option value="Haskel">Haskel</option>
                <option value="Clojure">Clojure</option>
                <option value="Prolog">Prolog</option>
                <option value="Scala">Scala</option>
            </select>

            <label for="bio">Биография:</label>
            <textarea id="bio" name="bio" required></textarea>

            <input type="checkbox" id="agreement" name="agreement" required>
            <label for="agreement">С контрактом ознакомлен</label>

            <input type="submit" value="Сохранить">
        </form>
    </div>
</body>
</html>
