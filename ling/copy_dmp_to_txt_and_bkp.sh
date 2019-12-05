# copy backups
cp DESSBAS.txt DESSBAS.bkp
cp SPSSBAS.txt SPSSBAS.bkp
cp FRSSBAS.txt FRSSBAS.bkp
cp ENSSBAS.txt ENSSBAS.bkp
# copy dumps
cp DESSBAS.dmp DESSBAS.txt
cp SPSSBAS.dmp SPSSBAS.txt
cp FRSSBAS.dmp FRSSBAS.txt
cp ENSSBAS.dmp ENSSBAS.txt
# remove custom model
rm XM*.dmp
rm XM*.txt
