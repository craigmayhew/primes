/*
* This file will generate an sql file to populate a database table with priem numbers
* The storage is 100 numbers per database row, however to save space - it is compressed.
* The compressions is that each column value is the numeric difference of the previous prime and the current one.
* It's almost entirely procedural with the exception of the object "out"
*/


#include <cstdlib>
#include <iostream>
#include <fstream>
#include <cmath>
#include <string>
#include <sstream>

/*Commnad line*/
using namespace std;

long primes_lookup[10000] = {2, 3, 5};

bool is_prime (long long n)
{ //this function doesnt work for 2...
  float squareroot = sqrt(n)+1;
  int i;
  for (i = 0; primes_lookup[i]<squareroot; i++){
      if (n % primes_lookup[i] == 0){      
         return false;
      }
  }
  return true;
}

long long next_prime(long long n){
   if (n < 2){
         n = 2; 
   }else if (n % 2 == 0){//prevent even number loop
         n += 1;
   }else{
         n += 2;
   }
   
   while (false == false){
         if (is_prime(n) == true){
                  return n;
         }else{
                  n += 2;
         }
   }
}

int file_put_contents(string stringy, string fileName="unnamed.txt")
{
  ofstream myfile;
  myfile.open ((fileName).c_str(),ios::app);
  myfile << stringy;
  myfile.close();
  return 1;
}

int main(int argc, char *argv[])
{
    int a, i, j;
    
    //generate primes array of first 100000 primes
    //this speeds up prime checking for bigger numbers
    for (i=1; i<10000; i++){
            primes_lookup[i] = next_prime(primes_lookup[i-1]);
    }
    //done building primes array
    
    unsigned long long p,
                       original_p,
                       prev_p,
                       n;
    
    string primes_output = "";
    
	//request a starting number from user interface
    cout << "Type a starting number (e.g 2) \n";
    
    cin >> p;
    original_p = p;
    cout << "What number prime is  " << p << "? (e.g. 2 is the 1st, so you would type 1)\n";
    cin >> n;
   
    
    const unsigned int COLUMNS_IN_DB = 1000,
    // A x B gives you the total number of primes we will produce.
    // it's faster to loop through two smaller numbers A and B and give a command line output every A primes.
    // setting A to 1000, and B to 1000 would produce 1 million numbers.
                       TOTAL_NUMBERS_TO_PRODUCE_A = 1000,
                       TOTAL_NUMBERS_TO_PRODUCE_B = 1000;
    
    std::stringstream out,
                      filenamesql,
                      filenamelist;
    
    //BEGIN OUTPUT OF LIST
    filenamelist << "primeslist.txt";
    out.str("");
    out << p << "\n";
    for (a = 0; a < TOTAL_NUMBERS_TO_PRODUCE_A; a++){
        for (j = 0; j < TOTAL_NUMBERS_TO_PRODUCE_B; j++){    
            for (i = 0; i < COLUMNS_IN_DB; i++){
                p = next_prime(p);
                out << p << "\n";
            }
        }

		//output a prime number to show progress
        cout << p << "\n";
        
		//save prime list to disk
		file_put_contents(out.str(),filenamelist.str());
		
		//reset buffers
		out.str("");
    }
    //END OUTPUT OF LIST
    
    
  
	//the 3 hardcoded values here represent the three for loops (a,j and i) a little further down
    filenamesql << (unsigned long long) (n+(TOTAL_NUMBERS_TO_PRODUCE_A*TOTAL_NUMBERS_TO_PRODUCE_B*COLUMNS_IN_DB)) << ".sql";
    
    //db column names string
    string sql_start = "";
    out << "INSERT INTO primes_upto_million (n,prime";
    for (i = 0; i < COLUMNS_IN_DB; i++){
        out << ",diff_" << i;
    }
    out << ") VALUES ";
    sql_start = out.str();
    out.str("");
    
    p = original_p;
    for (a = 0; a < TOTAL_NUMBERS_TO_PRODUCE_A; a++){
        out << sql_start;
        for (j = 0; j < TOTAL_NUMBERS_TO_PRODUCE_B; j++){    
            for (i = 0; i < COLUMNS_IN_DB; i++){
                p = next_prime(p);
                n++;
                if (i == 0){
                      out << "(" << n << "," << p;
                }else{
                      out << "," << ((p - prev_p)/2);
                }
                prev_p = p;
            }
            out << "),";
        }
        primes_output = out.str();
        primes_output = primes_output.substr(0,primes_output.length()-1) + ";\n";
        out.str("");

		//output a prime number to show progress
        cout << p << "\n";
        
		//save sql queries to disk
		file_put_contents(primes_output,filenamesql.str());
    }
    
    return EXIT_SUCCESS;
}
