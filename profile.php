<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//Note: we have this up here, so our update happens before our get/fetch
//that way we'll fetch the updated data and have it correctly reflect on the form below
//As an exercise swap these two and see how things change
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

$db = getDB();
$stmt = $db->prepare("SELECT visibility from Users WHERE id = :id LIMIT 1");
$stmt->execute([":id" => get_user_id()]);
$vis_result = $stmt->fetch(PDO::FETCH_ASSOC);
$visibility = $vis_result["visibility"];
//save data if we submitted the form
if (isset($_POST["saved"])) {
    $isValid = true;
    $newEmail = get_email();
    if (get_email() != $_POST["email"]) {
        //TODO we'll need to check if the email is available
        $email = $_POST["email"];
        $stmt = $db->prepare("SELECT COUNT(1) as InUse from Users where email = :email");
        $stmt->execute([":email" => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $inUse = 1;
        if ($result && isset($result["InUse"])) {
            try {
                $inUse = intval($result["InUse"]);
            }
            catch (Exception $e) {

            }
        }
        if ($inUse > 0) {
            flash("Email already in use");
            $isValid = false;
        }
        else {
            $newEmail = $email;
        }
    }
    $newUsername = get_username();
    if (get_username() != $_POST["username"]) {
        $username = $_POST["username"];
        $stmt = $db->prepare("SELECT COUNT(1) as InUse from Users where username = :username");
        $stmt->execute([":username" => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $inUse = 1;
        if ($result && isset($result["InUse"])) {
            try {
                $inUse = intval($result["InUse"]);
            }
            catch (Exception $e) {

            }
        }
        if ($inUse > 0) {
            flash("Username already in use");
            $isValid = false;
        }
        else {
            $newUsername = $username;
        }
    }
    if ($isValid) {
        $visibility = $_POST["visibility"];
        $stmt = $db->prepare("UPDATE Users set email = :email, username= :username, visibility= :visibility where id = :id");
        $r = $stmt->execute([":email" => $newEmail, ":username" => $newUsername, ":id" => get_user_id(), ":visibility" => $visibility]);
        if ($r) {
            flash("Updated profile");
        }
        else {
            flash("Error updating profile");
        }
        if (!empty($_POST["password"]) && !empty($_POST["confirm"])) {
            if ($_POST["password"] == $_POST["confirm"]) {
                $password = $_POST["password"];
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $db->prepare("UPDATE Users set password = :password where id = :id");
                $r = $stmt->execute([":id" => get_user_id(), ":password" => $hash]);
                if ($r) {
                    flash("Reset Password");
                }
                else {
                    flash("Error resetting password");
                }
            }
        }
        $stmt = $db->prepare("SELECT email, username, visibility from Users WHERE id = :id LIMIT 1");
        $stmt->execute([":id" => get_user_id()]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $email = $result["email"];
            $username = $result["username"];
            $visibility = $result["visibility"];
            $_SESSION["user"]["email"] = $email;
            $_SESSION["user"]["username"] = $username;
        }
    }
    else {
    }
}

?>
    <div class="container-fluid">
        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input class="form-control" type="email" name="email" value="<?php safer_echo(get_email()); ?>"/>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input class="form-control" type="text" maxlength="60" name="username" value="<?php safer_echo(get_username()); ?>"/>
            </div>
            <div class="form-group">
                <label>Visibility</label>
                <select class="form-control" name="visibility" value="<?php echo getVisibility($visibility);?>">
                    <option value="1" <?php echo ($visibility == "1"?'selected=selected"selected"':'');?>>Private</option>
                    <option value="2" <?php echo ($visibility == "2"?'selected=selected"selected"':'');?>>Public</option>
                </select>
            </div>
            <!-- DO NOT PRELOAD PASSWORD-->
            <div class="form-group">
                <label for="pw">Password</label>
                <input class="form-control" type="password" name="password"/>
            </div>
            <div class="form-group">
                <label for="cpw">Confirm Password</label>
                <input class="form-control" type="password" name="confirm"/>
            </div>
            <input class="btn btn-primary" type="submit" name="saved" value="Save Profile"/>
        </form>
    </div>
<?php require(__DIR__ . "/partials/flash.php");