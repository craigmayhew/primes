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

long primes_lookup[10000];

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
  myfile << stringy << "\n";
  myfile.close();
  return 1;
}

int main(int argc, char *argv[])
{
    int i, j;
    
    //generate primes array of first 100000 primes
    //this speeds up prime checking for bigger numbers
    primes_lookup[0] = 2;
    primes_lookup[1] = 3;
    primes_lookup[2] = 5;
    for (i=1; i<10000; i++){
            primes_lookup[i] = next_prime(primes_lookup[i-1]);
    }
    //done building primes array
    
    long long p;
    long long n;
    string primes_output = "";
    
	//request a starting number from user interface
    cout << "Type a starting number (e.g 1) \n";
    
    cin >> p;
    //p = round(p);
    cout << "What number prime is  " << p << "? (e.g. 17 is the 7th, so you would type 7)\n";
    cin >> n;
    
    long a;
    long long prev_p;
    
    std::stringstream out;
    std::stringstream filename;
    
	//the 3 hardcoded values here represent the three for loops (a,j and i) a little further down
	//todo: these would be better as nicely named constants
    filename << (long long) (n+(200000*100*1000)) << ".sql";
    
    //db column names string
    string sql_start = "";
    out << "INSERT INTO primes_upto_million (n,prime";
    for (i = 0; i < 1000; i++){
        out << ",diff_" << i;
    }
    out << ") VALUES ";
    sql_start = out.str();
    out.str("");
    
    for (a = 0; a < 200000; a++){
        out << sql_start;
        for (j = 0; j < 100; j++){    
            for (i = 0; i < 1000; i++){
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
		file_put_contents(primes_output,filename.str());
    }
    
    system("PAUSE");
    return EXIT_SUCCESS;
}
