# Usage Overview
This is a console run php script that takes in 0 or 1 parameter and is activated using the following command:

php generate_website.php <optional script file filename>

If no optional script file is specified, it is assumed by default to be "script.txt" from the current directory. In the documentation, "script file" refers to either the script.txt file, or the file specified in the command line to execute the generate_website.php script.

See below for information on how to configure the Document Compiling CMS (abbreviated DCC), the format of the script file and files needed to control the behaviour of generate_website.php.

# Capabilities
This is a program that generates a website. It is capable of doing the following things:

1. Generating the directory structure of a website
2. Copying files from one location to another
3. Generating a file by specifying a template, a variable area of text and what that area of text should be filled with.
4. Generating a file by gluing together different fragments of text.
5. Executing a series of commands from the command line.

# Installation

This software is supposed to be installed using composer. To install the software, you can try to do the following:

1)In the composer.json file, add the following repository:

        "repositories":[{
                        "type": "vcs",
                        "url": "git@github.com:raymond1/Document-Compiling-CMS.git"
        }]
    
2)From the command line, type the following command:

composer require raymond1/document-compiling-cms

3)Copy the file generate_website.php into the base of the folder where you are going to put the files used to create your website.


# Creating a script file, script file syntax

The file script.txt needs to be created and placed in the working directory. If it is not there, you need to add it. It contains the set of instructions that will be performed by the cms.

Example file:
directoryscript
copyscript copy.txt

Explanation:
The format of the script.txt file consists of lines of instructions, with one instruction per line. Each line contains what are called "directives", which are key words or phrases that have special meaning for the CMS.

"directoryscript" and "copyscript" in the above example file are known as directives. The complete list of available directives and their usage are indicated below.

# Script file directives

## Comments
Use the # symbol at the beginning of a line in the script file in order to ignore the rest of the line.

## "directoryscript" directive

Usage:

Inside the main script file, add the following code:

```
directoryscript <directoryscript filename>
```

The directoryscript directive will do the following:
1. open and read the file specified.
2. generate the directories listed specified as a parameter in the directoryscript directive line.

An example directories.txt file might contain the following:

```
output/example
output/example/images
example2
```

This will generate the folders output, output/example, output/example/images and example2.

## "copyscript" directive

Usage:

In the script file, add a line that contains a line containing two tokens. The first token is string "copyscript". The second token is a filepath pointing to a file that contains a list of what files to copy over.

For example:

```
copyscript copy.txt
```

will tell the DCC to copy all the files indicated in the file copy.txt file when the php generate_directories.php command is run.

The format for the copy.txt file is a two-column file where the two columns are separated by a space. The left column will be a source file. The right column will be the destination file or folder name.

Both the source and destination specified are relative to the working directory.

For example, to copy the file snail2.jpg from the src directory into a directory named output, the following line would need to be added to copy.txt:


```
snail2.jpg output
```
The result after processing the ```copyscript copy.txt``` command from above is that the file snail2.jpg located in the src directory would be copied into the output directory.

Both the source and the destination can be either a directory or a file. The behaviour is as follows:
```
If source is a directory, do the following:
  Check if the destination already exists.
  If the destination already exists, check if the destination is a directory or a file.
    If the destination is a file, delete the file. Generate the new destination directory and copy the items under the source directory into the newly created destination directory.
    If the destination is a directory, copy the items under the source directory into the destination directory.
  If the destination does not exist, interpret it as a directory, create it and copy the items under the source directory into the newly created destination directory.

If the source is a file, do the following:
  Check if the destination exists.
  If it exists, check if it is a directory or a file.
    If it is a directory, copy the source into the destination directory.
    If it is a file, overwrite the file with the contents from the source file.
  If the destination does not exist, copy the source file to the destination.
```
## "compile" directive

For most templating purposes, the "template" directory below is better, but the "compile" directive can be used in simple cases where there is only one changing section in the template.

There are many websites that contain multiple pages that are very similar to each other, where, except for the content in a certain number of limited places, the pages are the same. To generate these files, the 'compile' directive exists to allow people to specify the template, the content, and the output file inside the script.txt file.

To use the 'compile directive', first modify the script.txt file. For each web page that shares the same structure, add a line with the following syntax:

```
compile <template file> <content file> <output file>
```

The output file will be produced by the document compiling cms by replacing the contents between and including the opening and end <% %> tag in the template file with the contents of the content file. The template file is assumed to have one <% %> tag in it, that is, the string <% followed by the string %>. If these two string sequences are not detected, the compile directive will not work.

## "template" directive
The template directive instructs the document compiling cms to read in a template file and process its instructions. Syntax:

```
template <template filename> <output filename>
```

The template file's contents will be copied to the output filename, with any <% %> tags processed along the way. Within the <% %> tags, certain commands can be used, as explained in the template file mini-language section below.

### Template file mini-language

Currently, the template files support only two commands, 'transcribe' and 'print'.

#### 'transcribe' command
Example:

<%

transcribe
 file_1
 file_2

%>

Transcribe will print the contents of the files listed under the transcribe node to the output at the location where the directive tag was located. For example, if file A's contents are:

----------------
abcdefg<%

transcribe
 B.txt
 C.txt
%>hijklmnop
---------------

And the content of file B.txt is the string 'cat', and the content of C.txt is the string 'dog', then the string produced following the processing of the A.txt as a template would be the string

'abcdefgcatdoghijklmnop
'.

Note: there could potentially be more than one transcribe directive in a template file.
#### 'print' command

Example:

<%

print
 string
%>

print will print the strings listed under the print node to the output. The purpose of the print command is to insert strings such as '<%', which might cause problems, or spaces or newline characters in between two transcribe commands.

## "execute" directive
The commands directive is used to specify a file to run commands from the command line.

Usage:

execute <command file>

The format for the command file is that each line in the file will be the command that will be executed from the command line.


# Development process and Makefile
## Makefile for copying generate_website.php
Use the Makefile to copy your development generate_website.php into the target directory. 

Usage: make copy

## To bump the version number

In the document-compiling-cms folder, commit and tag your changes with the newest version.
git add .
git commit -m "..."
git push
git tag  (to get tag name)
git tag 1.1.7
git push origin 1.1.7

In your development/test directory, where the document-compiling-cms was installed with composer, do a composer update.