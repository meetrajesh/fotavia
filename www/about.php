<?php

require '../config.php';

$page = new page;
$page->title(_('About Us'));
$page->header();

?>

<h2>About Fotavia</h2>

<div id="about">

  <p>Like many high-quality sites, Fotavia was born out of frustration. Fotavia
  is a 3-year old dream come true. It was built with high standards in mind,
  and with an eye for professionalism and perfectionism.</p>
  
  <p>Features you will find at Fotavia that you may not find on other sites:</p>
  
  <ul>
    <li>Only premium-quality photos. No cruft, only the icing.
    <li>We designed this site for large screen resolutions which is becoming the norm among photographers these days. Why waste all that free real estate?
    <li>You can see a large-version of each photo <em>and</em> see navigation links at the same time without scrolling.
    <li>Keyboard-based navigation. You can use the left and right arrow keys to navigate a friend&#39;s album.
    <li>A blazingly fast site. We use only the best technologies out there to give you the best possible experience.
    <li>We pre-load photos so you never waste time waiting for photos to load.
    <li>We keep our site super simple and clutter-free. And we intend you to check out every single photo your friends upload because of the limited number they
    upload. Hence we stayed away from grouping concepts like tags, tag clusters, groups, sets, and albums. You get only one album and you can tag 10 of them to show on your
    profile page (pending).
    <li>Each word in your photo title and body automatically becomes a keyword.
    <li>You get notified instantly when friends you follow upload a new photo of the day.
    <li>You get RSS feeds for each of your friends you follow, or one giant feed for all of them.
    <li>We use a fashionable dark background to improve the contrast on photos. Dark backgrounds are all the rage these days. They are also stunning for showcasing black and white photography.
    <li>We publish an open RESTful API so you can always build features on top of us. (pending)
    <li>Standard social features like friending, rss feeds, and commenting.
    <li>Pixel-perfect alignment. If you notice something amiss, we will fix it! Just send us a shout on our <a href="/feedback/">feedback form</a>. We don&#39;t have a PhD in CSS, but we'll try our best.
    <li>Ability to build your own custom profile that lets you show off your best photos. (pending)
    <li>Since you upload only your premium photos, you&#39;ll be so confident that you wouldn&#39;t think twice if you wanted to use your Fotavia portfolio to apply to art/photography school.
  </ul>

  <p>... and many more features you&#39;ll discover over time as you continue to use our site.</p>

  <h3>In short, if you asked your best photos where they wanted to live, we hope they will say Fotavia!</h3>

</div>
  
<? $page->footer(); ?>
