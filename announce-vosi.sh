#!/bin/bash

DATE="`date +%Y-%m-%d`"
#DATE="2011-03-01"
PAGE="Vorstand/Sitzung $DATE"
WIKILINK=`$(dirname $0)/bin/getwikiurl.php "$PAGE"`
PAD="vorstandssitzung-$DATE"
PADLINK=`$(dirname $0)/bin/getpadurl.php "$PAD"`

RCPT="announce@lists.junge-piraten.de"
TMPFILE=/tmp/jupis.$$

$(dirname $0)/bin/getwikipage.php "$PAGE" > $TMPFILE && {
	$(dirname $0)/bin/createpad.php "$PAD"

	HASH=`echo "$PAGE $RCPT" | md5sum | awk '{ print $1 }'`
	/usr/sbin/sendmail "$RCPT" <<EOT
From: vorstand@junge-piraten.de
To: $RCPT
Subject: Vorstandssitzung $DATE
Message-Id: <$HASH@announce.wiki.junge-piraten.de>
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit

$WIKILINK
$PADLINK

$($(dirname "$0")/bin/getwikipage.php "$PAGE" 0)

$($(dirname "$0")/getwikitoplist.php "$DATE")
EOT
}

[ -e $TMPFILE ] && rm $TMPFILE
