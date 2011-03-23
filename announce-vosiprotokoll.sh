#!/bin/bash

DATE="`date +%Y-%m-%d`"
#DATE="2011-03-01"
VOSIPAGE="Vorstand/Sitzung $DATE"
PAGE="$VOSIPAGE/Protokoll"
URLPAGE=`perl -MURI::Escape -e 'print uri_escape($ARGV[0]);' "$PAGE"`
WIKILINK="http://wiki.junge-piraten.de/wiki/$URLPAGE"
RCPT="announce@lists.junge-piraten.de"
TMPFILE=/tmp/jupis.$$

# Abbrechen, wenn keine VoSi stattfand
$(dirname $0)/bin/getwikipage.php "$VOSIPAGE" > /dev/null || exit

# Protokoll bearbeiten, falls noch nicht geschehen
$(dirname $0)/bin/getwikipage.php "$PAGE" > $TMPFILE || {
	$(dirname $0)/bin/getpadtext.php "vorstandssitzung-$DATE" > "$TMPFILE"
	$(dirname $0)/bin/setwikipage.php "$PAGE" "Aus http://jupis.piratenpad.de/vorstandssitzung-$DATE" <<EOT
{{Protokoll}}
{{Offiziell}}
$(cat "$TMPFILE" | $(dirname $0)/parsevosiprotokoll.php "$DATE")
EOT
	$(dirname $0)/bin/protectwikipage.php "$PAGE"
}

VOSIHASH=`echo "$VOSIPAGE $RCPT" | md5sum | awk '{ print $1 }'`
HASH=`echo "$PAGE $RCPT" | md5sum | awk '{ print $1 }'`
/usr/sbin/sendmail "$RCPT" <<EOT
From: vorstand@junge-piraten.de
To: $RCPT
Subject: Vorstandssitzung $DATE - Protokoll
References: <$VOSIHASH@announce.wiki.junge-piraten.de>
Message-Id: <$HASH@announce.wiki.junge-piraten.de>
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit

$WIKILINK

$($(dirname $0)/bin/getwikipage.php "$PAGE")
EOT

[ -e $TMPFILE ] && rm $TMPFILE
