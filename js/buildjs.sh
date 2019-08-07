# I hate JS ... not even a good (= straightforward) include-function!
# This skript concatenates the different parts of the program to one file
# (vsteno_editor.js) which can be include with <script> in the HTML-file

# add license at beginn of each file
cat license.txt global_functions_code.js > global_functions.js
cat license.txt static_graphical_elements_code.js > static_graphical_elements.js
cat license.txt data_code.js > data.js
cat license.txt freehand_code.js > freehand.js
cat license.txt rotating_axis_code.js > rotating_axis.js
cat license.txt connection_points_code.js > connection_points.js
cat license.txt drawing_area_code.js > drawing_area.js
cat license.txt vsteno_editor_main_code.js > vsteno_editor_main.js
cat license.txt tension_slider_code.js > tension_slider.js
cat license.txt thickness_slider_code.js > thickness_slider.js
cat license.txt parallel_rotating_axis_code.js > parallel_rotating_axis.js
cat license.txt js2db_code.js > js2db.js

# join files into one file containing all the code 

cat license.txt global_functions_code.js static_graphical_elements_code.js data_code.js freehand_code.js rotating_axis_code.js connection_points_code.js drawing_area_code.js tension_slider_code.js thickness_slider_code.js parallel_rotating_axis_code.js js2db_code.js vsteno_editor_main_code.js > vsteno_editor.js
