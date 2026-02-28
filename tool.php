<?php 
error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", 1);

// Load up the LTI Support code
require_once 'util/lti_util.php';
require_once 'util/mimeparse.php';

session_start();
header('Content-Type: text/html; charset=utf-8'); 

$cur_url = curPageURL();

// Initialize, all secrets are 'secret', do not set session, and do not redirect
$key = isset($_POST['oauth_consumer_key']) ? $_POST['oauth_consumer_key'] : false;
$secret = "secret";
$_SESSION['oauth_consumer_key'] = $_POST['oauth_consumer_key'] ?? '';
$_SESSION['secret'] = "secret";
$context = new BLTI($secret, false, false);
?>
<html>
<head>
  <title>External Tool API Test Harness</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <style>
    .tab-nav { margin: 1em 0; }
    .tab-nav a { padding: 0.5em 1em; background:#fff; border:1px solid #666; margin-right:2px; text-decoration:none; color:#333; border-radius:4px 4px 0 0; }
    .tab-nav a:hover { background:#e8e8e8; }
    .tab-nav a.active { background:#add8e6; border-bottom-color:#add8e6; margin-bottom:-1px; }
    .tab-panel { display:none; padding:1em; background:#fff; border:1px solid #666; border-radius:0 4px 4px 4px; }
    .tab-panel.active { display:block; }
  </style>
</head>
<body style="font-family:sans-serif; background-color:#add8e6">
<?php
echo("<p><b>External Tool API Test Harness</b></p>\n");

$sourcedid = $_REQUEST['lis_result_sourcedid'] ?? '';

echo('<div class="tab-nav">');
echo('<a href="#" class="tab-link active" data-tab="tab-tool">Tool</a>');
if ( $context->valid ) {
   echo('<a href="#" class="tab-link" data-tab="tab-postmessages">Post Messages</a>');
}
echo('</div>');

echo('<div id="tab-tool" class="tab-panel active">');
if ( $context->valid ) {
   print "<p style=\"color:green\">Launch Validated.<p>\n";
   if ( $_POST['launch_presentation_return_url'] ?? null ) {
     $msg = 'A%20message%20from%20the%20tool%20provider.';
     $error_msg = 'An%20error%20message%20from%20the%20tool%20provider.';
     $sep = (strpos($_POST['launch_presentation_return_url'], '?') === FALSE) ? '?' : '&amp;';
     print "<a href=\"{$_POST['launch_presentation_return_url']}\">Return to tool consumer</a> (";
     print "<a href=\"{$_POST['launch_presentation_return_url']}{$sep}lti_msg={$msg}&amp;lti_log=LTI%20log%20entry:%20{$msg}\">with a message</a> or ";
     print "<a href=\"{$_POST['launch_presentation_return_url']}{$sep}lti_errormsg={$error_msg}&amp;lti_errorlog=LTI%20error%20log%20entry:%20{$error_msg}\">with an error</a>";
     print ")</p>\n";
   }

   $found = false;
   if ( ($_POST['lis_result_sourcedid'] ?? null) && 
        ($_POST['lis_outcome_service_url'] ?? null) ) {
        print "<p>\n";
        print '<a href="common/tool_provider_outcome.php?sourcedid='.urlencode($sourcedid);
        print '&key='.urlencode($_POST['oauth_consumer_key']);
        print '&secret=secret';
        print '&url='.urlencode($_POST['lis_outcome_service_url']);
        if ( isset($_POST['oauth_signature_method']) && $_POST['oauth_signature_method'] != 'HMAC-SHA1' ) {
            print '&oauth_signature_method='.urlencode($_POST['oauth_signature_method']).'">';
        }
        print '&accepted='.urlencode($_POST['ext_outcome_data_values_accepted'] ?? '').'">';
        print 'Test LTI 1.1 Outcome Service</a>.</p>'."\n";
		$found = true;
    }

    if ( isset($_POST['custom_result_url']) ) {
        print "<p>\n";
        print '<a href="json/result_json.php?url='.urlencode($_POST['custom_result_url']).'">';
        print 'Test LTI 2.0 Outcome Service</a>.</p>'."\n";
		$found = true;
    }

    if ( isset($_POST['custom_ltilink_custom_url']) || isset($_POST['custom_toolproxy_custom_url']) ||
		isset($_POST['custom_toolproxybinding_custom_url']) ) {
        print "<p>\n";
        print '<a href="json/settings_json.php?';
		if ( isset($_POST['custom_ltilink_custom_url']) ) { 
			print 'link='.urlencode($_POST['custom_ltilink_custom_url'])."&";
		}
		if ( isset($_POST['custom_toolproxy_custom_url']) ) { 
			print 'proxy='.urlencode($_POST['custom_toolproxy_custom_url'])."&";
		}
		if ( isset($_POST['custom_toolproxybinding_custom_url']) ) { 
			print 'tool='.urlencode($_POST['custom_toolproxybinding_custom_url'])."&";
		}
		print 'x=24">';
        print 'Test LTI 2.0 Settings Service</a>.</p>'."\n";
		$found = true;
    }

    if ( isset($_POST['ext_sakai_encrypted_session']) && isset($_POST['ext_sakai_serverid']) &&
	  isset($_POST['ext_sakai_server']) ) {
	// In the future support key lengths beyond 128 bits
	$keylength = isset($_POST['ext_sakai_blowfish_length']) ? $_POST['ext_sakai_blowfish_length'] / 8 : 16;
	if ( $keylength < 1 ) $keylength = 16;
	// hash is returning binary - not hex encoded so we get the full 160 bits
	$sha1Secret = hash('sha1',$secret, true);
	if ( strlen($sha1Secret) > $keylength ) $sha1Secret = substr($sha1Secret,0,$keylength);
	$encrypted_session=hex2bin($_POST['ext_sakai_encrypted_session']);
	$session = mcrypt_decrypt(MCRYPT_BLOWFISH, $sha1Secret, $encrypted_session, MCRYPT_MODE_ECB);

	// The encryption pads out the input string to a full block with non-printing characters
	// so we must remove them here.  Since the pre-encrypted sesison only includes non-printing
	// characters it is fafe to rtrim the non-printing characters up to \32 - initial testing
	// of Sakai indicates that the padding used by this versio of Java is 0x04 - but that could change
	// so we are playing it safe and right-trimming all non-printing characters.

	// http://stackoverflow.com/questions/1061765/should-i-trim-the-decrypted-string-after-mcrypt-decrypt
	$session = rtrim($session,"\0..\32");

	$session .= '.' . $_POST['ext_sakai_serverid'];
        print "<p>\n";
        print '<a href="retrieve.php?session='.urlencode($session);
	print '&server='.urlencode($_POST['ext_sakai_server']).'">';
        print 'Test Encrypted Session Extension</a>.</p>'."\n";
	$found = true;
    }

    if ( ($_POST['ext_ims_lis_memberships_id'] ?? null) && 
        ($_POST['ext_ims_lis_memberships_url'] ?? null) ) {
        print "<p>\n";
        print '<a href="ext/memberships.php?id='.htmlent_utf8($_POST['ext_ims_lis_memberships_id']);
        print '&key='.urlencode($_POST['oauth_consumer_key']);
        print '&url='.urlencode($_POST['ext_ims_lis_memberships_url']).'">';
        print 'Test Sakai Roster API</a>.</p>'."\n";
		$found = true;
    }

    if ( ($_POST['ext_ims_lti_tool_setting_id'] ?? null) && 
         ($_POST['ext_ims_lti_tool_setting_url'] ?? null) ) {
        print "<p>\n";
        print '<a href="ext/setting.php?id='.htmlent_utf8($_POST['ext_ims_lti_tool_setting_id']);
        print '&key='.urlencode($_POST['oauth_consumer_key']);
        print '&url='.urlencode($_POST['ext_ims_lti_tool_setting_url']).'">';
        print 'Test Sakai Settings API</a>.</p>'."\n";
		$found = true;
    }

    $ltilink_allowed = false;
    if ( isset($_POST['accept_media_types']) ) {
        $ltilink_mimetype = 'application/vnd.ims.lti.v1.ltilink';
        $m = new Mimeparse;
        $ltilink_allowed = $m->best_match(array($ltilink_mimetype), $_POST['accept_media_types']);
    }

    if ( $ltilink_allowed && $_POST['content_item_return_url'] ) {
        print '<p><form action="json/content_json.php" method="post">'."\n";
        foreach ( $_POST as $k => $v ) {
            print '<input type="hidden" name="'.$k.'" ';
            print 'value="'.htmlentities($v).'"/>';
        }
        print '<input type="submit" value="Test LtiLinkItem Content Item"/>';
        print "</form></p>\n";
        $found = true;
    }

    $fileitem_allowed = false;
    if ( isset($_POST['accept_media_types']) ) {
        $fileitem_mimetype = 'application/vnd.ims.imsccv1p3';
        $m = new Mimeparse;
        $fileitem_allowed = $m->best_match(array($fileitem_mimetype), $_POST['accept_media_types']);
    }

    if ( $fileitem_allowed && $_POST['content_item_return_url'] ) {
        print '<p><form action="json/fileitem_json.php" method="post">'."\n";
        foreach ( $_POST as $k => $v ) {
            print '<input type="hidden" name="'.$k.'" ';
            print 'value="'.htmlentities($v).'"/>';
        }
        print '<input type="submit" value="Test FileItem Content Item"/>';
        print "</form></p>\n";
        $found = true;
    }

    if ( ! $found ) {
		echo("<p>No Services are available for this launch.</p>\n");
	}
    print "<pre>\n";
    print "Context Information:\n\n";
    print htmlent_utf8($context->dump());
    print "</pre>\n";
} else {
    print "<p style=\"color:red\">Could not establish context: ".$context->message."<p>\n";
}
print "<p>Base String:<br/>\n";
print htmlent_utf8($context->basestring);
print "<br/></p>\n";

echo('<a href="basecheck.php?b='.urlencode($context->basestring).'" target="_blank">Compare This Base String</a><br/>');
print "<br/></p>\n";

print "<pre>\n";
print "Raw POST Parameters:\n\n";
ksort($_POST);
foreach($_POST as $key => $value ) {
    print htmlent_utf8($key) . "=" . htmlent_utf8($value) . " (".mb_detect_encoding($value).")\n";
}

print "\nRaw GET Parameters:\n\n";
ksort($_GET);
foreach($_GET as $key => $value ) {
    print htmlent_utf8($key) . "=" . htmlent_utf8($value) . " (".mb_detect_encoding($value).")\n";
}
print "</pre>";
echo('</div>'); // tab-tool

if ( $context->valid ) {
?>
<div id="tab-postmessages" class="tab-panel">
<p><b>Post Messages</b></p>
<div id="pm-frame-warning" style="display:none; background:#fff3cd; border:1px solid #856404; padding:0.5em 1em; margin:0.5em 0; border-radius:4px;">
<strong>⚠ No parent or opener.</strong> <code>window.parent</code> and <code>window.opener</code> are this window (or null), so messages are echoed back to yourself. To test real LMS behavior, launch this tool from an LMS so it loads in an iframe.
</div>
<div id="pm-frame-ok" style="display:none; background:#d4edda; border:1px solid #28a745; padding:0.5em 1em; margin:0.5em 0; border-radius:4px;">
✓ Loaded in an iframe — messages will go to the parent (LMS).
</div>
<div id="pm-opener-ok" style="display:none; background:#d4edda; border:1px solid #28a745; padding:0.5em 1em; margin:0.5em 0; border-radius:4px;">
✓ Opened in new tab/popup — messages will go to the opener.
</div>
<p>Exercises window.postMessage for LTI 1.1 and LTI 1.3. Subjects prefixed with <code>lti.</code> are for LTI 1.3; <code>org.imsglobal.lti.</code> for LTI 1.1.</p>
<form>
<label for="subject">subject:</label>
<select name="subject" id="subject">
   <option value="lti.capabilities">lti.capabilities</option>
   <option value="lti.put_data">lti.put_data</option>
   <option value="lti.get_data">lti.get_data</option>
   <option value="lti.close">lti.close</option>
   <option value="lti.frameResize" selected>lti.frameResize</option>
   <option value="lti.pageRefresh">lti.pageRefresh</option>
   <option value="org.imsglobal.lti.capabilities">org.imsglobal.lti.capabilities</option>
   <option value="org.imsglobal.lti.put_data">org.imsglobal.lti.put_data</option>
   <option value="org.imsglobal.lti.get_data">org.imsglobal.lti.get_data</option>
   <option value="">Other</option>
</select>
<span id="pm-height-wrap"> <label for="height">height:</label> <input type="text" name="height" id="height" size="6" placeholder="e.g. 400"></span>
<span id="pm-other-wrap" style="display:none;"> <input type="text" name="other_subject" id="other_subject" placeholder="custom subject"></span><br/>
<label for="message_id">message_id:</label>
<input type="text" name="message_id" id="message_id"> (optional)<br/>
<label for="key">key:</label>
<input type="text" name="key" id="key"> (optional)<br/>
<label for="value">value:</label>
<input type="text" name="value" id="value"> (optional)<br/>
<input type="submit" value="Send" onclick="postMsgSendForm(); return false;">
</form>
<p><b>Sent:</b></p>
<pre id="pm-sent" style="background:#fff; padding:0.5em; border:1px solid #ccc;">(none yet)</pre>
<p><b>Received:</b></p>
<pre id="pm-received" style="background:#fff; padding:0.5em; border:1px solid #ccc;">(none yet)</pre>
</div>
<?php
}
?>

<script>
(function() {
  function checkPostMsgFrame() {
    var warning = document.getElementById('pm-frame-warning');
    var ok = document.getElementById('pm-frame-ok');
    var openerOk = document.getElementById('pm-opener-ok');
    if (warning && ok && openerOk) {
      var inIframe = window.parent !== window;
      var hasOpener = window.opener && window.opener !== window;
      warning.style.display = 'none';
      ok.style.display = 'none';
      openerOk.style.display = 'none';
      if (inIframe) {
        ok.style.display = 'block';
      } else if (hasOpener) {
        openerOk.style.display = 'block';
      } else {
        warning.style.display = 'block';
      }
    }
  }
  checkPostMsgFrame();
  function updateSubjectFields() {
    var subjectSelect = document.getElementById('subject');
    var otherWrap = document.getElementById('pm-other-wrap');
    var heightWrap = document.getElementById('pm-height-wrap');
    if (subjectSelect && otherWrap && heightWrap) {
      var val = subjectSelect.value;
      var isFrameResize = (val === 'lti.frameResize');
      otherWrap.style.display = (val === '') ? 'inline' : 'none';
      heightWrap.style.display = isFrameResize ? 'inline' : 'none';
    }
  }
  var subjectSelect = document.getElementById('subject');
  if (subjectSelect) {
    subjectSelect.addEventListener('change', updateSubjectFields);
    updateSubjectFields();
  }
  document.querySelectorAll('.tab-link').forEach(function(link) {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      var tabId = this.getAttribute('data-tab');
      document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.remove('active'); });
      document.querySelectorAll('.tab-link').forEach(function(a) { a.classList.remove('active'); });
      document.getElementById(tabId).classList.add('active');
      this.classList.add('active');
      if (tabId === 'tab-postmessages') checkPostMsgFrame();
    });
  });
  window.addEventListener('message', function(event) {
    var el = document.getElementById('pm-received');
    if (el) {
      el.textContent = JSON.stringify(event.data, null, '    ');
      el.style.background = '#e8f5e9';
    }
  }, false);
  window.postMsgSendForm = function() {
    var sent = document.getElementById('pm-sent');
    var received = document.getElementById('pm-received');
    if (sent) { sent.textContent = ''; sent.style.background = '#fff'; }
    if (received) { received.textContent = '(none yet)'; received.style.background = '#fff'; }
    var subject = document.getElementById('subject').value;
    if (subject.length < 1) subject = document.getElementById('other_subject').value;
    var send_data = { subject: subject };
    var message_id = document.getElementById('message_id').value;
    var height = document.getElementById('height').value;
    var key = document.getElementById('key').value;
    var value = document.getElementById('value').value;
    if (message_id.length > 0) send_data.message_id = message_id;
    if (height.length > 0) send_data.height = height;
    if (key.length > 0) send_data.key = key;
    if (value.length > 0) send_data.value = value;
    if (sent) { sent.textContent = JSON.stringify(send_data, null, '    '); sent.style.background = '#e3f2fd'; }
    var target = (window.opener && window.opener !== window) ? window.opener : window.parent;
    target.postMessage(send_data, '*');
  };
})();
</script>
