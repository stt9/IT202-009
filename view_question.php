<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}

if (isset($_GET["survey_id"])) {
    $sid = $_GET["survey_id"];
}
?>
<?php
//fetching
$result = [];
if (isset($id)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT Questions.id,Questions.question,Questions.survey_id, Users.username, Survey.title as title FROM Questions JOIN Users on Questions.user_id = Users.id LEFT JOIN Survey on Survey.id = Questions.survey_id where Questions.id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
}
?>
    <h3>View Question</h3>
<?php if (isset($result) && !empty($result)): ?>
    <div class="card">
        <div class="card-title">
            <?php safer_echo($result["question"]); ?>
        </div>
        <div class="card-body">
            <div>
                <p>Details</p>
                <div>Survey: <?php safer_echo($result["title"]); ?></div>
                <div>Survey ID: <?php safer_echo($result["survey_id"]); ?></div>
                <div>Created by: <?php safer_echo($result["username"]); ?></div>
                <a class="btn btn-primary" type="button" href="list_questions.php?id=<?php safer_echo($sid); ?>">Go back to Search</a>
            </div>
        </div>
    </div>
<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>
<?php require(__DIR__ . "/partials/flash.php");
