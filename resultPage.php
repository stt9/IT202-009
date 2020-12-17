<?php require_once(__DIR__ . "/partials/nav.php");?>
<?php
if(isset($_GET["id"])){
    $id = $_GET["id"];
}
?>
<?php
$i = 1;
if(isset($id)) {
    $id = $_GET["id"];
    $db = getDB();
    $stmt = $db->prepare("SELECT q.id as GroupId, q.id as QuestionId, q.question, s.id as SurveyId, s.title as SurveyName, s.description as SurveyDesc, a.id as AnswerId, a.answer FROM Survey as s JOIN Questions as q on s.id = q.survey_id JOIN Answers as a on a.question_id = q.id WHERE s.id = :survey_id");
    $r = $stmt->execute([":survey_id" => $id]);
    $name = "";
    $desc = "";
    $questions = [];
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_GROUP);

        if ($results) {
            foreach ($results as $index => $group) {
                foreach ($group as $details) {
                    if (empty($name)) {
                        $name = $details["SurveyName"];
                    }
                    if (empty($desc)) {
                        $desc = $details["SurveyDesc"];
                    }
                    $qid = $details["QuestionId"];
                    $answer = ["answerId" => intval($details["AnswerId"]), "answer" => $details["answer"]];
                    if (!isset($questions[$qid]["answers"])) {
                        $questions[$qid]["question"] = $details["question"];
                        $questions[$qid]["questionId"] = intval($details["QuestionId"]);
                        $questions[$qid]["answers"] = [];
                    }
                    array_push($questions[$qid]["answers"], $answer);
                }
            }
        }
    }

    $stmt = $db->prepare("SELECT question_id as GroupId, question_id as QuestionId, survey_id as SurveyId, answer_id as AnswerId FROM Responses where survey_id = :sid");
    $r = $stmt->execute([":sid" => $id]);
    $responses = [];
    if ($r){
        $outcome = $stmt->fetchAll(PDO::FETCH_GROUP);

        if ($outcome){
            foreach ($outcome as $index => $group){
                foreach ($group as $details){
                    $qid = $details["QuestionId"];
                    $answer = intval($details["AnswerId"]);
                    $sid = intval($details["SurveyId"]);

                    if (!isset($responses[$qid])){
                        $responses[$qid] = [];
                    }
                    array_push($responses[$qid], $answer);
                }
            }
        }
    }
}




$math = [];

foreach ($questions as $index => $question){
    $qid = $question["questionId"];
    foreach ($question["answers"] as $answer){
        $aid = $answer["answerId"];
        $answercounter = 0;
        foreach ($responses as $ind => $questionid){
            $counter = 0;
            foreach ($questionid as $answerid){
                if (!isset($math[$qid][$aid])){
                    $math[$qid][$aid] = [];
                }
                if (!isset($math[$qid]["answer counter"])){
                    $math[$qid]["answer counter"] = [];
                }
                if ($answerid == $aid){
                    array_push($math[$qid][$aid], $counter++);
                    array_push($math[$qid]["answer counter"], $answercounter++);
                }
            }
        }
    }
}
?>
<h2>Survey Results for: <?php echo $name ?></h2>
<h4>Survey Description: <?php echo $desc ?></h4>
<div class="results">
    <?php if (count($questions) > 0): ?>
        <div class="list-group">
            <?php foreach ($questions as $index => $question): ?>
                <?php $qid = $question["questionId"]; ?>
                <div class="list-group-item">
                    <div class="h2"><?php echo "Question " . $i++; ?></div>
                    <div class="h5 justify-content-center text-center"><?php safer_echo($question["question"]); ?></div>
                    <div>
                        <?php foreach ($question["answers"] as $answer): ?>
                            <?php $aid = $answer["answerId"]; ?>
                            <p class="text-center"><?php safer_echo($answer["answer"]); ?>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo (round(((count($math[$qid][$aid])/count($math[$qid]["answer counter"]))*100))) . "%"; ?>;" aria-valuenow="<?php echo (round(((count($math[$qid][$aid])/count($math[$qid]["answer counter"]))*100))) ?>" aria-valuemin="0" aria-valuemax="100"><b><?php echo " ". (round(((count($math[$qid][$aid])/count($math[$qid]["answer counter"]))*100))) . "%"; ?></b></div>
                            </div>
                            </p>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php require(__DIR__ . "/partials/flash.php"); ?>
