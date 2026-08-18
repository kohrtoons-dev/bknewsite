<?php
$analyticsHost = strtolower($_SERVER['HTTP_HOST'] ?? '');
$analyticsDisabledHosts = ['localhost', '127.0.0.1', '::1'];
if (!in_array(preg_replace('/:\d+$/', '', $analyticsHost), $analyticsDisabledHosts, true)):
?>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-WV9EF4YFT0"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', 'G-WV9EF4YFT0');
</script>
<?php endif; ?>
