<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="theme-color" content="#000000" />
    <meta
        name="description"
        content="MakeStories - Web Stories Builder"
    />
    <script async src="https://cdn.ampproject.org/v0.js"></script>
    <script async custom-element="amp-facebook" src="https://cdn.ampproject.org/v0/amp-facebook-0.1.js"></script>
    <script async custom-element="amp-instagram" src="https://cdn.ampproject.org/v0/amp-instagram-0.1.js"></script>
    <script async custom-element='amp-video' src='https://cdn.ampproject.org/v0/amp-video-0.1.js'></script>
    <script async custom-element='amp-youtube' src='https://cdn.ampproject.org/v0/amp-youtube-0.1.js'></script>
    <script async custom-element='amp-vimeo' src='https://cdn.ampproject.org/v0/amp-vimeo-0.1.js'></script>
    <script async custom-element='amp-dailymotion' src='https://cdn.ampproject.org/v0/amp-dailymotion-0.1.js'></script>
    <script custom-element="amp-twitter" src="https://cdn.ampproject.org/v0/amp-twitter-0.1.js" async></script>
    <script>
        /*(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
        setTimeout(function(){
            if(window.isProduction){
                ga('create', 'UA-161794006-1', 'auto');
                ga('send', 'pageview');
            }
        }, 1000); */
    </script>
    <script type="text/javascript">window.$crisp=[];window.CRISP_WEBSITE_ID="ba635223-d68b-476d-be2f-44e216b447eb";(function(){d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();</script>
    <title>MakeStories 2.0 - Beta</title>
</head>
<body>
<noscript>You need to enable JavaScript to run this app.</noscript>
<div id="root"></div>
<script>
    (function () {
        var e, r, s, i, a, o, t, n, c;
        r = navigator.platform.toUpperCase().indexOf("MAC") >= 0, window.macKeys = {
            cmdKey: !1,
            ctrlKey: !1,
            shiftKey: !1,
            altKey: !1,
            reset: function () {
                this.cmdKey = !1, this.ctrlKey = !1, this.shiftKey = !1, this.altKey = !1
            }
        }, r && (n = navigator.userAgent, c = n.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [], e = /trident/i.test(c[1]) ? {
            browser: "IE",
            version: (t = /\brv[ :]+(\d+)/g.exec(n) || [])[1] || ""
        } : "Chrome" === c[1] && null != (t = n.match(/\b(OPR|Edge)\/(\d+)/)) ? {
            browser: t.slice(1)[0].replace("OPR", "Opera"),
            version: t.slice(1)[1]
        } : (c = c[2] ? [c[1], c[2]] : [navigator.appName, navigator.appVersion, "-?"], null != (t = n.match(/version\/(\d+)/i)) && c.splice(1, 1, t[1]), {
            browser: c[0],
            version: c[1]
        }), s = "Chrome" === e.browser || "Safari" === e.browser, i = "Firefox" === e.browser, a = "Opera" === e.browser, window.onkeydown = function (e) {
            o = e.keyCode, (s || a) && (91 === o || 93 === o) || i && 224 === o ? macKeys.cmdKey = !0 : 16 === o ? macKeys.shiftKey = !0 : 17 === o ? macKeys.ctrlKey = !0 : 18 === o && (macKeys.altKey = !0)
        }, window.onkeyup = function (e) {
            o = e.keyCode, (s || a) && (91 === o || 93 === o) || i && 224 === o ? macKeys.cmdKey = !1 : 16 === o ? macKeys.shiftKey = !1 : 17 === o ? macKeys.ctrlKey = !1 : 18 === o && (macKeys.altKey = !1)
        }, window.onblur = function () {
            macKeys.reset()
        })
    })();
</script>
<script>
    <?php
    $slug = get_option('mscpt_makestories_settings');
    $baseUrl = get_site_url();
    if(!empty($slug) && isset($slug['post_slug'])) {
        $baseUrl = trailingslashit($baseUrl) . trailingslashit($slug['post_slug']);
    }
    ?>
    const msWPConfig = {
        wpBaseUrl: '<?php echo get_site_url(""); ?>',
        currentPage: "<?php echo $subpage; ?>",
        wpAdminBaseURL: '<?php echo MS_WP_ADMIN_BASE_URL; ?>',
        adminAjaxUrl: '<?php echo admin_url('admin-ajax.php') ?>',
        cpt: "<?php echo MS_POST_TYPE; ?>",
        wpStoriesBaseURL: '<?php echo $baseUrl; ?>',
        wpNonce:'<?php echo wp_create_nonce( MS_NONCE_REFERRER ) ?>',
    };
    window.msWPConfig = msWPConfig;
</script>
<script src="<?php echo MS_MAIN_SCRIPT_URL ?>"></script>
</body>
</html>
