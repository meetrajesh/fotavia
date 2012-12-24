<? $optouts = $user->get_email_optouts(); ?>

<h2>Email Settings for <?=hsc($user->get_email())?></h2>
<p>Please choose which emails you&#39;d lke to receive:</p>

<form id="optout" method="post" action="<?=$page->get_form_action()?>">

  <p>
  <? $all_checked = true;
     foreach (email::$types as $type => $str) {
         if (preg_match('/^HEADER-/', $type)) {
             echo spf('<h3>%s</h3>', spf($str));
             continue;
         }
         $checked = !in_array($type, $optouts); 
         $all_checked = $all_checked && $checked; ?>
         <input type="checkbox" id="<?=$type?>" name="check[<?=$type?>]" <?=$checked ? 'checked="checked"' : ''?> />
         <label for="<?=$type?>"> <?=hsc($str)?></label><br/>
  <? } ?>
  </p>

  <p><a id="checkall" href="#">All</a> | <a id="checknone" href="#">None</a></p>

  <input type="submit" name="btn_optout_save" value="Save" />

</form>
