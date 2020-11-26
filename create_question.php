<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php

if(isset($_GET["id"])){
    $id = $_GET["id"];
}
?>

<?php
if (isset($_POST["save"])) {

    $db = getDB();
    $question = $_POST["question"];
    $user = get_user_id();
    $stmt = $db->prepare("INSERT INTO Questions (question, survey_id, user_id) VALUES(:question, :survey_id, :user)");
    $r = $stmt->execute([
        ":question" => $question,
        ":survey_id" => $id,
        ":user" => $user
    ]);
    if ($r) {
        //flash("Created successfully with id: " . $db->lastInsertID());
        $qid = $db->lastInsertId();
        die(header("Location: create_answer.php?id=$qid&survey_id=$id"));
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
    $stmt = $db->prepare("SELECT * FROM Questions where survey_id=:sid");
    $r = $stmt->execute([":sid" => $id]);
    if($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else{
        $e = $stmt->errorInfo();
    }
}
?>
    <div class="container-fluid">
        <h3>Create Question</h3>
        <h5>Must have at least 1 question per survey</h5>
        <form method="POST">
            <div class="form-group">
                <label>Question</label>
                <input class="form-control" name="question" placeholder="Question"/>
            </div>
            <input class="btn btn-primary" type="submit" name="save" value="Add Answers"/>
            <?php if(count($results) > 0): ?>
                <a class="btn btn-success" type="button" href="edit_survey.php?id=<?php echo($id); ?>">View Survey</a>
            <?php else: ?>
                <p>Please add a question</p>
            <?php endif; ?>
        </form>
    </div>
<?php require(__DIR__ . "/partials/flash.php");
