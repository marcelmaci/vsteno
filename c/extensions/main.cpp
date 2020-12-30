#include <iostream>
#include <string>
#include <phpcpp.h>
//#include <thread>
//#include <cmath>
//#include <mutex>
//#include <chrono>

using namespace std;
//using namespace std::chrono;


Php::Value HelloWorld(Php::Parameters &params) {
     string from_php = params[0];
     string result = "Hello from native C++ function which was called with parameter " + from_php + ".";
     return result;
}

/**
 *  tell the compiler that the get_module is a pure C function
 */
extern "C" {
    
    /**
     *  Function that is called by PHP right after the PHP process
     *  has started, and that returns an address of an internal PHP
     *  strucure with all the details and features of your extension
     *
     *  @return void*   a pointer to an address that is understood by PHP
     */
    PHPCPP_EXPORT void *get_module() 
    {
        // static(!) Php::Extension object that should stay in memory
        // for the entire duration of the process (that's why it's static)
        static Php::Extension extension("vsteno_native", "1.0");
		extension.add<HelloWorld>("HelloWorld");
	// return the extension
        return extension;
    }
}
