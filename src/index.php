<?php 
require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Collection;
use MTNewton\IdleClansUtils\ItemDatabase;

$data = new Collection(require('data.php'));

$items = ItemDatabase::load($data)->getData()->sortByDesc('gps')->toArray();

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <link
      href="https://unpkg.com/gridjs/dist/theme/mermaid.min.css"
      rel="stylesheet"
    />
  </head>
  <body>
    <div id="wrapper"></div>

    <script src="https://unpkg.com/gridjs/dist/gridjs.umd.js"></script>
    <script>
        new gridjs.Grid({
        columns: ["Skill", "Item", "Value", "Total Time", "Gold/s", {name: "Material Tree", formatter: (cell) => gridjs.html(`${cell}`)}/** , "Raw Materials"*/],
        resizable: true,
        sort: true,
        data: [
            <?php foreach ($items as $item){                    
                echo json_encode([
                    $item['skill'], 
                    $item['name'], 
                    $item['value'], 
                    $item['totalTime'], 
                    round($item['gps'], 2),
                    $item['materialsTreeText'],
                    //join(', ', collect($item['materialsRaw'])->map(fn($mat, $num) => $mat . 'x ' . $num)->toArray())
                ]) . ',';
            } ?>
        ]
        }).render(document.getElementById("wrapper"));
    </script>
  </body>
</html>


