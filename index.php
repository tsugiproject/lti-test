<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set("display_errors", 1);
require_once("util/lti_util.php");
header('Content-Type: text/html; charset=utf-8');
session_start();
?>
<html>
<head>
  <title>LTI Unit Tests</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body style="font-family:sans-serif;">
<h1>LTI Unit Tests</h1>
<p>
This code is sample code and unit test code for IMS LTI 1.0, and 1.1.
It also supports the Sakai LTI extensions.  
</p>
<p>
The latest Sakai LTI documentation is here:
<a href="https://confluence.sakaiproject.org/display/LTI/Home" target="_blank">
https://confluence.sakaiproject.org/display/LTI/Home</a>.
</p>
<ul>
<li>
<p>If you want to test your LMS with LTI 1.0 or LTI 1.1 or Sakai's extensions, use
the following test harness:
<pre>
<?php
  $cur_url = curPageURL();
  $content_url = str_replace("index.php","content_return.php",$cur_url);
  $cur_url = str_replace("index.php","tool.php",$cur_url);
echo("URL: ".$cur_url."\n");
?>
Key: 12345
Secret: secret
</pre>
You can also launch this URL using the Content Item request.
</p>
</li>
<li>
<p>
If you want to test your LTI 1.0 or 1.1 tool, you can use this fake LMS test harness:
<pre>
<?php
  $cur_url = curPageURL();
  $cur_url = str_replace("index.php","lms.php",$cur_url);
echo('URL: <a href="'.$cur_url.'">'.$cur_url."</a>\n");
?>
</pre>
</p>
</li>
<li>
If you want to play with frame resizing you can use:
<pre>
<?php
  $cur_url = curPageURL();
  $cur_url = str_replace("index.php","resize.htm",$cur_url);
echo('URL: <a href="'.$cur_url.'" target="_blank">'.$cur_url."</a>\n");
?>
</pre>
</li>
<li>
If you want to play with the experimental event postMessage you can use:
<pre>
<?php
  $cur_url = curPageURL();
  $cur_url = str_replace("index.php","event-consumer.htm",$cur_url);
echo('URL: <a href="'.$cur_url.'" target="_blank">'.$cur_url."</a>\n");
  $cur_url = curPageURL();
  $cur_url = str_replace("index.php","event-provider.htm",$cur_url);
echo('URL: <a href="'.$cur_url.'" target="_blank">'.$cur_url."</a> (intended to be in an iframe)\n");
?>
</pre>
</li>

</ul>
<p>
Sakai itself has passed the LTI certifications but this test suite itself 
has not passed IMS certifications.
<p>
You can also compare Base Strings using my
<a href="basecheck.php" target="_blank">Base String Comparison Tool</a>.  This tool also accepts 
"a" and "b" as request parameters in case you want to link to this tool  and provide
one of the base strings from output that you have.
</p>
<p>
Github repo: <a href="https://github.com/tsugiproject/lti-test" target="_blank">https://github.com/tsugiproject/lti-test</a>
</p>
<p>
If you have questions, contact Dr. Chuck.
