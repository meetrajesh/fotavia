<?php
include '../config.php';
ini_set('display_errors', true);

$page = new page;
$user = user::active();
$u = $user ? $user->get_id() : 0;

if ($page->is_post()) {
    $exec_str = rtrim($_POST['exec']);
    if (!in_array(substr($exec_str, -1), array('}', ';'))) {
        $exec_str .= ';';
    }
    $_POST['exec'] = $exec_str;
    ob_start();
    try {
        eval($exec_str);
    } catch (Exception $e) {
        v($e);
    }
    $res = ob_get_contents();
    ob_clean();
}
?>
<html>
  <body onload="document.getElementById('exec').focus()">
    This box:
    <?=php_uname('n')?><br/>
    Predefined: <span style="font:12px monospace;">$u=<?php echo $u?>, $user=user::get($u), v()=var_dump()</span>
    <hr>
    <table>
      <tr>
        <td valign="top">
          <h2>Test code:</h2>
          <form method="post">
            <textarea id="exec" name="exec" cols="80" rows="20"><?=pts('exec')?></textarea>
            <br />
            <input type="submit" name="btn_exec" value="Execute" style="font-size: 1.8em; background:#e5e5e5; border:2px solid #e5e5e5; border-right-color:#747474; border-bottom-color:#747474; padding:3px 6px; cursor:pointer; margin-top:7px; " />&nbsp;&nbsp;
          </form>
        </td>
        <?php if (isset($res)) { ?>
          <td valign="top">
            <h2>Response:</h2>
            <pre><?php echo $res; ?></pre>
          </td>
        <?php } ?>
      </tr>
    </table>
  </body>
</html>
