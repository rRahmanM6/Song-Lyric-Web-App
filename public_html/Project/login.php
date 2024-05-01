<?php
require_once(__DIR__ . "/../../partials/nav.php");
?>
<div class="container-fluid">
    <form onsubmit="return validate(this)" method="POST">
        <?php render_input(["type" => "text", "id" => "email", "name" => "email", "label" => "Email", "rules" => ["required" => true]]); ?>
        <?php render_input(["type" => "password", "id" => "password", "name" => "password", "label" => "Password", "rules" => ["required" => true, "minlength" => 8]]); ?>
        <?php render_button(["text" => "Login", "type" => "submit"]); ?>
    </form>
</div>

<script>
    function validate(form) {
        var emailField = form.email.value.trim();
        var passwordField = form.password.value.trim();
        
        if (emailField === "") {
            flash("Email/Username must be provided [client]", "warning");
            return false;
        }
        
        if (passwordField === "") {
            flash("Password must be provided [client]", "warning");
            return false;
        }

        if (emailField.includes("@")) {
            if (!isValidEmail(emailField)) {
                flash("Please enter a valid email address [client]", "warning");
                return false;
            }

        }
        
        if (passwordField.length < 8) {
            flash("Password must be at least 8 characters long [client]", "warning");
            return false;
        }

        return true;
    }

    function isValidEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

</script>

<?php
if (isset($_POST["email"]) && isset($_POST["password"])) {
    $email = se($_POST, "email", "", false); 
    $password = se($_POST, "password", "", false); 

    //TODO 3
    $hasError = false;
    if (empty($email)) {
        flash("Email must be provided <br>");
        $hasError = true;
    }

    if (str_contains($email, "@")) {
        $email = sanitize_email($email);

        if (!is_valid_email($email)) {
            flash("Invalid email address");
            $hasError = true;
        }
    } else {
        if (!is_valid_username($email)) {
            flash("Invalid username");
            $hasError = true;
        }
    }
    if (empty($password)) {
        flash("Password must be provided <br>");
        $hasError = true;
    }
    if (strlen($password) < 8) {
        flash("Password must be at least 8 characters long <br>");
        $hasError = true;
    }
    if (!$hasError) {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, email, username, password from Users where email = :email or username = :email");
        try {
            $r = $stmt->execute([":email" => $email]);
            if ($r) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $hash = $user["password"];
                    unset($user["password"]);
                    if (password_verify($password, $hash)) {
                        $_SESSION["user"] = $user;
                        try {
                            $stmt = $db->prepare("SELECT Roles.name FROM Roles 
                        JOIN UserRoles on Roles.id = UserRoles.role_id 
                        where UserRoles.user_id = :user_id and Roles.is_active = 1 and UserRoles.is_active = 1");
                            $stmt->execute([":user_id" => $user["id"]]);
                            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC); 
                        } catch (Exception $e) {
                            error_log(var_export($e, true));
                        }
                        if (isset($roles)) {
                            $_SESSION["user"]["roles"] = $roles;
                        } else {
                            $_SESSION["user"]["roles"] = []; 
                        }
                        flash("Welcome, " . get_username());
                        die(header("Location: search.php"));
                    } else {
                        flash("Invalid password");
                    }
                } else {
                    flash("Email not found");
                }
            }
        } catch (Exception $e) {
            flash("<pre>" . var_export($e, true) . "</pre>");
        }
    }
}
?>
<?php require_once(__DIR__ . "/../../partials/flash.php");