<?php

if (isset($_GET['key'])) {
  $status = apc_fetch('upload_' . $_GET['key']);
  if ($status['total'] == 0) {
      echo 0;
  } else {
      echo $status['current'] / $status['total'] * 100;
  }
} else {
    echo 0;
}

?>