<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="//aui-cdn.atlassian.com/aui-adg/6.0.3/css/aui.min.css" media="all">

    @yield('styles')
</head>
<body>
<section id="content" class="ac-content">
    @yield('content')
</section>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script src="//aui-cdn.atlassian.com/aui-adg/6.0.3/js/aui.min.js" data-options="sizeToParent: true"></script>

@yield('scripts')

<script id="connect-loader" data-options="sizeToParent:true;"></script>
<script>
    function getUrlParam(p){var m=(new RegExp(p+'=([^&]*)')).exec(window.location.search);return m?decodeURIComponent(m[1]):''}
    var s=document.createElement('script');
    var b=getUrlParam('xdm_e')+getUrlParam('cp');
    s.src=b+'/atlassian-connect/all.js';
    s.async=false;
    s.setAttribute('data-options',document.getElementById('connect-loader').getAttribute('data-options'));
    document.querySelector('head').appendChild(s)
</script>

</body>
</html>