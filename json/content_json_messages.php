<?php

function getLtiLinkJSON($url) {

// https://www.imsglobal.org/specs/lticiv1p0/specification
$return = '{
  "@context" : [
    "http://purl.imsglobal.org/ctx/lti/v1/ContentItem",
    {
      "lineItem" : "http://purl.imsglobal.org/ctx/lis/v2/LineItem",
      "res" : "http://purl.imsglobal.org/ctx/lis/v2p1/Result#"
    }
  ],
  "@graph" : [
    { "@type" : "LtiLinkItem",
      "mediaType" : "application/vnd.ims.lti.v1.ltilink",
      "title" : "Chapter 12 quiz",
      "lineItem" : {
        "@type" : "LineItem",
        "label" : "Chapter 12 quiz",
        "reportingMethod" : "res:totalScore",
        "assignedActivity" : {
          "@id" : "http://toolprovider.example.com/assessment/66400",
          "activity_id" : "a-9334df-33"
        },
        "scoreConstraints" : {
          "@type" : "NumericLimits",
          "normalMaximum" : 110
        }
      }
    }
  ]
}';

    $json = json_decode($return);
    $json->{'@graph'}[0]->url = $url;
    return $json;
}

function getFileItemJSON($url) {

$return = '{
  "@context" : "http://purl.imsglobal.org/ctx/lti/v1/ContentItem", 
  "@graph" : [ 
    { "@type" : "FileItem",
      "@id" : ":item3",
      "url" : "http://developers.imsglobal.org/images/imscertifiedsm.png",
      "mediaType" : "application/vnd.ims.imsccv1p3", 
      "copyAdvice" : true
    }
  ]
}';

    $json = json_decode($return);
    $json->{'@graph'}[0]->url = $url;
    return $json;
}
