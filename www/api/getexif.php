<?php

require '../../config.php';

if (empty($_POST['pid']) || !is_id($_POST['pid']) || !photo::exists($_POST['pid'])) {
    exif_popup_header(_('No exif data available for this photo.'));
    return;
}

$photo = photo::get($_POST['pid']);
// @todo: insert logging here to track how many people are requesting exif

if (!$photo->has_exif()) {
    exif_popup_header(_('No exif data available for this photo.'));
    return;
}

exif_popup_header(_('Exif Data')); ?>

<table>

<?php

$exif = $photo->get_exif_data();
$i = 0;
foreach ($exif as $key => $val) { 
    if (is_array($val)) {
        $queue[] = $val;
        continue;
    }
    $i++;
?>
    <tr class="<?=oddeven($i)?>">
      <td><?=htmlspecialchars($key)?></td>
      <td><?=htmlspecialchars($val)?></td>
    </tr>
<?
}

?>

</table>

