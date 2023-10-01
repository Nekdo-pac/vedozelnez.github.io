<!DOCTYPE html>
<html>
<head>
    <title>Prijava</title>
</head>
<body>

<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    
    // Preverite pravilnost uporabniškega imena in gesla
    if (preveriUporabnika($username, $password)) {
        $_SESSION["loggedin"] = true;
        $_SESSION["username"] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Neveljavno uporabniško ime ali geslo.";
    }
}

function preveriUporabnika($username, $password) {
    // Preverjanje uporabniških podatkov v bazi podatkov z uporabo pripravljenih izjav
    $servername = "localhost";
    $dbUsername = "uporabnik";
    $dbPassword = "geslo";
    $dbName = "baza";

    $conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);

    if ($conn->connect_error) {
        die("Povezava do baze podatkov ni uspela: " . $conn->connect_error);
    }

    $sql = "SELECT id, username, password, status FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if ($row["status"] == 1 && password_verify($password, $row["password"])) {
            // Preverite status računa in geslo
            return true;
        }
    }

    return false;
}
?>

<h2>Prijava</h2>
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <label for="username">Uporabniško ime:</label>
    <input type="text" name="username" required><br><br>
    
    <label for="password">Geslo:</label>
    <input type="password" name="password" required><br><br>
    
    <input type="submit" value="Prijava">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
</form>

<?php
function generateCSRFToken() {
    if (!isset($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}
?>

</body>
</html>
