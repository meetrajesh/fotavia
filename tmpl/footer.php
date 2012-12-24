  </div>
  <div id="footer">
    <a href="/about">about us</a> | 
    <a href="/feedback">feedback</a> | 
    <a href="http://twitter.com/fotavia/">twitter</a>
    <? if (IS_DEV) {
        echo '| ' . spf('%d %s (%sms)', db::$num_queries, ngettext('query', 'queries', db::$num_queries), round((microtime(true) - START_TIME)*1000, 2));
    } ?>
  </div>
  <? if (!IS_DEV) { ?>
    <script type="text/javascript">
    var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
    document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
    </script>
    <script type="text/javascript">
    try {
    var pageTracker = _gat._getTracker("UA-10319723-1");
    pageTracker._trackPageview();
    } catch(err) {}</script>
  <? } ?>
</body>
</html>
