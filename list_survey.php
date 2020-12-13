<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
$query = "";
$results = [];
if (isset($_POST["query"])){
    $query = $_POST["query"];
}
?>
<?php

if (isset($_POST["search"]) && !empty($query)){
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Survey Where visibility = 2 AND (title like :q) LIMIT 10");
    $r = $stmt->execute([":q" => "%$query%"]);
    if ($r) {
        $results = $stmt->fetchALL(PDO::FETCH_ASSOC);
    }
    else{
        flash("There was a problem fetching the results");
    }
}
else{
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Survey Where visibility = 2 LIMIT 10");
    $r = $stmt->execute([":q" => "%$query%"]);
    if ($r) {
        $results = $stmt->fetchALL(PDO::FETCH_ASSOC);
    }
    else{
        flash("There was a problem fetching the results");
    }
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
    <h3>List Surveys</h3>
    <form method="POST" class="form-inline">
        <input class="form-control" name="query" placeholder="Search" value="<?php safer_echo($query); ?>"/>
<input class="btn btn-primary" type="submit" value="Search" name="search"/>
</form>

<div class="results">
    <?php if (count($results) > 0): ?>
    <div class="list-group">
        <?php if (count($results) > 0): ?>
            <div class="list-group">
                <?php foreach ($results as $r): ?>
                    <div class="list-group-item">
                        <?php foreach($taken as $ind): ?>
                            <?php if ($ind["title"] == $r["title"]): ?>
                                <div class="row">
                                    <div class="col">
                                        <div>Survey Title:</div>
                                        <div><?php safer_echo($r["title"]); ?></div>
                                    </div>
                                    <div class="col">
                                        <a class="btn btn-success" type="button" href="user_survey.php?id=<?php safer_echo($r['id']); ?>">Start Survey</a>
                                        <?php if (intval($ind["total"]) > 0): ?>
                                            <a class="btn btn-primary" type="button" href="resultPage.php?id=<?php safer_echo($r['id']); ?>">Result</a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col">
                                        <div>Times Survey Taken:</div> <div><?php safer_echo($ind["total"]); ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No results</p>
        <?php endif; ?>
    </div>
</div>
<?php require(__DIR__ . "/partials/flash.php"); ?>
