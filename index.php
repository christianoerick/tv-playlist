<?php
require(__DIR__.'/lib/Tv.class.php');

$tv = new Tv;
$lista = $tv->lista('arquivos/lista.json');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="@christianoerick">
  <title>TV</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.1/animate.min.css">
  <style type="text/css">.jumbotron {padding-top: 3rem;padding-bottom: 3rem;margin-bottom: 0;background-color: #fff;}@media (min-width: 768px) {.jumbotron {padding-top: 3rem;padding-bottom: 3rem;}}.jumbotron p:last-child {margin-bottom: 0;}.jumbotron-heading {font-weight: 300;}.jumbotron .container {max-width: 40rem;}footer {padding-top: 3rem;padding-bottom: 3rem;}footer p {margin-bottom: .25rem;}.bd-placeholder-img {font-size: 1.125rem;text-anchor: middle;-webkit-user-select: none;-moz-user-select: none;-ms-user-select: none;user-select: none;}@media (min-width: 768px) {.bd-placeholder-img-lg {font-size: 3.5rem;}}iframe{display:none}
  .list {
    list-style-type:none;
    padding:0;
    margin:0;
  }
  .list--list-item {
    padding-bottom:20px;
    margin-bottom:20px;
  }
  .list--list-item:last-child {
    border-bottom:0;
    margin:0;
  }
  .no-result {
    display:none;
  }
  .pagination {
    font-size: 24px;
    margin-left: 20px;
  }
  .pagination li {
    margin-right: 20px;
  }
  .pagination li.active a {
    font-weight: bold;
    color: darkred !important;
  }
  </style>
</head>
<body>
<header>
  <div class="navbar navbar-dark bg-dark shadow-sm">
    <div class="container d-flex justify-content-between">
      <a href="./" class="navbar-brand d-flex align-items-center">
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 26 26" version="1.1" width="26" height="26" fill="#FFFFFF"><g id="surface1"><path style=" " d="M 19.875 0 C 19.652344 0.0234375 19.441406 0.125 19.28125 0.28125 L 13 6.5625 L 9.71875 3.28125 C 9.320313 2.882813 8.679688 2.882813 8.28125 3.28125 C 7.882813 3.679688 7.882813 4.320313 8.28125 4.71875 L 11.5625 8 L 3 8 C 1.34375 8 0 9.34375 0 11 L 0 23 C 0 24.65625 1.34375 26 3 26 L 23 26 C 24.65625 26 26 24.65625 26 23 L 26 11 C 26 9.34375 24.65625 8 23 8 L 14.4375 8 L 20.71875 1.71875 C 21.042969 1.417969 21.128906 0.941406 20.933594 0.546875 C 20.742188 0.148438 20.308594 -0.0703125 19.875 0 Z M 10.5 10 C 17.945313 10 19 10.019531 19 17 C 19 23.980469 17.871094 24 10.5 24 C 3.09375 24 2 23.925781 2 17 C 2 10.074219 3.09375 10 10.5 10 Z M 22.5 12.9375 C 23.367188 12.9375 24.0625 13.632813 24.0625 14.5 C 24.0625 15.367188 23.367188 16.0625 22.5 16.0625 C 21.632813 16.0625 20.9375 15.367188 20.9375 14.5 C 20.9375 13.632813 21.632813 12.9375 22.5 12.9375 Z M 22.5 16.9375 C 23.367188 16.9375 24.0625 17.632813 24.0625 18.5 C 24.0625 19.367188 23.367188 20.0625 22.5 20.0625 C 21.632813 20.0625 20.9375 19.367188 20.9375 18.5 C 20.9375 17.632813 21.632813 16.9375 22.5 16.9375 Z "/></g></svg>
        <strong>&nbsp;TV</strong>
      </a>
<?php foreach($tv->getMenu() as $menu_chave => $menu_item) { if(trim($menu_item['nome']) !== "") { ?>
      <a class="btn btn-sm btn-outline-secondary <?php echo ($tv->getModulo() == $menu_chave)?'active':''; ?>" href="./?modulo=<?php echo $menu_chave; ?>"><?php echo $menu_item['nome']; ?></a>
<?php }} ?>
    </div>
  </div>
</header>
<main role="main">
  <section class="jumbotron text-center">
    <div class="container">
      <h1 class="jumbotron-heading"><?php echo $tv->getTitulo(); ?></h1>
    </div>
  </section>
  <div class="album py-5 bg-light">
    <div class="container" id="items">
      <div class="row">
        <div class="col-xs-12">
          <div class="filter-group row">
            <div class="form-group col-12">
              <input type="text" class="search form-control" placeholder="Busca...">
            </div>
          </div>
          <ul class="list row">
<?php if ($lista['status']) { ?>
<?php foreach ($lista['data'] as $modulo => $item) { if(trim($item['nome']) !== "") { ?>
          <li class="list--list-item col-12 col-sm-6 col-md-4 col-xl-3">
            <a href="<?php echo $tv->getLink($modulo, $item); ?>">
<?php if (array_key_exists('imagem', $item) && $item['imagem']) { ?>
              <figure style="background:#000;padding:20px 0;">
                <img src="<?php echo $item['imagem']; ?>" style="height:160px;max-width:100%;display:block;margin:0 auto">
                <figcaption style="color:#fff;text-align:center;" class="title"><?php echo $item['nome'];?></figcaption>
              </figure>
<?php } else { ?>
              <svg class="bd-placeholder-img card-img-top" width="100%" height="225" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" focusable="false" role="img" aria-label="Placeholder: Thumbnail">
                <title class="title"><?php echo $item['nome']; ?></title>
                <rect width="100%" height="100%" fill="#000"/>
                <text x="50%" y="50%" fill="#eceeef" dy=".3em"><?php echo $item['nome']; ?></text>
              </svg>
<?php } ?>
            </a>
          </li>
<?php }} ?>
<?php } ?>
        </ul>
        <div class="no-result">Sem resultados</div>
        <ul class="pagination float-right"></ul>
      </div>
    </div>
  </div>
  </div>
</main>
<footer class="text-muted">
  <div class="container">
    <p>By @christianoerick</p>
  </div>
</footer>
<iframe name="iframe"></iframe>
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/list.pagination.js/0.1.1/list.pagination.js"></script>
<script>
var options = { valueNames: ['title'], page: 12, pagination: true};
var itemsList = new List('items', options);

$(document).ready(function(){
  itemsList.on('updated', function (list) {
    if (list.matchingItems.length > 0) {
      $('.no-result').hide()
    } else {
      $('.no-result').show()
    }
  });
});
</script>
</body>
</html>