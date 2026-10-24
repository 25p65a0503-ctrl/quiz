<?php require 'config.php';
$tid = (int)($_REQUEST['topic'] ?? 0);
$st = $pdo->prepare('SELECT name FROM topics WHERE id=?'); $st->execute([$tid]);
$topic = $st->fetchColumn();
if (!$topic) { header('Location: index.php'); exit; }
$st = $pdo->prepare('SELECT * FROM questions WHERE topic_id=? ORDER BY RANDOM() LIMIT 5');
$st->execute([$tid]); $qs = $st->fetchAll();
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';
if ($submitted) { // re-load the same questions the user saw and score on the server
  $ids = array_map('intval', array_keys($_POST['a'] ?? []));
  if ($ids) {
    $in = implode(',', array_fill(0, count($ids), '?'));
    $st = $pdo->prepare("SELECT * FROM questions WHERE topic_id=? AND id IN ($in)");
    $st->execute(array_merge([$tid], $ids)); $qs = $st->fetchAll();
  }
  $score = 0;
  foreach ($qs as $q) if ((int)($_POST['a'][$q['id']] ?? 0) === (int)$q['correct']) $score++;
  $total = count($qs);
}
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($topic)?> Quiz</title><link rel="stylesheet" href="style.css"></head><body><div class="box">
<h1><?=e($topic)?> Quiz</h1>
<?php if ($submitted): ?>
  <div class="score">You scored <?=$score?> / <?=$total?></div>
  <p><?= $score==$total ? 'Perfect! Expert level.' : ($score>=$total*0.6 ? 'Good knowledge, a bit more practice!' : 'Keep learning, you will get there!') ?></p>
  <?php foreach ($qs as $i=>$q): $pick=(int)($_POST['a'][$q['id']] ?? 0); ?>
    <div class="q"><b><?=$i+1?>. <?=e($q['question'])?></b>
    <?php for($n=1;$n<=4;$n++): $value = trim((string)($q['opt'.$n] ?? '')); if ($value === '') { $value = 'Option ' . $n; } $cls = $n==$q['correct'] ? 'ok' : ($n==$pick ? 'bad' : ''); ?>
      <label class="<?=$cls?>"><?=e($value)?><?= $n==$q['correct']?' ✓':'' ?></label>
    <?php endfor; ?></div>
  <?php endforeach; ?>
  <a href="quiz.php?topic=<?=$tid?>">Try again</a> · <a href="index.php">Choose another topic</a>
<?php else: ?>
  <form method="post"><input type="hidden" name="topic" value="<?=$tid?>">
  <?php foreach ($qs as $i=>$q): $order=[1,2,3,4]; shuffle($order); ?>
    <div class="q"><b><?=$i+1?>. <?=e($q['question'])?></b>
    <?php foreach ($order as $n): $value = trim((string)($q['opt'.$n] ?? '')); if ($value === '') { $value = 'Option ' . $n; } ?>
      <label><input type="radio" name="a[<?=$q['id']?>]" value="<?=$n?>" required> <?=e($value)?></label>
    <?php endforeach; ?></div>
  <?php endforeach; ?>
  <button>Submit answers</button></form>
<?php endif; ?></div></body></html>
