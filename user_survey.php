<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
if (isset($_POST["submit"])) {
    echo "<pre>" . var_export($_POST, true) . "</pre>";
    $survey_id = $_GET["id"];
    $user_id = get_user_id();
    $params = [];
    $query = "INSERT INTO Responses (survey_id, question_id, answer_id, user_id) VALUES";//ignore sql error hint
    $i = 0;//can't use $key here since it presents a question_id and will always be > 0, so using a temp var to count
    foreach ($_POST as $key => $item) {
        if (is_numeric($key)) {
            //assuming this is question id
            //assuming value is answer id
            if ($i > 0) {
                $query .= ",";
            }
            $query .= "(:sid, :q$i, :a$i, :uid)";
            $params[":q$i"] = $key;
            $params[":a$i"] = $item;
        }
        $i++;
    }
    $params[":sid"] = $survey_id;
    $params[":uid"] = $user_id;
    $db = getDB();
    $stmt = $db->prepare($query);
    $r = $stmt->execute($params);
    if ($r) {
        die(header("Location: results.php?id=$survey_id"));
    }
    else {
        flash("Error on recording on Answers " . var_export($stmt->errorInfo(), true), "danger");
    }
}
?>


<?php
if (isset($_GET["id"])) {
    $sid = $_GET["id"];
    $db = getDB();
    $stmt = $db->prepare("SELECT q.id as GroupId, q.id as QuestionId, q.question, s.id as SurveyId, s.title as SurveyName, a.id as AnswerId, a.answer FROM Survey a>
    $r = $stmt->execute([":id" => get_user_id(), ":survey_id" => $sid]);
    $name = "";
    $questions = [];
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_GROUP);
        if ($results) {
            foreach ($results as $index => $group) {
                foreach ($group as $details) {
                    if (empty($name)) {
                        $name = $details["SurveyName"];
                    }
                    $qid = $details["QuestionId"];
                    $answer = ["answerId" => $details["AnswerId"], "answer" => $details["answer"]];
                    if (!isset($questions[$qid]["answers"])) {
                        $questions[$qid]["question"] = $details["question"];
                        $questions[$qid]["answers"] = [];
                    }
                    array_push($questions[$qid]["answers"], $answer);
                }
            }
        }
        else {
            flash("Survey have already been taken", "warning");
            die(header("Location: " . getURL("list_survey.php")));
        }
    }
     else {
        flash("There was a problem fetching the survey: " . var_export($stmt->errorInfo(), true), "danger");
        die(header("Location: " . getURL("list_survey.php")));
    }
}
else {
    flash("This Survey is Invalid", "warning");
    die(header("Location: " . getURL("surveys.php")));
}
?>
    <div class="container-fluid">
        <h3><?php safer_echo($name); ?></h3>
        <form method="POST">
            <div class="list-group">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="list-group-item">
                        <div class="h5 justify-content-center text-center"><?php safer_echo($question["question"]); ?></div>
                        <div>
                            <div class="d-flex btn-group-vertical btn-group-toggle w-50 text-center justify-content-center mx-auto"
                                 data-toggle="buttons">
                                <?php foreach ($question["answers"] as $answer): ?>
                                    <?php $eleId = $index . '-' . $answer["answerId"]; ?>
                                    <label class="btn btn-primary m-1 btn-outline-light btn-block" style="border-radius: 0"
                                           role="button" for="option-<?php echo $eleId; ?>">
                                        <input type="radio" name="<?php safer_echo($index); ?>"
                                               id="option-<?php echo $eleId; ?>"
                                               autocomplete="off"
                                               value="<?php safer_echo($answer["answerId"]); ?>">
                                        <?php safer_echo($answer["answer"]); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
             <input type="submit" name="submit" class="btn btn-success btn-block" value="Submit Response"/>
        </form>
    </div>
<?php require(__DIR__ . "/partials/flash.php"); ?>
