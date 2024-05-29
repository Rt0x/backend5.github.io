<?PHP

$user = 'u67307';
$pass = '2532509';
$db = new PDO(
    'mysql:host=localhost;dbname=u67307',
    $user,
    $pass,
    [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

try {
    $stmt = $db->prepare("INSERT INTO programming_languages (language_name) VALUES (:language_name)");
    $lange = $_POST['lange'];

    $kl = implode($Languages);
    $stmt->bindParam(':language_name', $kl);
    $stmt->execute();

} catch (PDOException $e) {
    print ('Error : ' . $e->getMessage());
    exit();
}

?>