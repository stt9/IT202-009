<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php require_once(__DIR__ . "/lib/helpers.php"); ?>
<?php
if(isset($_GET["id"])){
    $id = $_GET["id"];
}

if(isset($_GET["survey_id"])){
    $sid = $_GET["survey_id"];
}
?>

<?php
if (isset($_POST["save"])) {
    $answer = $_POST["answer"];
    $user = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO Answers (answer, question_id, user_id) VALUES(:answer, :question_id, :user)");
    $r = $stmt->execute([
        ":answer" => $answer,
        ":question_id" => $id,
        ":user" => $user
    ]);
    if ($r) {
        flash("Created successfully with id: " . $db->lastInsertID());
    }
    else{
        $e = $stmt->errorInfo();
        flash("Error creating: " . var_export($e, true));
    }
}
?>
<?php
$results = [];
if (isset($id)){
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Answers where question_id=:qid");
    $r = $stmt->execute([":qid" => $id]);
    if($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else{
        $e = $stmt->errorInfo();
    }
}
?>
    <div class="container-fluid">
        <h3>Create Answer</h3>
        <form method="POST">
            <div class="form-group">
                <h5>Must have at least 2 answers</h5>
                <label>Answer</label>
                <input class="form-control" name="answer" placeholder="Answer"/>
            </div>
            <input class="btn btn-secondary" type="submit" name="save" value="Add Answer"/>
            <?php if (count($results) > 1): ?>
                <a class="btn btn-primary" type="button" href="create_question.php?id=<?php echo($sid); ?>">Add new question</a>
                <a class="btn btn-success" type="button" href="edit_survey.php?id=<?php echo($sid); ?>">View Survey</a>
            <?php else: ?>
                <p>Please add answers</p>
            <?php endif; ?>
        </form>
    </div>
<?php require(__DIR__ . "/partials/flash.php");