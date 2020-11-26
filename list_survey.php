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
                <?php foreach ($results as $r): ?>
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col">
                                <div>Title:</div>
                                <div><?php safer_echo($r["title"]); ?></div>
                            </div>
                            <div class="col">
                                <a class="btn btn-success" type="button" href="view_survey.php?id=<?php safer_echo($r['id']); ?>">View</a>
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
