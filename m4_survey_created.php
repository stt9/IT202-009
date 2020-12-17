<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (isset($_GET["pid"])){
    $pid = $_GET["pid"];
    $_SESSION["pid"] = $pid;
}
elseif (isset($_SESSION["pid"])){
    $pid = $_SESSION["pid"];
}
if (isset($_GET["pusername"])){
    $pusername = $_GET["pusername"];
    $_SESSION["pusername"] = $pusername;
}
elseif (isset($_SESSION["pusername"])){
    $pusername = $_SESSION["pusername"];
}
?>
<?php
$results = [];
$per_page = 10;

$db = getDB();
$query = "SELECT count(*) as total FROM Survey Where Survey.user_id = :user_id AND visibility = 2";
$params = [":user_id" => $pid];
paginate($query, $params, $per_page);

$stmt = $db->prepare("SELECT * FROM Survey Where Survey.user_id = :user_id AND visibility = 2 LIMIT :offset, :count");
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
$stmt->bindValue(":user_id", $pid);
$r = $stmt->execute();
$e = $stmt->errorInfo();
if($e[0] != "00000"){
    flash(var_export($e, true), "alert");
}
if ($r) {
    $results = $stmt->fetchALL(PDO::FETCH_ASSOC);
}
else{
    flash("There was a problem fetching the results");
}

$stmt = $db->prepare("SELECT Survey.title, Survey.id, count(Responses.survey_id) as total FROM Survey LEFT JOIN (SELECT distinct user_id, survey_id FROM Responses) as Responses on Survey.id = Responses.survey_id GROUP BY title");
$r = $stmt->execute();
if ($r){
    $taken = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else{
    flash("There was a problem fetching the results");
}
?>

    <div class="container-fluid">
        <h3><?php echo $pusername . "'s " ?>Surveys</h3>
        <a class="btn btn-secondary" type="button" href="view_profile.php?id=<?php safer_echo($pid); ?>">Back to profile</a>
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
                                        <?php if (has_role("Admin")): ?>
                                            <a class="btn btn-warning" type="button" href="edit_survey.php?id=<?php safer_echo($r['id']); ?>">Edit</a>
                                        <?php endif; ?>
                                        <?php if (intval($ind["total"]) == 0): ?>
                                            <a class="btn btn-success" type="button" href="survey_taken.php?id=<?php safer_echo($r['id']); ?>">Take Survey</a>
                                        <?php endif; ?>
                                        <?php if (intval($ind["total"]) > 0): ?>
                                            <a class="btn btn-primary" type="button" href="resultPage.php?id=<?php safer_echo($r['id']); ?>">View Results</a>
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
                <?php else: ?>
                    <p>No results</p>
                <?php endif; ?>
            </div>
        </div>
        <?php include(__DIR__."/partials/pagination.php");?>
    </div>
<?php require_once(__DIR__ . "/partials/flash.php");
