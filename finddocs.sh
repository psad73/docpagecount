rm files-doc.lst
rm files-pdf.lst
find /volume1/ -type f -iname '*.doc' >>files-doc.lst
find /volume1/ -type f -iname '*.pdf' >>files-pdf.lst
