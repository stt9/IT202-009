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
    $stmt = $db->prepare("SELECT q.id as GroupId, q.id as QuestionId, q.question, s.id as SurveyId, s.title as SurveyName, s.description as SurveyDesc, a.id as Ans>
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

    $stmt = $db->prepare("SELECT question_id as GroupId, question_id as QuestionId, survey_id as SurveyId, answer_id as AnswerId FROM Responses where survey_id = :>
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




$percentage = [];

foreach ($questions as $index => $question){
    $qid = $question["questionId"];
    foreach ($question["answers"] as $answer){
        $aid = $answer["answerId"];
        $counter = 0;
        foreach ($responses as $ind => $questionid){
            $counter2 = 0;
            foreach ($questionid as $answerid){
                if (!isset($percentage[$qid][$aid])){
                    $percentage[$qid][$aid] = [];
                }
                if (!isset($percentage[$qid]["answer counter"])){
                    $percentage[$qid]["answer counter"] = [];
                }
                if ($answerid == $aid){
                    array_push($percentage[$qid][$aid], $counter2++);
                    array_push($percentage[$qid]["answer counter"], $counter++);
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
            <p class="text-center"><?php safer_echo($answer["answer"]); ?><b><?php echo " ". (round(((count($math[$qid][$aid])/count($math[>
                        <?php endforeach; ?>
        </div>
        </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>
    </div>
<?php require(__DIR__ . "/partials/flash.php"); ?>
