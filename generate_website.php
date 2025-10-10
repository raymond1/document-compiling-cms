<?php
//Version 1.2.8
//Please update version for each update.

/*
Debugging function
*/
function d_log($s){
  $file = fopen('log.log', 'a+');
  fwrite($file, $s);
  fclose($file);
}

$GLOBALS['script file'] = 'script.txt';
if ($argc == 2) $GLOBALS['script file'] = $argv[1];

$GLOBALS['output directory'] = 'output';
$GLOBALS['source directory'] = 'src';
$GLOBALS['directory structure file'] = $GLOBALS['source directory'] . "/" . 'directories.txt';

function is_execute_directive($s){
  $tokens = explode(" ", $s);

  if (count($tokens) != 2) return false;
  if ($tokens[0]!='execute') return false;

  return true;
}

//Executes the commands inside $filename, which is the path to the command file.
function processCommandFile($filename){
  $lines = file($filename, FILE_IGNORE_NEW_LINES);

  foreach ($lines as $line) {
    if (trim($line) != ''){
      exec($line);
    }
  }
  echo "Command file processed.\n";
}

//Syntax of copy.txt file is:
//source destination

//source can be a file or a directory
//destination refers to a directory where source will be copied to
function copy_files($copy_instructions_filename){
  if (file_exists($copy_instructions_filename)){
    echo ("Copying files.\n");
    $lines = explode(PHP_EOL, file_get_contents($copy_instructions_filename));

    $lineNumber = 1;
    foreach ($lines as $line){
      if (trim($line)=='') continue;

      $parts = explode(" ", $line);
      if (count($parts) != 2){
        echo "Incorrect number of parameters for copy command on line $lineNumber. 2 are needed. The line was:\n";
        echo $line;
        continue;
      }
      $source = $parts[0];
      $destination = $parts[1];
  
      //Otherwise, if it is a directory, copy it if it is new
      //Copy the contents in it if the directory is not new
      //Copying is done recursively.
      if (!file_exists($destination)){
        mkdir($destination, 0777, true);
      }
      exec("cp -R $source $destination");
      $lineNumber++;
    }  
  }else{
    echo "Copyscript not found: $copy_instructions_filename\n";
  }
}

//Opens up the directories.txt file and generates the directories listed in that file.
//The format of the directories.txt file is as follows:
//Every line contains the name of a directory or a / separated list of directories.
//For example:
//
//cat
//bat
//cat/dog
//cat/dog/fish
//
//For each line in the file that contains a list of directories, those directories will be created if they did not previously exist.
//If the directories already exist, nothing is done.
function process_directories(){
  $directories_file = "directories.txt";
  if (file_exists($directories_file)){
    $contents = file_get_contents($directories_file);
    $lines = explode("\n", $contents);
    foreach ($lines as $line){
      if (!empty($line)){
        @mkdir($line,0777,true);
      }
    }
  }else{
    echo("Missing directories.txt file.\n");
  }
}

//Takes in a string $s and finds the first occurrence of the string "<%" that is followed by 0 or more letters and then the string "%>".
//If both the opening and closing tags exist, then 
//This function will return an array with two values[START,LENGTH].
//START will be equal to the index of the first character after "<%" inside $s.
//LENGTH will be equal to the number of characters in between the first character after "<%" and the first character before "%>".
//If opening does not exist, then -1 is returned.
//If opening tag exists but closing tag doesn't exist, then -1 is returned.
function getFirstDirectiveLocation($s){
  $returnObject = new stdClass;
  $returnObject->start = -1;
  $returnObject->length = -1;

  $openingTagStart = strpos($s,"<%");
  if ($openingTagStart === false){
    return $returnObject;
  }
  $closingTagStart = strpos($s, "%>", $openingTagStart + 2);
  if ($closingTagStart === false){
    return $returnObject;
  }

  //Start of area between "<%" and "%>"
  $returnObject->start = $openingTagStart + 2;
  $returnObject->length = $closingTagStart - ($openingTagStart + 2);
  return $returnObject;
}

//copyscript <filename>
function is_copyscript_directive($s){
  $tokens = explode(" ", $s);

  if (count($tokens) != 2) return false;
  if ($tokens[0]!='copyscript') return false;
  
  return true;
}

function is_compile_directive($s){
  $tokens = explode(" ", $s);
  if ($tokens[0] == 'compile'){
    return true;
  }

  return false;
}

function is_template_directive($s){
  $tokens = explode(" ", $s);
  if ($tokens[0] == 'template'){
    return true;
  }

  return false;
}

//Takes in a string and returns an array with two elements
//Element 1 is the location of the <% tag
//Element 2 is the length from the <% tag to the end of the %> tag
//If tag is not found, returns a -1
function findTemplateTag($templateFileContents){
  $openingTagLocation = strpos($templateFileContents,'<%');
  $closingTagLocation = strpos($templateFileContents,'%>', $openingTagLocation);
  $tagLength = $closingTagLocation - $openingTagLocation + 2;
  $returnArray = [];
  $returnArray[0] = $openingTagLocation;
  $returnArray[1] = $tagLength;
  return $returnArray;
}

//script.txt syntax: template <template filename> <output filename>
//First non-empty line gives the type of the command
//Template files currently support two commands: transcribe and print
//Takes in a directive string and returns the processed result
//For example:
//transcribe
// src/nodes.js 
// src/parser_fragment.js 
// src/tree.js 
// src/strings.js 
// src/tree_viewer.js 
// src/module_ending.js 
//
// Will produce the string created by concatenating all the listed files.
// The syntax for the transcribe directive is as follows:
// "transcribe" followed by a carriage return is the first line.
// The first line is followed by n or more non-empty lines starting with a single space. Each non-empty line that follows the first
// lists a file whose contents will be read and added to the output string.
// The end result is the output string after it contains all the contents from the lines indicated by the copy directive.
//
//print
// string1
// string2
//Adds the strings from each line to the output string. One purpose of this command is so that the string "<%" can be printed.
function processTemplateDirective($s){
  // echo "Inside processTemplateDirective s is:|$s|";
  $parts = explode("\n", trim($s));
  $directive = $parts[0];
  $outputString = '';
  switch ($directive){
    case 'transcribe':
      for ($i = 1; $i < count($parts); $i++){
        $filename = trim($parts[$i]);
        if (file_exists($filename)){
          $outputString .= file_get_contents($filename);
        }
        else{
          echo "Could not find $filename while processing the ". $directive . " directive.\n";
          exit;
        }
      }

      break;
    case 'print':
      for ($i = 1; $i < count($parts); $i++){
        $outputString .= substr($parts[$i], 1);
      }
      break;
    default:
      echo "Error. Unknown directive in template file:" . $parts[0] . ".";
      exit;
      break;
  }
  return $outputString;
}

//Takes in a template file, processes it, and produces an output file.
function processTemplate($template, $outputFilename){
  //This parsing is really fragile.
  if (!file_exists($template)){
    echo ("Template not found:$template\n");
  }

  //Everything not enclosed in <% %> will be printed
  //Within the <% %> tags, directives can be given.

  //All paths are relative to the directory where the script was run
  $templateContents = @file_get_contents($template);

  //Does <% exist?
  //If Yes, does %> exist after it?
  //If both are yes, then there is a directive. Process it.
  $caret = 0; //caret refers to the position in the template file that has been processed
  $outputString = '';
  while($caret < strlen($templateContents)){
    $templateStringProcessed = '';

    $directiveInformation = getFirstDirectiveLocation(substr($templateContents,$caret));
    if ($directiveInformation->start == -1){
      //If no more directives remain, then simply print the contents from $caret to the end of the template
      $templateStringProcessed = substr($templateContents, $caret);
      $outputString = $outputString . $templateStringProcessed;
    }else{
      //Read until directive start
      $templateStringProcessed .= substr($templateContents,$caret, $directiveInformation->start);
      $outputString .= substr($templateStringProcessed,0, -2);

      $stuffBetweenDirectives = substr($templateContents,$caret + $directiveInformation->start, $directiveInformation->length);
      $templateStringProcessed .= $stuffBetweenDirectives;
      $outputString .= processTemplateDirective($stuffBetweenDirectives);

      //Skip the end tag
      $templateStringProcessed .= substr($templateContents, $caret + $directiveInformation->start + $directiveInformation->length, 2);
    }

    $caret += strlen($templateStringProcessed);
  }

  $directory = dirname($outputFilename);

  if (!file_exists($directory)) mkdir($directory);
  $outputFile = fopen($outputFilename, "w+");
  fwrite($outputFile, $outputString);
  fclose($outputFile);
}


//Opens up the template file. Assumes that there is only one <%%> tag. Replaces that tag's contents with the contents from contentFile. Writes to the file
//outputFile.
function process_compile_directive($templateFilename,$contentFilenames,$outputFilename){
  $templateContents = file_get_contents($templateFilename);

  for ($i = 0; $i < count($contentFilenames); $i++){
    $contentFilename = $contentFilenames[$i];
    $templateTagInfo = findTemplateTag($templateContents);
    $openingTagLocation = $templateTagInfo[0];
    $afterClosingTagLocation = $openingTagLocation + $templateTagInfo[1];

    $contentFileContents = file_get_contents($contentFilename);
    $templateContents = substr($templateContents,0, $templateTagInfo[0]) . $contentFileContents . substr($templateContents,$afterClosingTagLocation);
  }

  $outputFileHandle = fopen($outputFilename, 'w+');
  fwrite($outputFileHandle, $templateContents);
  fclose($outputFileHandle);
}

//parses the script.txt file and executes the commands within it
function process_script_file(){
  $script_file_contents = @file_get_contents($GLOBALS['script file']);
  if ($script_file_contents === false){
    echo("Missing script.txt file.\n");
    exit;
  }

  //From here on, assume file is present
  $lines = explode("\n", $script_file_contents);
  for($i = 0; $i < count($lines); $i++){
    $line = $lines[$i];

    if (trim($line) == ''||substr($line,0,1) == '#'){
      continue; //ignore whitespace-only lines or lines that start with a comment character
    }
    else if ($line == 'generate directories'){
      echo ("Generating directories.\n");
      process_directories();
    }else if (is_copyscript_directive($line)){
      $tokens = explode(" ", $line);
      echo ("Processing copyscript directive. Filename is {$tokens[1]}. \n");
      copy_files($tokens[1]);
    }else if (is_template_directive($line)){
      $tokens = explode(" ", $line);
      
      processTemplate($tokens[1], $tokens[2]);
    }
    else if (is_compile_directive($line)){
      try{
        $tokens = explode(" ", $line);
        $number_of_tokens = count($tokens);
        if ($number_of_tokens < 3){
          throw new Exception("$number_of_tokens tokens were provided to the compile directive. At least 3 tokens are expected. compile <template> [content1, content2, ...] <output filename>");
        }

        $compileKeyword = $tokens[0];

        $templateFile = $tokens[1];
        $contentFiles = array_slice($tokens,2, $number_of_tokens - 3);
        $outputFile = $tokens[$number_of_tokens - 1];

        process_compile_directive($templateFile,$contentFiles,$outputFile);
      }
      catch(Exception $e){
        echo ("Error processing compiled documents file:" . $e->getMessage() . "\n");
      }
    }else if (is_execute_directive($line)){
      echo ("Executing execute directive\n");
      try {
        $tokens = explode(" ", $line);
        $command_file = $tokens[1];

        echo("Running commands in $command_file.\n");
        if (file_exists($command_file)){
          processCommandFile($command_file);
        }else{
          echo "Command file not found: $command_file. Further processing aborted. \n";
          exit;
        }
      }catch(Exception $e){
        echo "Error processing command file.\n";
      }
    }
    else{
      echo ("Command not recognized. Error in script.txt around line $i at the part that says '$line'\n");
    }
  }
}


process_script_file();