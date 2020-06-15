
// compile with: g++ -o main main.cpp lib.o -lpcrecpp
// result: compiles, main.cpp, links lib.o and creates main

#define MAXGROUPS 10

#include <iostream>
#include <string>
//#include <regex> // this is the "old" regex library
#include <pcrecpp.h> // this is the modern pcre cpp wrapper

using namespace std;
using namespace pcrecpp;

string test_text = " alpha Beta Beta GAMMA delta ";
string search_pattern[] = {
	"Beta",				// normal	
	"(?<= )Beta",		// positive lookbehind
	"Beta(?= )",		// positive lookahead
	"Beta(?! B)",		// negative lookahead
	"(?<!Beta )Beta", 	// negativ lookbehind
	".*Beta.* ", 		// greedy *
	".*Beta.*? ",		// ungreedy *?
	".*?Beta.*? ",		// ungreedy *? (2x)
	"^.*?Beta ",    	// anchor ^ (ungreedy)	
	"^.*Beta ",	    	// anchor ^ (greedy)
	"Beta.*$",			// anchor $ (greedy)
	"Beta.*?$",	 		// anchor $ (ungreedy);
	"B.{3,3}",			// quantizer
	"(B|d)[a-z]{3,4}", 	// marking parenthesis with logical or
	"(?:B|d)[a-z]{3,4}",// non marking parenthesis with logical or
	"[Bd][a-z]{3,4}", 	// positive bracket, bracket with range and quantizer
	"[Bd][a-z]{,4}", 	// item, quantizer without start value
	"[Bd][a-z]{4}", 	// item, quantizer without start value
	"[Bd][a-z]{4,}", 	// item, quantizer with only 1 value
	"[Bd]el?ta",		// positive bracket [] and optional ?
	"[Bd]e[^t]ta",		// negative brakcket []
	"(?<= )([a-zA-Z]+)(?= )",	// group (range: [a-Z] and [:alpha:] do NOT work!)	
	"(?<= )([a-zA-Z]+) ([a-zA-Z]+)(?= )",
	" (.)(.)(.)(.)(.)(.)(.)(.)" // groups
};
string groups[MAXGROUPS];
// according to documentation extraction only works iff 
// number of matched sub-patterns is >= number of supplied pointers;
// as a workaround, supply additional dummy groups to pattern
// in the end
// additional groups are tolerated (they may slow down execution a 
// little bit)
// maybe there's another (more elegant way) to do that?!
string dummy_groups = "()()()()()()()()()()";

int number_patterns = sizeof(search_pattern)/sizeof(search_pattern[0]);

void show_test_strings(void) {
	cout << "TEXT: " << test_text << endl;
	cout << "PATTERNS: " << endl;
	for (int i=0; i<number_patterns; i++) {
		cout << "[" << i << "]: " << search_pattern[i] << endl;
	}
}

string get_result_string(int result) {
	return result ? "HIT " : "--- ";
}

void regex_match_test_sequence(void) {
	cout << "MATCHES:" << endl;	
	cout << "NBR: full | partial" << endl;
	for (int i=0; i<number_patterns; i++) {
		pcrecpp::RE re(search_pattern[i]);		
		// test different pcrecpp methods (full, partial etc.)
		string full = get_result_string(re.FullMatch(test_text));
		string partial = get_result_string(re.PartialMatch(test_text));
		cout << "[" << i << "]: " << full << " | " << partial << endl;
		cout << "------------------------------------------------------------------------" << endl;
	}
}

void regex_replace_test_sequence(void) {
	cout << "REPLACE:" << endl;	
	for (int i=0; i<number_patterns; i++) {
		pcrecpp::RE re(search_pattern[i]);	
		pcrecpp::RE grre(search_pattern[i] + dummy_groups);	// group regex
		// test different pcrecpp methods (full, partial etc.)
		string simple_replace = test_text;
		string global_replace = test_text;		
		re.Replace("xxxx", &simple_replace); // replaces 1st ocurrence
		re.GlobalReplace("xxxx", &global_replace);	// replaces all ocurrencies
		cout << "[" << i << "]: " << search_pattern[i] << endl;
		// quote function (preg_quote in PHP)
		string quoted = RE::QuoteMeta(search_pattern[i]);
		cout << "QUOTED: " << quoted << endl;
		cout << "SIMPLE REPLACE: " << simple_replace << endl;
		cout << "GLOBAL REPLACE: " << global_replace << endl;
		// the group thing ...		
		string group;		
		// re.PartialMatch(test_text, &groups[0], &groups[1], &groups[2]);		
		// reset array before extracting (to be sure it is empty)
		for (int i=0; i<MAXGROUPS; i++)	groups[i] = "";		
		grre.PartialMatch(test_text, &groups[0], &groups[1], &groups[2], &groups[3], &groups[4], &groups[5], &groups[6], &groups[7], &groups[8], &groups[9]);		
		cout << "GROUPS: " << endl;
		for (int i=0; i<MAXGROUPS; i++) {	// recreate i as new local variable inside this for-loop ;-)
			cout << "$" << (i+1) << "=" << groups[i] << " " << endl;   
		}		
		cout << "------------------------------------------------------------------------" << endl;
	}
}

void regex_test_sequence(void) {
	regex_match_test_sequence();
	regex_replace_test_sequence();
}

int main(void) {

	show_test_strings();
	regex_test_sequence();

	return 0;
}
