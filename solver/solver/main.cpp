//
//  main.cpp
//  solver
//
//  Created by Leonhard Spiegelberg on 17.06.13.
//  Copyright (c) 2013 Leonhard Spiegelberg. All rights reserved.
//

#include <iostream>

int main(int argc, const char * argv[])
{

    if(argc < 2)
    // insert code here...
    std::cout << "Too few arguments!\n";
    else
        std::cout<<argv[1];
    return 0;
}

