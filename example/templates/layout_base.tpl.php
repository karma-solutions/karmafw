<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>{$meta_title}</title>

    <link rel="stylesheet" type="text/css" href="/assets/vendor/bootstrap-4.3.1/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/vendor/select2-4.0.6-rc.0/select2.min.css" />
    <link rel="stylesheet" type="text/css" href="/assets/vendor/alertify-1.11.1/css/alertify.css">
    <link rel="stylesheet" type="text/css" href="/assets/vendor/tui.chart-3.11.2/tui-chart.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/app.css">
    {yield block_css}

    <script type="text/javascript">var onload_actions = []; function registerOnloadAction(func) { onload_actions.push(func); }</script>
  </head>
  <body>
    {$child_content}

    <script type="text/javascript" src="/assets/vendor/jquery-3.2.1/jquery-3.2.1.min.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script> -->
    <script type="text/javascript" src="/assets/vendor/bootstrap-4.3.1/bootstrap.min.js"></script>
    <script type="text/javascript" src="/assets/vendor/select2-4.0.6-rc.0/select2.min.js"></script>
    <script type="text/javascript" src="/assets/vendor/alertify-1.11.1/alertify.js"></script>
    <script type="text/javascript" src="/assets/vendor/raphael.js"></script>
    <script type="text/javascript" src="/assets/vendor/tui.chart-3.11.2/tui-chart.min.js"></script>
    <script type="text/javascript" src="/assets/vendor/jQuery-Autocomplete-1.4.11/jquery.autocomplete.min.js"></script>
    <script type="text/javascript" src="/assets/js/app.js"></script>
    {yield block_js}

    <script type="text/javascript">executeOnloadActions();</script>
  </body>
</html>
