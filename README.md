# Usage
This is a console run script meant for generating websites. It is activated using the following command:

bash generate_website.sh <command file>

This command is referred to as the "action script" in the documentation. The action script is capabile of copying files, generating directories and piecing together files from a combination of templates and template insertion snippets.

See below for information on how to configure the Document Compiling CMS (abbreviated DCC), the format of the action script file and files needed to control its behaviour.

# Installation
<not yet written>
# Operation

When the action script is run, using the syntax

basy generate_website.sh <command file>

the command file is opened up and each line is parsed to look for commands. These commands are executed to perform a combination of generating directories, copying files and compiling templates and template snippets into output files.

Details for the commands are given in the sections below.

## Creating a command file/Command file syntax
The command file contains one command per line. The possible commands each have a different syntax, which is described in the sections below.

## "generate directories" command

Description:

This command is used to generate directories to ensure a directory structure is in place.

Syntax:

This command takes no parameters and is triggered by the following string:

```generate directories```

Details:

When the php generate_website.php script is run, the generate directories command will open and read the file directories.txt in the same directory where the action script is located, called the script home directory. You will need to create this file if it doesn't exist.

Inside of the directories.txt file will be a series of directories, one per line.

An example directories.txt is described below:

output/example
output/example/images
example2

Here, each line represents a directory name. If a directory does not already exist, it will be created. If a directory already exists, it will not be recreated.

The first line will generate the directory "output", containing a subdirectory named "example". The second line will add the subdirectory images to the previous example directory. The third line will create the directory example2 in the script home directory.

## "copy files" directive

Usage:

In the script file, add a line that contains only the following text:

copy files

When the php generate_directories.php command is run, the copy files directive will copy files or directories from a source location into a destination folder. The files that will be selected for the copy operation will come from the contents listed in the copy.txt file. You will need to create this file if it doesn't exist. By default, the working directory is searched for the copy.txt file.

For each line in the copy.txt file, a copy command will be executed. Both the source and destination specified are relative to the working directory.

For example, to copy the file snail2.jpg from the src directory into a directory named output, the following line would need to be added to copy.txt:

snail2.jpg output

The result after processing the copy files directive is that the file snail2.jpg located in the src directory would be copied into the output directory.

Similary, a directory can be copied recursively into the output directory by specifying a directory. You can use nested directories such as directory1/directory2/directory3 for this purpose.

## "compile" directive
Overview:
This feature requires multiple files in order to be specified. The way to understand this feature is to understand the underlying use case it was meant to solve. The idea is that there are many websites that contain repeating sections containing parts within them that are different.

Consider, for example, the following two pages of a fictitious website:
Page 1:
<html>
<title>A website</title>
<body>
<h1>Page 1<h1>
<p>Some content goes here.
</body>
</html>

Page 2:

<html>
<title>A website</title>
<body>
<h1>Page 2<h1>
<p>Some different content for page 2 goes here.
</body>
</html>

Note that only the content between the body start and end tags is different. In order to generate this website using the document compiling cms, the following steps need to be taken.

1)In the script.txt file, add a line like the following:
```
compile <filename.cdf>
```
The syntax is the word "compile" followed by the name of a .cdf file.

2)Generate the cdf file. Inside that file, put in the name of the template file, the name of the content files, and the resultant file after substitution.

For the example above, the file can be:
```
template.template page1.content output_directory/page1.html
template.template page2.content output_directory/page2.html
```

Explanation:

Each line of a .cdf file generates one output file. The syntax for a line of the .cdf file is as follows:

[.template filename] [.content filename1] [.content filename2] [.content filename3] ... [output filename]

In other words, each line in a .cdf file consists of a filename followed by any number of .content filenames and ends with an output filename. When a line of the .cdf file is processed, the following things happen:

1)The .template filename specified on each line is opened and read
2)For each MAGIC string encountered in the .template file, the contents of a .content file is substituted for it.
3)An output file corresponding to the last filename listed on the line of a .cdf file is generated containing the contents of the .template file after all the MAGIC constants have been replaced.

3)Generate all the .template and .content files needed in step 2.

The template.template file will have the following contents for the example:

<html>
<title>A website</title>
<body>MAGIC</body>
</html>

page1.content will have the following contents:
```
<h1>Page 1<h1>
<p>Some content goes here.
```
page2.content will have the following contents:
```
<h1>Page 2<h1>
<p>Some different content for page 2 goes here.
```

4)Run php generate_website.php
The following files will be generated:
output_directory/page1.html
output_directory/page2.html

These two files were specified in the .cdf file in step 2 as the last argument on each line of the file. The contents will be determined by substituting the MAGIC string found in the template.template file with the contents specified by the second argument on each line of the .cdf file.


## Development process and Makefile
# Makefile for copying generate_website.php
Use the Makefile to copy your development generate_website.php into the target directory. 

Usage: make copy

# To bump the version number

In the document-compiling-cms folder, commit and tag your changes with the newest version.
git add .
git commit -m "..."
git push
git tag  (to get tag name)
git tag 1.0.7
git push origin 1.0.7

In your development/test directory, where the document-compiling-cms was installed with composer, do a composer update.

cp ~/Desktop/cantonese/cantonesecentral.com.v2/vendor/raymond1/document-compiling-cms/generate_website.php ~/Desktop/cantonese/cantonesecentral.com.v2/generate_website.php


