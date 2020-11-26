<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
if (isset($_GET["id"])){
    $id = $_GET["id"];
}
?>
<?php
$result = [];
if (isset($id)){
    $db = getDB();
    $stmt = $db->prepare("SELECT Survey.id, title, description, visibility, modified,  user_id,  Users.username FROM Survey JOIN Users on Survey.user_id = Users.id where Survey.id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt ->fetch(PDO::FETCH_ASSOC);
    if (!$result){
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
}
?>
    <h2>View Survey</h2>
<?php if (isset($result) && !empty($result)): ?>
    <div class="card">
        <div class="card-title">
            <h3><?php safer_echo($result["title"]); ?></h3>
        </div>
        <div class="card-body">
            <div>
                <p>Details</p>
                <div>Description: <?php safer_echo($result["description"]); ?></div>
                <div>Visibility: <?php getVisibility($result["visibility"]); ?></div>
                <div>Last modified on: <?php safer_echo($result["modified"]); ?></div>
                <div>Survey ID: <?php safer_echo($result["id"]); ?></div>
                <div>Owned by: <?php safer_echo($result["username"]); ?></div>
            </div>
        </div>
    </div>
<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>
<?php require_once(__DIR__ . "/partials/flash.php");