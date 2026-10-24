<?php require 'config.php';
$topics = $pdo->query('SELECT id,name FROM topics ORDER BY name')->fetchAll(); ?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Quick Code Quiz</title><link rel="stylesheet" href="style.css"></head><body><div class="box">
<h1>Quick Code Quiz</h1><p>Pick a language and answer 5 questions.</p>
<form action="quiz.php" method="get">
<select name="topic" required><option value="">Choose a topic...</option>
<?php foreach($topics as $t): ?><option value="<?=$t['id']?>"><?=e($t['name'])?></option><?php endforeach; ?>
</select><br><button>Start quiz</button></form></div></body></html>
