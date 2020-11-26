<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (isset($_GET["id"])) {
    $qid = $_GET["id"];
}
if (isset($_GET["survey_id"])) {
    $sid = $_GET["survey_id"];
}

?>
<?php
$query = "";
$results = [];
if (isset($_POST["query"])) {
    $query = $_POST["query"];
}
if (isset($_POST["search"]) && !empty($query)) {
    $db = getDB();
    $user_id = get_user_id();
    $stmt = $db->prepare("SELECT Answers.id,Answers.answer,Questions.question, Users.username from Answers JOIN Users on Answers.user_id = Users.id JOIN Questions on Answers.question_id = Questions.id WHERE (Answers.user_id = :user_id) AND Answers.answer like :q LIMIT 10");
    $r = $stmt->execute([
        ":q" => "%$query%",
        ":user_id" => $user_id
    ]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results " . var_export($stmt->errorInfo(), true));
    }
}
?>
<div class="container-fluid">
    <h3>List Questions</h3>
    <form method="POST" class="form-inline">
        <input class="form-control" name="query" placeholder="Search" value="<?php safer_echo($query); ?>"/>
        <input class="btn btn-primary" type="submit" value="Search" name="search"/>
        <a class="btn btn-success" type="button" href="edit_question.php?id=<?php safer_echo($qid); ?>&survey_id=<?php safer_echo($sid); ?>">Back to question</a>
    </form>
    <div class="results">
        <?php if (count($results) > 0): ?>
            <div class="list-group">
                <?php foreach ($results as $r): ?>
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col">
                                <div>Answer:</div>
                                <div><?php safer_echo($r["answer"]); ?></div>
                            </div>
                            <div class="col">
                                <div>Question:</div>
                                <div><?php safer_echo($r["question"]); ?></div>
                            </div>
                            <div class="col">
                                <div>Creator:</div>
                                <div><?php safer_echo($r["username"]); ?></div>
                            </div>
                            <div class="col">
                                <a class="btn btn-warning" type="button" href="edit_answer.php?id=<?php safer_echo($r['id']); ?>&question_id=<?php safer_echo($qid); ?>&survey_id=<?php safer_echo($sid); ?>">Edit</a>
                                <a class="btn btn-success" type="button" href="view_answer.php?id=<?php safer_echo($r['id']); ?>&question_id=<?php safer_echo($qid); ?>&survey_id=<?php safer_echo($sid); ?>">View</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No results</p>
        <?php endif; ?>
    </div>
</div>
<?php require(__DIR__ . "/partials/flash.php");?>
