#!/bin/bash
#
# Load the templates specified in the array
#  into the PenTDB database.
set -x

filelist=("port-22_chart.dat" "port-53_chart.dat" "port-80_chart.dat" "webapp_chart.dat")

for item in ${filelist[*]}
do
	sudo mysql -uroot -e "LOAD DATA LOCAL INFILE '$item' INTO TABLE pentdb.porttest FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' IGNORE 9 ROWS"
done

