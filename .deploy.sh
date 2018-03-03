#!/bin/bash

if [ $# -eq 0 ]; then upload="*"; else upload="$1"; fi
uniqueid=`git remote -v show | head -1 | sed 's/\(.*\):\(.*\)\/\(.*\)/\2/g'`

for x in `ls -d */ 2>/dev/null`; do
sftp $uniqueid@cse252.spikeshroud.com <<EOF
mkdir public_html/semester-project/$x 2>/dev/null
EOF
done

sftp $uniqueid@cse252.spikeshroud.com <<EOF
cd public_html/semester-project/
mput -r $upload
EOF


