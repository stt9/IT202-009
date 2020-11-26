<?php require_once(__DIR__ . "/partials/nav.php");?>
<?php
if(isset($_GET["id"])){
    $id = $_GET["id"];
}
?>
<?php
if(isset($_POST["save"])){
    $title = $_POST["title"];
    $description = $_POST["description"];
    $visibility = $_POST["visibility"];
    $user = get_user_id();
    $db = getDB();
    if(isset($id)){
        $stmt = $db->prepare("UPDATE Survey set title=:title, description=:description, visibility=:visibility where id=:id");
        $r = $stmt->execute([
            ":title"=>$title,
            ":description"=>$description,
            ":visibility"=>$visibility,
            ":id"=>$id,
        ]);
        if($r){
            flash("Updated successfully with id: " . $id);
        }
        else{
            $e = $stmt->errorInfo();
            flash("Error updating:". var_export($e, true));
        }
    }
    else{
        flash("ID isn't set, we need an ID in order to update");
    }


}
elseif (isset($_POST["deleteq"])) {
    $itemID = $_POST["deleteq"];
    deleteQuestion($itemID);
}
?>
<?php
//fetching
$result = [];
$qa_result = [];
$i = 1;
if(isset($id)) {
    $id = $_GET["id"];
    $user = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Survey where id = :id AND user_id = :user_id");
    $r = $stmt->execute([
        ":id" => $id,
        ":user_id" => $user
    ]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $db->prepare("SELECT q.id as GroupId, q.id as QuestionId, q.question, s.id as SurveyId, s.title as SurveyName, a.id as AnswerId, a.answer FROM Survey as s JOIN Questions as q on s.id = q.survey_id JOIN Answers as a on a.question_id = q.id WHERE s.id = :survey_id");
    $r = $stmt->execute([":survey_id" => $id]);
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
    }
}

?>

    <div class="container-fluid">
        <form method="POST">
            <?php if ($result["user_id"] == get_user_id()): ?>
                <div class="form-group">
                    <label>Title</label>
                    <input class="form-control" name="title" placeholder="Title" value="<?php echo $result["title"];?>"/>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input class="form-control" name="description" placeholder="Description" value="<?php echo $result["description"];?>"/>
                </div>
                <div class="form-group">
                    <label>Visibility</label>
                    <?php if (count($questions) > 0): ?>
                        <select class="form-control" name="visibility" value="<?php echo $result["visibility"];?>">
                            <option value="0" <?php echo ($result["visibility"] == "0"?'selected=selected"selected"':'');?>>Draft</option>
                            <option value="1" <?php echo ($result["visibility"] == "1"?'selected=selected"selected"':'');?>>Private</option>
                            <option value="2" <?php echo ($result["visibility"] == "2"?'selected=selected"selected"':'');?>>Public</option>
                        </select>
                    <?php else: ?>
                        <p>Draft</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>You are not the owner of this survey</p>
            <?php endif; ?>
            <input class="btn btn-primary" type="submit" name="save" value="Update"/>
        </form>

        <div class="results">
            <?php if (count($questions) > 0): ?>
                <div class="list-group">
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="list-group-item">
                            <div class="h2"><?php echo "Question " . $i; ?></div>
                            <div class="h5 justify-content-center text-center"><?php safer_echo($question["question"]); ?></div>
                            <form method="POST">
                                <div>
                                    <p align="right">
                                        <input type="hidden" name="deleteq" value="<?php echo($index); ?>"/>
                                        <input class="btn btn-danger " type="submit" value="X"/>
                                        <a class="btn btn-success" type="button" href="edit_question.php?id=<?php safer_echo($index); ?>&survey_id=<?php safer_echo($id); ?>">Edit Question <?php echo $i++; ?></a>
                                    </p>
                                </div>
                            </form>
                            <div>
                                <?php foreach ($question["answers"] as $answer): ?>
                                    <?php $eleId = $index . '-' . $answer["answerId"]; ?>
                                    <p class="text-center"><?php safer_echo($answer["answer"]); ?></p>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Please add questions</p>
            <?php endif; ?>
        </div>
        <br>
        <a class="btn btn-primary" type="button" href="create_question.php?id=<?php echo($id); ?>">Add Questions</a>
    </div>

<?php require(__DIR__ . "/partials/flash.php");?>