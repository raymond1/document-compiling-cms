# cms
This is a program that generates a website. It is capable of doing the following things:

1)Generating the directory structure of a website
2)Copying files from one location to another
3)Generating multiple files based off of a template and individual fragments of data.

# Usage
This is a php script and is activated using the following command:

php generate_website.php

By default, nothing happens, but a file called script.txt can be configured with instructions to specify actions to occur. Some things that this cms is capable of:

1)Create the directory structure of a website in an output directory.
2)Copy files from an input directory to an output directory.
3)Create templated files

The typical use case for this CMS is to generate the set of files that will be uploaded via FTP to the final website.

The default output folder is called "output" and is relative to where the generate_website.php script is run.

## Creating a script file

Add in the file script.txt in the src directory. If the src directory doesn't exist, create it first. The script.txt file contains the set of instructions that will be performed by the cms.

Example file:
generate directories
copy files

Explanation:
This file contains just one line. The string "directories" when put on one line by itself indicates to the generate_website.php script to look in the src/directories.txt file to generate the directory structure.

## Script file syntax

The script file contains instructions, referred to in the documentation as directives, written one on each line. The script file is read line by line, and when a line is equal to a directive, the actions associated with it will be executed.

There are currently two directives:

generate directories
copy files

??template [template name]

See the feature documentation below for more details.

## "generate directories" directive

Usage:

In the script file script.txt in the src directory, add a line that contains only the following text:

generate directories

When the php genereate_directories.php command is run, the generate directories directive will do the following:
1)open and read the file directories.txt. You will need to create this file if it doesn't exist.
2)generate the directories listed in the directories.php file.

The syntax for specifying the directories to be created is as follows: each line contains a series of directory names separated by slashes. These directories will be created when the "generate directories" directive is processed.

An example directories.txt file might contain the following:

example
example/images
example2

This will generate the folders example, add an images subfolder in it and the folder example2. These folders will be relative to the working directory where the command php generate_directories is called.

If a directory already exists, it will not be recreated. If a directory already exists and is not empty, the directory will not be wiped clean due to the generate directories directive.

## "copy files" directive

Usage:

In the script file script.txt in the src directory, add a line that contains only the following text:

copy files

When the php generate_directories.php command is run, the copy files directive will copy files from the src directory or subdirectories into the output directory(named "output"). The files that will be selected for the copy operation will come from the contents listed in the src/copy.txt file. You will need to create this file if it doesn't exist.

For each line in the src/copy.txt file, a copy command will be executed. For each line, a copy command will be executed to copy from a path relative to the "src" directory into the "output" directory. For each line that is a directory, a recursive copy command will be executed to copy all contents of a directory into the output directory. For each line that is a file, only a single file will be copied over.

For example, to copy the file snail2.jpg from the src directory into the output directory, the following line would need to be added to src/copy.txt:

snail2.jpg

The result after processing the copy files directive is that the file snail2.jpg located in the src directory would be copied into the output directory.

Similary, a directory can be copied recursively into the output directory by specifying a directory. You can use nested directories such as directory1/directory2/directory3 for this purpose.

## Template directive

A template file has the following format:





When you run the generate_website.php script, the following things happen:
1)The directory structures indicated in src/directories.txt is generated.
2)The files and directories indicated in src/copy.txt will copy files from the source to destination. This is done recursively using cp -R.
3)There is a function, generate_website() which will generate the different pages of the website
4)Currently, generate_website works as follows:
  1)Generate the index.html file
5)Templated files are generated
  Templating works as follows:  there is a file: template.container in the src directory that is always used. Inside the template.container file, there is a
  string, MAGIC that can be replaced by content located inside .content files that are also inside the src directory.

Files are placed in the 'output' directory, currently specified by $GLOBALS['output directory'].

To edit the menu, edit the generate_menu function.
To add a new page,



Ideally, there should be a generation_sequence.txt file.

template 1.txt

Design:
1)Read through a script for what generate_website.php should do. Script file is called script.txt and is located in the same directory as generate_website.php.
2)
