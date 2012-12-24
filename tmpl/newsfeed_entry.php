<? // global $entry; 

// get the entry owner
$owner = user::get($entry->get_owner_id());
?>

<li>
<? switch ($entry->get_item_type()) {
  case newsfeed_item_types::FOLLOW_TYPE:
      // follow/unfollow icon
      if ($entry->is_follow) {
          echo '<img src="/img/icons/follow.gif" /> ';
      } else {
          echo '<img src="/img/icons/unfollow.gif" /> ';
      }
      $leader = user::get($entry->get_target_id());
      // 4 cases
      if ($owner->get_id() == user::active()->get_id()) {
          if ($entry->is_follow) {
              // i started following someone
              echo spf(_('You followed %s %s.'), $leader->get_full_profile_link(), fuzzydate($entry->get_stamp()));
          } else {
              // i unfollowed someone
              echo spf(_('You unfollowed %s %s.'), $leader->get_full_profile_link(), fuzzydate($entry->get_stamp()));
          }
      } else {
          if ($leader->get_id() == user::active()->get_id()) {
              // one of my leaders is following me
              echo spf(_('%s followed you %s.'), $owner->get_full_profile_link(), fuzzydate($entry->get_stamp()));
          } else {
              // one of my leaders is following a 3rd person
              echo spf(_('%s followed %s %s.'), $owner->get_full_profile_link(), $leader->get_full_profile_link(), fuzzydate($entry->get_stamp()));
          }
      }
      break;
  case newsfeed_item_types::PHOTO_TYPE:
      echo '<img src="/img/icons/photo.png" /> ';
      $photo = photo::get($entry->get_target_id());
      // 2 cases
      if ($owner->get_id() == $user->get_id()) {
          // a) i uploaded a photo
          echo spf(_('You added a photo %s %s.'), $photo->get_link(), fuzzydate($entry->get_stamp()));
      } else {
          // b) someone else uploaded a photo
          echo spf(_('%s added a photo %s %s.'),
                   $owner->get_full_profile_link(), $photo->get_link(), fuzzydate($entry->get_stamp()));
      }
      break;
  
  case newsfeed_item_types::COMMENT_TYPE:
      echo '<img src="/img/icons/comment.gif" /> ';
      // the photo this comment was made on
      $photo = photo::get($entry->get_target_id());
  
      // 5 cases
      // 1) i'm doing the commenting
      if ($owner->get_id() == $user->get_id()) {
          // 2 sub cases
          if ($photo->get_owner_id() == $user->get_id()) {
              // 1a) photo belongs to me
              echo spf(_('You commented on your photo %s %s.'), $photo->get_link(true), fuzzydate($entry->get_stamp()));
          } else {
              // 1b) photo belongs to a 3rd person
              $photo_owner = $photo->owner();
              echo spf(_('You commented on %s&#39;s photo %s %s.'), $photo_owner->get_full_profile_link(), $photo->get_link(true), fuzzydate($entry->get_stamp()));
  
          }
      } else {
          // 2) someone else is doing the commenting
          if ($photo->get_owner_id() == $user->get_id()) {
              // 2a) photo belongs to me
              echo spf(_('%s commented on your photo %s %s.'), $owner->get_full_profile_link(), $photo->get_link(true), fuzzydate($entry->get_stamp()));
          } elseif ($photo->get_owner_id() == $entry->get_owner_id()) {
              // 2b) photo belongs to the commenter
              echo spf(_('%s commented on his photo %s %s.'), $owner->get_full_profile_link(), $photo->get_link(true), fuzzydate($entry->get_stamp()));
          } else {
              // 2c) photo belongs to a 3rd person
              $photo_owner = $photo->owner();
              echo spf(_('%s commented on %s&#39;s photo %s %s.'), $owner->get_full_profile_link(), $photo_owner->get_full_profile_link(), 
                       $photo->get_link(true), fuzzydate($entry->get_stamp()));
          }
      }
      break;
} ?>
