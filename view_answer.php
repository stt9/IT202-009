<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
if (isset($_GET["question_id"])) {
    $qid = $_GET["question_id"];
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
    $stmt = $db->prepare("SELECT Answers.id,Answers.answer,Answers.question_id, Users.username, Questions.question as question FROM Answers JOIN Users on Answers.user_id = Users.id LEFT JOIN Questions on Questions.id = Answers.question_id where Answers.id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
}
?>
    <h3>View Answer</h3>
<?php if (isset($result) && !empty($result)): ?>
    <div class="card">
        <div class="card-title">
            <?php safer_echo($result["answer"]); ?>
        </div>
        <div class="card-body">
            <div>
                <p>Details</p>
                <div>Question: <?php safer_echo($result["question"]); ?></div>
                <div>Question ID: <?php safer_echo($result["question_id"]); ?></div>
                <div>Created by: <?php safer_echo($result["username"]); ?></div>
                <a class="btn btn-primary" type="button" href="list_answers.php?id=<?php safer_echo($qid); ?>&survey_id=<?php safer_echo($sid); ?>">Go back to Search</a>
            </div>
        </div>
    </div>
<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>
<?php require(__DIR__ . "/partials/flash.php");
