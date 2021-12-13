rm -f files-doc.lst
rm -f files-pdf.lst
find /volume1/ -type f -iname '*.doc' >>files-doc.lst
find /volume1/ -type f -iname '*.pdf' >>files-pdf.lst
