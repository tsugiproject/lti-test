<?php

function getLtiLinkJSON($url) {

// https://www.imsglobal.org/specs/lticiv1p0/specification
$return = '{
  "@context" : "http://purl.imsglobal.org/ctx/lti/v1/ContentItem", 
  "@graph" : [ 
    { "@type" : "LtiLinkItem",
      "@id" : ":item2",
      "text" : "The mascot for the Sakai Project", 
      "title" : "The fearsome mascot of the Sakai Project",
      "url" : "http://developers.imsglobal.org/images/imscertifiedsm.png",
      "icon" : {
        "@id" : "fa-bullseye",
        "width" : 50,
        "height" : 50
      },
      "submission" : {
          "startDatetime" : "2016-11-07T00:00:00Z",
          "endDatetime" : "2016-12-01T00:00:00Z"
      },
      "available" : {
        "startDatetime" : "2016-10-31T19:20:30Z",
        "endDatetime" : "2016-12-01T00:00:00Z"
      },
      "lineItem" : {
         "@type" : "LineItem",
          "label" : "The mascot for the Sakai Project",
          "reportingMethod" : "res:totalScore",
           "assignedActivity" : {
                "@id" : "http://www.tsugi.org/assessment/66400",
                "activity_id" : "a-9334df-33"
            }
        },
        "scoreConstraints" : {
            "@type" : "NumericLimits",
            "normalMaximum" : 100,
            "extraCreditMaximum" : 10,
            "totalMaximum" : 110
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
