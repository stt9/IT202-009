<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
$results = [];

$db = getDB();
$user = get_user_id();
$stmt = $db->prepare("SELECT * FROM Survey Where Survey.user_id = :user_id LIMIT 10");
$r = $stmt->execute([":user_id" => $user]);
if ($r) {
    $results = $stmt->fetchALL(PDO::FETCH_ASSOC);
}
else{
    flash("There was a problem fetching the results");
}

$stmt = $db->prepare("SELECT Survey.title, Survey.id, count(Responses.survey_id) as total FROM Survey LEFT JOIN (SELECT distinct user_id, survey_id FROM Responses)>
$r = $stmt->execute();
if ($r){
    $taken = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else{
    flash("There was a problem fetching the results");
}
?>

<div class="container-fluid">
    <h3>Personal Surveys</h3>
    <div class="results">
        <?php if (count($results) > 0): ?>
        <div class="list-group">
            <?php foreach ($results as $r): ?>
                <div class="list-group-item">
                    <?php foreach($taken as $ind): ?>
                        <?php if ($ind["title"] == $r["title"]): ?>
                            <div class="row">
                                <div class="col">
                                    <div>Title:</div>
                                    <div><?php safer_echo($r["title"]); ?></div>
                                    </div>
                                <div class="col">
                                    <div>Description:</div>
                                    <div><?php safer_echo($r["description"]); ?></div>
                                </div>
                                <div class="col">
                                    <div>Visibility:</div>
                                    <div><?php getVisibility($r["visibility"]); ?></div>
                                </div>
                                <div class="col">
                                    <a class="btn btn-warning" type="button" href="edit_survey.php?id=<?php safer_echo($r['id']); ?>">Edit</a>
    <a class="btn btn-success" type="button" href="view_survey.php?id=<?php safer_echo($r['id']); ?>">View</a>
    <a class="btn btn-primary" type="button" href="resultPage.php?id=<?php safer_echo($r['id']); ?>">Results</a>
    </div>
    <div class="col">
        <div>Times Survey Taken:</div> <div><?php safer_echo($ind["total"]); ?></div>
    </div>
    </div>
<?php endif; ?>
                    <?php endforeach; ?>
    </div>
<?php endforeach; ?>
            <?php else: ?>
    <p>No results</p>
<?php endif; ?>
        </div>
    </div>
</div>
<?php require(__DIR__ . "/partials/flash.php");