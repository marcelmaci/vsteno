
// VSTENO: some tests with hunspell from C++ (as native code)
// Goal: replace php-code for linguistical analysis (linguistics.php)
// with native (compiled) C-code to improve performance.
//
// requirements:
// (1) link hunspell library and call API directly from C++
// (2) create C++-bridge from PHP (possible with www.phpcpp.com) 
//
// Compile this test file with:
// g++ test.cc -o test_hunspell -lhunspell-1.3
// (or simply: g++ test.cc -o test_hunspell -lhunspell)
//
// Run it: ./test_hunspell
//
// The program loads a german hunspell dictionary (de_CH), asks for
// a word, test the word by calling the spellchecker.
// (1) If the word is wrong, suggestions made by hunspell are printed out.
// (2) If the word is correct, the program lists possible stem(s) and
// also prints available morphological information (prefixes, suffixes)

#include <iostream>
#include <string>
#include <hunspell/hunspell.hxx>

int main(void) {
 
    Hunspell german ("/usr/share/hunspell/de_CH.aff", "/usr/share/hunspell/de_CH.dic");
  
    std::cout << "Wort eingeben:" << std::endl;
    std::string word;
    std::cin >> word;

    // orthographical correctness
    int correct = german.spell(word.c_str());
    
    if (!correct) {
		
            std::cout << "Rechtschreibfehler!" << std::endl;
    
			// suggestions
			// if word is wrong, make some suggestions.
			
			char ** result;
			int n = german.suggest( &result, word.c_str());   // n = number of suggestions
	
			for (int i=0; i<n; i++) { 
				std::cout << (*(result+i)) << std::endl;	// +i = good old pointer arithmetics ... ;-)
			}
	
    
    } else { 

            std::cout << "Korrekt geschrieben!" << std::endl; 
  
			// stemming
			char ** result1;
			int n1 = german.stem(&result1, word.c_str());
    
			// output
			std::cout << "Stammbildung:" << std::endl;
    
			for (int i=0; i<n1; i++) { 
				std::cout << (*(result1+i)) << std::endl;	// +i = good old pointer arithmetics ... ;-)
			}
	
			// morphological analysis
			char ** result2;
			int n2 = german.analyze(&result2, word.c_str());
    
			// output
			std::cout << "Morphologie:" << std::endl;
    
			for (int i=0; i<n2; i++) { 
				std::cout << (*(result2+i)) << std::endl;	// +i = good old pointer arithmetics ... ;-)
			}  

    }
  
    // extended stemming (doesn't work!?)
    /*
    char ** result, result2;
    int n1 = german.analyze(&result, "words");
	int n2 = german.stem(&result2, result, n1);
    */
	
    return 0;
}
