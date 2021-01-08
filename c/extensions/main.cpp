#include <iostream>
#include <string>
#include <phpcpp.h>
#include <vector>
#include <sstream>
#include <algorithm>
#include <tuple>
#include <hunspell/hunspell.hxx>   // sudo apt-get install libhunspell-1.3-0 libhunspell-dev
#include <pcrecpp.h> 
//#include <json>
//#include <thread>
//#include <cmath>
//#include <mutex>
//#include <chrono>

using namespace std;
using namespace pcrecpp;
//using namespace std::chrono;

// test functions

Php::Value HelloWorld(Php::Parameters &params) {
     string from_php = params[0];
     string result = "Hello from native C++ function which was called with parameter " + from_php + ".";
     return result;
}

Php::Value SimpleTest(Php::Parameters &params) { // returns the same string
     string from_php = params[0];
     return from_php;
}

// real stuff

// functions for 3d array and memory management 

string*** Allocate3DArray(int x, int y, int z) {
	string*** array = new string**[x];
	for (int i=0; i<x; i++) {
		array[i] = new string*[y];
		for (int j=0; j<y; j++) {
			array[i][j] = new string[z];
		}
	}
	return array;
}

/*
template<typename FType, typename PType> FType*** Allocate3DArray(PType x, PType y, PType z) {
	FType*** array = new FType**[x];
	for (int i=0; i<x; i++) {
		array[i] = new FType*[y];
		for (int j=0; j<y; j++) {
			array[i][j] = new FType[z];
		}
	}
	return array;
}
*/

void DeAllocate3DArray(string*** array, int x, int y, int z) {
  try {
	for (int i=0; i<x; i++) {
		for (int j=0; j<y; j++) {
			delete[] array[i][j];
		}
		delete[] array[i];
	}
	delete[] array;
  }
  catch (exception& e) {
	cout << "ignore ..." << endl; // don't know why we get segmentation fault ?!
  }
}

void Initialize3DArray(string*** array, int x, int y, int z) {
	for (int i=0; i<x; i++) {
		for (int j=0; j<y; j++) {
			for (int l=0; l<z; l++) 
				array[i][j][l] = "";
		}
	}
}

// functions that replace php functionalities

// php explode()
// standard
vector<string> explode(char delim, string &s) {
    vector<string> result;
    istringstream iss(s);
    for (string token; getline(iss, token, delim); ) result.push_back(move(token));
    return result;
}
// overloaded => in addition eliminates char
vector<string> explode(char delim, string &s, char eliminate) {
    vector<string> result;
	string trimmed = s; 
	
	remove(trimmed.begin(), trimmed.end(), ' ');
    istringstream iss(trimmed);

    for (string token; getline(iss, token, delim); ) result.push_back(move(token));

    return result;
}

// function for morphological analysis
int regex_match_in_array(string word, auto array) {
	for (string s : array) {
		RE_Options opt;
		opt.set_caseless(true);
		RE re(s, opt); // set insensitive option (no distinction upper/lower case)
		//int result = re.FullMatch(word);
		//cout << endl << "RegexMatchinArray: " << word << " Pattern: " << s << " Result: " << result;
		if (re.FullMatch(word)) return true;
	}
	return false;
}

int try_affixes_and_stems(string word, auto prefixes_array, auto stems_array, auto suffixes_array, auto block_array) {
	if (regex_match_in_array(word, prefixes_array)) return true;
	else if (regex_match_in_array(word, stems_array)) return true;
	else if (regex_match_in_array(word, suffixes_array)) return true;
	else if (regex_match_in_array(word, block_array)) return true;
	else return false;
}

int extended_check(string word, auto prefixes_array, auto stems_array, auto suffixes_array, auto block_array) {
	Hunspell german ("/usr/share/hunspell/de_CH.aff", "/usr/share/hunspell/de_CH.dic");	

	int spell_check = german.spell(word.c_str());
	char** suggestions_list;
	int suggestions_number;
	string word_ending_with_hyphen = word + '-';
	
	if (spell_check) return true;
	else {
		// second check: get suggestions
		suggestions_number = german.suggest( &suggestions_list, word.c_str());
		if (suggestions_number>0) {
			// there are suggestions => check if one of them corresponds to "word-" (= word ending with hyphen)
			int i=0;
			while (i<suggestions_number) {
				if (suggestions_list[i++] == word_ending_with_hyphen) {
					//cout << "Found: " << word_ending_with_hyphen << " as suggestion";
					return true;
				} else {
					// hunspell doesn't find a valid word => try affixes and stems
					return try_affixes_and_stems(word, prefixes_array, stems_array, suffixes_array, block_array);
				}
			} 
			// all checked => no luck => return false
			return false;
		} else return false;
	
	}
}

void eliminate_inexistent_words_from_array(string list, auto &array, int n, auto prefixes_array, auto stems_array, auto suffixes_array, auto block_array) {
	// erase words in array[][][0]
	for (int i=0;i<n;i++) {
		for (int j=0; j<n; j++) {
			int is_correct = extended_check(array[i][j][0].c_str(), prefixes_array, stems_array, suffixes_array, block_array); ///*.c_str()*/ + "-");	
			if (!is_correct) array[i][j][0] = ""; // erase word from list
		}
	}
	// erase words in array[][][1]
	for (int i=0;i<n;i++) {
		for (int j=0; j<n; j++) {
			int is_correct = array[i][j][0] != "";	
			if (!is_correct) {
				int e;
				for (e=0; e<=i; e++) {
					if (j+e<n) array[i][j+e][1] = ""; // erase syllable from list
				}
				j += e-1;
			} else j += i;
		}
	}

}

string GetListOfWordsFromArray(auto &array, int n, int a) {
	string result = to_string(n) + ": ";
	for (int i=0; i<n; i++)
		for (int j=0; j<n; j++)
			if (array[i][j][a] != "")
				result += "[" + to_string(i) + "][" + to_string(j) + "]=" + array[i][j][a] + " ";
	return result;
}

string create_word_list_as_string(string word, string*** array) { 
	auto hyphenated_array = explode('-', word);
	string word_list_as_string = "";
	string one_syllable = "";
	int syllables_count = hyphenated_array.size();	
	
	for (int l=0; l<syllables_count; l++) { // l = line of combinations
		for (int r=0; r<syllables_count; r++) { // r = row of combinations
			string single="";
			if (l+r < syllables_count) { // this fix should probably be included in php code!!!
				for (int n=0; n<l+1; n++) { // n= length of combination
					single += hyphenated_array[r+n];
				}
			}
			// capitalize first letter (pretty sure this is not multibyte safe ...)
			single[0] = toupper(single[0]); // to be defined
			word_list_as_string += single + " ";
			array[l][r][0] = single; 
			// make an additional copy of one syllable parts
			one_syllable = hyphenated_array[r];
			array[l][r][1] = one_syllable;
		}
	}
	return word_list_as_string;
}

string collapse_array(auto array, int n) {
	int v, h, c, i; // vertical, horizontal, copy, index
	int result[n];
	for (i=0; i<n; i++) result[i] = -1;
	string analyzed = "";
	
	// collapse
	for (v=0; v<n; v++) {
		if (result[v] == -1) {
			// search match
			h=0;
			while ((array[h][v][1].length()==0) && (h<n)) h++;
			// copy match
			for (c=0; c<=n; c++) {
				//cout << "v=" << v << " h=" << h << " c=" << c << endl;
				if ((c<n) && (h<n)) {
					if (array[h][c][1]!="") result[c] = h;
				}
			}
		}
	}
	
	// build string
	int last = -1;
	int actual = -1;
	for (v=0; v<n; v++) {
		actual = result[v];
		if ((v>0) && (actual!=last) && (v<n-1)) analyzed += "|";
		else if ((v>0) && (v<n)) analyzed += "-";
		if (actual>-1) analyzed += array[actual][v][1]; // actual == -1 => segmentation fault
		last = actual;
	}
	return analyzed;
}

int isacronym(string &s) {
	if ((s.length() >= 2) && (isupper(s[0])) && (isupper(s[1]))) return true;
	else return false;
}

string analyze_one_word_linguistically(string word, string hyphenate, int decompose, int separate, auto prefixes_array, auto stems_array, auto suffixes_array, auto block_array ) {
	//int value_separate = separate; // probably not needed?
	string list;
	if (isacronym(word)) return word; // return acronym unchanged
	else {	
		if (decompose) {
			string hyphenated = (hyphenate != "") ? hyphenate : word;
			// in addition hyphenated must be transformed to lower case
			transform(hyphenate.begin(), hyphenate.end(), hyphenate.begin(), ::tolower); // doesn't work
			// count hyphens to get number of words			
			int n = std::count(hyphenate.begin(), hyphenate.end(), '-') + 1;
			// take this as size for the array
			string*** array = Allocate3DArray(n,n,2);
			list = create_word_list_as_string(hyphenated, array); // return must be implemented differently => use reference to array
			//string list2 = GetListOfWordsFromArray(array,n, 0);
			eliminate_inexistent_words_from_array(list, array, n, prefixes_array, stems_array, suffixes_array, block_array);			
			//list = recursive_search(0,0,array,n, recursive_path);
			list = collapse_array(array, n);			
			DeAllocate3DArray(array, n, n, 2);
		}
	}	
	return list;
}

// phpcpp bridge: analyze_word_linguistically()

Php::Value analyze_word_linguistically_native(Php::Parameters &params) {
		string word = params[0];
		string hyphenate = params[1]; // use hyphenate as string: empty = false; not empty = hyphenated word
		int decompose = params[2];
		int separate = params[3];
		//int glue = params[4]; // obsolete, anyway ... // comment it out to avoid compiler warning 'unused variable'
		string prefixes = params[5];
		string stems = params[6];
		string suffixes = params[7];
		string block = params[8]; 
		
		// prepare data
		auto prefixes_array = explode(',', prefixes, ' ');		
		auto stems_array = explode(',', stems, ' ');		
		auto suffixes_array = explode(',', suffixes, ' ');		
		auto block_array = explode(',', block, ' ');		

		string result = analyze_one_word_linguistically(word, hyphenate, decompose, separate, prefixes_array, stems_array, suffixes_array, block_array );	
/*
		auto several_words = explode('-', word); 
		string result = ""; //to_string(several_words.size()); // to_string(count(several_words)); //""; //  = word; //"";

		string single_result;
		for (unsigned int i=0; i<several_words.size(); i++) {
			single_result = analyze_one_word_linguistically(word, hyphenate, decompose, separate, prefixes_array, stems_array, suffixes_array, block_array );
			result += (i==0) ? single_result : "=" + single_result;	
		}
*/
	return result;
}

/*
 *  tell the compiler that the get_module is a pure C function
 */
extern "C" {
    
    /*
     *  Function that is called by PHP right after the PHP process
     *  has started, and that returns an address of an internal PHP
     *  strucure with all the details and features of your extension
     *
     *  @return void*   a pointer to an address that is understood by PHP
     */
    PHPCPP_EXPORT void *get_module() {
        // static(!) Php::Extension object that should stay in memory
        // for the entire duration of the process (that's why it's static)
        static Php::Extension extension("vsteno_native", "1.0");
			extension.add<HelloWorld>("HelloWorld");
			extension.add<analyze_word_linguistically_native>("analyze_word_linguistically_native");
			extension.add<SimpleTest>("SimpleTest");
		// return the extension
        return extension;
    }
}
