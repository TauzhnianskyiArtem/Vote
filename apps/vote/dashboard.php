<?php


$json = file_get_contents( "https://vote.pnit.od.ua/get/result", false);
$result = json_decode($json, TRUE)['data'];
$headRSI = $result['headRSI'];
$membersRSI = $result['membersRSI'];
?>

<!DOCTYPE html>
<html lang="">
<head>
<title>Результати виборів</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<link href="frontend/layout/styles/layout.css" rel="stylesheet" type="text/css" media="all">
</head>
<body id="top">

<div class="wrapper bgded overlay" style="background-image:url('frontend/images/01.jpg')">
  <div id="pageintro" class="hoc clear">
    <article>
      <h3 class="heading">Результати виборів</h3>
   </article>

  </div>
</div>

<div class="wrapper row2">
  <section id="introblocks" class="hoc container clear">
    <ul class="nospace group">
      <li class="one_third first">
        <article class="art-first">
          <h6 class="heading underline">Голова РСІ:</h6>
          <p><?php echo $headRSI?></p>
        </article>
      </li>
      <li class="one_third">
        <article>
          <h6 class="heading underline">Члени РСІ:</h6>
          <p><?php foreach ($membersRSI as $member)
                        echo $member . '<br>';
              ?></p>
        </article>
      </li>
    </ul>
  </section>
</div>
</body>
</html>